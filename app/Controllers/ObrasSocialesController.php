<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Security;
use App\Core\Upload;
use App\Models\ObraSocialModel;
use Exception;

class ObrasSocialesController extends BaseController
{
    /** Extensiones permitidas para logo (solo PNG). */
    private const LOGO_MIME = ['image/png' => 'png'];

    // GET /obras-sociales
    public function index(): void
    {
        $this->requireLogin();
        $model = new ObraSocialModel();
        $obras = $model->getAll();

        $this->render('obras_sociales/index', 'Obras Sociales - CPEE', [
            'obras' => $obras,
            'csrf_token' => Security::generateCSRFToken()
        ]);
    }

    // GET /obras-sociales/ver/{id}
    public function ver(?string $id = null): void
    {
        $this->requireLogin();

        $id = (int)($id ?? 0);
        if ($id <= 0) {
            Security::flash('danger', 'ID de obra social no provisto.');
            $this->redirect('/cpee/obras-sociales');
        }

        $model = new ObraSocialModel();
        $obra = $model->getById($id);

        if (!$obra) {
            Security::flash('danger', 'Obra social no encontrada.');
            $this->redirect('/cpee/obras-sociales');
        }

        $this->render('obras_sociales/show', 'Detalle de Obra Social - CPEE', ['obra' => $obra]);
    }

    // GET /obras-sociales/crear
    public function crear(): void
    {
        $this->requireLogin();
        $this->render('obras_sociales/create', 'Nueva Obra Social - CPEE', [
            'csrf_token' => Security::generateCSRFToken()
        ]);
    }

    // POST /obras-sociales/guardar
    public function guardar(): void
    {
        $this->requireLogin();
        $this->requirePost();
        $this->requireCsrf();

        try {
            $datosLimpios = Security::sanitizeInput($_POST);

            if (empty($datosLimpios['nombre'])) {
                throw new Exception("El nombre de la obra social es obligatorio");
            }

            // Logo opcional (solo PNG)
            $logo = Upload::store($_FILES['logo'] ?? [], 'logos', self::LOGO_MIME);
            $datosLimpios['logo'] = $logo['ruta'] ?? null;
            $datosLimpios['usuario_abm'] = $this->getCurrentUserAmb();

            $model = new ObraSocialModel();
            $id = $model->create($datosLimpios);

            Security::logAudit(
                $this->getCurrentUserId(),
                'INSERT',
                'obras_sociales',
                $id,
                null,
                $datosLimpios
            );

            Security::flash('success', 'Obra social registrada correctamente.');
            $this->redirect('/cpee/obras-sociales');
        } catch (Exception $e) {
            Security::flash('danger', $e->getMessage());
            $this->redirect('/cpee/obras-sociales/crear');
        }
    }

    // GET /obras-sociales/editar/{id}
    public function editar(?string $id = null): void
    {
        $this->requireLogin();

        $id = (int)($id ?? 0);
        if ($id <= 0) {
            Security::flash('danger', 'ID de obra social no provisto.');
            $this->redirect('/cpee/obras-sociales');
        }

        $model = new ObraSocialModel();
        $obra = $model->getById($id);

        if (!$obra) {
            Security::flash('danger', 'Obra social no encontrada.');
            $this->redirect('/cpee/obras-sociales');
        }

        $this->render('obras_sociales/edit', 'Editar Obra Social - CPEE', [
            'obra' => $obra,
            'csrf_token' => Security::generateCSRFToken()
        ]);
    }

