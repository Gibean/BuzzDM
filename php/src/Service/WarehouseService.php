<?php
require_once __DIR__ . '/../Repository/WarehouseRepository.php';

class WarehouseService {
    private $repository;

    public function __construct() {
        $this->repository = new WarehouseRepository();
    }

    // --- Warehouses ---

    public function getAllWarehouses($showInactive = false) {
        $warehouses = $this->repository->findAllWarehouses($showInactive);
        $stats = $this->repository->getStats();
        return ['warehouses' => $warehouses, 'stats' => $stats];
    }

    public function getWarehouse($id) {
        $warehouse = $this->repository->findWarehouseById($id);
        if (!$warehouse) {
            throw new Exception("Almacén no encontrado.");
        }
        $locations = $this->repository->findLocationsByWarehouseId($id);
        $warehouse['ubicaciones'] = $locations;
        return $warehouse;
    }

    public function createWarehouse($data) {
        $this->validateWarehouseData($data);

        // Check Unique Code
        if ($this->repository->findWarehouseByCode($data['codigo'])) {
            throw new Exception("El código de almacén ya existe.");
        }

        // Handle Principal Flag
        if (!empty($data['es_principal'])) {
            $this->repository->resetPrincipalWarehouse();
        }

        return $this->repository->createWarehouse($data);
    }

    public function updateWarehouse($id, $data) {
        $this->validateWarehouseData($data);

        // Check Unique Code
        if ($this->repository->findWarehouseByCode($data['codigo'], $id)) {
            throw new Exception("El código de almacén ya existe.");
        }

        // Handle Principal Flag
        if (!empty($data['es_principal'])) {
            $this->repository->resetPrincipalWarehouse($id);
        }

        return $this->repository->updateWarehouse($id, $data);
    }

    public function deactivateWarehouse($id) {
        $warehouse = $this->repository->findWarehouseById($id);
        if (!$warehouse) {
            throw new Exception("Almacén no encontrado.");
        }

        if ($warehouse['es_principal']) {
            throw new Exception("No se puede desactivar el almacén principal.");
        }

        return $this->repository->deactivateWarehouse($id);
    }

    private function validateWarehouseData($data) {
        if (empty($data['codigo']) || empty($data['nombre'])) {
            throw new Exception("Código y nombre son obligatorios.");
        }
    }

    // --- Locations ---

    public function getLocations($warehouseId) {
        if (!$warehouseId) {
            throw new Exception("ID de almacén requerido.");
        }
        return $this->repository->findLocationsByWarehouseId($warehouseId);
    }

    public function createLocation($data) {
        $this->validateLocationData($data);

        // Check Unique Code in Warehouse
        if ($this->repository->findLocationByCodeInWarehouse($data['almacen_id'], $data['codigo'])) {
            throw new Exception("El código de ubicación ya existe en este almacén.");
        }

        return $this->repository->createLocation($data);
    }

    public function updateLocation($id, $data) {
        $existing = $this->repository->findLocationById($id);
        if (!$existing) {
            throw new Exception("Ubicación no encontrada.");
        }

        // Use existing warehouse_id if not provided (though logic implies we update properties, not parent)
        // Ensure data has warehouse_id for unique check
        $warehouseId = $existing['almacen_id'];

        if (empty($data['codigo'])) {
             throw new Exception("El código es obligatorio.");
        }

        // Check Unique Code in Warehouse
        if ($this->repository->findLocationByCodeInWarehouse($warehouseId, $data['codigo'], $id)) {
            throw new Exception("El código de ubicación ya existe en este almacén.");
        }

        return $this->repository->updateLocation($id, $data);
    }

    public function deleteLocation($id) {
        $stock = $this->repository->getLocationStockCount($id);
        if ($stock > 0) {
            throw new Exception("No se puede eliminar: tiene productos asignados.");
        }
        return $this->repository->deleteLocation($id);
    }

    private function validateLocationData($data) {
        if (empty($data['almacen_id']) || empty($data['codigo'])) {
            throw new Exception("ID de almacén y código son obligatorios.");
        }
    }
}
