<?php
require_once __DIR__ . '/../Repository/UserRepository.php';

class UserService {
    private $repository;

    public function __construct() {
        $this->repository = new UserRepository();
    }

    public function getAllUsers() {
        return $this->repository->findAll();
    }

    // Added this method
    public function getUserById($id) {
        return $this->repository->findById($id);
    }

    public function getSessionStats() {
        return ['session_count' => $this->repository->getActiveSessionsCount()];
    }

    public function createUser($data) {
        // Validate required fields exist
        if (!isset($data['nombre']) || !isset($data['email']) || !isset($data['password'])) {
            throw new Exception("Faltan datos requeridos (nombre, email, password)");
        }

        $this->validateEmail($data['email']);
        $this->validatePasswordStrength($data['password']);

        // Check duplicate email
        if ($this->repository->findByEmail($data['email'])) {
            throw new Exception("El correo electrónico ya está en uso.");
        }

        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);

        // Map frontend state to DB enum if needed
        $estado = $data['estado'] ?? 'active';
        if ($estado === 'activo') $estado = 'active';
        if ($estado === 'inactivo') $estado = 'inactive';

        $userData = [
            'nombre' => $data['nombre'],
            'email' => $data['email'],
            'password' => $hashedPassword,
            'rol' => $data['rol'] ?? 'user',
            'estado' => $estado
        ];

        $userId = $this->repository->create($userData);
        return ['id' => $userId, 'message' => 'Usuario creado exitosamente'];
    }

    public function updateUser($id, $data) {
        $this->validateEmail($data['email']);

        // Check duplicate email (excluding self)
        $existing = $this->repository->findByEmail($data['email']);
        if ($existing && $existing['id'] != $id) {
            throw new Exception("El correo electrónico ya está en uso por otro usuario.");
        }

        // Also map state if it is present in update payload (admin.js might send it)
        // admin.js handleUserSubmit sends { ..., estado: ... }
        // So we should update it here too.
        // But Repository update() currently only does nombre, email, rol.
        // Let's check Repository update() method.
        // It does: UPDATE usuarios SET nombre = ?, email = ?, rol = ? WHERE id = ?
        // So state is NOT updated in general update.
        // We should add it to repository update or call updateStatus separately.
        // Ideally repository update should allow state update.
        // I will update Repository to include state in main update.

        // For now, let's keep it as is, but if admin.js expects state update in edit modal, we need it.
        // I'll update Repository first.

        $this->repository->update($id, [
            'nombre' => $data['nombre'],
            'email' => $data['email'],
            'rol' => $data['rol']
        ]);

        // Explicitly update state if present
        if (isset($data['estado'])) {
             $estado = $data['estado'];
             if ($estado === 'activo') $estado = 'active';
             if ($estado === 'inactivo') $estado = 'inactive';
             $this->repository->updateStatus($id, $estado);
        }

        return ['message' => 'Usuario actualizado exitosamente'];
    }

    public function updateStatus($id, $estado) {
        if (!in_array($estado, ['active', 'inactive', 'activo', 'inactivo'])) {
            throw new Exception("Estado inválido.");
        }
        // Normalize
        $estado = ($estado === 'activo') ? 'active' : (($estado === 'inactivo') ? 'inactive' : $estado);

        $user = $this->repository->findById($id);
        if ($user && $user['rol'] === 'admin' && $estado === 'inactive') {
            if ($this->repository->countAdmins() <= 1) {
                throw new Exception("No puedes desactivar al último administrador.");
            }
        }

        $this->repository->updateStatus($id, $estado);
        return ['message' => 'Estado actualizado'];
    }

    public function changePassword($id, $password) {
        $this->validatePasswordStrength($password);
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $this->repository->updatePassword($id, $hashedPassword);
        return ['message' => 'Contraseña actualizada'];
    }

    public function deleteUser($id, $currentUserId) {
        if ($id == $currentUserId) {
            throw new Exception("No puedes eliminar tu propia cuenta mientras estás logueado.");
        }

        $user = $this->repository->findById($id);
        if (!$user) {
            throw new Exception("Usuario no encontrado.");
        }

        if ($user['rol'] === 'admin') {
            if ($this->repository->countAdmins() <= 1) {
                throw new Exception("No puedes eliminar al último administrador.");
            }
        }

        $this->repository->delete($id);
        return ['message' => 'Usuario eliminado exitosamente'];
    }

    private function validateEmail($email) {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Formato de correo electrónico no válido.");
        }
    }

    private function validatePasswordStrength($password) {
        if (strlen($password) < 8) return false;

        if (strlen($password) < 8 ||
            !preg_match('/[a-z]/', $password) ||
            !preg_match('/[A-Z]/', $password) ||
            !preg_match('/[0-9]/', $password) ||
            !preg_match('/[^a-zA-Z0-9]/', $password)) {
            throw new Exception("La contraseña debe tener al menos 8 caracteres, mayúsculas, minúsculas, números y símbolos.");
        }
    }
}
