<?php
require_once __DIR__ . '/../Repository/KitRepository.php';

/**
 * KitService - Business logic for product kit components
 */
class KitService {
    private $repository;

    public function __construct() {
        $this->repository = new KitRepository();
    }

    public function getComponents($kitId) {
        if (empty($kitId)) {
            throw new Exception('kit_id es requerido');
        }

        $components = $this->repository->getComponents($kitId);
        $kit = $this->repository->getKit($kitId);

        $stockKit = null;
        if ($kit && $kit['es_kit_virtual']) {
            $stockKit = $this->repository->getKitVirtualStock($kitId);
        }

        $totalComponents = count($components);
        $costTotal = array_reduce($components, fn($sum, $c) => $sum + ($c['subtotal'] ?? 0), 0);

        return [
            'componentes' => $components,
            'stock_kit' => $stockKit,
            'total_componentes' => $totalComponents,
            'costo_componentes' => $costTotal
        ];
    }

    public function addComponent($data) {
        $kitId = $data['kit_id'] ?? null;
        $componentId = $data['componente_id'] ?? null;
        $quantity = floatval($data['cantidad'] ?? 1);

        if (!$kitId || !$componentId) {
            throw new Exception('kit_id y componente_id son requeridos');
        }

        if ($quantity <= 0) {
            throw new Exception('La cantidad debe ser mayor a 0');
        }

        $kit = $this->repository->getKit($kitId);
        if (!$kit || $kit['tipo_producto'] !== 'kit') {
            throw new Exception('El producto no es un kit');
        }

        if (!$this->repository->productExists($componentId)) {
            throw new Exception('El componente no existe');
        }

        if ($kitId == $componentId) {
            throw new Exception('Un kit no puede ser componente de sí mismo');
        }

        $maxOrder = $this->repository->getMaxOrder($kitId);
        $id = $this->repository->addComponent($kitId, $componentId, $quantity, $maxOrder + 1);

        return ['id' => $id, 'message' => 'Componente agregado'];
    }

    public function updateComponent($id, $data) {
        if (empty($id)) {
            throw new Exception('ID no proporcionado');
        }

        $quantity = floatval($data['cantidad'] ?? 0);
        if ($quantity <= 0) {
            throw new Exception('La cantidad debe ser mayor a 0');
        }

        $order = isset($data['orden']) ? intval($data['orden']) : null;
        $this->repository->updateComponent($id, $quantity, $order);

        return ['message' => 'Componente actualizado'];
    }

    public function deleteComponent($id) {
        if (empty($id)) {
            throw new Exception('ID no proporcionado');
        }

        $this->repository->deleteComponent($id);
        return ['message' => 'Componente eliminado'];
    }
}
