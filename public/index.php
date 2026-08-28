<?php

// Root path definition
define('ROOT_PATH', dirname(__DIR__));

// Simple Autoloader for 'App\' namespace
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = ROOT_PATH . '/app/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return; // namespace doesn't match
    }

    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

// Cargar variables de entorno desde .env (getenv, $_ENV, $_SERVER)
\App\Core\Env::load(ROOT_PATH . '/.env');

// Start session securely
\App\Core\Security::startSession();

// Basic Routing Logic
$url = isset($_GET['url']) ? rtrim($_GET['url'], '/') : '';
$urlParts = explode('/', $url);

$controllerName = 'usuarios';
$actionName = 'index';

if (!empty($urlParts[0])) {
    $controllerName = $urlParts[0];
}

if (isset($urlParts[1]) && !empty($urlParts[1])) {
    $actionName = $urlParts[1];
}

// ------------------------------------------------------------------
// Ruteo por Clean URLs: /modulo/accion/parametro
//   Ejemplo: /usuarios/edit/5   ->   \App\Controllers\UsuariosController->edit('5')
//   Ejemplo: /caja/index        ->   \App\Controllers\CajaController->index()
// La URL llega vía 'url' desde public/.htaccess, sin archivos físicos.
// ------------------------------------------------------------------
$url = isset($_GET['url']) ? rtrim((string)$_GET['url'], '/') : '';
$urlParts = $url === '' ? [] : explode('/', $url);

$controllerName = $urlParts[0] ?? 'usuarios';
$actionName = $urlParts[1] ?? 'index';

// Sanitizar: sólo caracteres alfanuméricos y guión bajo
// Sanitizar: sólo caracteres alfanuméricos, guiones y guión bajo
$controllerName = preg_replace('/[^a-zA-Z0-9_-]/', '', $controllerName);
$actionName = preg_replace('/[^a-zA-Z0-9_]/', '', $actionName);

// Convertir 'obras-sociales' en 'ObrasSociales' (kebab-case -> PascalCase)
$controllerName = implode('', array_map('ucfirst', preg_split('/[-_]+/', $controllerName)));

// Convert url like 'profesionales' to 'ProfesionalesController'
$controllerClass = '\\App\\Controllers\\' . $controllerName . 'Controller';

// Módulos públicos que no requieren autenticación
$publicControllers = ['login'];

// Guard de autenticación: proteger todos los módulos excepto los públicos
if (!in_array($controllerName, $publicControllers, true) && empty($_SESSION['usuario_id'])) {
    header('Location: /cpee/login');
    exit;
}

if (class_exists($controllerClass)) {
    $controller = new $controllerClass();

    if (method_exists($controller, $actionName)) {
        // Invoca el método pasándole los parámetros de ruta restantes.
        //   /usuarios/editar/5      -> editar('5')
        //   /caja/index/2025-01/31  -> index('2025-01-01', '2025-01-31')
        call_user_func_array([$controller, $actionName], array_slice($urlParts, 2));
    } else {
        // Fallback to index if method not found
        if (method_exists($controller, 'index')) {
            call_user_func_array([$controller, 'index'], array_slice($urlParts, 2));
        } else {
            http_response_code(404);
            echo "<h1>404 Not Found - Action no existe</h1>";
        }
    }
} else {
    // If controller doesn't exist, redirect or fallback to default
    if ($controllerName !== 'usuarios') {
        header('Location: /cpee/usuarios');
        exit;
    } else {
        http_response_code(404);
        echo "<h1>404 Not Found - Controlador no existe</h1>";
    }
}
