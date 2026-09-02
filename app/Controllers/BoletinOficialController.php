<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Security;
use App\Core\Upload;
use App\Models\BoletinOficialModel;
use Exception;

class BoletinOficialController extends BaseController
{
    // GET /boletin-oficial
    public function index(): void
    {
        $this->requireLogin();
        $model = new BoletinOficialModel();
        $boletines = $model->getAll();

        $this->render('boletin_oficial/index', 'Boletín Oficial - CPEE', [
            'boletines' => $boletines,
            'csrf_token' => Security::generateCSRFToken()
        ]);
    }

    // GET /boletin-oficial/ver/{id}
    public function ver(?string $id = null): void
    {
        $this->requireLogin();

        $id = (int)($id ?? 0);
        if ($id <= 0) {
            Security::flash('danger', 'ID de boletín no provisto.');
            $this->redirect('/cpee/boletin-oficial');
        }

        $model = new BoletinOficialModel();
        $boletin = $model->getById($id);

        if (!$boletin) {
            Security::flash('danger', 'Boletín no encontrado.');
            $this->redirect('/cpee/boletin-oficial');
        }

        $this->render('boletin_oficial/show', 'Detalle de Boletín - CPEE', ['boletin' => $boletin]);
    }

    // GET /boletin-oficial/crear
    public function crear(): void
    {
        $this->requireLogin();
        $this->render('boletin_oficial/create', 'Nuevo Boletín - CPEE', [
            'csrf_token' => Security::generateCSRFToken()
        ]);
    }

    // POST /boletin-oficial/guardar
    public function guardar(): void
    {
        $this->requireLogin();
        $this->requirePost();
        $this->requireCsrf();

        try {
            $datosLimpios = Security::sanitizeInput($_POST);

            if (empty($datosLimpios['titulo'])) {
                throw new Exception("El título del boletín es obligatorio");
            }

            $model = new BoletinOficialModel();
            $archivo = Upload::store($_FILES['archivo'] ?? [], 'boletin');
            $id = $model->create($this->datosParaGuardar($datosLimpios, $archivo));

            Security::logAudit(
                $this->getCurrentUserId(),
                'INSERT',
                'boletines_oficiales',
                $id,
                null,
                $datosLimpios
            );

            Security::flash('success', 'Boletín creado correctamente.');
            $this->redirect('/cpee/boletin-oficial');
        } catch (Exception $e) {
            Security::flash('danger', $e->getMessage());
            $this->redirect('/cpee/boletin-oficial/crear');
        }
    }

    // GET /boletin-oficial/editar/{id}
    public function editar(?string $id = null): void
    {
        $this->requireLogin();

        $id = (int)($id ?? 0);
        if ($id <= 0) {
            Security::flash('danger', 'ID de boletín no provisto.');
            $this->redirect('/cpee/boletin-oficial');
        }

        $model = new BoletinOficialModel();
        $boletin = $model->getById($id);

        if (!$boletin) {
            Security::flash('danger', 'Boletín no encontrado.');
            $this->redirect('/cpee/boletin-oficial');
        }

        $this->render('boletin_oficial/edit', 'Editar Boletín - CPEE', [
            'boletin' => $boletin,
            'csrf_token' => Security::generateCSRFToken()
        ]);
    }

