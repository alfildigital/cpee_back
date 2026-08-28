<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Security;
use App\Models\AuditoriaModel;

class AuditoriaController extends BaseController
{
    // GET /auditoria
    // GET /auditoria/index/{tabla}/{limite}
    public function index(?string $tabla = null, ?string $limite = null): void
    {
        $this->requireLogin();

        $model = new AuditoriaModel();

        $tabla = $tabla !== null ? preg_replace('/[^a-zA-Z0-9_]/', '', $tabla) : '';
        $limit = ($limite !== null && ctype_digit($limite)) ? (int)$limite : 100;

        if ($tabla !== '') {
            $logs = $model->getByTabla($tabla, $limit);
        } else {
            $logs = $model->getAll($limit);
        }

        $this->render('auditoria/index', 'Módulo de Auditoría - CPEE', [
            'logs' => $logs,
            'tablaFiltro' => $tabla,
            'limit' => $limit,
        ]);
    }
}