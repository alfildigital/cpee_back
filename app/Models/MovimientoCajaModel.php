<?php

namespace App\Models;

use App\Core\Database;
use PDO;
use Exception;

class MovimientoCajaModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Registra un nuevo movimiento de caja con transacciones PDO
     */
    public function registrarMovimiento(array $datos): int
    {
        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare("
                INSERT INTO caja_movimientos 
                (usuario_id, profesional_id, tipo, concepto, tipo_comprobante, punto_venta, nro_comprobante, cuit, monto_neto, iva, monto_total, archivo_nombre, archivo_ruta, archivo_tipo, archivo_tamano, usuario_abm)
                VALUES (:usuario_id, :profesional_id, :tipo, :concepto, :tipo_comprobante, :punto_venta, :nro_comprobante, :cuit, :monto_neto, :iva, :monto_total, :archivo_nombre, :archivo_ruta, :archivo_tipo, :archivo_tamano, :usuario_abm)
                RETURNING id
            ");

            $stmt->execute([
                ':usuario_id' => $datos['usuario_id'],
                ':profesional_id' => $datos['profesional_id'] ?? null,
                ':tipo' => $datos['tipo'],
                ':concepto' => $datos['concepto'],
                ':tipo_comprobante' => $datos['tipo_comprobante'] ?? null,
                ':punto_venta' => $datos['punto_venta'] ?? null,
                ':nro_comprobante' => $datos['nro_comprobante'] ?? null,
                ':cuit' => $datos['cuit'] ?? null,
                ':monto_neto' => $datos['monto_neto'],
                ':iva' => $datos['iva'] ?? 0,
                ':monto_total' => $datos['monto_total'],
                ':archivo_nombre' => $datos['archivo_nombre'] ?? null,
                ':archivo_ruta' => $datos['archivo_ruta'] ?? null,
                ':archivo_tipo' => $datos['archivo_tipo'] ?? null,
                ':archivo_tamano' => $datos['archivo_tamano'] ?? null,
                ':usuario_abm' => $datos['usuario_abm'] ?? null
            ]);

            $id = $stmt->fetchColumn();

            $this->db->commit();
            return (int)$id;
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw new Exception("Error al registrar movimiento: " . $e->getMessage());
        }
    }

    public function obtenerMovimientosPorFecha(string $fechaInicio, string $fechaFin): array
    {
        $stmt = $this->db->prepare("
            SELECT cm.*, u.nombre as usuario, p.nombre as prof_nombre, p.apellido as prof_apellido 
            FROM caja_movimientos cm
            LEFT JOIN usuarios u ON cm.usuario_id = u.id
            LEFT JOIN profesionales p ON cm.profesional_id = p.id
            WHERE cm.fecha_movimiento >= :inicio AND cm.fecha_movimiento <= :fin
            ORDER BY cm.fecha_movimiento DESC
        ");
        $stmt->execute([':inicio' => $fechaInicio, ':fin' => $fechaFin . ' 23:59:59']);
        return $stmt->fetchAll();
    }

    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT cm.*, u.nombre as usuario
            FROM caja_movimientos cm
            LEFT JOIN usuarios u ON cm.usuario_id = u.id
            WHERE cm.id = :id
        ");
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }
}
