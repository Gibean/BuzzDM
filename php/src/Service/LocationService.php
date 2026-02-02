<?php
require_once __DIR__ . '/../Repository/LocationRepository.php';

/**
 * LocationService - Business logic for warehouse locations
 */
class LocationService {
    private $repository;

    public function __construct() {
        $this->repository = new LocationRepository();
    }

    public function getByWarehouse($warehouseId) {
        if (empty($warehouseId)) {
            throw new Exception('almacen_id es requerido');
        }
        return $this->repository->findByWarehouse($warehouseId);
    }

    public function create($data) {
        if (empty($data['almacen_id']) || empty($data['codigo'])) {
            throw new Exception('almacen_id y código son obligatorios');
        }

        if ($this->repository->codeExists($data['almacen_id'], trim($data['codigo']))) {
            throw new Exception('El código ya existe en este almacén');
        }

        $id = $this->repository->create($data);
        return ['id' => $id, 'message' => 'Ubicación creada exitosamente'];
    }

    public function update($id, $data) {
        if (empty($id)) {
            throw new Exception('ID no proporcionado');
        }

        if (empty($data['codigo'])) {
            throw new Exception('El código es obligatorio');
        }

        $location = $this->repository->findById($id);
        if (!$location) {
            throw new Exception('Ubicación no encontrada');
        }

        if ($this->repository->codeExists($location['almacen_id'], trim($data['codigo']), $id)) {
            throw new Exception('El código ya existe en este almacén');
        }

        $this->repository->update($id, $data);
        return ['message' => 'Ubicación actualizada exitosamente'];
    }

    public function delete($id) {
        if (empty($id)) {
            throw new Exception('ID no proporcionado');
        }

        if ($this->repository->hasStock($id)) {
            throw new Exception('No se puede eliminar: tiene productos asignados');
        }

        $this->repository->delete($id);
        return ['message' => 'Ubicación eliminada exitosamente'];
    }
}
