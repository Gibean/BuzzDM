<?php
// Autoload handled by composer
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Auth", description: "Authentication Operations")]
class AuthController {
    private $service;

    public function __construct() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $this->service = new AuthService();
    }

    public function handleRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        $action = $_GET['action'] ?? $_POST['action'] ?? null;

        try {
            switch ($action) {
                case 'login':
                    $email = trim($_POST['email'] ?? '');
                    $password = $_POST['password'] ?? '';

                    // El limite se aplica por IP y por email. El alta de empresa
                    // (api_register_demo.php) no se limita de forma intencionada:
                    // dar de alta una empresa no es un ataque de fuerza bruta.
                    if ($this->excedeLimite(RateLimiter::LOGIN_POR_IP, RateLimiter::ipCliente())) {
                        return;
                    }
                    if ($email !== '' && $this->excedeLimite(RateLimiter::LOGIN_POR_EMAIL, $email)) {
                        return;
                    }

                    $result = $this->service->login($email, $password);

                    // Un login correcto limpia el contador: si el usuario se
                    // equivoco tres veces y luego entra bien, no debe quedar
                    // arrastrando intentos consumidos.
                    if (!empty($result['success']) && empty($result['requires_2fa'])) {
                        if ($email !== '') {
                            RateLimiter::limpiar(RateLimiter::LOGIN_POR_EMAIL, $email);
                        }
                        RateLimiter::limpiar(RateLimiter::LOGIN_POR_IP, RateLimiter::ipCliente());
                    }

                    $this->jsonResponse(true, $result);
                    break;

                case 'forgot_password':
                    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);

                    // Evita usar el formulario para bombardear el buzon de terceros.
                    if ($this->excedeLimite(RateLimiter::RECUPERACION, (string) $email)) {
                        return;
                    }
                    if ($this->excedeLimite(RateLimiter::LOGIN_POR_IP, RateLimiter::ipCliente())) {
                        return;
                    }

                    $result = $this->service->forgotPassword($email);
                    $this->jsonResponse(true, $result);
                    break;

                case 'reset_password':
                    $token = filter_input(INPUT_POST, 'token', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
                    $password = $_POST['password'] ?? '';
                    $confirmPassword = $_POST['confirmPassword'] ?? '';

                    if ($this->excedeLimite(RateLimiter::RESET_PASSWORD, RateLimiter::ipCliente())) {
                        return;
                    }

                    $result = $this->service->resetPassword($token, $password, $confirmPassword);
                    $this->jsonResponse(true, $result);
                    break;

                case 'validate_token':
                    $token = filter_input(INPUT_POST, 'token', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
                    $result = $this->service->validateResetToken($token);
                    $this->jsonResponse(true, $result);
                    break;

                case 'logout':
                    $result = $this->service->logout();
                    $this->jsonResponse(true, $result);
                    break;

                case 'verify_2fa':
                    $code = trim($_POST['code'] ?? '');
                    if (empty($code)) {
                        throw new Exception('El código de verificación es obligatorio.');
                    }

                    // 6 digitos son 1.000.000 de combinaciones: sin este limite se
                    // podrian agotar dentro de la ventana de 5 minutos.
                    if ($this->excedeLimite(RateLimiter::DOS_FACTOR, $this->claveDosFactor())) {
                        return;
                    }

                    $result = $this->service->verify2FA($code);

                    if (!empty($result['success'])) {
                        RateLimiter::limpiar(RateLimiter::DOS_FACTOR, $this->claveDosFactor());
                    }

                    $this->jsonResponse(true, $result);
                    break;

                default:
                    $this->jsonResponse(false, null, 'Acción no válida');
            }
        } catch (Exception $e) {
            $this->jsonResponse(false, null, $e->getMessage());
        }
    }

    /**
     * Consume un intento de la accion y corta la peticion con 429 si queda bloqueada.
     *
     * La respuesta usa el mismo formato que jsonResponse() porque el frontend
     * ya lee `message` en los cuatro formularios publicos, de modo que un 429 se
     * muestra en la alerta existente sin cambios en el JavaScript.
     *
     * @param string $accion
     * @param string $identificador
     * @return bool true si se corto la peticion
     */
    private function excedeLimite($accion, $identificador) {
        if ($identificador === '') {
            return false;
        }

        $resultado = RateLimiter::consumir($accion, $identificador);
        if (!$resultado['bloqueado']) {
            return false;
        }

        http_response_code(429);
        header('Content-Type: application/json');
        header('Retry-After: ' . $resultado['segundos']);
        echo json_encode([
            'success' => false,
            'message' => RateLimiter::mensajeEspera($resultado['segundos']),
            'rate_limited' => true,
        ]);
        exit;
    }

    /**
     * Identificador de la sesion 2FA pendiente.
     *
     * Se usa el usuario pendiente si existe; si no, la IP. Evita que el limite
     * de 2FA se comparta entre todas las personas de una misma red.
     *
     * @return string
     */
    private function claveDosFactor() {
        if (!empty($_SESSION['pending_2fa_user_id'])) {
            return 'user_' . (int) $_SESSION['pending_2fa_user_id'];
        }

        return 'ip_' . RateLimiter::ipCliente();
    }

    #[OA\Post(
        path: "/php/api_auth.php",
        tags: ["Auth"],
        summary: "Authentication Actions",
        description: "Login, Logout, Forgot Password, Reset Password via 'action' param",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "application/x-www-form-urlencoded",
                schema: new OA\Schema(
                    required: ["action"],
                    properties: [
                        new OA\Property(property: "action", type: "string", enum: ["login", "logout", "forgot_password", "reset_password"]),
                        new OA\Property(property: "email", type: "string", description: "For login/forgot"),
                        new OA\Property(property: "password", type: "string", description: "For login/reset"),
                        new OA\Property(property: "confirmPassword", type: "string", description: "For reset"),
                        new OA\Property(property: "token", type: "string", description: "For reset")
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Action result")
        ]
    )]
    private function authActions() {
        // Placeholder for OpenAPI to attach to
    }

    private function jsonResponse($success, $data = null, $message = '') {
        header('Content-Type: application/json');

        if (!$success) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => $message]);
        } else {
            echo json_encode($data);
        }
        exit;
    }
}
