<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Pdf;
use App\Core\Security;
use App\Core\Upload;
use App\Models\ProfesionalModel;
use Exception;

class ProfesionalesController extends BaseController
{
    // GET /profesionales
    public function index(): void
    {
        $this->requireLogin();
        $model = new ProfesionalModel();
        $profesionales = $model->getAll();

        $this->render('profesionales/index', 'Módulo Matriculados - CPEE', [
            'profesionales' => $profesionales,
            'csrf_token' => Security::generateCSRFToken()
        ]);
    }

    // GET /profesionales/ver/{id}
    public function ver(?string $id = null): void
    {
        $this->requireLogin();

        $id = (int)($id ?? 0);
        if ($id <= 0) {
            Security::flash('danger', 'ID de matriculado no provisto.');
            $this->redirect('/cpee/profesionales');
        }

        $model = new ProfesionalModel();
        $profesional = $model->getById($id);

        if (!$profesional) {
            Security::flash('danger', 'Matriculado no encontrado.');
            $this->redirect('/cpee/profesionales');
        }

        $this->render('profesionales/show', 'Detalle de Matriculado - CPEE', ['profesional' => $profesional]);
    }

    // GET /profesionales/crear
    public function crear(): void
    {
        $this->requireLogin();
        $this->render('profesionales/create', 'Nuevo Matriculado - CPEE', [
            'csrf_token' => Security::generateCSRFToken()
        ]);
    }

    // POST /profesionales/guardar
    public function guardar(): void
    {
        $this->requireLogin();
        $this->requirePost();
        $this->requireCsrf();

        try {
            $datosLimpios = Security::sanitizeInput($_POST);

            if (empty($datosLimpios['DNI']) || empty($datosLimpios['nro_matricula']) || empty($datosLimpios['fecha_matriculacion'])) {
                throw new Exception("DNI, Nro Matrícula y Fecha de Matriculación son obligatorios");
            }

            // Foto opcional
            $foto = Upload::storeImage($_FILES['foto'] ?? []);
            $datosLimpios['foto'] = $foto['ruta'] ?? null;

            $model = new ProfesionalModel();
            $id = $model->create($datosLimpios);

            Security::logAudit(
                $this->getCurrentUserId(),
                'INSERT',
                'profesionales',
                $id,
                null,
                $datosLimpios
            );

            Security::flash('success', 'Matriculado registrado correctamente.');
            $this->redirect('/cpee/profesionales');
        } catch (Exception $e) {
            Security::flash('danger', $e->getMessage());
            $this->redirect('/cpee/profesionales/crear');
        }
    }

    // GET /profesionales/editar/{id}
    public function editar(?string $id = null): void
    {
        $this->requireLogin();

        $id = (int)($id ?? 0);
        if ($id <= 0) {
            Security::flash('danger', 'ID de matriculado no provisto.');
            $this->redirect('/cpee/profesionales');
        }

        $model = new ProfesionalModel();
        $profesional = $model->getById($id);

        if (!$profesional) {
            Security::flash('danger', 'Matriculado no encontrado.');
            $this->redirect('/cpee/profesionales');
        }

        $this->render('profesionales/edit', 'Editar Matriculado - CPEE', [
            'profesional' => $profesional,
            'csrf_token' => Security::generateCSRFToken()
        ]);
    }

