# Quick Fix Guide for Clientes and Pedidos Modals

## Issue
The modals in clientes.php and pedidos.php are using Bootstrap modal structure which conflicts with our custom modal-buzz system.

## Solution

### For clientes.php

Replace the Bootstrap modal structure (starting around line 98) with this custom modal-buzz structure:

```html
<!-- Create Customer Modal -->
<div class="modal-buzz" id="createCustomerModal">
    <div class="modal-content-buzz">
        <div class="modal-header-buzz">
            <h5 class="font-primary text-primary">
                <i class="fas fa-user-plus"></i> Nuevo Cliente
            </h5>
            <button class="close-buzz" onclick="closeModal('createCustomerModal')" aria-label="Cerrar">&times;</button>
        </div>
        <form id="createCustomerForm">
            <div class="modal-body-buzz">
                <!-- Keep all the existing form fields here -->
                <!-- Just wrap them in modal-section divs for better organization -->
            </div>
            <div class="modal-footer-buzz">
                <button type="button" class="btn btn-outline-buzz" onclick="closeModal('createCustomerModal')">
                    <i class="fas fa-times"></i> Cancelar
                </button>
                <button type="submit" class="btn btn-buzz">
                    <i class="fas fa-save"></i> Guardar Cliente
                </button>
            </div>
        </form>
    </div>
</div>
```

### For pedidos.php

Same approach - replace Bootstrap modals with modal-buzz structure.

### Key Changes Needed:

1. **Replace:**
   - `<div class="modal fade" id="...">` → `<div class="modal-buzz" id="...">`
   - `<div class="modal-dialog modal-lg">` → `<div class="modal-content-buzz">`
   - `<div class="modal-content buzz-modal-dark-override">` → (remove this div)
   - `<div class="modal-header">` → `<div class="modal-header-buzz">`
   - `<button class="btn-close" data-bs-dismiss="modal">` → `<button class="close-buzz" onclick="closeModal('modalId')">&times;</button>`
   - `<div class="modal-body">` → `<div class="modal-body-buzz">`
   - `<div class="modal-footer">` → `<div class="modal-footer-buzz">`
   - `data-bs-dismiss="modal"` → `onclick="closeModal('modalId')"`

2. **Update all buttons that open modals:**
   - Replace `data-bs-toggle="modal" data-bs-target="#modalId"` with `onclick="openModal('modalId')"`

## Performance Note

All animations have been removed from the modal system to eliminate lag. Modals now open and close instantly.
