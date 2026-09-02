<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;
use Exception;

/**
 * Acceso a datos de "boletines oficiales".
 * Cada boletín posee título, resumen y un archivo PDF adjunto
 * (almacenado en uploads/boletin/, fuera del Document Root).
 */
class BoletinOficialModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /** Todos los boletines, más recientes primero. */
    public function getAll(): array
    {
        $stmt = $this->db->query("
            SELECT * FROM boletines_oficiales
            ORDER BY created_at DESC, id DESC
        ");
        return $stmt->fetchAll();
    }

    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM boletines_oficiales WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function getPaginated(int $limit, int $offset, ?string $q = null): array
    {
        $sql = "SELECT * FROM boletines_oficiales";
        $params = [];

        if ($q !== null) {
            $sql .= " WHERE titulo ILIKE :q OR resumen ILIKE :q2";
            $params[':q'] = '%' . $q . '%';
            $params[':q2'] = '%' . $q . '%';
        }

        $sql .= " ORDER BY created_at DESC, id DESC LIMIT :limit OFFSET :offset";
        $params[':limit'] = $limit;
        $params[':offset'] = $offset;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function count(?string $q = null): int
    {
        $sql = "SELECT COUNT(*) FROM boletines_oficiales";
        $params = [];

        if ($q !== null) {
            $sql .= " WHERE titulo ILIKE :q OR resumen ILIKE :q2";
            $params[':q'] = '%' . $q . '%';
            $params[':q2'] = '%' . $q . '%';
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    public function create(array $data): int
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO boletines_oficiales (titulo, resumen, archivo_nombre, archivo_ruta, archivo_tipo, archivo_tamano, usuario_abm)
                VALUES (:titulo, :resumen, :archivo_nombre, :archivo_ruta, :archivo_tipo, :archivo_tamano, :usuario_abm)
                RETURNING id
            ");
            $stmt->execute([
                ':titulo' => $data['titulo'],
                ':resumen' => $data['resumen'] ?? null,
                ':archivo_nombre' => $data['archivo_nombre'] ?? null,
                ':archivo_ruta' => $data['archivo_ruta'] ?? null,
                ':archivo_tipo' => $data['archivo_tipo'] ?? null,
                ':archivo_tamano' => $data['archivo_tamano'] ?? null,
                ':usuario_abm' => $data['usuario_abm'] ?? null,
            ]);
            return (int)$stmt->fetchColumn();
        } catch (Exception $e) {
            throw new Exception("Error al crear el boletín: " . $e->getMessage());
        }
    }

    public function update(int $id, array $data): bool
    {
        try {
            $stmt = $this->db->prepare("
                UPDATE boletines_oficiales SET
                    titulo = :titulo,
                    resumen = :resumen,
                    archivo_nombre = :archivo_nombre,
                    archivo_ruta = :archivo_ruta,
                    archivo_tipo = :archivo_tipo,
                    archivo_tamano = :archivo_tamano,
                    usuario_abm = :usuario_abm,
                    updated_at = CURRENT_TIMESTAMP
                WHERE id = :id
            ");
            return $stmt->execute([
                ':id' => $id,
                ':titulo' => $data['titulo'],
                ':resumen' => $data['resumen'] ?? null,
                ':archivo_nombre' => $data['archivo_nombre'] ?? null,
                ':archivo_ruta' => $data['archivo_ruta'] ?? null,
                ':archivo_tipo' => $data['archivo_tipo'] ?? null,
                ':archivo_tamano' => $data['archivo_tamano'] ?? null,
                ':usuario_abm' => $data['usuario_abm'] ?? null,
            ]);
        } catch (Exception $e) {
            throw new Exception("Error al actualizar el boletín: " . $e->getMessage());
        }
    }

    public function delete(int $id): bool
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM boletines_oficiales WHERE id = :id");
            return $stmt->execute([':id' => $id]);
        } catch (Exception $e) {
            throw new Exception("Error al eliminar el boletín: " . $e->getMessage());
        }
    }
}
