<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Security;
use App\Models\RolPermisoModel;
use Exception;

class RolesController extends BaseController
{
    // GET /roles
    public function index(): void
    {
        $this->requireLogin();
        $model = new RolPermisoModel();
        $roles = $model->getAll();

        $this->render('roles/index', 'Roles y Permisos - CPEE', [
            'roles' => $roles,
            'csrf_token' => Security::generateCSRFToken()
        ]);
    }

    // GET /roles/ver/{id}
    public function ver(?string $id = null): void
    {
        $this->requireLogin();

        $id = (int)($id ?? 0);
        if ($id <= 0) {
            Security::flash('danger', 'ID de rol no provisto.');
            $this->redirect('/cpee/roles');
        }

        $model = new RolPermisoModel();
        $rol = $model->getById($id);

        if (!$rol) {
            Security::flash('danger', 'Rol no encontrado.');
            $this->redirect('/cpee/roles');
        }

        $this->render('roles/show', 'Detalle de Rol - CPEE', [
            'rol' => $rol,
            'permisos' => $model->getAllPermisos(),
            'csrf_token' => Security::generateCSRFToken()
        ]);
    }

    // GET /roles/crear
    public function crear(): void
    {
        $this->requireLogin();
        $model = new RolPermisoModel();

        $this->render('roles/create', 'Nuevo Rol - CPEE', [
            'permisos' => $model->getAllPermisos(),
            'csrf_token' => Security::generateCSRFToken()
        ]);
    }

    // POST /roles/guardar
    public function guardar(): void
    {
        $this->requireLogin();
        $this->requirePost();
        $this->requireCsrf();

        try {
            $datosLimpios = Security::sanitizeInput($_POST);
            $permisosRol = array_map(static fn($p) => (int)$p, (array)($_POST['permisos'] ?? []));

            if (empty($datosLimpios['nombre'])) {
                throw new Exception("El nombre del rol es obligatorio");
            }

            $model = new RolPermisoModel();
            $id = $model->create($this->datosParaGuardar($datosLimpios), $permisosRol);

            Security::logAudit(
                $this->getCurrentUserId(),
                'INSERT',
                'roles',
                $id,
                null,
                $datosLimpios
            );

            Security::flash('success', 'Rol creado correctamente.');
            $this->redirect('/cpee/roles');
        } catch (Exception $e) {
            Security::flash('danger', $e->getMessage());
            $this->redirect('/cpee/roles/crear');
        }
    }

    // GET /roles/editar/{id}
    public function editar(?string $id = null): void
    {
        $this->requireLogin();

        $id = (int)($id ?? 0);
        if ($id <= 0) {
            Security::flash('danger', 'ID de rol no provisto.');
            $this->redirect('/cpee/roles');
        }

        $model = new RolPermisoModel();
        $rol = $model->getById($id);

        if (!$rol) {
            Security::flash('danger', 'Rol no encontrado.');
            $this->redirect('/cpee/roles');
        }

        $this->render('roles/edit', 'Editar Rol - CPEE', [
            'rol' => $rol,
            'permisos' => $model->getAllPermisos(),
            'csrf_token' => Security::generateCSRFToken()
        ]);
    }

    // POST /roles/actualizar
    public function actualizar(): void
    {
        $this->requireLogin();
        $this->requirePost();
        $this->requireCsrf();

        try {
            $datosLimpios = Security::sanitizeInput($_POST);
            $permisosRol = array_map(static fn($p) => (int)$p, (array)($_POST['permisos'] ?? []));
            $id = (int)($datosLimpios['id'] ?? 0);

            if ($id <= 0 || empty($datosLimpios['nombre'])) {
                throw new Exception("El nombre del rol es obligatorio");
            }

            $model = new RolPermisoModel();
            $datosAnteriores = $model->getById($id);
            if (!$datosAnteriores) {
                throw new Exception("Rol no encontrado");
            }

            $model->update($id, $this->datosParaGuardar($datosLimpios), $permisosRol);

            Security::logAudit(
                $this->getCurrentUserId(),
                'UPDATE',
                'roles',
                $id,
                $datosAnteriores,
                $datosLimpios
            );

            Security::flash('success', 'Rol actualizado correctamente.');
            $this->redirect('/cpee/roles');
        } catch (Exception $e) {
            Security::flash('danger', $e->getMessage());
            $id = (int)($_POST['id'] ?? 0);
            $this->redirect('/cpee/roles/editar/' . $id);
        }
    }

