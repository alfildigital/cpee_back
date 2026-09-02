<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;
use Exception;

class ProfesionalModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAll(): array
    {
        $stmt = $this->db->query("SELECT * FROM profesionales ORDER BY apellido ASC, nombre ASC");
        return $stmt->fetchAll();
    }

    private function buildWhere(?string $q = null, ?string $estado = null): array
    {
        $where = [];
        $params = [];

        if ($q !== null && trim($q) !== '') {
            $where[] = "(nombre ILIKE :q OR apellido ILIKE :q OR DNI ILIKE :q OR nro_matricula ILIKE :q OR observaciones ILIKE :q)";
            $params[':q'] = '%' . trim($q) . '%';
        }

        if ($estado !== null && $estado !== '') {
            $where[] = "estado = :estado";
            $params[':estado'] = $estado;
        }

        return [$where, $params];
    }

    public function getPaginated(int $limit, int $offset, ?string $q = null, ?string $estado = null): array
    {
        [$where, $params] = $this->buildWhere($q, $estado);

        $sql = "SELECT * FROM profesionales";
        if ($where !== []) {
            $sql .= " WHERE " . implode(' AND ', $where);
        }
        $sql .= " ORDER BY apellido ASC, nombre ASC LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        if (isset($params[':q'])) {
            $stmt->bindValue(':q', $params[':q'], PDO::PARAM_STR);
        }
        if (isset($params[':estado'])) {
            $stmt->bindValue(':estado', $params[':estado'], PDO::PARAM_STR);
        }
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function count(?string $q = null, ?string $estado = null): int
    {
        [$where, $params] = $this->buildWhere($q, $estado);

        $sql = "SELECT COUNT(*) FROM profesionales";
        if ($where !== []) {
            $sql .= " WHERE " . implode(' AND ', $where);
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM profesionales WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO profesionales (nro_matricula, DNI, nombre, apellido, email, telefono, localidad, direccion, estado, fecha_matriculacion, observaciones, foto, usuario_abm)
            VALUES (:nro_matricula, :dni, :nombre, :apellido, :email, :telefono, :localidad, :direccion, :estado, :fecha_matriculacion, :observaciones, :foto, :usuario_abm)
            RETURNING id
        ");
        $stmt->execute([
            ':nro_matricula' => $data['nro_matricula'],
            ':dni' => $data['DNI'],
            ':nombre' => $data['nombre'],
            ':apellido' => $data['apellido'],
            ':email' => $data['email'] ?? null,
            ':telefono' => $data['telefono'] ?? null,
            ':localidad' => $data['localidad'] ?? null,
            ':direccion' => $data['direccion'] ?? null,
            ':estado' => $data['estado'] ?? 'Activa',
            ':fecha_matriculacion' => $data['fecha_matriculacion'],
            ':observaciones' => $data['observaciones'] ?? null,
            ':foto' => $data['foto'] ?? null,
            ':usuario_abm' => $data['usuario_abm'] ?? null
        ]);
        return (int)$stmt->fetchColumn();
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare("
            UPDATE profesionales SET 
                nro_matricula = :nro_matricula, 
                DNI = :dni, 
                nombre = :nombre, 
                apellido = :apellido, 
                email = :email, 
                telefono = :telefono, 
                localidad = :localidad,
                direccion = :direccion,
                estado = :estado, 
                fecha_matriculacion = :fecha_matriculacion, 
                observaciones = :observaciones,
                foto = :foto,
                usuario_abm = :usuario_abm,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = :id
        ");
        return $stmt->execute([
            ':id' => $id,
            ':nro_matricula' => $data['nro_matricula'],
            ':dni' => $data['DNI'],
            ':nombre' => $data['nombre'],
            ':apellido' => $data['apellido'],
            ':email' => $data['email'] ?? null,
            ':telefono' => $data['telefono'] ?? null,
            ':localidad' => $data['localidad'] ?? null,
            ':direccion' => $data['direccion'] ?? null,
            ':estado' => $data['estado'] ?? 'Activa',
            ':fecha_matriculacion' => $data['fecha_matriculacion'],
            ':observaciones' => $data['observaciones'] ?? null,
            ':foto' => $data['foto'] ?? null,
            ':usuario_abm' => $data['usuario_abm'] ?? null
        ]);
    }

    public function delete(int $id): bool
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM profesionales WHERE id = :id");
            return $stmt->execute([':id' => $id]);
        } catch (Exception $e) {
            throw new Exception("Error al eliminar matriculado: " . $e->getMessage());
        }
    }
}
