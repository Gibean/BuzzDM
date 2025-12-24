<?php require_once 'php/sesion.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BUZZ Data Manager - Importación</title>
    <link rel="shortcut icon" href="assets/img/logov1_icon.png" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
     <!-- Sidebar (same structure as other pages)  -->
    <?php require_once 'php/sidebar.php'; ?>

    <!--  Main Content  -->
    <main class="main-content">
         <!-- Top Navbar  -->
        <nav class="top-navbar">
            <div class="d-flex align-items-center">
                <button class="btn btn-outline-buzz me-3 d-md-none" id="sidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>
                <h1 class="font-primary mb-0">IMPORTACIÓN DE DATOS</h1>
                <span class="text-muted ms-2 font-mono">/ SINCRONIZACIÓN</span>
            </div>
        </nav>

        <!--  Upload Section  -->
        <div class="buzz-card mb-4">
            <h5 class="font-primary text-primary mb-3">SUBIR ARCHIVO</h5>
            <form id="importForm" action="php/process_import.php" method="POST" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-md-8">
                        <div class="upload-area border border-dashed border-secondary rounded p-4 text-center" id="uploadArea">
                            <i class="fas fa-cloud-upload-alt text-primary" style="font-size: 3rem;"></i>
                            <h6 class="mt-3 font-mono">Arrastra y suelta tu archivo aquí</h6>
                            <p class="text-muted">o haz clic para seleccionar</p>
                            <input type="file" id="fileInput" name="import_file" accept=".csv,.xlsx,.xls" style="display: none;">
                            <button type="button" class="btn btn-outline-buzz" id="selectFileBtn">
                                <i class="fas fa-folder-open"></i> Seleccionar Archivo
                            </button>
                            <div class="mt-3">
                                <small class="text-muted">Formatos soportados: CSV, Excel (.xlsx, .xls)</small>
                            </div>
                        </div>
                        <div id="fileInfo" class="mt-3" style="display: none;">
                            <div class="alert alert-info">
                                <i class="fas fa-file"></i>
                                <span id="fileName"></span>
                                <span class="float-end">
                                    <span id="fileSize"></span>
                                    <button type="button" class="btn btn-sm btn-outline-danger ms-2" onclick="clearFile()">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <h6 class="font-mono text-muted mb-3">OPCIONES DE IMPORTACIÓN</h6>
                        <button type="submit" class="btn btn-buzz w-100" id="importBtn" disabled>
                            <i class="fas fa-upload"></i> Iniciar Importación
                        </button>
                    </div>
                </div>
            </form>
        </div>

         <!-- Import Progress  -->
        <div class="buzz-card mb-4" id="importProgress" style="display: none;">
            <h5 class="font-primary text-primary mb-3">PROGRESO DE IMPORTACIÓN</h5>
            <div class="progress mb-3" style="height: 20px;">
                <div class="progress-bar bg-primary" id="progressBar" style="width: 0%"></div>
            </div>
            <div class="row">
                <div class="col-md-3">
                    <div class="text-center">
                        <div class="font-mono text-primary" id="processedCount">0</div>
                        <small class="text-muted">Procesados</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center">
                        <div class="font-mono text-success" id="successCount">0</div>
                        <small class="text-muted">Exitosos</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center">
                        <div class="font-mono text-danger" id="errorCount">0</div>
                        <small class="text-muted">Errores</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center">
                        <div class="font-mono" id="totalCount">0</div>
                        <small class="text-muted">Total</small>
                    </div>
                </div>
            </div>
            <div class="mt-3">
                <div class="font-mono text-muted" id="currentStatus">Preparando importación...</div>
            </div>
        </div>

       <!--   Import History  -->
        <div class="buzz-card">
            <h5 class="font-primary text-primary mb-3">HISTORIAL DE IMPORTACIONES</h5>
            <div class="table-responsive">
                <table class="table table-buzz">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Tipo</th>
                            <th>Archivo</th>
                            <th>Estado</th>
                            <th>Usuario</th>
                        </tr>
                    </thead>
                    <tbody id="importHistoryTable">
                         <!-- History will be populated by JavaScript  -->
                    </tbody>
                </table>
            </div>
        </div>

        <!-- System Footer -->
        <?php require_once 'php/system_footer.php'; ?>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/common.js"></script>
    <script src="assets/js/importacion.js"></script>

    <!-- Notification Container -->
    <div id="notificationContainer"></div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const importForm = document.getElementById('importForm');
            const fileInput = document.getElementById('fileInput');
            const importBtn = document.getElementById('importBtn');
            const uploadArea = document.getElementById('uploadArea');
            const fileInfo = document.getElementById('fileInfo');
            const fileNameSpan = document.getElementById('fileName');
            const fileSizeSpan = document.getElementById('fileSize');
            const importProgress = document.getElementById('importProgress');
            const progressBar = document.getElementById('progressBar');
            const processedCount = document.getElementById('processedCount');
            const successCount = document.getElementById('successCount');
            const errorCount = document.getElementById('errorCount');
            const totalCount = document.getElementById('totalCount');
            const currentStatus = document.getElementById('currentStatus');
            const importHistoryTable = document.getElementById('importHistoryTable');
            const selectFileBtn = document.getElementById('selectFileBtn');

            // Event listener for the "Seleccionar Archivo" button
            selectFileBtn.addEventListener('click', function() {
                fileInput.click();
            });

            // Drag and Drop functionality
            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                uploadArea.addEventListener(eventName, preventDefaults, false);
            });

            function preventDefaults(e) {
                e.preventDefault();
                e.stopPropagation();
            }

            ['dragenter', 'dragover'].forEach(eventName => {
                uploadArea.addEventListener(eventName, highlight, false);
            });

            ['dragleave', 'drop'].forEach(eventName => {
                uploadArea.addEventListener(eventName, unhighlight, false);
            });

            function highlight() {
                uploadArea.classList.add('highlight');
            }

            function unhighlight() {
                uploadArea.classList.remove('highlight');
            }

            uploadArea.addEventListener('drop', handleDrop, false);

            function handleDrop(e) {
                const dt = e.dataTransfer;
                const files = dt.files;
                fileInput.files = files;
                displayFileInfo();
            }

            fileInput.addEventListener('change', displayFileInfo);

            function displayFileInfo() {
                if (fileInput.files.length > 0) {
                    const file = fileInput.files[0];
                    fileNameSpan.textContent = file.name;
                    fileSizeSpan.textContent = (file.size / 1024 / 1024).toFixed(2) + ' MB';
                    fileInfo.style.display = 'block';
                    importBtn.disabled = false;
                } else {
                    clearFile();
                }
            }

            window.clearFile = function() {
                fileInput.value = '';
                fileInfo.style.display = 'none';
                importBtn.disabled = true;
            };

            importForm.addEventListener('submit', function(e) {
                e.preventDefault();

                if (fileInput.files.length === 0) {
                    alert('Por favor, selecciona un archivo para importar.');
                    return;
                }

                importBtn.disabled = true;
                importBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Importando...';
                importProgress.style.display = 'block';
                progressBar.style.width = '0%';
                processedCount.textContent = '0';
                successCount.textContent = '0';
                errorCount.textContent = '0';
                totalCount.textContent = '0';
                currentStatus.textContent = 'Iniciando importación...';
                importHistoryTable.innerHTML = ''; // Clear previous results

                const formData = new FormData();
                formData.append('import_file', fileInput.files[0]);

                fetch(importForm.action, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    importBtn.disabled = false;
                    importBtn.innerHTML = '<i class="fas fa-upload"></i> Iniciar Importación';

                    if (data.success) {
                        currentStatus.textContent = data.message;
                        let totalProcessed = 0;
                        let totalInserted = 0;
                        let totalUpdated = 0;
                        let totalFailed = 0;

                        for (const sheetName in data.results) {
                            const result = data.results[sheetName];
                            totalProcessed += result.processed;
                            totalInserted += result.inserted;
                            totalUpdated += result.updated;
                            totalFailed += result.failed;

                            let statusClass = 'text-success';
                            let statusText = 'Completado';
                            if (result.status === 'failed') {
                                statusClass = 'text-danger';
                                statusText = 'Fallido';
                            } else if (result.status === 'skipped') {
                                statusClass = 'text-warning';
                                statusText = 'Omitido';
                            }

                            const row = `
                                <tr>
                                    <td>${new Date().toLocaleString()}</td>
                                    <td>${sheetName}</td>
                                    <td>${fileNameSpan.textContent}</td>
                                    <td class="${statusClass}">${statusText}</td>
                                    <td>${result.processed} (${result.inserted} insertados, ${result.updated} actualizados, ${result.failed} errores)</td>
                                    <td><?php echo $_SESSION['user_email'] ?? 'N/A'; ?></td>
                                    <td>
                                        ${result.errors.length > 0 ?
                                            `<button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#errorModal" data-errors='${JSON.stringify(result.errors)}'>
                                                Ver Errores
                                            </button>` : '-'
                                        }
                                    </td>
                                </tr>
                            `;
                            importHistoryTable.insertAdjacentHTML('beforeend', row);
                        }

                        processedCount.textContent = totalProcessed;
                        successCount.textContent = totalInserted + totalUpdated;
                        errorCount.textContent = totalFailed;
                        totalCount.textContent = totalProcessed;
                        progressBar.style.width = '100%';
                        progressBar.classList.remove('bg-primary');
                        progressBar.classList.add(totalFailed > 0 ? 'bg-danger' : 'bg-success');

                    } else {
                        currentStatus.textContent = 'Error: ' + data.message;
                        progressBar.style.width = '100%';
                        progressBar.classList.remove('bg-primary', 'bg-success');
                        progressBar.classList.add('bg-danger');
                        showNotification('Error de importación: ' + data.message, 'danger');
                    }
                })
                .catch(error => {
                    importBtn.disabled = false;
                    importBtn.innerHTML = '<i class="fas fa-upload"></i> Iniciar Importación';
                    currentStatus.textContent = 'Error de red o servidor: ' + error.message;
                    progressBar.style.width = '100%';
                    progressBar.classList.remove('bg-primary', 'bg-success');
                    progressBar.classList.add('bg-danger');
                    showNotification('Error de red o servidor: ' + error.message, 'danger');
                    console.error('Error:', error);
                });
            });

            // Error Modal
            const errorModal = new bootstrap.Modal(document.getElementById('errorModal'));
            document.getElementById('errorModal').addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                const errors = JSON.parse(button.getAttribute('data-errors'));
                const modalBody = errorModal._element.querySelector('.modal-body');
                modalBody.innerHTML = '';
                errors.forEach(error => {
                    modalBody.innerHTML += `<p>${error}</p>`;
                });
            });

            // Initial state
            clearFile();
            loadImportHistory(); // Load history on page load
        });

        async function loadImportHistory() {
            const importHistoryTable = document.getElementById('importHistoryTable');
            importHistoryTable.innerHTML = '<tr><td colspan="5" class="text-center">Cargando historial...</td></tr>';

            try {
                const response = await fetch('php/get_import_history.php');
                const data = await response.json();

                if (data.success && data.history.length > 0) {
                    importHistoryTable.innerHTML = '';
                    data.history.forEach(log => {
                        // Extract type and status from the data
                        const tipo = log.accion.replace('Importación: ', '');
                        const archivo = log.detalles.split(' - Estado: ')[0];
                        const estado = log.detalles.includes(' - Estado: ')
                            ? log.detalles.split(' - Estado: ')[1]
                            : 'N/A';

                        // Determine badge colors for tipo
                        let tipoBadgeClass = 'badge-primary';
                        if (tipo.toLowerCase().includes('general')) tipoBadgeClass = 'badge-primary';
                        else if (tipo.toLowerCase().includes('productos')) tipoBadgeClass = 'badge-warning';
                        else if (tipo.toLowerCase().includes('clientes')) tipoBadgeClass = 'badge-success';
                        else if (tipo.toLowerCase().includes('pedidos')) tipoBadgeClass = 'badge-info';

                        // Determine badge colors for estado
                        let estadoBadgeClass = 'badge-secondary';
                        if (estado === 'procesado' || estado === 'completado') estadoBadgeClass = 'badge-success';
                        else if (estado === 'pendiente') estadoBadgeClass = 'badge-warning';
                        else if (estado === 'error' || estado === 'fallido') estadoBadgeClass = 'badge-danger';

                        const row = `
                            <tr>
                                <td class="font-mono">${new Date(log.fecha).toLocaleString('es-ES')}</td>
                                <td>
                                    <span class="badge ${tipoBadgeClass}">
                                        ${tipo}
                                    </span>
                                </td>
                                <td class="font-mono">${archivo}</td>
                                <td>
                                    <span class="badge ${estadoBadgeClass}">
                                        ${estado}
                                    </span>
                                </td>
                                <td class="font-mono">${log.usuario_nombre || 'Sistema'}</td>
                            </tr>
                        `;
                        importHistoryTable.insertAdjacentHTML('beforeend', row);
                    });
                } else {
                    importHistoryTable.innerHTML = '<tr><td colspan="5" class="text-center">No hay historial de importaciones o backups.</td></tr>';
                }
            } catch (error) {
                console.error('Error al cargar el historial:', error);
                importHistoryTable.innerHTML = '<tr><td colspan="5" class="text-center">Error al cargar el historial.</td></tr>';
            }
        }

        // Function to show notifications (assuming it's defined in common.js or similar)
        function showNotification(message, type) {
            const notificationContainer = document.getElementById('notificationContainer');
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
            alertDiv.role = 'alert';
            alertDiv.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            `;
            notificationContainer.appendChild(alertDiv);
            setTimeout(() => alertDiv.remove(), 5000); // Remove after 5 seconds
        }
    </script>

    <!-- Error Modal Structure -->
    <div class="modal fade" id="errorModal" tabindex="-1" aria-labelledby="errorModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="errorModalLabel">Detalles de Errores de Importación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Errors will be loaded here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
