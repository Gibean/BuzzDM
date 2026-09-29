<?php
require_once __DIR__ . '/BaseRepository.php';

/**
 * CatalogRepository - Handles DB operations for master catalogs
 * Supports: categorias, marcas, colores, materiales
 */
class CatalogRepository extends BaseRepository
{
    // Allowed catalog tables
    private $allowedCatalogs = ['categorias', 'marcas', 'colores', 'materiales'];

    public function isValidCatalog($catalog) {
        return in_array($catalog, $this->allowedCatalogs, true);
    }

    private function assertValidCatalog($catalog) {
        if (!$this->isValidCatalog($catalog)) {
            // El nombre de la tabla se interpola en el SQL, así que la lista
            // blanca es la única barrera contra inyección: si se relaja, esto
            // pasa a ser arbitrario.
            throw new Exception("Catálogo no válido: $catalog");
        }
    }

    /**
     * Get all items from a catalog
     */
    public function findAll($catalog) {
        $this->assertValidCatalog($catalog);

        $where = ['1=1'];
        $params = [];
        $this->scopeTenant($where, $params, '', "CatalogRepository::findAll($catalog)");

        $stmt = $this->pdo->prepare(
            "SELECT * FROM `$catalog` WHERE " . implode(' AND ', $where) . ' ORDER BY nombre'
        );
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get summary data for all catalogs (for dropdowns)
     */
    public function getSummary() {
        // Este método alimenta los desplegables del formulario de producto.
        // Sin filtro devolvía los catálogos de todas las empresas a la vez:
        // un cliente demo podía ver y asignar la marca o el proveedor que otra
        // empresa había creado, y el nombre de esos proveedores es información
        // comercial de un tercero.
        $resumen = [];
        foreach (['marcas', 'categorias', 'proveedores', 'colores', 'materiales'] as $tabla) {
            $where = ['1=1'];
            $params = [];
            $this->scopeTenant($where, $params, '', "CatalogRepository::getSummary($tabla)");

            if ($tabla === 'proveedores') {
                $where[] = 'deleted_at IS NULL';
            }

            $stmt = $this->pdo->prepare(
                "SELECT id, nombre FROM `$tabla` WHERE " . implode(' AND ', $where) . ' ORDER BY nombre'
            );
            $stmt->execute($params);
            $resumen[$tabla] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        return $resumen;
    }

    /**
     * Get all catalogs data for management page
     */
    public function getAllCatalogs() {
        return [
            'marcas' => $this->findAll('marcas'),
            'categorias' => $this->findAll('categorias'),
            'colores' => $this->findAll('colores'),
            'materiales' => $this->findAll('materiales')
        ];
    }

    /**
     * Find by ID
     */
    public function findById($catalog, $id) {
        $this->assertValidCatalog($catalog);

        $where = ['id = ?'];
        $params = [$id];
        $this->scopeTenant($where, $params, '', "CatalogRepository::findById($catalog, $id)");

        $stmt = $this->pdo->prepare(
            "SELECT * FROM `$catalog` WHERE " . implode(' AND ', $where)
        );
        $stmt->execute($params);
        return $this->assertTenantRow($stmt->fetch(PDO::FETCH_ASSOC) ?: null);
    }

    /**
     * Create a new item
     */
    public function create($catalog, array $data) {
        $this->assertValidCatalog($catalog);

        $columnas = match ($catalog) {
            'categorias' => ['nombre' => $data['nombre'], 'descripcion' => $data['descripcion'] ?? ''],
            'marcas' => [
                'nombre' => $data['nombre'],
                'descripcion' => $data['descripcion'] ?? '',
                'sitio_web' => $data['sitio_web'] ?? '',
            ],
            'colores' => [
                'nombre' => $data['nombre'],
                'codigo_hex' => $data['codigo_hex'] ?? '#000000',
            ],
            'materiales' => ['nombre' => $data['nombre']],
        };

        return $this->insertTenant($catalog, $columnas, "CatalogRepository::create($catalog)");
    }

    /**
     * Update an item
     */
    public function update($catalog, $id, array $data) {
        $this->assertValidCatalog($catalog);

        $columnas = match ($catalog) {
            'categorias' => ['nombre' => $data['nombre'], 'descripcion' => $data['descripcion'] ?? ''],
            'marcas' => [
                'nombre' => $data['nombre'],
                'descripcion' => $data['descripcion'] ?? '',
                'sitio_web' => $data['sitio_web'] ?? '',
            ],
            'colores' => [
                'nombre' => $data['nombre'],
                'codigo_hex' => $data['codigo_hex'] ?? '#000000',
            ],
            'materiales' => ['nombre' => $data['nombre']],
        };

        return $this->applyTenantUpdate(
            $catalog,
            $columnas,
            (int)$id,
            "CatalogRepository::update($catalog, $id)"
        );
    }

    /**
     * Delete an item
     *
     * Borrado físico, no lógico: es el comportamiento previo y las claves
     * foráneas de productos hacia catálogos están en ON DELETE SET NULL, así
     * que los productos no se pierden, pero sí quedan sin marca ni categoría.
     * Conviene una baja lógica en el futuro; mientras tanto lo que se corrige
     * aquí es que no se pueda borrar el catálogo de otra empresa.
     */
    public function delete($catalog, $id) {
        $this->assertValidCatalog($catalog);

        return $this->deleteTenant(
            $catalog,
            (int)$id,
            "CatalogRepository::delete($catalog, $id)"
        ) > 0;
    }
}
