<?php

namespace App\Models;

use App\Core\Database;
use PDO;
use Exception;

class AuditoriaModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAll(int $limit = 100): array
    {
        $stmt = $this->db->prepare("
            SELECT a.*, u.nombre as usuario_nombre 
            FROM auditoria_logs a
            LEFT JOIN usuarios u ON a.usuario_id = u.id
            ORDER BY a.timestamp DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function getByTabla(string $tabla, int $limit = 100): array
    {
        $stmt = $this->db->prepare("
            SELECT a.*, u.nombre as usuario_nombre 
            FROM auditoria_logs a
            LEFT JOIN usuarios u ON a.usuario_id = u.id
            WHERE a.tabla_afectada = :tabla
            ORDER BY a.timestamp DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':tabla', $tabla, PDO::PARAM_STR);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }
}
