<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Security;

abstract class BaseController
{
    protected const VIEWS_PATH = __DIR__ . '/../Views';

    protected function getCurrentUserId(): int
    {
        return (int)($_SESSION['usuario_id'] ?? 0);
    }

    protected function requireLogin(): void
    {
        Security::requireLogin();
    }

    protected function requirePost(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect($_SERVER['HTTP_REFERER'] ?? '/cpee');
        }
    }

    protected function requireCsrf(): void
    {
        if (!Security::validateCSRFToken($_POST['csrf_token'] ?? null)) {
            $this->redirect($_SERVER['HTTP_REFERER'] ?? '/cpee');
        }
    }

    protected function redirect(string $url): void
    {
        header('Location: ' . $url);
        exit;
    }

    protected function render(string $view, string $title, array $data = []): void
    {
        extract($data, EXTR_SKIP);

        ob_start();
        require_once self::VIEWS_PATH . '/' . $view . '.php';
        $content = ob_get_clean();

        require_once self::VIEWS_PATH . '/layouts/base.php';
    }
}