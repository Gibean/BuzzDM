<?php
require_once __DIR__ . '/../Repository/CatalogRepository.php';

/**
 * CatalogService - Business logic for master catalogs
 */
class CatalogService {
    private $repository;

    public function __construct() {
        $this->repository = new CatalogRepository();
    }

    /**
     * Get summary for dropdowns
     */
    public function getSummary() {
        return $this->repository->getSummary();
    }

    /**
     * Get all catalogs for management
     */
    public function getAllCatalogs() {
        return $this->repository->getAllCatalogs();
    }

    /**
     * Get all items from a specific catalog
     */
    public function getCatalog($catalog) {
        return $this->repository->findAll($catalog);
    }

    /**
     * Create a new item
     */
    public function createItem($catalog, array $data) {
        // Validate required field
        if (empty($data['nombre'])) {
            throw new Exception("El nombre es obligatorio");
        }

        if (!$this->repository->isValidCatalog($catalog)) {
            throw new Exception("Catálogo no válido");
        }

        $id = $this->repository->create($catalog, $data);
        return ['id' => $id, 'message' => 'Registro creado exitosamente'];
    }

    /**
     * Update an item
     */
    public function updateItem($catalog, $id, array $data) {
        if (empty($id)) {
            throw new Exception("ID requerido");
        }

        if (empty($data['nombre'])) {
            throw new Exception("El nombre es obligatorio");
        }

        if (!$this->repository->isValidCatalog($catalog)) {
            throw new Exception("Catálogo no válido");
        }

        $this->repository->update($catalog, $id, $data);
        return ['message' => 'Registro actualizado exitosamente'];
    }

    /**
     * Delete an item
     */
    public function deleteItem($catalog, $id) {
        if (empty($catalog) || empty($id)) {
            throw new Exception("Catálogo e ID requeridos");
        }

        $this->repository->delete($catalog, $id);
        return ['message' => 'Registro eliminado exitosamente'];
    }
}