    // POST /profesionales/actualizar
    public function actualizar(): void
    {
        $this->requireLogin();
        $this->requirePost();
        $this->requireCsrf();

        try {
            $datosLimpios = Security::sanitizeInput($_POST);
            $id = (int)($datosLimpios['id'] ?? 0);

            if ($id <= 0 || empty($datosLimpios['DNI']) || empty($datosLimpios['nro_matricula']) || empty($datosLimpios['fecha_matriculacion'])) {
                throw new Exception("DNI, Nro Matrícula y Fecha de Matriculación son obligatorios");
            }

            $model = new ProfesionalModel();
            $datosAnteriores = $model->getById($id);

            if (!$datosAnteriores) {
                throw new Exception("Matriculado no encontrado");
            }

            // Foto: nueva subida / remover / conservar
            $nuevaFoto = Upload::storeImage($_FILES['foto'] ?? []);
            $removerFoto = isset($_POST['remover_foto']) && $_POST['remover_foto'] === '1';

            if ($nuevaFoto) {
                if (!empty($datosAnteriores['foto']) && $datosAnteriores['foto'] !== $nuevaFoto['ruta']) {
                    Upload::delete($datosAnteriores['foto']);
                }
                $datosLimpios['foto'] = $nuevaFoto['ruta'];
            } elseif ($removerFoto) {
                if (!empty($datosAnteriores['foto'])) {
                    Upload::delete($datosAnteriores['foto']);
                }
                $datosLimpios['foto'] = null;
            } else {
                $datosLimpios['foto'] = $datosAnteriores['foto'] ?? null;
            }

            $model->update($id, $datosLimpios);

            Security::logAudit(
                $this->getCurrentUserId(),
                'UPDATE',
                'profesionales',
                $id,
                $datosAnteriores,
                $datosLimpios
            );

            Security::flash('success', 'Matriculado actualizado correctamente.');
            $this->redirect('/cpee/profesionales');
        } catch (Exception $e) {
            Security::flash('danger', $e->getMessage());
            $id = (int)($_POST['id'] ?? 0);
            $this->redirect('/cpee/profesionales/editar/' . $id);
        }
    }

    // POST /profesionales/eliminar
    public function eliminar(): void
    {
        $this->requireLogin();
        $this->requirePost();
        $this->requireCsrf();

        try {
            $id = (int)($_POST['id'] ?? 0);
            if ($id <= 0) {
                throw new Exception("ID de matriculado inválido");
            }

            $model = new ProfesionalModel();
            $datosAnteriores = $model->getById($id);

            if (!$datosAnteriores) {
                throw new Exception("Matriculado no encontrado");
            }

            if (!empty($datosAnteriores['foto'])) {
                Upload::delete($datosAnteriores['foto']);
            }

            $model->delete($id);

            Security::logAudit(
                $this->getCurrentUserId(),
                'DELETE',
                'profesionales',
                $id,
                $datosAnteriores,
                null
            );

            Security::flash('success', 'Matriculado eliminado correctamente.');
            $this->redirect('/cpee/profesionales');
        } catch (Exception $e) {
            Security::flash('danger', $e->getMessage());
            $this->redirect('/cpee/profesionales');
        }
    }

