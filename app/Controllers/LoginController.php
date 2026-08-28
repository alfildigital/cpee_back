<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Security;
use App\Models\UsuarioModel;
use Exception;

class LoginController
{
    // GET /login
    public function index(): void
    {
        Security::startSession();

        // Si ya está logueado, ir directo al panel
        if (Security::isLoggedIn()) {
            header('Location: /cpee/dashboard');
            exit;
        }

        $flash = Security::getFlash();
        $csrf_token = Security::generateCSRFToken();

        require_once __DIR__ . '/../Views/auth/login.php';
    }

    // POST /login/autenticar
    public function autenticar(): void
    {
        Security::startSession();

        if (Security::isLoggedIn()) {
            header('Location: /cpee/dashboard');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /cpee/login');
            exit;
        }

        if (!Security::validateCSRFToken($_POST['csrf_token'] ?? null)) {
            Security::flash('danger', 'La sesión expiró. Intente nuevamente.');
            header('Location: /cpee/login');
            exit;
        }

        $email = trim((string)($_POST['email'] ?? ''));
        $password = (string)($_POST['password'] ?? '');

        try {
            $model = new UsuarioModel();
            $usuario = $model->autenticar($email, $password);

            if (!$usuario) {
                Security::logAudit(null, 'LOGIN', 'usuarios', null, null, ['email' => $email, 'resultado' => 'fallo']);
                Security::flash('danger', 'Credenciales inválidas o usuario inactivo.');
                header('Location: /cpee/login');
                exit;
            }

            Security::setSessionUser($usuario);
            Security::logAudit($usuario['id'], 'LOGIN', 'usuarios', $usuario['id'], null, ['email' => $email, 'resultado' => 'ok']);

            header('Location: /cpee/dashboard');
            exit;
        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            Security::flash('danger', 'Ocurrió un error al autenticar. Intente nuevamente.');
            header('Location: /cpee/login');
            exit;
        }
    }

    // GET /login/salir
    public function salir(): void
    {
        Security::logout();
        header('Location: /cpee/login');
        exit;
    }
}