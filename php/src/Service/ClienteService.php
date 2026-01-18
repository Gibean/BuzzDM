<?php
require_once __DIR__ . '/../Repository/ClienteRepository.php';

class ClienteService {
    private $repository;

    public function __construct() {
        $this->repository = new ClienteRepository();
    }

    public function getCustomer($id) {
        // Business logic could be added here (e.g. formatting, hiding sensitive fields)
        return $this->repository->findById($id);
    }

    public function searchCustomers($params) {
        $filters = [
            'search' => isset($params['search']) ? '%' . $params['search'] . '%' : '%',
            'limit' => $params['limit'] ?? 100,
            'offset' => (($params['page'] ?? 1) - 1) * ($params['limit'] ?? 100),
            'show_deleted' => isset($params['show_deleted']) && $params['show_deleted'] === 'true',
        ];

        if (isset($params['type'])) {
            if ($params['type'] === 'vip') {
                $filters['vip'] = 1;
            } elseif ($params['type'] === 'regular') {
                $filters['vip'] = 0; // Fixed string to number conversion issue
            }
        }

        return $this->repository->findAll($filters);
    }

    public function getFrequentCustomers() {
        return $this->repository->findFrequent(5);
    }

    public function createCustomer($data) {
        // Validation logic
        if (empty($data['id']) || empty($data['nombre'])) {
            throw new Exception("ID y Nombre son obligatorios");
        }

        // Check if exists
        if ($this->repository->findById($data['id'])) {
            throw new Exception("El cliente con ID {$data['id']} ya existe");
        }

        if ($this->repository->create($data)) {
            return $data['id'];
        }
        throw new Exception("Error al crear el cliente en base de datos");
    }

    public function updateCustomer($id, $data) {
        if (!$this->repository->findById($id)) {
            throw new Exception("Cliente no encontrado");
        }

        if ($this->repository->update($id, $data)) {
            return true;
        }
        throw new Exception("Error al actualizar cliente");
    }

    public function deleteCustomer($id) {
        if ($this->repository->softDelete($id)) {
            return true;
        }
        throw new Exception("Error al eliminar cliente");
    }

    public function restoreCustomer($id) {
        if ($this->repository->restore($id)) {
            return true;
        }
        throw new Exception("Error al restaurar cliente");
    }
}