    // GET /profesionales/carnet/{id}    -> genera el PDF carnet del matriculado
    public function carnet(?string $id = null): void
    {
        $this->requireLogin();

        $id = (int)($id ?? 0);
        if ($id <= 0) {
            Security::flash('danger', 'ID de matriculado no provisto.');
            $this->redirect('/cpee/profesionales');
        }

        $model = new ProfesionalModel();
        $profesional = $model->getById($id);

        if (!$profesional) {
            Security::flash('danger', 'Matriculado no encontrado.');
            $this->redirect('/cpee/profesionales');
        }

        Security::logAudit(
            $this->getCurrentUserId(),
            'GENERAR_CARNET',
            'profesionales',
            $id,
            null,
            ['nro_matricula' => $profesional['nro_matricula']]
        );

        // Dimensiones estandar carnet: 90 mm x 60 mm (sin margen de borde)
        $ancho = 255.12; // 90 mm
        $alto  = 170.08; // 60 mm

        $azul   = [16, 55, 110];
        $gris   = [110, 110, 110];

        $pdf = new Pdf(Pdf::ORIENT_HORIZONTAL, $ancho, $alto);

        // Fondo blanco (sin bordes)
        $pdf->setFillColor(255, 255, 255);
        $pdf->rect(0, 0, $ancho, $alto, 'F');

        // Banda institucional superior (a sangre)
        $pdf->setFillColor($azul[0], $azul[1], $azul[2]);
        $pdf->rect(0, 0, $ancho, 30, 'F');
        $pdf->setTextColor(255, 255, 255);
        $pdf->setFont('Helvetica', 'B', 8);
        $pdf->text($ancho / 2, 11, 'CPEE - COLEGIO DE PROFESIONALES', 'C');
        $pdf->setFont('Helvetica', '', 7);
        $pdf->text($ancho / 2, 22, 'Carnet de Matriculado', 'C');

        // Zona izquierda: foto
        $fotoX = 10;
        $fotoY = 40;
        $fotoW = 52;
        $fotoH = 66;
        $pdf->setFillColor(240, 245, 252);
        $pdf->rect($fotoX, $fotoY, $fotoW, $fotoH, 'F');
        $pdf->setDrawColor($azul[0], $azul[1], $azul[2]);
        $pdf->setLineWidth(0.8);
        $pdf->rect($fotoX, $fotoY, $fotoW, $fotoH, 'D');

        $rutaFoto = $profesional['foto'] ?? null;
        $fotoIncrustada = false;
        if ($rutaFoto) {
            $archivoFoto = ROOT_PATH . '/' . ltrim($rutaFoto, '/');
            $fotoIncrustada = $pdf->image($fotoX, $fotoY, $fotoW, $fotoH, $archivoFoto);
        }

        if (!$fotoIncrustada) {
            $pdf->setFont('', '', 8);
            $pdf->setTextColor($azul[0], $azul[1], $azul[2]);
            $pdf->text($fotoX + $fotoW / 2, $fotoY + $fotoH / 2 - 4, 'FOTO', 'C');
            $pdf->setFont('', '', 6);
            $pdf->text($fotoX + $fotoW / 2, $fotoY + $fotoH / 2 + 8, '(sin foto)', 'C');
        }

        // Zona derecha: datos
        $colX = $fotoX + $fotoW + 10; // 72
        $colW = $ancho - $colX - 8;   // hasta el borde derecho

        $nombreCompleto = $profesional['apellido'] . ', ' . $profesional['nombre'];
        $legajo = trim((string)($profesional['legajo'] ?? ''));
        if ($legajo === '') {
            $legajo = '-';
        } elseif ($this->strWidthPts($legajo, 7) > $colW) {
            $legajo = mb_substr($legajo, 0, 40, 'UTF-8') . '...';
        }

        $filaY = 40;
        $filaH = 27;

        $this->campoCarnet($pdf, $colX, $colW, $filaY, 'NOMBRE COMPLETO', $nombreCompleto, 7, 12);
        $this->campoCarnet($pdf, $colX, $colW, $filaY + $filaH, 'LEGAJO', $legajo, 7, 9);
        $this->campoCarnet($pdf, $colX, $colW, $filaY + $filaH * 2, 'MATRICULA', $profesional['nro_matricula'], 7, 11);

        // FECHA ALTA
        $yFecha = $filaY + $filaH * 3;
        $pdf->setFont('Helvetica', 'B', 6);
        $pdf->setTextColor($azul[0], $azul[1], $azul[2]);
        $pdf->text($colX, $yFecha, 'FECHA ALTA');
        $pdf->setFont('Helvetica', '', 10);
        $pdf->setTextColor(30, 30, 30);
        $pdf->text($colX, $yFecha + 11, $this->formatearFecha($profesional['fecha_matriculacion']));

        // Estado (badge coloreado a lo ancho)
        $estadoColor = [0, 150, 60];
        if ($profesional['estado'] === 'Suspendida') {
            $estadoColor = [230, 160, 0];
        } elseif ($profesional['estado'] === 'Inactiva') {
            $estadoColor = [200, 40, 40];
        }
        $estadoY = $filaY + $filaH * 4 + 2; // 150
        $pdf->setFillColor($estadoColor[0], $estadoColor[1], $estadoColor[2]);
        $pdf->rect(10, $estadoY, $ancho - 20, 14, 'F');
        $pdf->setTextColor(255, 255, 255);
        $pdf->setFont('Helvetica', 'B', 8);
        $pdf->text($ancho / 2, $estadoY + 9, mb_strtoupper($profesional['estado'], 'UTF-8'), 'C');

        // Pie informativo
        $pdf->setFont('Helvetica', '', 5);
        $pdf->setTextColor($gris[0], $gris[1], $gris[2]);
        $pdf->text($ancho / 2, $alto - 6, 'VALIDO SOLO CON FIRMA Y SELLO OFICIAL - Emitido: ' . date('d/m/Y'), 'C');

        if (ob_get_level()) {
            ob_end_clean();
        }
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="carnet-' . $profesional['nro_matricula'] . '.pdf"');
        header('Cache-Control: private, max-age=0, must-revalidate');
        header('Pragma: public');
        echo $pdf->output();
        exit;
    }

