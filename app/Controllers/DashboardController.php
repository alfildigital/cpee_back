<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Database;

class DashboardController extends BaseController
{
    public function index(): void
    {
        $this->requireLogin();

        $db = Database::getInstance()->getConnection();

        $counts = [
            'profesionales' => (int)$db->query("SELECT COUNT(*) FROM profesionales")->fetchColumn(),
            'usuarios' => (int)$db->query("SELECT COUNT(*) FROM usuarios")->fetchColumn(),
            'caja' => (int)$db->query("SELECT COUNT(*) FROM caja_movimientos")->fetchColumn(),
            // 'auditoria' => (int)$db->query("SELECT COUNT(*) FROM auditoria_logs")->fetchColumn(),
        ];

        $this->render('dashboard/index', 'Dashboard - CPEE', [
            'usuario' => $_SESSION['usuario_nombre'] ?? '',
            'counts' => $counts
        ]);
    }
}
