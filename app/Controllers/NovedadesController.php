<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Security;
use App\Core\Upload;
use App\Models\NovedadModel;
use Exception;

class NovedadesController extends BaseController
{
    // GET /novedades
    public function index(): void
    {
        $this->requireLogin();
        $model = new NovedadModel();
        $novedades = $model->getAll();
        $this->render('novedades/index', 'Novedades - CPEE', [
            'novedades' => $novedades,
            'csrf_token' => Security::generateCSRFToken()
        ]);
    }

    // GET /novedades/ver/{id}
    public function ver(?string $id = null): void
    {
        $this->requireLogin();

        $id = (int)($id ?? 0);
        if ($id <= 0) {
            Security::flash('danger', 'ID de novedad no provisto.');
            $this->redirect('/cpee/novedades');
        }

        $model = new NovedadModel();
        $novedad = $model->getById($id);

        if (!$novedad) {
            Security::flash('danger', 'Novedad no encontrada.');
            $this->redirect('/cpee/novedades');
        }

        $this->render('novedades/show', 'Detalle de Novedad - CPEE', [
            'novedad' => $novedad,
            'roles' => $model->getAllRoles(),
            'csrf_token' => Security::generateCSRFToken()
        ]);
    }

    // GET /novedades/crear
    public function crear(): void
    {
        $this->requireLogin();
        $model = new NovedadModel();
        $roles = $model->getAllRoles();
        $this->render('novedades/create', 'Nueva Novedad - CPEE', [
            'roles' => $roles,
            'csrf_token' => Security::generateCSRFToken()
        ]);
    }

    // POST /novedades/guardar
    public function guardar(): void
    {
        $this->requireLogin();
        $this->requirePost();
        $this->requireCsrf();

        try {
            $datosLimpios = Security::sanitizeInput($_POST);
            $rolesNovedad = array_map(static fn($r) => (int)$r, (array)($_POST['roles'] ?? []));

            if (empty($datosLimpios['titulo']) || empty($datosLimpios['contenido'])) {
                throw new Exception("Título y Contenido son obligatorios");
            }

            $model = new NovedadModel();
            $archivo = Upload::store($_FILES['archivo'] ?? [], 'novedades');
            $id = $model->create($this->datosParaGuardar($datosLimpios, $archivo), $rolesNovedad);

            Security::logAudit(
                $this->getCurrentUserId(),
                'INSERT',
                'novedades',
                $id,
                null,
                $datosLimpios
            );

            Security::flash('success', 'Novedad creada correctamente.');
            $this->redirect('/cpee/novedades');
        } catch (Exception $e) {
            Security::flash('danger', $e->getMessage());
            $this->redirect('/cpee/novedades/crear');
        }
    }

    // GET /novedades/editar/{id}
    public function editar(?string $id = null): void
    {
        $this->requireLogin();

        $id = (int)($id ?? 0);
        if ($id <= 0) {
            Security::flash('danger', 'ID de novedad no provisto.');
            $this->redirect('/cpee/novedades');
        }

        $model = new NovedadModel();
        $novedad = $model->getById($id);

        if (!$novedad) {
            Security::flash('danger', 'Novedad no encontrada.');
            $this->redirect('/cpee/novedades');
        }

        $this->render('novedades/edit', 'Editar Novedad - CPEE', [
            'novedad' => $novedad,
            'roles' => $model->getAllRoles(),
            'csrf_token' => Security::generateCSRFToken()
        ]);
    }

    // POST /novedades/actualizar
    public function actualizar(): void
    {
        $this->requireLogin();
        $this->requirePost();
        $this->requireCsrf();

        try {
            $datosLimpios = Security::sanitizeInput($_POST);
            $id = (int)($datosLimpios['id'] ?? 0);
            $rolesNovedad = array_map(static fn($r) => (int)$r, (array)($_POST['roles'] ?? []));

            if ($id <= 0 || empty($datosLimpios['titulo']) || empty($datosLimpios['contenido'])) {
                throw new Exception("Título y Contenido son obligatorios");
            }

            $model = new NovedadModel();
            $datosAnteriores = $model->getById($id);
            if (!$datosAnteriores) {
                throw new Exception("Novedad no encontrada");
            }

            // PDF adjunto: nuevo / quitar / conservar
            $nuevaArchivo = Upload::store($_FILES['archivo'] ?? [], 'novedades');
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

            $model->update($id, $this->datosParaGuardar($datosLimpios, $archivo), $rolesNovedad);

            Security::logAudit(
                $this->getCurrentUserId(),
                'UPDATE',
                'novedades',
                $id,
                $datosAnteriores,
                $datosLimpios
            );

            Security::flash('success', 'Novedad actualizada correctamente.');
            $this->redirect('/cpee/novedades');
        } catch (Exception $e) {
            Security::flash('danger', $e->getMessage());
            $id = (int)($_POST['id'] ?? 0);
            $this->redirect('/cpee/novedades/editar/' . $id);
        }
    }