    private function campoCarnet(Pdf $pdf, float $x, float $colW, float $y, string $label, string $value, float $labelSize, float $valueSize): void
    {
        $pdf->setFont('Helvetica', 'B', $labelSize);
        $pdf->setTextColor(16, 55, 110);
        $pdf->text($x, $y, $label);
        $pdf->setFont('Helvetica', '', $valueSize);
        $pdf->setTextColor(30, 30, 30);
        $pdf->text($x, $y + 11, $this->truncar($value, $x, $colW, $valueSize));
        $pdf->setDrawColor(210, 215, 222);
        $pdf->setLineWidth(0.4);
        $pdf->line($x, $y + 22, $x + $colW, $y + 22);
    }

    private function truncar(string $texto, float $x, float $colW, float $size): string
    {
        if ($this->strWidthPts($texto, $size) <= $colW) {
            return $texto;
        }
        $elipsis = '...';
        $corte = mb_strlen($texto, 'UTF-8');
        while ($corte > 1 && $this->strWidthPts(mb_substr($texto, 0, $corte, 'UTF-8') . $elipsis, $size) > $colW) {
            $corte--;
        }
        return mb_substr($texto, 0, $corte, 'UTF-8') . $elipsis;
    }

    private function strWidthPts(string $txt, float $size): float
    {
        $anchos = [
            32 => 278, 65 => 667, 66 => 667, 67 => 722, 68 => 722, 69 => 667, 70 => 611,
            71 => 778, 72 => 722, 73 => 278, 74 => 500, 75 => 667, 76 => 556, 77 => 833,
            78 => 722, 79 => 778, 80 => 667, 81 => 778, 82 => 722, 83 => 667, 84 => 611,
            85 => 722, 86 => 667, 87 => 944, 88 => 667, 89 => 667, 90 => 611,
            97 => 556, 98 => 556, 99 => 500, 100 => 556, 101 => 556, 102 => 278, 103 => 556,
            104 => 556, 105 => 222, 106 => 222, 107 => 500, 108 => 222, 109 => 833, 110 => 556,
            111 => 556, 112 => 556, 113 => 556, 114 => 333, 115 => 500, 116 => 278, 117 => 556,
            118 => 500, 119 => 722, 120 => 500, 121 => 500, 122 => 500,
        ];
        $w = 0.0;
        for ($i = 0; $i < strlen($txt); $i++) {
            $w += $anchos[ord($txt[$i])] ?? 500;
        }
        return $w * $size / 1000;
    }

    private function formatearFecha(?string $fecha): string
    {
        if (empty($fecha)) {
            return '-';
        }
        $ts = strtotime($fecha);
        return $ts !== false ? date('d/m/Y', $ts) : (string)$fecha;
    }

    // GET /profesionales/foto/{id}   -> sirve la foto del matriculado (con sesión)
    public function foto(?string $id = null): void
    {
        $this->requireLogin();

        $id = (int)($id ?? 0);
        if ($id <= 0) {
            $this->redirect('/cpee/profesionales');
        }

        $model = new ProfesionalModel();
        $profesional = $model->getById($id);

        if (!$profesional || empty($profesional['foto'])) {
            Security::flash('danger', 'El matriculado no posee foto.');
            $this->redirect('/cpee/profesionales');
        }

        $rutaAbsoluta = ROOT_PATH . '/' . $profesional['foto'];
        if (!is_file($rutaAbsoluta)) {
            Security::flash('danger', 'El archivo de foto no existe en el servidor.');
            $this->redirect('/cpee/profesionales');
        }

        header('Content-Type: image/jpeg');
        header('Content-Disposition: inline; filename="' . basename($profesional['foto']) . '"');
        header('Content-Length: ' . (string)filesize($rutaAbsoluta));
        header('Cache-Control: private, max-age=0, must-revalidate');
        readfile($rutaAbsoluta);
        exit;
    }
}