    // POST /roles/eliminar
    public function eliminar(): void
    {
        $this->requireLogin();
        $this->requirePost();
        $this->requireCsrf();

        try {
            $id = (int)($_POST['id'] ?? 0);
            if ($id <= 0) {
                throw new Exception("ID de rol inválido");
            }

            $model = new RolPermisoModel();
            $datosAnteriores = $model->getById($id);
            if (!$datosAnteriores) {
                throw new Exception("Rol no encontrado");
            }

            $model->delete($id);

            Security::logAudit(
                $this->getCurrentUserId(),
                'DELETE',
                'roles',
                $id,
                $datosAnteriores,
                null
            );

            Security::flash('success', 'Rol eliminado correctamente.');
            $this->redirect('/cpee/roles');
        } catch (Exception $e) {
            Security::flash('danger', $e->getMessage());
            $this->redirect('/cpee/roles');
        }
    }

    // GET /roles/permisos  -> catálogo de permisos
    public function permisos(): void
    {
        $this->requireLogin();
        $model = new RolPermisoModel();
        $permisos = $model->getAllPermisos();

        $this->render('roles/permisos', 'Catálogo de Permisos - CPEE', [
            'permisos' => $permisos,
            'csrf_token' => Security::generateCSRFToken()
        ]);
    }

    // POST /roles/guardarPermiso
    public function guardarPermiso(): void
    {
        $this->requireLogin();
        $this->requirePost();
        $this->requireCsrf();

        try {
            $datosLimpios = Security::sanitizeInput($_POST);
            if (empty($datosLimpios['nombre'])) {
                throw new Exception("El nombre (clave) del permiso es obligatorio");
            }

            $model = new RolPermisoModel();
            $id = $model->createPermiso([
                'nombre' => trim((string)$datosLimpios['nombre']),
                'descripcion' => isset($datosLimpios['descripcion']) ? trim((string)$datosLimpios['descripcion']) : null,
                'usuario_abm' => $this->getCurrentUserAmb(),
            ]);

            Security::logAudit($this->getCurrentUserId(), 'INSERT', 'permisos', $id, null, $datosLimpios);
            Security::flash('success', 'Permiso creado correctamente.');
        } catch (Exception $e) {
            Security::flash('danger', $e->getMessage());
        }
        $this->redirect('/cpee/roles/permisos');
    }

    // POST /roles/actualizarPermiso
    public function actualizarPermiso(): void
    {
        $this->requireLogin();
        $this->requirePost();
        $this->requireCsrf();

        try {
            $datosLimpios = Security::sanitizeInput($_POST);
            $id = (int)($datosLimpios['id'] ?? 0);

            if ($id <= 0 || empty($datosLimpios['nombre'])) {
                throw new Exception("El nombre (clave) del permiso es obligatorio");
            }

            $model = new RolPermisoModel();
            $datosAnteriores = $model->getPermisoById($id);
            if (!$datosAnteriores) {
                throw new Exception("Permiso no encontrado");
            }

            $model->updatePermiso($id, [
                'nombre' => trim((string)$datosLimpios['nombre']),
                'descripcion' => isset($datosLimpios['descripcion']) ? trim((string)$datosLimpios['descripcion']) : null,
                'usuario_abm' => $this->getCurrentUserAmb(),
            ]);

            Security::logAudit($this->getCurrentUserId(), 'UPDATE', 'permisos', $id, $datosAnteriores, $datosLimpios);
            Security::flash('success', 'Permiso actualizado correctamente.');
        } catch (Exception $e) {
            Security::flash('danger', $e->getMessage());
        }
        $this->redirect('/cpee/roles/permisos');
    }

    // POST /roles/eliminarPermiso
    public function eliminarPermiso(): void
    {
        $this->requireLogin();
        $this->requirePost();
        $this->requireCsrf();

        try {
            $id = (int)($_POST['id'] ?? 0);
            if ($id <= 0) {
                throw new Exception("ID de permiso inválido");
            }

            $model = new RolPermisoModel();
            $datosAnteriores = $model->getPermisoById($id);
            if (!$datosAnteriores) {
                throw new Exception("Permiso no encontrado");
            }

            $model->deletePermiso($id);

            Security::logAudit($this->getCurrentUserId(), 'DELETE', 'permisos', $id, $datosAnteriores, null);
            Security::flash('success', 'Permiso eliminado correctamente.');
        } catch (Exception $e) {
            Security::flash('danger', $e->getMessage());
        }
        $this->redirect('/cpee/roles/permisos');
    }

    /** Prepara conjunto de datos para create()/update() del modelo. */
    private function datosParaGuardar(array $datosLimpios): array
    {
        return [
            'nombre' => trim((string)$datosLimpios['nombre']),
            'descripcion' => isset($datosLimpios['descripcion']) ? trim((string)$datosLimpios['descripcion']) : null,
            'usuario_abm' => $this->getCurrentUserAmb(),
        ];
    }
}
