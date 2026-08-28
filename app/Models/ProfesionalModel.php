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
            INSERT INTO profesionales (nro_matricula, DNI, nombre, apellido, email, telefono, localidad, direccion, estado, fecha_matriculacion, legajo, foto)
            VALUES (:nro_matricula, :dni, :nombre, :apellido, :email, :telefono, :localidad, :direccion, :estado, :fecha_matriculacion, :legajo, :foto)
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
            ':legajo' => $data['legajo'] ?? null,
            ':foto' => $data['foto'] ?? null
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
                legajo = :legajo,
                foto = :foto,
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
            ':legajo' => $data['legajo'] ?? null,
            ':foto' => $data['foto'] ?? null
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
