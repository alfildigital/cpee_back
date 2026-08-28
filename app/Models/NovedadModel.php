<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;
use Exception;

/**
 * Acceso a datos de "novedades" (noticias / comunicados) y su vínculo
 * con los roles a los que va dirigida cada novedad (tabla novedad_roles).
 */
class NovedadModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /** Todas las novedades con autor y roles destinatarios, más recientes primero. */
    public function getAll(): array
    {
        $stmt = $this->db->query("
            SELECT n.*,
                   u.nombre AS autor,
                   COALESCE((
                     SELECT string_agg(r.nombre, ', ' ORDER BY r.nombre)
                     FROM novedad_roles nr
                     JOIN roles r ON r.id = nr.rol_id
                     WHERE nr.novedad_id = n.id
                   ), 'Todos') AS roles_nombres
            FROM novedades n
            LEFT JOIN usuarios u ON u.id = n.usuario_id
            ORDER BY n.fecha_publicacion DESC, n.id DESC
        ");
        return $stmt->fetchAll();
    }

    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT n.*, u.nombre AS autor
            FROM novedades n
            LEFT JOIN usuarios u ON u.id = n.usuario_id
            WHERE n.id = :id
        ");
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch();
        if (!$result) {
            return null;
        }

        $stmtRoles = $this->db->prepare("SELECT rol_id FROM novedad_roles WHERE novedad_id = :id");
        $stmtRoles->execute([':id' => $id]);
        $result['roles'] = array_map('intval', $stmtRoles->fetchAll(PDO::FETCH_COLUMN));

        return $result;
    }

    /** Roles del sistema disponibles para dirigir novedades. */
    public function getAllRoles(): array
    {
        $stmt = $this->db->query("SELECT * FROM roles ORDER BY nombre ASC");
        return $stmt->fetchAll();
    }

    /**
     * Crea una novedad y sus destinatarios de roles (transacción).
     *
     * @param array $roles lista de IDs de rol destinatarios (vacío = para todos)
     */
    public function create(array $data, array $roles): int
    {
        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare("
                INSERT INTO novedades (usuario_id, titulo, contenido, publicado, fecha_publicacion,
                                       archivo_nombre, archivo_ruta, archivo_tipo, archivo_tamano)
                VALUES (:usuario_id, :titulo, :contenido, :publicado, :fecha_publicacion,
                        :archivo_nombre, :archivo_ruta, :archivo_tipo, :archivo_tamano)
                RETURNING id
            ");
            $stmt->execute([
                ':usuario_id' => $data['usuario_id'] ?? null,
                ':titulo' => $data['titulo'],
                ':contenido' => $data['contenido'],
                ':publicado' => !empty($data['publicado']) ? 1 : 0,
                ':fecha_publicacion' => $data['fecha_publicacion'] ?? date('Y-m-d H:i:s'),
                ':archivo_nombre' => $data['archivo_nombre'] ?? null,
                ':archivo_ruta' => $data['archivo_ruta'] ?? null,
                ':archivo_tipo' => $data['archivo_tipo'] ?? null,
                ':archivo_tamano' => $data['archivo_tamano'] ?? null,
            ]);
            $id = (int)$stmt->fetchColumn();

            $this->asignarRoles($id, $roles);

            $this->db->commit();
            return $id;
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    /** Actualiza una novedad, reemplazando sus destinatarios de roles. */
    public function update(int $id, array $data, array $roles): bool
    {
        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare("
                UPDATE novedades SET
                    titulo = :titulo,
                    contenido = :contenido,
                    publicado = :publicado,
                    fecha_publicacion = :fecha_publicacion,
                    archivo_nombre = :archivo_nombre,
                    archivo_ruta = :archivo_ruta,
                    archivo_tipo = :archivo_tipo,
                    archivo_tamano = :archivo_tamano
                WHERE id = :id
            ");
            $stmt->execute([
                ':id' => $id,
                ':titulo' => $data['titulo'],
                ':contenido' => $data['contenido'],
                ':publicado' => !empty($data['publicado']) ? 1 : 0,
                ':fecha_publicacion' => $data['fecha_publicacion'] ?? date('Y-m-d H:i:s'),
                ':archivo_nombre' => $data['archivo_nombre'] ?? null,
                ':archivo_ruta' => $data['archivo_ruta'] ?? null,
                ':archivo_tipo' => $data['archivo_tipo'] ?? null,
                ':archivo_tamano' => $data['archivo_tamano'] ?? null,
            ]);

            $del = $this->db->prepare("DELETE FROM novedad_roles WHERE novedad_id = :id");
            $del->execute([':id' => $id]);
            $this->asignarRoles($id, $roles);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM novedades WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    /** Novedades publicadas (para la futura API pública / cartelera). */
    public function getPublicadas(): array
    {
        $stmt = $this->db->query("
            SELECT id, titulo, contenido, fecha_publicacion
            FROM novedades
            WHERE publicado = TRUE
            ORDER BY fecha_publicacion DESC, id DESC
        ");
        return $stmt->fetchAll();
    }

    private function asignarRoles(int $novedadId, array $roles): void
    {
        $roles = array_values(array_unique(array_map('intval', $roles)));
        $stmt = $this->db->prepare("INSERT INTO novedad_roles (novedad_id, rol_id) VALUES (:novedad_id, :rol_id)");
        foreach ($roles as $rolId) {
            if ($rolId <= 0) {
                continue;
            }
            $stmt->execute([':novedad_id' => $novedadId, ':rol_id' => $rolId]);
        }
    }
}