    // POST /obras-sociales/actualizar
    public function actualizar(): void
    {
        $this->requireLogin();
        $this->requirePost();
        $this->requireCsrf();

        try {
            $datosLimpios = Security::sanitizeInput($_POST);
            $id = (int)($datosLimpios['id'] ?? 0);

            if ($id <= 0 || empty($datosLimpios['nombre'])) {
                throw new Exception("El nombre de la obra social es obligatorio");
            }

            $model = new ObraSocialModel();
            $datosAnteriores = $model->getById($id);
            if (!$datosAnteriores) {
                throw new Exception("Obra social no encontrada");
            }

            // Logo: nueva subida / remover / conservar
            $nuevoLogo = Upload::store($_FILES['logo'] ?? [], 'logos', self::LOGO_MIME);
            $removerLogo = isset($_POST['remover_logo']) && $_POST['remover_logo'] === '1';

            if ($nuevoLogo) {
                if (!empty($datosAnteriores['logo']) && $datosAnteriores['logo'] !== $nuevoLogo['ruta']) {
                    Upload::delete($datosAnteriores['logo']);
                }
                $datosLimpios['logo'] = $nuevoLogo['ruta'];
            } elseif ($removerLogo) {
                if (!empty($datosAnteriores['logo'])) {
                    Upload::delete($datosAnteriores['logo']);
                }
                $datosLimpios['logo'] = null;
            } else {
                $datosLimpios['logo'] = $datosAnteriores['logo'] ?? null;
            }

            $datosLimpios['usuario_abm'] = $this->getCurrentUserAmb();

            $model->update($id, $datosLimpios);

            Security::logAudit(
                $this->getCurrentUserId(),
                'UPDATE',
                'obras_sociales',
                $id,
                $datosAnteriores,
                $datosLimpios
            );

            Security::flash('success', 'Obra social actualizada correctamente.');
            $this->redirect('/cpee/obras-sociales');
        } catch (Exception $e) {
            Security::flash('danger', $e->getMessage());
            $id = (int)($_POST['id'] ?? 0);
            $this->redirect('/cpee/obras-sociales/editar/' . $id);
        }
    }

    // POST /obras-sociales/eliminar
    public function eliminar(): void
    {
        $this->requireLogin();
        $this->requirePost();
        $this->requireCsrf();

        try {
            $id = (int)($_POST['id'] ?? 0);
            if ($id <= 0) {
                throw new Exception("ID de obra social inválido");
            }

            $model = new ObraSocialModel();
            $datosAnteriores = $model->getById($id);
            if (!$datosAnteriores) {
                throw new Exception("Obra social no encontrada");
            }

            if (!empty($datosAnteriores['logo'])) {
                Upload::delete($datosAnteriores['logo']);
            }

            $model->delete($id);

            Security::logAudit(
                $this->getCurrentUserId(),
                'DELETE',
                'obras_sociales',
                $id,
                $datosAnteriores,
                null
            );

            Security::flash('success', 'Obra social eliminada correctamente.');
            $this->redirect('/cpee/obras-sociales');
        } catch (Exception $e) {
            Security::flash('danger', $e->getMessage());
            $this->redirect('/cpee/obras-sociales');
        }
    }

    // GET /obras-sociales/logo/{id}   -> sirve el logo PNG (con sesión)
    public function logo(?string $id = null): void
    {
        $this->requireLogin();

        $id = (int)($id ?? 0);
        if ($id <= 0) {
            $this->redirect('/cpee/obras-sociales');
        }

        $model = new ObraSocialModel();
        $obra = $model->getById($id);

        if (!$obra || empty($obra['logo'])) {
            Security::flash('danger', 'La obra social no posee logo.');
            $this->redirect('/cpee/obras-sociales');
        }

        $rutaAbsoluta = ROOT_PATH . '/' . $obra['logo'];
        if (!is_file($rutaAbsoluta)) {
            Security::flash('danger', 'El archivo de logo no existe en el servidor.');
            $this->redirect('/cpee/obras-sociales');
        }

        header('Content-Type: image/png');
        header('Content-Disposition: inline; filename="' . basename($obra['logo']) . '"');
        header('Content-Length: ' . (string)filesize($rutaAbsoluta));
        header('Cache-Control: private, max-age=0, must-revalidate');
        readfile($rutaAbsoluta);
        exit;
    }
}
