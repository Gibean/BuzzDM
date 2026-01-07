<?php require_once 'php/sesion.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BUZZ Data Manager - TO-DO Kanban</title>
    <link rel="shortcut icon" href="assets/img/logov1_icon.png" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="assets/css/modal-enhanced.css">
    <link rel="stylesheet" href="assets/css/toast.css">
    <link rel="stylesheet" href="assets/css/notas.css">
</head>
<body>
    <!-- Sidebar  -->
    <?php require_once 'php/sidebar.php'; ?>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Top Navbar -->
        <nav class="top-navbar">
            <div class="d-flex align-items-center">
                <button class="btn btn-outline-buzz me-3 d-md-none" id="sidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>
                <h1 class="font-primary mb-0">TO-DO</h1>
                <span class="text-muted ms-2 font-mono">/ KANBAN</span>
            </div>
            <div>
                <button class="btn btn-buzz" onclick="openNewNoteModal()">
                    <i class="fas fa-plus"></i> Nueva Nota
                </button>
            </div>
        </nav>

        <!-- Kanban Board Area -->
        <div class="container-fluid py-4">
            <div class="kanban-board" id="kanbanBoard">

                <!-- Columna: Pendiente -->
                <div class="kanban-column" data-status="pendiente">
                    <div class="kanban-column-header">
                        <h3 class="kanban-column-title">Pendiente</h3>
                        <span class="badge bg-dark text-muted" id="count-pendiente">0</span>
                    </div>
                    <div class="kanban-cards-container" id="cards-pendiente" ondrop="drop(event)" ondragover="allowDrop(event)">
                        <!-- Tarjetas inyectadas por JS -->
                    </div>
                    <button class="add-card-btn" onclick="openNewNoteModal('pendiente')">
                        <i class="fas fa-plus me-2"></i> Añadir tarjeta
                    </button>
                </div>

                <!-- Columna: Seguimiento -->
                <div class="kanban-column" data-status="seguimiento">
                    <div class="kanban-column-header">
                        <h3 class="kanban-column-title">Seguimiento</h3>
                        <span class="badge bg-dark text-muted" id="count-seguimiento">0</span>
                    </div>
                    <div class="kanban-cards-container" id="cards-seguimiento" ondrop="drop(event)" ondragover="allowDrop(event)">
                        <!-- Tarjetas inyectadas por JS -->
                    </div>
                    <button class="add-card-btn" onclick="openNewNoteModal('seguimiento')">
                        <i class="fas fa-plus me-2"></i> Añadir tarjeta
                    </button>
                </div>

                <!-- Columna: Resuelto -->
                <div class="kanban-column" data-status="resuelto">
                    <div class="kanban-column-header">
                        <h3 class="kanban-column-title">Resuelto</h3>
                        <span class="badge bg-dark text-muted" id="count-resuelto">0</span>
                    </div>
                    <div class="kanban-cards-container" id="cards-resuelto" ondrop="drop(event)" ondragover="allowDrop(event)">
                        <!-- Tarjetas inyectadas por JS -->
                    </div>
                </div>

                <!-- Columna: Cancelado -->
                <div class="kanban-column" data-status="cancelado">
                    <div class="kanban-column-header">
                        <h3 class="kanban-column-title">Cancelado</h3>
                        <span class="badge bg-dark text-muted" id="count-cancelado">0</span>
                    </div>
                    <div class="kanban-cards-container" id="cards-cancelado" ondrop="drop(event)" ondragover="allowDrop(event)">
                        <!-- Tarjetas inyectadas por JS -->
                    </div>
                </div>

            </div>
        </div>

        <?php require_once 'php/system_footer.php'; ?>
    </main>

    <!-- Modal para Nueva/Editar Nota -->
    <div class="modal-buzz" id="noteModal">
        <div class="modal-content-buzz modal-lg">
            <div class="modal-header-buzz">
                <h5 class="font-primary text-primary" id="noteModalTitle">
                    <i class="fas fa-sticky-note"></i> DETALLE DE NOTA
                </h5>
                <button class="close-buzz" onclick="closeNoteModal()" aria-label="Cerrar">&times;</button>
            </div>
                <form id="noteForm">
                <div class="modal-body-buzz p-0">
                    <input type="hidden" id="noteId">
                    <input type="hidden" id="noteStatus">

                    <!-- Title Section -->
                    <div class="px-4 pt-4 pb-2 border-bottom border-secondary" style="background: rgba(0,0,0,0.2);">
                        <label class="form-label text-muted small mb-1 text-uppercase tracking-wider">Título de la tarjeta / Cliente Principal *</label>
                        <input type="text" class="form-control form-control-buzz form-control-lg border-0 bg-transparent p-0 fs-5 fw-bold mb-2 shadow-none" id="noteTitle" required placeholder="Ej: Consulta de Juan Pérez">
                        <div class="d-flex flex-wrap gap-2 mb-2">
                            <div id="activeLabels" class="d-flex gap-2"></div>
                            <div id="activeMembers" class="d-flex gap-2"></div>
                        </div>
                    </div>

                    <!-- Top Tools Row -->
                    <div class="modal-tools-top">
                        <button type="button" class="btn btn-sm btn-outline-buzz" onclick="toggleLabelEditor()">
                            <i class="fas fa-tag me-1"></i> Etiquetas
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-buzz" onclick="toggleMemberEditor()">
                            <i class="fas fa-users me-1"></i> Miembros
                        </button>
                        <div class="d-flex align-items-center gap-2 ms-auto">
                            <span class="text-muted small">Prioridad:</span>
                            <select class="form-select form-control-buzz form-control-sm w-auto" id="notePriority">
                                <option value="baja">Baja</option>
                                <option value="media" selected>Media</option>
                                <option value="alta">Alta</option>
                            </select>
                        </div>
                    </div>

                    <div class="row g-0">
                        <!-- Main Content (Left) -->
                        <div class="col-md-8 p-4">
                            <!-- Description -->
                            <div class="mb-5">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-align-left me-2 text-muted"></i>
                                        <h6 class="mb-0 font-primary text-uppercase small">Descripción</h6>
                                    </div>
                                    <!-- Markdown Toolbar -->
                                    <div class="markdown-toolbar">
                                        <button type="button" class="btn" onclick="insertMarkdown('**', '**')" title="Negrita"><i class="fas fa-bold"></i></button>
                                        <button type="button" class="btn" onclick="insertMarkdown('_', '_')" title="Cursiva"><i class="fas fa-italic"></i></button>
                                        <button type="button" class="btn" onclick="insertMarkdown('### ', '')" title="Título"><i class="fas fa-heading"></i></button>
                                        <button type="button" class="btn" onclick="insertMarkdown('- ', '')" title="Lista"><i class="fas fa-list"></i></button>
                                        <button type="button" class="btn" onclick="addAttachmentRow()" title="Enlace"><i class="fas fa-link"></i></button>
                                    </div>
                                </div>
                                <textarea class="form-control note-textarea" id="noteContent" placeholder="Escribe aquí los detalles del requerimiento..."></textarea>
                            </div>

                            <!-- Checklist Section -->
                            <div class="mb-5">
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-check-square me-2 text-muted"></i>
                                        <h6 class="mb-0 font-primary text-uppercase small">Tareas a Realizar</h6>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-buzz px-3" onclick="addChecklistItem()">
                                        <i class="fas fa-plus me-1"></i> Añadir Item
                                    </button>
                                </div>
                                <div class="mb-3 d-flex align-items-center gap-3">
                                    <small id="checklist-percent" class="text-muted font-mono" style="min-width: 40px;">0%</small>
                                    <div class="checklist-progress-container m-0 flex-grow-1" style="height: 6px;">
                                        <div class="checklist-progress-bar" id="checklist-bar" style="width: 0%"></div>
                                    </div>
                                </div>
                                <div id="noteChecklistContainer">
                                    <!-- Checklist items -->
                                </div>
                            </div>

                            <!-- Products Section -->
                            <div class="mb-5">
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-boxes me-2 text-muted"></i>
                                        <h6 class="mb-0 font-primary text-uppercase small">Productos Vinculados</h6>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-buzz px-3" id="addProductLine">
                                        <i class="fas fa-search me-1"></i> Buscar en Inventario
                                    </button>
                                </div>
                                <div id="noteProductsContainer">
                                    <!-- Products -->
                                </div>
                            </div>

                            <!-- Attachments Section (Bottom) -->
                            <div class="mb-4">
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-paperclip me-2 text-muted"></i>
                                        <h6 class="mb-0 font-primary text-uppercase small">Archivos y Enlaces</h6>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-buzz px-3" onclick="addAttachmentRow()">
                                        <i class="fas fa-link me-1"></i> Adjuntar archivo (enlace)
                                    </button>
                                </div>
                                <div id="noteAttachmentsContainer">
                                    <!-- Attachments -->
                                </div>
                            </div>
                        </div>

                        <!-- Sidebar Info (Right) -->
                        <div class="col-md-4 modal-section-sidebar">
                            <div class="mb-5">
                                <h6 class="text-uppercase tracking-wider">Detalles del Cliente</h6>
                                <div class="mb-3">
                                    <label class="text-muted small mb-1">Nombre Completo</label>
                                    <input type="text" class="form-control form-control-buzz form-control-sm" id="noteClientName" placeholder="Nombre completo">
                                </div>
                                <div class="mb-3">
                                    <label class="text-muted small mb-1">Teléfono</label>
                                    <input type="text" class="form-control form-control-buzz form-control-sm" id="notePhone" placeholder="+58 ...">
                                </div>
                                <div class="mb-3">
                                    <label class="text-muted small mb-1">Email</label>
                                    <input type="email" class="form-control form-control-buzz form-control-sm" id="noteEmail" placeholder="cliente@correo.com">
                                </div>
                            </div>

                            <div>
                                <h6 class="text-uppercase tracking-wider">Logística & Fechas</h6>
                                <div class="mb-3">
                                    <label class="text-muted small mb-1">Fecha de Entrega / Seguimiento</label>
                                    <input type="datetime-local" class="form-control form-control-buzz form-control-sm" id="noteDueDate">
                                </div>
                            </div>

                            <hr class="border-secondary my-4">

                            <div class="text-center py-2 opacity-50">
                                <small class="text-muted font-mono d-block mb-1">ID TARJETA</small>
                                <span class="badge bg-secondary font-mono" id="noteIdLabel"># NUEVA</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer-buzz">
                    <button type="button" class="btn btn-outline-danger me-auto" id="deleteNoteBtn" style="display:none">
                        <i class="fas fa-trash"></i> Eliminar Nota
                    </button>
                    <button type="button" class="btn btn-outline-buzz" onclick="closeNoteModal()">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-buzz">
                        <i class="fas fa-save me-1"></i> GUARDAR CAMBIOS
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/modal.js"></script>
    <script src="assets/js/toast.js"></script>
    <script src="assets/js/common.js"></script>
    <script src="assets/js/notas.js"></script>
</body>
</html>