    // POST /boletin-oficial/actualizar
    public function actualizar(): void
    {
        $this->requireLogin();
        $this->requirePost();
        $this->requireCsrf();

        try {
            $datosLimpios = Security::sanitizeInput($_POST);
            $id = (int)($datosLimpios['id'] ?? 0);

            if ($id <= 0 || empty($datosLimpios['titulo'])) {
                throw new Exception("El título del boletín es obligatorio");
            }

            $model = new BoletinOficialModel();
            $datosAnteriores = $model->getById($id);
            if (!$datosAnteriores) {
                throw new Exception("Boletín no encontrado");
            }

            // PDF adjunto: nuevo / quitar / conservar
            $nuevaArchivo = Upload::store($_FILES['archivo'] ?? [], 'boletin');
            $removerArchivo = isset($_POST['remover_archivo']) && $_POST['remover_archivo'] === '1';

            if ($nuevaArchivo) {
                if (!empty($datosAnteriores['archivo_ruta']) && $datosAnteriores['archivo_ruta'] !== $nuevaArchivo['ruta']) {
                    Upload::delete($datosAnteriores['archivo_ruta']);
                }
                $archivo = $nuevaArchivo;
            } elseif ($removerArchivo) {
                if (!empty($datosAnteriores['archivo_ruta'])) {
                    Upload::delete($datosAnteriores['archivo_ruta']);
                }
                $archivo = null;
            } else {
                $archivo = [
                    'nombre' => $datosAnteriores['archivo_nombre'] ?? null,
                    'ruta'   => $datosAnteriores['archivo_ruta'] ?? null,
                    'tipo'   => $datosAnteriores['archivo_tipo'] ?? null,
                    'tamano' => $datosAnteriores['archivo_tamano'] ?? null,
                ];
            }

            $model->update($id, $this->datosParaGuardar($datosLimpios, $archivo));

            Security::logAudit(
                $this->getCurrentUserId(),
                'UPDATE',
                'boletines_oficiales',
                $id,
                $datosAnteriores,
                $datosLimpios
            );

            Security::flash('success', 'Boletín actualizado correctamente.');
            $this->redirect('/cpee/boletin-oficial');
        } catch (Exception $e) {
            Security::flash('danger', $e->getMessage());
            $id = (int)($_POST['id'] ?? 0);
            $this->redirect('/cpee/boletin-oficial/editar/' . $id);
        }
    }

    // POST /boletin-oficial/eliminar
    public function eliminar(): void
    {
        $this->requireLogin();
        $this->requirePost();
        $this->requireCsrf();

        try {
            $id = (int)($_POST['id'] ?? 0);
            if ($id <= 0) {
                throw new Exception("ID de boletín inválido");
            }

            $model = new BoletinOficialModel();
            $datosAnteriores = $model->getById($id);
            if (!$datosAnteriores) {
                throw new Exception("Boletín no encontrado");
            }

            if (!empty($datosAnteriores['archivo_ruta'])) {
                Upload::delete($datosAnteriores['archivo_ruta']);
            }

            $model->delete($id);

            Security::logAudit(
                $this->getCurrentUserId(),
                'DELETE',
                'boletines_oficiales',
                $id,
                $datosAnteriores,
                null
            );

            Security::flash('success', 'Boletín eliminado correctamente.');
            $this->redirect('/cpee/boletin-oficial');
        } catch (Exception $e) {
            Security::flash('danger', $e->getMessage());
            $this->redirect('/cpee/boletin-oficial');
        }
    }

    /** Prepara el conjunto de datos para create()/update() del modelo. */
    private function datosParaGuardar(array $datosLimpios, ?array $archivo = null): array
    {
        $data = [
            'titulo' => trim((string)$datosLimpios['titulo']),
            'resumen' => trim((string)($datosLimpios['resumen'] ?? '')),
            'usuario_abm' => $this->getCurrentUserAmb(),
        ];

        if ($archivo !== null) {
            $data['archivo_nombre'] = $archivo['nombre'] ?? null;
            $data['archivo_ruta']   = $archivo['ruta'] ?? null;
            $data['archivo_tipo']   = $archivo['tipo'] ?? null;
            $data['archivo_tamano'] = $archivo['tamano'] ?? null;
        }

        return $data;
    }

    // GET /boletin-oficial/descargar/{id}   -> sirve el PDF adjunto (con sesión)
    public function descargar(?string $id = null): void
    {
        $this->requireLogin();

        $id = (int)($id ?? 0);
        if ($id <= 0) {
            $this->redirect('/cpee/boletin-oficial');
        }

        $model = new BoletinOficialModel();
        $boletin = $model->getById($id);

        if (!$boletin || empty($boletin['archivo_ruta'])) {
            Security::flash('danger', 'El boletín no posee documento adjunto.');
            $this->redirect('/cpee/boletin-oficial');
        }

        $rutaAbsoluta = ROOT_PATH . '/' . $boletin['archivo_ruta'];
        if (!is_file($rutaAbsoluta)) {
            Security::flash('danger', 'El archivo adjunto no existe en el servidor.');
            $this->redirect('/cpee/boletin-oficial');
        }

        $nombreDescarga = !empty($boletin['archivo_nombre'])
            ? $boletin['archivo_nombre']
            : basename($boletin['archivo_ruta']);

        header('Content-Type: ' . ($boletin['archivo_tipo'] ?: 'application/octet-stream'));
        header('Content-Disposition: inline; filename="' . basename($nombreDescarga) . '"');
        header('Content-Length: ' . (string)@filesize($rutaAbsoluta));
        readfile($rutaAbsoluta);
        exit;
    }
}
