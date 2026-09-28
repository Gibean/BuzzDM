<?php
// Autoload handled by composer

class ProductService {
    private $repository;

    public function __construct() {
        $this->repository = new ProductRepository();
    }

    public function getProduct($id) {
        return $this->repository->findById($id);
    }

    public function listProducts($filters) {
        return $this->repository->findAll($filters);
    }

    public function createProduct($data) {
        $this->validateProductData($data);

        // Validación de límite operativo para versión Demo (SaaS)
        $companyId = (int)($_SESSION['empresa_id'] ?? 1);
        $isDemo = (bool)($_SESSION['is_demo'] ?? false);
        $maxProducts = (int)($_ENV['DEMO_MAX_PRODUCTS'] ?? 50);

        if ($isDemo && $this->repository->countByCompanyId($companyId) >= $maxProducts) {
            throw new Exception("Límite de prueba alcanzado: Has registrado el máximo permitido de {$maxProducts} productos para la versión de demostración.");
        }

        // Check duplicate SKU
        if ($this->repository->findBySku($data['sku'])) {
            throw new Exception("El SKU '{$data['sku']}' ya existe.");
        }

        $formattedData = $this->mapInputToDb($data);

        $newId = $this->repository->create($formattedData);
        if ($newId) {
            return $newId;
        }
        throw new Exception("Error al crear el producto en base de datos.");
    }

    public function updateProduct($id, $data) {
        if (!$this->repository->findById($id)) {
            throw new Exception("Producto no encontrado.");
        }

        $this->validateProductData($data);

        // Note: Ideally check if SKU changed and conflicts, but skipping for simplicity as per original logic

        $formattedData = $this->mapInputToDb($data);

        if ($this->repository->update($id, $formattedData)) {
            return true;
        }
        throw new Exception("Error al actualizar el producto.");
    }

    public function deleteProduct($id) {
        if (!$this->repository->softDelete($id)) {
            throw new Exception("Error al eliminar el producto.");
        }
        return true;
    }

    public function restoreProduct($id) {
        if (!$this->repository->restore($id)) {
            throw new Exception("Error al restaurar el producto.");
        }
        return true;
    }

    private function validateProductData($data) {
        if (empty($data['sku']) || empty($data['name']) || !isset($data['price']) || !isset($data['stock'])) {
            throw new Exception("Datos incompletos. SKU, Nombre, Precio y Stock son obligatorios.");
        }
        if ($data['price'] < 0) throw new Exception("El precio no puede ser negativo.");
        if ($data['stock'] < 0) throw new Exception("El stock no puede ser negativo.");
    }

    private function mapInputToDb($input) {
        return [
            'sku' => trim($input['sku']),
            'nombre' => trim($input['name']),
            'descripcion' => trim($input['description'] ?? ''),
            'precio' => floatval($input['price']),
            'stock' => intval($input['stock']),
            'min_stock' => intval($input['min_stock'] ?? 0),
            'estado' => $input['status'] ?? 'activo',
            'marca_id' => !empty($input['marca_id']) ? intval($input['marca_id']) : null,
            'categoria' => !empty($input['categoria']) ? intval($input['categoria']) : null,
            'proveedor_id' => !empty($input['proveedor_id']) ? intval($input['proveedor_id']) : null,
            'modelo' => trim($input['modelo'] ?? ''),
            'color' => trim($input['color'] ?? ''),
            'tamanio' => trim($input['tamanio'] ?? ''),
            'medidas' => trim($input['medidas'] ?? ''),
            'peso_kg' => !empty($input['peso_kg']) ? floatval($input['peso_kg']) : null,
            'material' => trim($input['material'] ?? ''),
            'unidad_venta' => trim($input['unidad_venta'] ?? ''),
            'tipo_producto' => $input['tipo_producto'] ?? 'fisico',
            'maneja_stock' => isset($input['maneja_stock']) ? ($input['maneja_stock'] ? 1 : 0) : 1,
            'producto_padre_id' => !empty($input['producto_padre_id']) ? intval($input['producto_padre_id']) : null,
            'es_kit_virtual' => isset($input['es_kit_virtual']) ? ($input['es_kit_virtual'] ? 1 : 0) : 0,
            'variante_atributo' => trim($input['variante_atributo'] ?? '')
        ];
    }
}
