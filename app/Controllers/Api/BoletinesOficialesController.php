<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Models\BoletinOficialModel;

class BoletinesOficialesController extends ApiController
{
    private const LIMIT_MAX = 100;
    private const LIMIT_DEFAULT = 50;

    private function model(): BoletinOficialModel
    {
        return new BoletinOficialModel();
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
            'titulo' => $row['titulo'],
            'resumen' => $row['resumen'] ?? null,
            'archivo_nombre' => $row['archivo_nombre'] ?? null,
            'archivo_tipo' => $row['archivo_tipo'] ?? null,
            'archivo_tamano' => isset($row['archivo_tamano']) ? (int)$row['archivo_tamano'] : null,
            'archivo_contenido' => $archivoBase64,
            'usuario_abm' => $row['usuario_abm'] ?? null,
            'created_at' => $row['created_at'],
            'updated_at' => $row['updated_at'],
        ];
    }

    // ------------------------------------------------------------------
    // GET /api/v1/boletines-oficiales?page=1&limit=50&q=...
    // ------------------------------------------------------------------
    public function index(): void
    {
        $this->requireAuth();

        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = min(self::LIMIT_MAX, max(1, (int)($_GET['limit'] ?? self::LIMIT_DEFAULT)));

        $q = trim((string)($_GET['q'] ?? ''));
        $q = $q === '' ? null : $q;

        $offset = ($page - 1) * $limit;

        $model = $this->model();
        $items = array_map([$this, 'map'], $model->getPaginated($limit, $offset, $q));
        $total = $model->count($q);

        $this->success($items, 200, '', [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'pages' => (int)ceil($total / $limit),
        ]);
    }

    // ------------------------------------------------------------------
    // GET /api/v1/boletines-oficiales/{id}
    // ------------------------------------------------------------------
    public function show(int $id): void
    {
        $this->requireAuth();

        if ($id <= 0) {
            $this->error('ID inválido.', 400);
        }

        $row = $this->model()->getById($id);

        if ($row === null) {
            $this->error('Boletín oficial no encontrado.', 404);
        }

        $this->success($this->map($row));
    }
}
