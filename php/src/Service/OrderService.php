<?php
require_once __DIR__ . '/../Repository/OrderRepository.php';

class OrderService {
    private $repository;

    public function __construct() {
        $this->repository = new OrderRepository();
    }

    public function listOrders($filters) {
        return $this->repository->findAll($filters);
    }

    public function getOrder($id) {
        $order = $this->repository->findById($id);
        if (!$order) {
            throw new Exception("Pedido no encontrado");
        }
        return $order;
    }

    public function getOrderProducts($id) {
        return $this->repository->getDetails($id);
    }

    public function createOrder($data) {
        $this->validateOrderData($data);

        $this->repository->beginTransaction();
        try {
            // Generate Reference
            $referencia = strval(mt_rand(1000000000, 9999999999));

            $headerData = [
                'cliente_id' => $data['cliente_id'],
                'referencia' => $referencia,
                'fecha_creacion' => $data['fecha_creacion'] ?? date('Y-m-d H:i:s'),
                'total' => $this->calculateTotal($data['products']),
                'estado_pago' => $data['estado_pago'],
                'estado_envio' => $data['estado_envio'],
                'direccion_envio' => $data['direccion_envio'],
                'notas_envio' => $data['notas_envio'] ?? null
            ];

            $orderId = $this->repository->createHeader($headerData);

            if (!empty($data['products'])) {
                foreach ($data['products'] as $product) {
                    $this->repository->createDetail($orderId, $product);

                    // Update Inventory: Subtract quantity
                    // Delta is negative because we are selling
                    $this->repository->adjustStock($product['producto_id'], -1 * abs($product['cantidad']));
                }
            }

            $this->repository->commit();
            return ['id' => $orderId, 'referencia' => $referencia];

        } catch (Exception $e) {
            $this->repository->rollBack();
            throw $e;
        }
    }

    public function updateOrder($id, $data) {
        $this->validateOrderData($data);

        $this->repository->beginTransaction();
        try {
            // 1. Restore Inventory from OLD products
            $oldProducts = $this->repository->getDetailsForStockReversion($id);
            foreach ($oldProducts as $old) {
                // Add back to stock
                $this->repository->adjustStock($old['producto_id'], $old['cantidad']);
            }

            // 2. Update Header
            $headerData = [
                'cliente_id' => $data['cliente_id'],
                'referencia' => $data['referencia'],
                'fecha_creacion' => $data['fecha_creacion'] ?? date('Y-m-d H:i:s'),
                'total' => $this->calculateTotal($data['products']), // Recalculate based on input
                'estado_pago' => $data['estado_pago'],
                'estado_envio' => $data['estado_envio'],
                'direccion_envio' => $data['direccion_envio'],
                'notas_envio' => $data['notas_envio'] ?? null
            ];
            $this->repository->updateHeader($id, $headerData);

            // 3. Clear old details
            $this->repository->deleteDetails($id);

            // 4. Insert new details and deduct stock
            if (!empty($data['products'])) {
                foreach ($data['products'] as $product) {
                    $this->repository->createDetail($id, $product);

                    // Deduct stock
                    $this->repository->adjustStock($product['producto_id'], -1 * abs($product['cantidad']));
                }
            }

            $this->repository->commit();
            return ['id' => $id];

        } catch (Exception $e) {
            $this->repository->rollBack();
            throw $e;
        }
    }

    public function deleteOrder($id) {
        // Soft delete - NO stock restoration as per original logic
        // Original: UPDATE pedidos SET deleted_at = NOW()
        $this->repository->softDelete($id);
    }

    public function restoreOrder($id) {
        $this->repository->restore($id);
    }

    private function validateOrderData($data) {
        if (empty($data['cliente_id'])) throw new Exception("El cliente es obligatorio");
        if (empty($data['direccion_envio'])) throw new Exception("La dirección de envío es obligatoria");
        if (empty($data['products']) || !is_array($data['products'])) {
             // It's possible to create order without products in some systems, but original logic implied it.
             // Let's warn if empty but allow? Original logic loop checks if set.
             // Ideally an order should have products.
             throw new Exception("El pedido debe tener al menos un producto");
        }
    }

    private function calculateTotal($products) {
        $total = 0;
        foreach ($products as $p) {
            $total += ($p['cantidad'] * $p['precio_unitario']);
        }
        return $total;
    }
}
