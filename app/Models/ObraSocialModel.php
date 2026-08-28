<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;
use Exception;

class ObraSocialModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAll(): array
    {
        return $this->db->query("SELECT * FROM obras_sociales ORDER BY nombre ASC")->fetchAll();
    }

    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM obras_sociales WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO obras_sociales (nombre, descripcion, telefono, correo, url_sitio_web, logo)
            VALUES (:nombre, :descripcion, :telefono, :correo, :url_sitio_web, :logo)
            RETURNING id
        ");
        $stmt->execute([
            ':nombre' => $data['nombre'],
            ':descripcion' => $data['descripcion'] ?? null,
            ':telefono' => $data['telefono'] ?? null,
            ':correo' => $data['correo'] ?? null,
            ':url_sitio_web' => $data['url_sitio_web'] ?? null,
            ':logo' => $data['logo'] ?? null,
        ]);
        return (int)$stmt->fetchColumn();
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare("
            UPDATE obras_sociales SET
                nombre = :nombre,
                descripcion = :descripcion,
                telefono = :telefono,
                correo = :correo,
                url_sitio_web = :url_sitio_web,
                logo = :logo,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = :id
        ");
        return $stmt->execute([
            ':id' => $id,
            ':nombre' => $data['nombre'],
            ':descripcion' => $data['descripcion'] ?? null,
            ':telefono' => $data['telefono'] ?? null,
            ':correo' => $data['correo'] ?? null,
            ':url_sitio_web' => $data['url_sitio_web'] ?? null,
            ':logo' => $data['logo'] ?? null,
        ]);
    }

    public function delete(int $id): bool
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM obras_sociales WHERE id = :id");
            return $stmt->execute([':id' => $id]);
        } catch (Exception $e) {
            throw new Exception("Error al eliminar obra social: " . $e->getMessage());
        }
    }
}
