<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;
use Exception;

class UsuarioModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM usuarios WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => $email]);
        $result = $stmt->fetch();

        if ($result) {
            $rolesStmt = $this->db->prepare("
                SELECT r.nombre
                FROM usuario_roles ur
                JOIN roles r ON r.id = ur.rol_id
                WHERE ur.usuario_id = :id
            ");
            $rolesStmt->execute([':id' => $result['id']]);
            $result['roles'] = $rolesStmt->fetchAll(PDO::FETCH_COLUMN) ?: [];
        }

        return $result ?: null;
    }

    public function autenticar(string $email, string $password): ?array
    {
        $usuario = $this->findByEmail($email);

        if (!$usuario || !$usuario['activo']) {
            return null;
        }

        if (!password_verify($password, $usuario['password_hash'])) {
            return null;
        }

        // Algoritmo de hash objetivo (re-hash si es necesario)
        if (password_needs_rehash($usuario['password_hash'], PASSWORD_ARGON2ID)) {
            $stmt = $this->db->prepare("UPDATE usuarios SET password_hash = :hash WHERE id = :id");
            $stmt->execute([
                ':hash' => password_hash($password, PASSWORD_ARGON2ID),
                ':id' => $usuario['id']
            ]);
        }

        unset($usuario['password_hash']);
        return $usuario;
    }

    public function getAll(): array
    {
        $stmt = $this->db->query("
            SELECT u.*, s.nombre as sector_nombre 
            FROM usuarios u
            LEFT JOIN sectores s ON u.sector_id = s.id
            ORDER BY u.nombre ASC
        ");
        return $stmt->fetchAll();
    }

    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM usuarios WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch();

        if ($result) {
            // Get Roles
            $rolesStmt = $this->db->prepare("SELECT rol_id FROM usuario_roles WHERE usuario_id = :id");
            $rolesStmt->execute([':id' => $id]);
            $result['roles'] = $rolesStmt->fetchAll(PDO::FETCH_COLUMN) ?: [];
        }

        return $result ?: null;
    }

    public function getAllSectores(): array
    {
        return $this->db->query("SELECT * FROM sectores ORDER BY nombre ASC")->fetchAll();
    }

    public function getAllRoles(): array
    {
        return $this->db->query("SELECT * FROM roles ORDER BY nombre ASC")->fetchAll();
    }

    public function create(array $data, array $roles): int
    {
        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare("
                INSERT INTO usuarios (sector_id, nombre, email, password_hash, activo)
                VALUES (:sector_id, :nombre, :email, :password_hash, :activo)
                RETURNING id
            ");
            $stmt->execute([
                ':sector_id' => $data['sector_id'] ?? null,
                ':nombre' => $data['nombre'],
                ':email' => $data['email'],
                ':password_hash' => password_hash($data['password'], PASSWORD_ARGON2ID),
                ':activo' => $data['activo'] ?? true
            ]);

            $id = (int)$stmt->fetchColumn();

            // Insert roles
            $roleStmt = $this->db->prepare("INSERT INTO usuario_roles (usuario_id, rol_id) VALUES (:usuario_id, :rol_id)");
            foreach ($roles as $rol_id) {
                $roleStmt->execute([':usuario_id' => $id, ':rol_id' => (int)$rol_id]);
            }

            $this->db->commit();
            return $id;
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw new Exception("Error al crear usuario: " . $e->getMessage());
        }
    }

    public function update(int $id, array $data, array $roles): bool
    {
        try {
            $this->db->beginTransaction();

            $query = "UPDATE usuarios SET sector_id = :sector_id, nombre = :nombre, email = :email, activo = :activo, updated_at = CURRENT_TIMESTAMP";
            $params = [
                ':id' => $id,
                ':sector_id' => $data['sector_id'] ?? null,
                ':nombre' => $data['nombre'],
                ':email' => $data['email'],
                ':activo' => $data['activo']
            ];

            if (!empty($data['password'])) {
                $query .= ", password_hash = :password_hash";
                $params[':password_hash'] = password_hash($data['password'], PASSWORD_ARGON2ID);
            }
            $query .= " WHERE id = :id";

            $stmt = $this->db->prepare($query);
            $stmt->execute($params);

            // Update roles (delete all and re-insert)
            $this->db->prepare("DELETE FROM usuario_roles WHERE usuario_id = :id")->execute([':id' => $id]);
            $roleStmt = $this->db->prepare("INSERT INTO usuario_roles (usuario_id, rol_id) VALUES (:usuario_id, :rol_id)");
            foreach ($roles as $rol_id) {
                $roleStmt->execute([':usuario_id' => $id, ':rol_id' => (int)$rol_id]);
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw new Exception("Error al actualizar usuario: " . $e->getMessage());
        }
    }

    public function delete(int $id): bool
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM usuarios WHERE id = :id");
            return $stmt->execute([':id' => $id]);
        } catch (Exception $e) {
            throw new Exception("Error al eliminar usuario: " . $e->getMessage());
        }
    }
}
