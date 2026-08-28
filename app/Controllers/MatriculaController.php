<?php

namespace App\Controllers;

use App\Core\Security;
use App\Models\MovimientoCajaModel;
use Exception;

class MatriculaController
{

    // Simulate Middleware Check
    private function checkPermissions(string $permisoRequerido): void
    {
        Security::startSession();
        // Here you would check DB for $_SESSION['user_id'] roles/permissions
        // if (!hasPermission($permisoRequerido)) { throw new Exception("Acceso Denegado"); }
        if (empty($_SESSION['usuario_id'])) {
            die("Acceso Denegado: Inicie sesión");
        }
    }

    public function asentarPago(): void
    {
        $this->checkPermissions('registrar_pagos');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // CSRF Protection
            if (!Security::validateCSRFToken($_POST['csrf_token'] ?? null)) {
                die("Error: Token CSRF Inválido.");
            }

            // Sanitization
            $datosLimpios = Security::sanitizeInput($_POST);

            // Validation (example)
            if (empty($datosLimpios['monto_neto']) || !is_numeric($datosLimpios['monto_neto'])) {
                die("Error: Monto inválido.");
            }

            $monto_neto = (float)$datosLimpios['monto_neto'];
            $iva = (float)($datosLimpios['iva'] ?? 0);
            $monto_total = $monto_neto + $iva;

            $modelo = new MovimientoCajaModel();

            $datosMovimiento = [
                'usuario_id' => $_SESSION['usuario_id'],
                'profesional_id' => $datosLimpios['profesional_id'] ?? null,
                'tipo' => 'Ingreso',
                'concepto' => 'Pago de Matrícula - ' . ($datosLimpios['periodo'] ?? 'General'),
                'monto_neto' => $monto_neto,
                'iva' => $iva,
                'monto_total' => $monto_total
            ];

            try {
                $idInsertado = $modelo->registrarMovimiento($datosMovimiento);

                // Audit Log Activity
                Security::logAudit(
                    $_SESSION['usuario_id'],
                    'INSERT',
                    'caja_movimientos',
                    $idInsertado,
                    null, // No previous data for INSERT
                    $datosMovimiento
                );

                // Redirect on success (Pattern PRG - Post/Redirect/Get)
                header("Location: /cpee/matriculas/pagos?success=1");
                exit;
            } catch (Exception $e) {
                // Log and show safe error 
                error_log($e->getMessage());
                die("Ocurrió un error al procesar el pago. Por favor intente más tarde.");
            }
        } else {
            // Render View (GET Request)
            $csrf_token = Security::generateCSRFToken();
            // load view: require_once 'app/Views/matriculas/asentar_pago.php';
            echo "Formulario de Pago. CSRF Token: $csrf_token";
        }
    }
}
