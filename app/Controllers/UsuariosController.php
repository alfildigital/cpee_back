<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Security;
use App\Models\UsuarioModel;
use Exception;

class UsuariosController extends BaseController
{
    // GET /usuarios
    public function index(): void
    {
        $this->requireLogin();
        $model = new UsuarioModel();
        $usuarios = $model->getAll();

        $this->render('usuarios/index', 'Módulo Usuarios - CPEE', [
            'usuarios' => $usuarios,
            'csrf_token' => Security::generateCSRFToken()
        ]);
    }

    // GET /usuarios/ver/{id}
    public function ver(?string $id = null): void
    {
        $this->requireLogin();

        $id = (int)($id ?? 0);
        if ($id <= 0) {
            Security::flash('danger', 'ID de usuario no provisto.');
            $this->redirect('/cpee/usuarios');
        }

        $model = new UsuarioModel();
        $usuario = $model->getById($id);

        if (!$usuario) {
            Security::flash('danger', 'Usuario no encontrado.');
            $this->redirect('/cpee/usuarios');
        }

        $roles = $model->getAllRoles();
        $this->render('usuarios/show', 'Detalle de Usuario - CPEE', ['usuario' => $usuario, 'roles' => $roles]);
    }

    // GET /usuarios/crear
    public function crear(): void
    {
        $this->requireLogin();
        $model = new UsuarioModel();

        $this->render('usuarios/create', 'Nuevo Usuario - CPEE', [
            'sectores' => $model->getAllSectores(),
            'roles' => $model->getAllRoles(),
            'csrf_token' => Security::generateCSRFToken()
        ]);
    }

    // POST /usuarios/guardar
    public function guardar(): void
    {
        $this->requireLogin();
        $this->requirePost();
        $this->requireCsrf();

        try {
            $datosLimpios = Security::sanitizeInput($_POST);
            $rolesUsuario = array_map(static fn($r) => (int)$r, (array)($_POST['roles'] ?? []));

            if (empty($datosLimpios['nombre']) || empty($datosLimpios['email']) || empty($datosLimpios['password'])) {
                throw new Exception("Nombre, Email y Password son obligatorios");
            }

            $model = new UsuarioModel();
            $id = $model->create($datosLimpios, $rolesUsuario);

            Security::logAudit(
                $this->getCurrentUserId(),
                'INSERT',
                'usuarios',
                $id,
                null,
                $datosLimpios
            );

            Security::flash('success', 'Usuario creado correctamente.');
            $this->redirect('/cpee/usuarios');
        } catch (Exception $e) {
            Security::flash('danger', $e->getMessage());
            $this->redirect('/cpee/usuarios/crear');
        }
    }

    // GET /usuarios/editar/{id}
    public function editar(?string $id = null): void
    {
        $this->requireLogin();

        $id = (int)($id ?? 0);
        if ($id <= 0) {
            Security::flash('danger', 'ID de usuario no provisto.');
            $this->redirect('/cpee/usuarios');
        }

        $model = new UsuarioModel();
        $usuario = $model->getById($id);

        if (!$usuario) {
            Security::flash('danger', 'Usuario no encontrado.');
            $this->redirect('/cpee/usuarios');
        }

        $this->render('usuarios/edit', 'Editar Usuario - CPEE', [
            'usuario' => $usuario,
            'sectores' => $model->getAllSectores(),
            'allRoles' => $model->getAllRoles(),
            'csrf_token' => Security::generateCSRFToken()
        ]);
    }

    // POST /usuarios/actualizar
    public function actualizar(): void
    {
        $this->requireLogin();
        $this->requirePost();
        $this->requireCsrf();

        try {
            $datosLimpios = Security::sanitizeInput($_POST);
            $rolesUsuario = array_map(static fn($r) => (int)$r, (array)($_POST['roles'] ?? []));
            $id = (int)($datosLimpios['id'] ?? 0);

            if ($id <= 0 || empty($datosLimpios['nombre']) || empty($datosLimpios['email'])) {
                throw new Exception("Nombre y Email son obligatorios");
            }

            $datosLimpios['activo'] = isset($_POST['activo']) ? 1 : 0;

            if (empty($_POST['password'])) {
                unset($datosLimpios['password']);
            } else {
                $datosLimpios['password'] = $_POST['password'];
            }

            $model = new UsuarioModel();
            $datosAnteriores = $model->getById($id);
            if (!$datosAnteriores) {
                throw new Exception("Usuario no encontrado");
            }

            $model->update($id, $datosLimpios, $rolesUsuario);

            Security::logAudit(
                $this->getCurrentUserId(),
                'UPDATE',
                'usuarios',
                $id,
                $datosAnteriores,
                $datosLimpios
            );

            Security::flash('success', 'Usuario actualizado correctamente.');
            $this->redirect('/cpee/usuarios');
        } catch (Exception $e) {
            Security::flash('danger', $e->getMessage());
            $id = (int)($_POST['id'] ?? 0);
            $this->redirect('/cpee/usuarios/editar/' . $id);
        }
    }

    // POST /usuarios/eliminar
    public function eliminar(): void
    {
        $this->requireLogin();
        $this->requirePost();
        $this->requireCsrf();

        try {
            $id = (int)($_POST['id'] ?? 0);
            if ($id <= 0) {
                throw new Exception("ID de usuario inválido");
            }

            $model = new UsuarioModel();
            $datosAnteriores = $model->getById($id);

            if (!$datosAnteriores) {
                throw new Exception("Usuario no encontrado");
            }

            if ($id === $this->getCurrentUserId()) {
                throw new Exception("No puede eliminar su propio usuario");
            }

            $model->delete($id);

            Security::logAudit(
                $this->getCurrentUserId(),
                'DELETE',
                'usuarios',
                $id,
                $datosAnteriores,
                null
            );

            Security::flash('success', 'Usuario eliminado correctamente.');
            $this->redirect('/cpee/usuarios');
        } catch (Exception $e) {
            Security::flash('danger', $e->getMessage());
            $this->redirect('/cpee/usuarios');
        }
    }
}