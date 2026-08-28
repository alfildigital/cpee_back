<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;
use Exception;

class RolPermisoModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /** Roles del sistema con cantidad de permisos y de usuarios vinculados. */
    public function getAll(): array
    {
        $stmt = $this->db->query("
            SELECT r.id, r.nombre, r.descripcion,
                   COALESCE((SELECT COUNT(*) FROM rol_permisos rp WHERE rp.rol_id = r.id), 0) AS permisos_count,
                   COALESCE((SELECT COUNT(*) FROM usuario_roles ur WHERE ur.rol_id = r.id), 0) AS usuarios_count
            FROM roles r
            ORDER BY r.nombre ASC
        ");
        return $stmt->fetchAll();
    }

    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM roles WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch();

        if ($result) {
            // Permisos asignados (ids)
            $permStmt = $this->db->prepare("SELECT permiso_id FROM rol_permisos WHERE rol_id = :id");
            $permStmt->execute([':id' => $id]);
            $result['permisos'] = $permStmt->fetchAll(PDO::FETCH_COLUMN) ?: [];
        }

        return $result ?: null;
    }

    /** Catálogo de permisos. */
    public function getAllPermisos(): array
    {
        return $this->db->query("SELECT * FROM permisos ORDER BY nombre ASC")->fetchAll();
    }

    public function getPermisoById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM permisos WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function createPermiso(array $data): int
    {
        try {
            $stmt = $this->db->prepare("INSERT INTO permisos (nombre, descripcion) VALUES (:nombre, :descripcion) RETURNING id");
            $stmt->execute([
                ':nombre' => $data['nombre'],
                ':descripcion' => $data['descripcion'] ?? null,
            ]);
            return (int)$stmt->fetchColumn();
        } catch (Exception $e) {
            throw new Exception("Error al crear permiso: " . $e->getMessage());
        }
    }

    public function updatePermiso(int $id, array $data): bool
    {
        try {
            $stmt = $this->db->prepare("UPDATE permisos SET nombre = :nombre, descripcion = :descripcion WHERE id = :id");
            return $stmt->execute([
                ':id' => $id,
                ':nombre' => $data['nombre'],
                ':descripcion' => $data['descripcion'] ?? null,
            ]);
        } catch (Exception $e) {
            throw new Exception("Error al actualizar permiso: " . $e->getMessage());
        }
    }

    public function deletePermiso(int $id): bool
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM permisos WHERE id = :id");
            return $stmt->execute([':id' => $id]);
        } catch (Exception $e) {
            throw new Exception("Error al eliminar permiso: " . $e->getMessage());
        }
    }

    public function create(array $data, array $permisosIds): int
    {
        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare("
                INSERT INTO roles (nombre, descripcion)
                VALUES (:nombre, :descripcion)
                RETURNING id
            ");
            $stmt->execute([
                ':nombre' => $data['nombre'],
                ':descripcion' => $data['descripcion'] ?? null,
            ]);
            $id = (int)$stmt->fetchColumn();

            $this->asignarPermisos($id, $permisosIds);

            $this->db->commit();
            return $id;
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw new Exception("Error al crear rol: " . $e->getMessage());
        }
    }

    public function update(int $id, array $data, array $permisosIds): bool
    {
        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare("UPDATE roles SET nombre = :nombre, descripcion = :descripcion WHERE id = :id");
            $stmt->execute([
                ':id' => $id,
                ':nombre' => $data['nombre'],
                ':descripcion' => $data['descripcion'] ?? null,
            ]);

            // Actualizar permisos (borrar y reinsertar)
            $this->db->prepare("DELETE FROM rol_permisos WHERE rol_id = :id")->execute([':id' => $id]);
            $this->asignarPermisos($id, $permisosIds);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw new Exception("Error al actualizar rol: " . $e->getMessage());
        }
    }

    public function delete(int $id): bool
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM roles WHERE id = :id");
            return $stmt->execute([':id' => $id]);
        } catch (Exception $e) {
            throw new Exception("Error al eliminar rol: " . $e->getMessage());
        }
    }

    private function asignarPermisos(int $rolId, array $permisosIds): void
    {
        if (empty($permisosIds)) {
            return;
        }
        $stmt = $this->db->prepare("INSERT INTO rol_permisos (rol_id, permiso_id) VALUES (:rol_id, :permiso_id)");
        foreach ($permisosIds as $permisoId) {
            $stmt->execute([':rol_id' => $rolId, ':permiso_id' => (int)$permisoId]);
        }
    }
}
