<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Models\NovedadModel;

class NovedadesController extends ApiController
{
    private const LIMIT_MAX = 100;
    private const LIMIT_DEFAULT = 50;

    private function model(): NovedadModel
    {
        return new NovedadModel();
    }

    private function map(array $row): array
    {
        $archivoBase64 = null;
        if (!empty($row['archivo_ruta'])) {
            $rutaAbsoluta = ROOT_PATH . '/' . $row['archivo_ruta'];
            if (is_file($rutaAbsoluta)) {
                $contenido = file_get_contents($rutaAbsoluta);
                if ($contenido !== false) {
                    $archivoBase64 = base64_encode($contenido);
                }
            }
        }
        return [
            'id' => (int)$row['id'],
            'usuario_id' => isset($row['usuario_id']) ? (int)$row['usuario_id'] : null,
            'titulo' => $row['titulo'],
            'contenido' => $row['contenido'],
            'publicado' => (bool)$row['publicado'],
            'fecha_publicacion' => $row['fecha_publicacion'],
            'archivo_nombre' => $row['archivo_nombre'] ?? null,
            'archivo_ruta' => $row['archivo_ruta'] ?? null,
            'archivo_tipo' => $row['archivo_tipo'] ?? null,
            'archivo_contenido' => $archivoBase64,
            'archivo_tamano' => isset($row['archivo_tamano']) ? (int)$row['archivo_tamano'] : null,
            'autor' => "Secretaria General",
            'roles_nombres' => $row['roles_nombres'] ?? null,
            'roles' => isset($row['roles']) ? array_map('intval', $row['roles']) : null,
            'usuario_abm' => $row['usuario_abm'] ?? null,
            'created_at' => $row['created_at'],
            'updated_at' => $row['updated_at'],
        ];
    }

    // ------------------------------------------------------------------
    // GET /api/v1/novedades?page=1&limit=50&q=...&publicada=true|false
    // ------------------------------------------------------------------
    public function index(): void
    {
        $this->requireAuth();

        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = min(self::LIMIT_MAX, max(1, (int)($_GET['limit'] ?? self::LIMIT_DEFAULT)));

        $q = trim((string)($_GET['q'] ?? ''));
        $q = $q === '' ? null : $q;

        $publicada = null;
        if (isset($_GET['publicada']) && $_GET['publicada'] !== '') {
            $valor = strtolower(trim((string)$_GET['publicada']));
            if (in_array($valor, ['true', '1', 'si', 's'], true)) {
                $publicada = true;
            } elseif (in_array($valor, ['false', '0', 'no', 'n'], true)) {
                $publicada = false;
            } else {
                $this->error('Parámetro "publicada" inválido.', 422, [
                    'publicada' => 'Valores permitidos: true o false.',
                ]);
            }
        }

        $offset = ($page - 1) * $limit;

        $model = $this->model();
        $items = array_map([$this, 'map'], $model->getPaginated($limit, $offset, $q, $publicada));
        $total = $model->count($q, $publicada);

        $this->success($items, 200, '', [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'pages' => (int)ceil($total / $limit),
        ]);
    }

    // ------------------------------------------------------------------
    // GET /api/v1/novedades/{id}
    // ------------------------------------------------------------------
    public function show(int $id): void
    {
        $this->requireAuth();

        if ($id <= 0) {
            $this->error('ID inválido.', 400);
        }

        $row = $this->model()->getById($id);

        if ($row === null) {
            $this->error('Novedad no encontrada.', 404);
        }

        $this->success($this->map($row));
    }
}
