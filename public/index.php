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

// ------------------------------------------------------------------
// CORS: permite peticiones cross-origin (p. ej. un frontend en dev server
// o en otro dominio) y responde al preflight OPTIONS.
// ------------------------------------------------------------------
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Authorization, Content-Type, X-API-Key');
header('Access-Control-Max-Age: 86400');

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// ------------------------------------------------------------------
// API REST /api/v1: se resuelve antes de iniciar sesión (stateless).
//   GET    /api/v1/profesionales        -> lista paginada
//   GET    /api/v1/profesionales/{id}   -> detalle
//   POST   /api/v1/profesionales        -> crear
//   PUT    /api/v1/profesionales/{id}   -> actualizar (PATCH también)
//   DELETE /api/v1/profesionales/{id}   -> eliminar
// Autenticación: header "Authorization: Bearer <API_API_KEY>".
// ------------------------------------------------------------------
$apiUrl = isset($_GET['url']) ? rtrim((string)$_GET['url'], '/') : '';
$apiParts = $apiUrl === '' ? [] : explode('/', $apiUrl);

if (($apiParts[0] ?? '') === 'api') {
    $apiVersion = strtolower($apiParts[1] ?? '');

    if ($apiVersion !== 'v1') {
        http_response_code(404);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(['success' => false, 'message' => 'Versión de API no soportada. Use /api/v1/...', 'errors' => []]);
        exit;
    }

    $apiResource = preg_replace('/[^a-zA-Z0-9_-]/', '', $apiParts[2] ?? '');
    $apiId = isset($apiParts[3]) && $apiParts[3] !== '' ? (int)$apiParts[3] : null;

    if ($apiResource === '') {
        http_response_code(404);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(['success' => false, 'message' => 'Recurso no especificado.', 'errors' => []]);
        exit;
    }

    // profesionales -> ProfesionalesController dentro de App\Controllers\Api
    $apiResourceCamel = implode('', array_map('ucfirst', preg_split('/[-_]+/', $apiResource)));
    $apiClass = '\\App\\Controllers\\Api\\' . $apiResourceCamel . 'Controller';

    if (!class_exists($apiClass)) {
        http_response_code(404);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(['success' => false, 'message' => 'Recurso de API no encontrado.', 'errors' => []]);
        exit;
    }

    $controller = new $apiClass();
    $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

    $apiAction = match (true) {
        $method === 'GET' && $apiId === null => 'index',
        $method === 'GET' => 'show',
        $method === 'POST' && $apiId === null => 'create',
        in_array($method, ['PUT', 'PATCH'], true) && $apiId !== null => 'update',
        $method === 'DELETE' && $apiId !== null => 'delete',
        default => null
    };

    if ($apiAction === null || !method_exists($controller, $apiAction)) {
        http_response_code(405);
        header('Allow: GET, POST, PUT, PATCH, DELETE');
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(['success' => false, 'message' => 'Método HTTP no permitido para este recurso.', 'errors' => []]);
        exit;
    }

    call_user_func_array([$controller, $apiAction], $apiId !== null ? [$apiId] : []);
    exit;
}

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

// Guard de autenticación: proteger todos los módulos excepto los públicos.
// $controllerName es PascalCase (login → Login), se compara en minúscula.
if (!in_array(strtolower($controllerName), $publicControllers, true) && empty($_SESSION['usuario_id'])) {
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
