<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Core\Security;

abstract class ApiController
{
    /**
     * Envuelve una respuesta exitosa en el formato estándar de la API:
     * { "success": true, "data": ..., "message": ..., "meta": ... }
     */
    protected function success(mixed $data = null, int $status = 200, string $message = '', array $meta = []): void
    {
        $payload = ['success' => true, 'data' => $data];
        if ($message !== '') {
            $payload['message'] = $message;
        }
        if ($meta !== []) {
            $payload['meta'] = $meta;
        }
        $this->json($payload, $status);
    }

    /**
     * Envuelve un error en el formato estándar de la API:
     * { "success": false, "message": "...", "errors": { campo: "..." } }
     */
    protected function error(string $message, int $status = 400, array $errors = []): void
    {
        $payload = [
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ];
        $this->json($payload, $status);
    }

    protected function json(array $payload, int $status): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=UTF-8');
        header('Cache-Control: no-store, no-cache, must-revalidate');
        header('Pragma: no-cache');
        header('X-Content-Type-Options: nosniff');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    /**
     * Lee y decodifica el cuerpo JSON de la petición.
     * Si el cuerpo no contiene JSON válido devuelve un arreglo vacío.
     */
    protected function jsonBody(): array
    {
        $raw = file_get_contents('php://input');
        if ($raw === false || trim($raw) === '') {
            return [];
        }
        $data = json_decode($raw, true);
        return is_array($data) ? $data : [];
    }

    /**
     * Autenticación por Bearer token. Comparación en tiempo constante para
     * evitar timing attacks. Si falla, responde 401 y termina la petición.
     */
    protected function requireAuth(): void
    {
        if (getenv('API_ENABLED') !== 'true' && getenv('API_ENABLED') !== '1') {
            $this->error('La API está deshabilitada.', 403);
        }

        $token = $this->readBearerToken();
        $expected = (string)(getenv('API_API_KEY') ?: '');

        if ($expected === '' || !is_string($token) || $token === '') {
            header('WWW-Authenticate: Bearer realm="CPEE API"');
            $this->error('No autorizado. Se requiere un token de acceso válido.', 401);
        }

        if (!hash_equals($expected, $token)) {
            header('WWW-Authenticate: Bearer realm="CPEE API"');
            $this->error('No autorizado. Se requiere un token de acceso válido.', 401);
        }
    }

    private function readBearerToken(): ?string
    {
        $header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';

        // Reescrituras de Apache u otros SAPIs
        if ($header === '') {
            $header = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';
        }

        if ($header === '' && function_exists('getallheaders')) {
            foreach (getallheaders() as $name => $value) {
                if (strtolower((string)$name) === 'authorization') {
                    $header = (string)$value;
                    break;
                }
            }
        }

        if (preg_match('/^Bearer\s+(.+)$/i', trim($header), $matches) !== 1) {
            return null;
        }

        return trim($matches[1]);
    }

    /**
     * Registra la operación en la auditoría. La API no posee sesión de
     * usuario, por lo que usuario_id queda en NULL.
     */
    protected function logAudit(string $accion, string $tabla, ?int $registroId = null, ?array $antes = null, ?array $despues = null): void
    {
        Security::logAudit(null, $accion, $tabla, $registroId, $antes, $despues);
    }
}