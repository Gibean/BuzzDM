# Clientes.php Modal Conversion - URGENT FIX NEEDED

## Problem
The clientes.php file has become corrupted with duplicate modal code after the conversion attempt.

## Quick Fix

The file needs to be manually cleaned. Here's what needs to be done:

1. **Remove duplicate modal sections** (lines ~170-440 appear to be duplicates)
2. **Keep only the modal-buzz versions** (the new ones at the top)
3. **Remove all Bootstrap modal remnants**

## Clean Modal Structure (Use This)

Replace ALL modal sections in clientes.php with these three clean modals:

### 1. Create Customer Modal
```html
<!-- Create Customer Modal -->
<div class="modal-buzz" id="createCustomerModal">
    <div class="modal-content-buzz">
        <div class="modal-header-buzz">
            <h5 class="font-primary text-primary"><i class="fas fa-user-plus"></i> Nuevo Cliente</h5>
            <button class="close-buzz" onclick="closeModal('createCustomerModal')" aria-label="Cerrar">&times;</button>
        </div>
        <form id="createCustomerForm">
            <div class="modal-body-buzz">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label text-muted">ID Cliente *</label>
                        <div class="input-group">
                            <select class="form-select form-control-buzz" id="createCustomerIdType" style="max-width: 80px;" required>
                                <option value="V">V</option>
                                <option value="E">E</option>
                                <option value="J">J</option>
                                <option value="G">G</option>
                                <option value="P">P</option>
                            </select>
                            <input type="text" class="form-control form-control-buzz" id="createCustomerId" maxlength="20" pattern="[0-9]*" inputmode="numeric" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted">Email</label>
                        <input type="email" class="form-control form-control-buzz" id="createCustomerEmail">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label text-muted">Nombre *</label>
                    <input type="text" class="form-control form-control-buzz" id="createCustomerName" required>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label text-muted">Código</label>
                        <select class="form-select form-control-buzz" id="createCustomerPhoneCountryCode" required>
                            <option value="+58">Venezuela (+58)</option>
                            <option value="+1">USA/Canada (+1)</option>
                            <option value="+57">Colombia (+57)</option>
                            <option value="+54">Argentina (+54)</option>
                            <option value="+34">España (+34)</option>
                        </select>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label text-muted">Teléfono</label>
                        <input type="text" class="form-control form-control-buzz" id="createCustomerPhone" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label text-muted">Dirección</label>
                    <textarea class="form-control form-control-buzz" id="createCustomerAddress" rows="2"></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label text-muted">Notas</label>
                    <textarea class="form-control form-control-buzz" id="createCustomerNotes" rows="2"></textarea>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="1" id="createCustomerVip">
                    <label class="form-check-label text-muted" for="createCustomerVip">Cliente VIP</label>
                </div>
            </div>
            <div class="modal-footer-buzz">
                <button type="button" class="btn btn-outline-buzz" onclick="closeModal('createCustomerModal')"><i class="fas fa-times"></i> Cancelar</button>
                <button type="submit" class="btn btn-buzz"><i class="fas fa-save"></i> Guardar Cliente</button>
            </div>
        </form>
    </div>
</div>
```

### 2. View Customer Modal
```html
<!-- View Customer Modal -->
<div class="modal-buzz" id="viewCustomerModal">
    <div class="modal-content-buzz">
        <div class="modal-header-buzz">
            <h5 class="font-primary text-primary"><i class="fas fa-eye"></i> Detalle del Cliente #<span id="viewCustomerId"></span></h5>
            <button class="close-buzz" onclick="closeModal('viewCustomerModal')" aria-label="Cerrar">&times;</button>
        </div>
        <div class="modal-body-buzz">
            <p><strong>Nombre:</strong> <span id="viewCustomerName"></span></p>
            <p><strong>Email:</strong> <span id="viewCustomerEmail"></span></p>
            <p><strong>Teléfono:</strong> <span id="viewCustomerPhone"></span></p>
            <p><strong>Dirección:</strong> <span id="viewCustomerAddress"></span></p>
            <p><strong>Notas:</strong> <span id="viewCustomerNotes"></span></p>
            <p><strong>VIP:</strong> <span id="viewCustomerVip"></span></p>
            <p><strong>Creado en:</strong> <span id="viewCustomerCreatedAt"></span></p>
        </div>
        <div class="modal-footer-buzz">
            <button type="button" class="btn btn-outline-buzz" onclick="closeModal('viewCustomerModal')">Cerrar</button>
        </div>
    </div>
</div>
```

### 3. Edit Customer Modal
```html
<!-- Edit Customer Modal -->
<div class="modal-buzz" id="editCustomerModal">
    <div class="modal-content-buzz">
        <div class="modal-header-buzz">
            <h5 class="font-primary text-primary"><i class="fas fa-edit"></i> Editar Cliente #<span id="editCustomerId"></span></h5>
            <button class="close-buzz" onclick="closeModal('editCustomerModal')" aria-label="Cerrar">&times;</button>
        </div>
        <form id="editCustomerForm">
            <div class="modal-body-buzz">
                <input type="hidden" id="editCustomerIdHidden">
                <div class="mb-3">
                    <label class="form-label text-muted">Nombre *</label>
                    <input type="text" class="form-control form-control-buzz" id="editCustomerName" required>
                </div>
                <div class="mb-3">
                    <label class="form-label text-muted">Email</label>
                    <input type="email" class="form-control form-control-buzz" id="editCustomerEmail">
                </div>
                <div class="mb-3">
                    <label class="form-label text-muted">Teléfono</label>
                    <input type="text" class="form-control form-control-buzz" id="editCustomerPhone">
                </div>
                <div class="mb-3">
                    <label class="form-label text-muted">Dirección</label>
                    <textarea class="form-control form-control-buzz" id="editCustomerAddress" rows="2"></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label text-muted">Notas</label>
                    <textarea class="form-control form-control-buzz" id="editCustomerNotes" rows="2"></textarea>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="1" id="editCustomerVip">
                    <label class="form-check-label text-muted" for="editCustomerVip">Cliente VIP</label>
                </div>
            </div>
            <div class="modal-footer-buzz">
                <button type="button" class="btn btn-outline-buzz" onclick="closeModal('editCustomerModal')"><i class="fas fa-times"></i> Cancelar</button>
                <button type="submit" class="btn btn-buzz"><i class="fas fa-save"></i> Guardar Cambios</button>
            </div>
        </form>
    </div>
</div>
```

## Steps to Fix

1. Open clientes.php in your editor
2. Find the section starting with `<!-- Create Customer Modal -->` (around line 97)
3. Delete everything from there until you see `<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>`
4. Paste the three clean modals above
5. Save the file

This will remove all duplicates and leave only the clean modal-buzz versions.
