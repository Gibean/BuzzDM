<?php
require_once __DIR__ . '/../Repository/SupplierRepository.php';

class SupplierService {
    private $repository;

    public function __construct() {
        $this->repository = new SupplierRepository();
    }

    public function getAllSuppliers() {
        return $this->repository->findAll();
    }

    public function searchSuppliers($query) {
        return $this->repository->search($query);
    }

    public function getSupplierById($id) {
        return $this->repository->findById($id);
    }

    public function createSupplier($data) {
        if (empty($data['nombre'])) {
            throw new Exception("El nombre es obligatorio");
        }
        return $this->repository->create($data);
    }

    public function updateSupplier($id, $data) {
        if (empty($data['nombre'])) {
            throw new Exception("El nombre es obligatorio");
        }
        if (!$this->repository->findById($id)) {
            throw new Exception("Proveedor no encontrado");
        }
        return $this->repository->update($id, $data);
    }

    public function deleteSupplier($id) {
        if (!$this->repository->findById($id)) {
            throw new Exception("Proveedor no encontrado");
        }
        return $this->repository->delete($id);
    }
}
