<?php
require_once __DIR__ . '/../../db.php';

/**
 * CatalogRepository - Handles DB operations for master catalogs
 * Supports: categorias, marcas, colores, materiales
 */
class CatalogRepository {
    private $pdo;

    // Allowed catalog tables
    private $allowedCatalogs = ['categorias', 'marcas', 'colores', 'materiales'];

    public function __construct() {
        $this->pdo = getDbConnection();
    }

    public function isValidCatalog($catalog) {
        return in_array($catalog, $this->allowedCatalogs);
    }

    /**
     * Get all items from a catalog
     */
    public function findAll($catalog) {
        if (!$this->isValidCatalog($catalog)) {
            throw new Exception("Catálogo no válido: $catalog");
        }
        $stmt = $this->pdo->query("SELECT * FROM $catalog ORDER BY nombre");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get summary data for all catalogs (for dropdowns)
     */
    public function getSummary() {
        return [
            'marcas' => $this->pdo->query("SELECT id, nombre FROM marcas ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC),
            'categorias' => $this->pdo->query("SELECT id, nombre FROM categorias ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC),
            'proveedores' => $this->pdo->query("SELECT id, nombre FROM proveedores ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC),
            'colores' => $this->pdo->query("SELECT id, nombre FROM colores ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC),
            'materiales' => $this->pdo->query("SELECT id, nombre FROM materiales ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC)
        ];
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
        if (!$this->isValidCatalog($catalog)) {
            throw new Exception("Catálogo no válido: $catalog");
        }
        $stmt = $this->pdo->prepare("SELECT * FROM $catalog WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Create a new item
     */
    public function create($catalog, array $data) {
        switch ($catalog) {
            case 'categorias':
                $stmt = $this->pdo->prepare("INSERT INTO categorias (nombre, descripcion) VALUES (?, ?)");
                $stmt->execute([$data['nombre'], $data['descripcion'] ?? '']);
                break;
            case 'marcas':
                $stmt = $this->pdo->prepare("INSERT INTO marcas (nombre, descripcion, sitio_web) VALUES (?, ?, ?)");
                $stmt->execute([$data['nombre'], $data['descripcion'] ?? '', $data['sitio_web'] ?? '']);
                break;
            case 'colores':
                $stmt = $this->pdo->prepare("INSERT INTO colores (nombre, codigo_hex) VALUES (?, ?)");
                $stmt->execute([$data['nombre'], $data['codigo_hex'] ?? '#000000']);
                break;
            case 'materiales':
                $stmt = $this->pdo->prepare("INSERT INTO materiales (nombre) VALUES (?)");
                $stmt->execute([$data['nombre']]);
                break;
            default:
                throw new Exception("Catálogo no soportado para creación");
        }
        return $this->pdo->lastInsertId();
    }

    /**
     * Update an item
     */
    public function update($catalog, $id, array $data) {
        switch ($catalog) {
            case 'categorias':
                $stmt = $this->pdo->prepare("UPDATE categorias SET nombre = ?, descripcion = ? WHERE id = ?");
                return $stmt->execute([$data['nombre'], $data['descripcion'] ?? '', $id]);
            case 'marcas':
                $stmt = $this->pdo->prepare("UPDATE marcas SET nombre = ?, descripcion = ?, sitio_web = ? WHERE id = ?");
                return $stmt->execute([$data['nombre'], $data['descripcion'] ?? '', $data['sitio_web'] ?? '', $id]);
            case 'colores':
                $stmt = $this->pdo->prepare("UPDATE colores SET nombre = ?, codigo_hex = ? WHERE id = ?");
                return $stmt->execute([$data['nombre'], $data['codigo_hex'] ?? '#000000', $id]);
            case 'materiales':
                $stmt = $this->pdo->prepare("UPDATE materiales SET nombre = ? WHERE id = ?");
                return $stmt->execute([$data['nombre'], $id]);
            default:
                throw new Exception("Catálogo no soportado para actualización");
        }
    }

    /**
     * Delete an item
     */
    public function delete($catalog, $id) {
        if (!$this->isValidCatalog($catalog)) {
            throw new Exception("Catálogo no válido: $catalog");
        }
        $stmt = $this->pdo->prepare("DELETE FROM $catalog WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
