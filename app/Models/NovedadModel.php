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

    /** Todas las novedades qque esten publicadas, más recientes primero. */
    public function getAll(): array
    {
        $stmt = $this->db->query("
            SELECT n.*
            FROM novedades n
            WHERE n.publicado = true
            ORDER BY n.fecha_publicacion DESC, n.id DESC
        ");
        return $stmt->fetchAll();
    }

    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT n.*
            FROM novedades n
            WHERE n.publicado = true
            AND n.id = :id
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

    public function getPaginated(int $limit, int $offset, ?string $q = null, ?bool $publicada = null): array
    {
        $sql = "
            SELECT n.*
            FROM novedades n
            WHERE n.publicado = true
        ";
        $params = [];
        $condiciones = [];

        if ($q !== null) {
            $condiciones[] = "(n.titulo ILIKE :q OR n.contenido ILIKE :q2)";
            $params[':q'] = '%' . $q . '%';
            $params[':q2'] = '%' . $q . '%';
        }

        if ($publicada !== null) {
            $condiciones[] = "n.publicado = :publicado";
            $params[':publicado'] = $publicada ? 1 : 0;
        }

        if ($condiciones !== []) {
            $sql .= " WHERE " . implode(' AND ', $condiciones);
        }

        $sql .= " ORDER BY n.fecha_publicacion DESC, n.id DESC LIMIT :limit OFFSET :offset";
        $params[':limit'] = $limit;
        $params[':offset'] = $offset;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function count(?string $q = null, ?bool $publicada = null): int
    {
        $sql = "SELECT COUNT(*) FROM novedades n WHERE n.publicado = true";
        $params = [];
        $condiciones = [];

        if ($q !== null) {
            $condiciones[] = "(n.titulo ILIKE :q OR n.contenido ILIKE :q2)";
            $params[':q'] = '%' . $q . '%';
            $params[':q2'] = '%' . $q . '%';
        }

        if ($publicada !== null) {
            $condiciones[] = "n.publicado = :publicado";
            $params[':publicado'] = $publicada ? 1 : 0;
        }

        if ($condiciones !== []) {
            $sql .= " WHERE " . implode(' AND ', $condiciones);
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    /** Roles del sistema disponibles para dirigir novedades. */
    public function getAllRoles(): array
    {
        $stmt = $this->db->query("SELECT * FROM roles ORDER BY nombre ASC");
        return $stmt->fetchAll();
    }

    public function create(array $data): int
    {

        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare("
                INSERT INTO novedades (usuario_id, titulo, contenido, publicado, fecha_publicacion,
                                       archivo_nombre, archivo_ruta, archivo_tipo, archivo_tamano,
                                       img_nombre, img_ruta, img_tipo, img_tamano, usuario_abm)
                VALUES (:usuario_id, :titulo, :contenido, :publicado, :fecha_publicacion,
                        :archivo_nombre, :archivo_ruta, :archivo_tipo, :archivo_tamano,
                        :img_nombre, :img_ruta, :img_tipo, :img_tamano, :usuario_abm)
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
                ':img_nombre' => $data['img_nombre'] ?? null,
                ':img_ruta' => $data['img_ruta'] ?? null,
                ':img_tipo' => $data['img_tipo'] ?? null,
                ':img_tamano' => $data['img_tamano'] ?? null,
                ':usuario_abm' => $data['usuario_abm'] ?? null,
            ]);
            $id = (int)$stmt->fetchColumn();

            // $this->asignarRoles($id, $roles);

            $this->db->commit();
            return $id;
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    /** Actualiza una novedad */
    public function update(int $id, array $data): bool
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
                    archivo_tamano = :archivo_tamano,
                    img_nombre = :img_nombre,
                    img_ruta = :img_ruta,
                    img_tipo = :img_tipo,
                    img_tamano = :img_tamano,
                    usuario_abm = :usuario_abm,
                    updated_at = CURRENT_TIMESTAMP
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
                ':img_nombre' => $data['img_nombre'] ?? null,
                ':img_ruta' => $data['img_ruta'] ?? null,
                ':img_tipo' => $data['img_tipo'] ?? null,
                ':img_tamano' => $data['img_tamano'] ?? null,
                ':usuario_abm' => $data['usuario_abm'] ?? null,
            ]);

            $del = $this->db->prepare("DELETE FROM novedad_roles WHERE novedad_id = :id");
            $del->execute([':id' => $id]);
            //$this->asignarRoles($id, $roles);

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
}
