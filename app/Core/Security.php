<?php

namespace App\Core;

use App\Core\Database;
use PDO;

class Security
{
    public static function startSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            $sessionName = getenv('SESSION_NAME') ?: 'SECURE_SESSION';
            $sessionLifetime = (int)(getenv('SESSION_LIFETIME') ?: 7200);

            session_set_cookie_params([
                'lifetime' => $sessionLifetime,
                'path' => '/',
                'domain' => '',
                'secure' => isset($_SERVER['HTTPS']), // Cookies seguras solo bajo HTTPS
                'httponly' => true,
                'samesite' => 'Strict'
            ]);

            session_name($sessionName);
            session_start();
        }
    }

    public static function regenerateSession(): void
    {
        session_regenerate_id(true);
    }

    public static function isLoggedIn(): bool
    {
        self::startSession();
        return !empty($_SESSION['usuario_id']);
    }

    public static function requireLogin(): void
    {
        if (!self::isLoggedIn()) {
            header('Location: /cpee/login');
            exit;
        }
    }

    public static function setSessionUser(array $usuario): void
    {
        self::startSession();
        $_SESSION['usuario_id'] = (int)($usuario['id'] ?? 0);
        $_SESSION['usuario_nombre'] = $usuario['nombre'] ?? '';
        $_SESSION['usuario_email'] = $usuario['email'] ?? '';
        $_SESSION['usuario_roles'] = $usuario['roles'] ?? [];
        // Prevenir session fixation
        self::regenerateSession();
    }

    public static function logout(): void
    {
        self::startSession();

        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();
    }

    // ------------------------------------------------------------------
    // Mensajes flash entre peticiones (URLs amigables sin ?success=?error=)
    // ------------------------------------------------------------------
    public static function flash(string $type, string $message): void
    {
        self::startSession();
        $_SESSION['flash'][] = [
            'type' => $type,
            'message' => $message,
        ];
    }

    public static function getFlash(): array
    {
        self::startSession();
        $mensajes = $_SESSION['flash'] ?? [];
        unset($_SESSION['flash']);
        return $mensajes;
    }

    public static function setSecurityHeaders(): void
    {
        header("X-Frame-Options: DENY"); // Previene Clickjacking
        header("X-XSS-Protection: 1; mode=block"); // Previene XSS básico
        header("X-Content-Type-Options: nosniff"); // Previene MIME-sniffing
        header("Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self'; img-src 'self' data:;");
    }

    public static function generateCSRFToken(): string
    {
        self::startSession();
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function validateCSRFToken(?string $token): bool
    {
        self::startSession();
        if (!isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
            return false;
        }
        return true;
    }

    public static function sanitizeInput(mixed $data): mixed
    {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = self::sanitizeInput($value);
            }
            return $data;
        }
        return htmlspecialchars(strip_tags(trim((string)$data)), ENT_QUOTES, 'UTF-8');
    }

    public static function logAudit(
        ?int $usuarioId,
        string $accion,
        string $tablaAfectada,
        ?int $registroId = null,
        ?array $datosAnteriores = null,
        ?array $datosNuevos = null
    ): void {
        $db = Database::getInstance()->getConnection();

        $ipOrigen = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN';

        $stmt = $db->prepare("
            INSERT INTO auditoria_logs 
            (usuario_id, accion, tabla_afectada, registro_id, datos_anteriores, datos_nuevos, ip_origen, user_agent)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $usuarioId,
            $accion,
            $tablaAfectada,
            $registroId,
            $datosAnteriores ? json_encode($datosAnteriores) : null,
            $datosNuevos ? json_encode($datosNuevos) : null,
            $ipOrigen,
            $userAgent
        ]);
    }
}
