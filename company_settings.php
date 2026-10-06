<?php
require_once 'php/sesion.php';
require_once 'php/db.php';

$companyData = getCompanyData();

// Check for session messages
$message = null;
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']); // Clear the message after displaying
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BUZZ Data Manager - Configuración de Empresa</title>
    <link rel="shortcut icon" href="assets/img/logov1_icon.png" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/styles.css">
    <style>
        .upload-area {
            min-height: 150px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            cursor: pointer;
        }
        .current-logo-preview {
            max-width: 150px;
            max-height: 150px;
            margin-top: 15px;
            border: 1px solid #ddd;
            padding: 5px;
            background-color: #f9f9f9;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <?php require_once 'php/sidebar.php'; ?>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Top Navbar -->
        <?php
        $nav_title = 'ADMINISTRACIÓN DEL SISTEMA';
        $nav_subtitle = 'Información de la empresa';
        require_once 'php/top_navbar.php';
        ?>

        <?php if ($message): ?>
        <div class="alert alert-<?php echo htmlspecialchars($message['type']); ?> alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($message['text']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>

        <!-- Company Settings Form -->
        <div class="buzz-card mb-4">
            <h5 class="font-primary text-primary mb-3">DATOS DE LA EMPRESA</h5>
            <form id="companySettingsForm" action="php/api_settings.php" method="POST" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="rif" class="form-label text-muted">RIF / Cédula</label>
                            <input type="text" class="form-control form-control-buzz" id="rif" name="rif" value="<?php echo htmlspecialchars($companyData['rif'] ?? ''); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="nombre" class="form-label text-muted">Nombre de la Empresa</label>
                            <input type="text" class="form-control form-control-buzz" id="nombre" name="nombre" value="<?php echo htmlspecialchars($companyData['nombre'] ?? ''); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="direccion" class="form-label text-muted">Dirección</label>
                            <textarea class="form-control form-control-buzz" id="direccion" name="direccion" rows="3"><?php echo htmlspecialchars($companyData['direccion'] ?? ''); ?></textarea>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="telefono" class="form-label text-muted">Teléfono</label>
                            <input type="text" class="form-control form-control-buzz" id="telefono" name="telefono" value="<?php echo htmlspecialchars($companyData['telefono'] ?? ''); ?>">
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label text-muted">Email</label>
                            <input type="email" class="form-control form-control-buzz" id="email" name="email" value="<?php echo htmlspecialchars($companyData['email'] ?? ''); ?>">
                        </div>
                        <div class="mb-3">
                            <label for="logo" class="form-label text-muted">Logo (PNG o SVG)</label>
                            <div class="upload-area border border-dashed border-secondary rounded p-4 text-center" id="uploadArea">
                                <i class="fas fa-cloud-upload-alt text-primary" style="font-size: 3rem;"></i>
                                <h6 class="mt-3 font-mono">Arrastra y suelta tu logo aquí</h6>
                                <p class="text-muted">o haz clic para seleccionar</p>
                                <input type="file" id="logoInput" name="logo" accept=".png,.svg" style="display: none;">
                                <button type="button" class="btn btn-outline-buzz" onclick="document.getElementById('logoInput').click()">
                                    <i class="fas fa-folder-open"></i> Seleccionar Logo
                                </button>
                                <div class="mt-3">
                                    <small class="text-muted">Formatos soportados: PNG, SVG</small>
                                </div>
                            </div>
                            <div id="logoInfo" class="mt-3" style="display: none;">
                                <div class="alert alert-info">
                                    <i class="fas fa-file-image"></i>
                                    <span id="logoName"></span>
                                    <span class="float-end">
                                        <button type="button" class="btn btn-sm btn-outline-danger ms-2" onclick="clearLogo()">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </span>
                                </div>
                            </div>
                            <?php if (!empty($companyData['logo_svg'])): ?>
                                <div class="mt-3 text-center">
                                    <p class="text-muted mb-1">Logo Actual:</p>
                                    <img src="uploads/<?php echo htmlspecialchars($companyData['logo_svg']); ?>" alt="Current Company Logo" class="img-fluid current-logo-preview">
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="text-end">
                    <button type="submit" class="btn btn-buzz"><i class="fas fa-save"></i> Guardar Configuración</button>
                </div>
            </form>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/common.js"></script>
    <script nonce="<?= cspNonce() ?>">
        // Logo upload handling
        const logoInput = document.getElementById('logoInput');
        const uploadArea = document.getElementById('uploadArea');
        const logoInfo = document.getElementById('logoInfo');
        const logoName = document.getElementById('logoName');

        logoInput.addEventListener('change', function() {
            if (this.files.length > 0) {
                logoName.textContent = this.files[0].name;
                logoInfo.style.display = 'block';
                uploadArea.style.display = 'none';
            } else {
                clearLogo();
            }
        });

        uploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadArea.classList.add('border-primary');
        });

        uploadArea.addEventListener('dragleave', () => {
            uploadArea.classList.remove('border-primary');
        });

        uploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadArea.classList.remove('border-primary');
            logoInput.files = e.dataTransfer.files;
            logoInput.dispatchEvent(new Event('change'));
        });

        function clearLogo() {
            logoInput.value = '';
            logoInfo.style.display = 'none';
            uploadArea.style.display = 'flex';
            logoName.textContent = '';
        }

        // Clear logo input on successful submission
        <?php if ($message && $message['type'] === 'success'): ?>
            document.addEventListener('DOMContentLoaded', function() {
                clearLogo();
            });
        <?php endif; ?>
    </script>
    <!-- Notification Container -->
    <div id="notificationContainer"></div>
</body>
</html>
