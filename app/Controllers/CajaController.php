<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Security;
use App\Core\Upload;
use App\Models\MovimientoCajaModel;
use App\Models\ProfesionalModel;
use Exception;

class CajaController extends BaseController
{
    // GET /caja
    // GET /caja/index/{desde}/{hasta}   (fechas YYYY-MM-DD)
    public function index(?string $desde = null, ?string $hasta = null): void
    {
        $this->requireLogin();

        $desde = $desde ?? '';
        $hasta = $hasta ?? '';

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $desde) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $hasta)) {
            $desde = date('Y-m-01');
            $hasta = date('Y-m-t');
        }

        $model = new MovimientoCajaModel();
        $movimientos = $model->obtenerMovimientosPorFecha($desde, $hasta);

        $totalIngresos = 0;
        $totalEgresos = 0;

        foreach ($movimientos as $mov) {
            if ($mov['tipo'] === 'Ingreso') {
                $totalIngresos += (float)$mov['monto_total'];
            } else {
                $totalEgresos += (float)$mov['monto_total'];
            }
        }
        $saldo = $totalIngresos - $totalEgresos;

        $this->render('caja/index', 'Caja y Tesorería - CPEE', [
            'movimientos' => $movimientos,
            'desde' => $desde,
            'hasta' => $hasta,
            'totalIngresos' => $totalIngresos,
            'totalEgresos' => $totalEgresos,
            'saldo' => $saldo,
        ]);
    }

    // GET /caja/crear[/{tipo}/{profesional_id}]
    public function crear(?string $tipo = null, ?string $profesionalId = null): void
    {
        $this->requireLogin();

        $profModel = new ProfesionalModel();

        $this->render('caja/create', 'Nuevo Movimiento de Caja - CPEE', [
            'profesionales' => $profModel->getAll(),
            'tipoPreseleccionado' => $tipo,
            'profesionalPreseleccionado' => (int)($profesionalId ?? 0),
            'csrf_token' => Security::generateCSRFToken()
        ]);
    }

    // POST /caja/guardar
    public function guardar(): void
    {
        $this->requireLogin();
        $this->requirePost();
        $this->requireCsrf();

        try {
            $datosLimpios = Security::sanitizeInput($_POST);

            $montoNeto = (float)($datosLimpios['monto_neto'] ?? 0);
            $iva = (float)($datosLimpios['iva'] ?? 0);

            if ($montoNeto <= 0) {
                throw new Exception("El monto debe ser mayor a cero.");
            }

            // Procesar documento adjunto (PDF o imagen) — opcional
            $archivo = Upload::store($_FILES['archivo'] ?? []);

            $datosMovimiento = [
                'usuario_id' => $this->getCurrentUserId(),
                'usuario_abm' => $this->getCurrentUserAmb(),
                'profesional_id' => empty($datosLimpios['profesional_id']) ? null : (int)$datosLimpios['profesional_id'],
                'tipo' => $datosLimpios['tipo'],
                'concepto' => $datosLimpios['concepto'],
                'tipo_comprobante' => $datosLimpios['tipo_comprobante'] ?? null,
                'punto_venta' => $datosLimpios['punto_venta'] ?? null,
                'nro_comprobante' => $datosLimpios['nro_comprobante'] ?? null,
                'cuit' => $datosLimpios['cuit'] ?? null,
                'monto_neto' => $montoNeto,
                'iva' => $iva,
                'monto_total' => $montoNeto + $iva,
                'archivo_nombre' => $archivo['nombre'] ?? null,
                'archivo_ruta' => $archivo['ruta'] ?? null,
                'archivo_tipo' => $archivo['tipo'] ?? null,
                'archivo_tamano' => $archivo['tamano'] ?? null,
            ];

            $model = new MovimientoCajaModel();
            $id = $model->registrarMovimiento($datosMovimiento);

            Security::logAudit(
                $this->getCurrentUserId(),
                'INSERT',
                'caja_movimientos',
                $id,
                null,
                $datosMovimiento
            );

            Security::flash('success', 'Movimiento de caja registrado correctamente.');
            $this->redirect('/cpee/caja');
        } catch (Exception $e) {
            Security::flash('danger', $e->getMessage());
            $this->redirect('/cpee/caja/crear');
        }
    }

    // GET /caja/descargar/{id}   -> sirve el documento adjunto del movimiento
    public function descargar(?string $id = null): void
    {
        $this->requireLogin();

        $id = (int)($id ?? 0);
        if ($id <= 0) {
            $this->redirect('/cpee/caja');
        }

        $model = new MovimientoCajaModel();
        $archivo = $model->getById($id);

        if (!$archivo || empty($archivo['archivo_ruta'])) {
            Security::flash('danger', 'El movimiento no posee documento adjunto.');
            $this->redirect('/cpee/caja');
        }

        $rutaAbsoluta = ROOT_PATH . '/' . $archivo['archivo_ruta'];
        if (!is_file($rutaAbsoluta)) {
            Security::flash('danger', 'El archivo adjunto no existe en el servidor.');
            $this->redirect('/cpee/caja');
        }

        $nombreDescarga = !empty($archivo['archivo_nombre'])
            ? $archivo['archivo_nombre']
            : basename($archivo['archivo_ruta']);

        header('Content-Type: ' . ($archivo['archivo_tipo'] ?: 'application/octet-stream'));
        header('Content-Disposition: inline; filename="' . basename($nombreDescarga) . '"');
        header('Content-Length: ' . (string)@filesize($rutaAbsoluta));
        readfile($rutaAbsoluta);
        exit;
    }
}