    // POST /novedades/eliminar
    public function eliminar(): void
    {
        $this->requireLogin();
        $this->requirePost();
        $this->requireCsrf();

        try {
            $id = (int)($_POST['id'] ?? 0);
            if ($id <= 0) {
                throw new Exception("ID de novedad inválido");
            }

            $model = new NovedadModel();
            $datosAnteriores = $model->getById($id);
            if (!$datosAnteriores) {
                throw new Exception("Novedad no encontrada");
            }

            if (!empty($datosAnteriores['archivo_ruta'])) {
                Upload::delete($datosAnteriores['archivo_ruta']);
            }

            $model->delete($id);

            Security::logAudit(
                $this->getCurrentUserId(),
                'DELETE',
                'novedades',
                $id,
                $datosAnteriores,
                null
            );

            Security::flash('success', 'Novedad eliminada correctamente.');
            $this->redirect('/cpee/novedades');
        } catch (Exception $e) {
            Security::flash('danger', $e->getMessage());
            $this->redirect('/cpee/novedades');
        }
    }

    /** Prepara conjunto de datos para create()/update() del modelo. */
    private function datosParaGuardar(array $datosLimpios, ?array $archivo = null): array
    {
        $fecha = isset($datosLimpios['fecha_publicacion']) && trim((string)$datosLimpios['fecha_publicacion']) !== ''
            ? $datosLimpios['fecha_publicacion']
            : date('Y-m-d H:i:s');

        $data = [
            'usuario_id' => $this->getCurrentUserId(),
            'titulo' => trim((string)$datosLimpios['titulo']),
            'contenido' => trim((string)$datosLimpios['contenido']),
            'publicado' => isset($_POST['publicado']),
            'fecha_publicacion' => $fecha,
        ];

        if ($archivo !== null) {
            $data['archivo_nombre'] = $archivo['nombre'] ?? null;
            $data['archivo_ruta']   = $archivo['ruta'] ?? null;
            $data['archivo_tipo']   = $archivo['tipo'] ?? null;
            $data['archivo_tamano'] = $archivo['tamano'] ?? null;
        }

        return $data;
    }

    // GET /novedades/descargar/{id}   -> sirve el PDF adjunto (con sesión)
    public function descargar(?string $id = null): void
    {
        $this->requireLogin();

        $id = (int)($id ?? 0);
        if ($id <= 0) {
            $this->redirect('/cpee/novedades');
        }

        $model = new NovedadModel();
        $novedad = $model->getById($id);

        if (!$novedad || empty($novedad['archivo_ruta'])) {
            Security::flash('danger', 'La novedad no posee documento adjunto.');
            $this->redirect('/cpee/novedades');
        }

        $rutaAbsoluta = ROOT_PATH . '/' . $novedad['archivo_ruta'];
        if (!is_file($rutaAbsoluta)) {
            Security::flash('danger', 'El archivo adjunto no existe en el servidor.');
            $this->redirect('/cpee/novedades');
        }

        $nombreDescarga = !empty($novedad['archivo_nombre'])
            ? $novedad['archivo_nombre']
            : basename($novedad['archivo_ruta']);

        header('Content-Type: ' . ($novedad['archivo_tipo'] ?: 'application/octet-stream'));
        header('Content-Disposition: inline; filename="' . basename($nombreDescarga) . '"');
        header('Content-Length: ' . (string)@filesize($rutaAbsoluta));
        readfile($rutaAbsoluta);
        exit;
    }
}
