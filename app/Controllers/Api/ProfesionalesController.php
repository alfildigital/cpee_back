<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Core\Security;
use App\Core\Upload;
use App\Models\ProfesionalModel;
use Exception;
use PDOException;

class ProfesionalesController extends ApiController
{
    private const ESTADOS = ['Activa', 'Suspendida', 'Inactiva'];

    private const LIMIT_MAX = 100;
    private const LIMIT_DEFAULT = 50;

    private const FOTO_MAX_SIZE = 2097152;

    private function model(): ProfesionalModel
    {
        return new ProfesionalModel();
    }

    private function map(array $row): array
    {
        return [
            'id' => (int)$row['id'],
            'nro_matricula' => $row['nro_matricula'],
            'dni' => $row['dni'] ?? $row['DNI'] ?? null,
            'nombre' => $row['nombre'],
            'apellido' => $row['apellido'],
            'email' => $row['email'],
            'telefono' => $row['telefono'],
            'localidad' => $row['localidad'],
            'direccion' => $row['direccion'],
            'estado' => $row['estado'],
            'fecha_matriculacion' => $row['fecha_matriculacion'],
            'observaciones' => $row['observaciones'] ?? $row['legajo'] ?? $row['notas'] ?? null,
            'foto' => $row['foto'],
            'usuario_abm' => $row['usuario_abm'],
            'created_at' => $row['created_at'],
            'updated_at' => $row['updated_at'],
        ];
    }

    // ------------------------------------------------------------------
    // GET /api/v1/profesionales?page=1&limit=50&q=...&estado=...
    // ------------------------------------------------------------------
    public function index(): void
    {
        $this->requireAuth();

        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = min(self::LIMIT_MAX, max(1, (int)($_GET['limit'] ?? self::LIMIT_DEFAULT)));

        $q = trim((string)($_GET['q'] ?? ''));
        $q = $q === '' ? null : $q;

        $estado = trim((string)($_GET['estado'] ?? ''));
        $estado = $estado === '' ? null : $estado;
        if ($estado !== null && !in_array($estado, self::ESTADOS, true)) {
            $this->error('Parámetro "estado" inválido.', 422, [
                'estado' => 'Valores permitidos: ' . implode(', ', self::ESTADOS) . '.',
            ]);
        }

        $offset = ($page - 1) * $limit;

        $model = $this->model();
        $items = array_map([$this, 'map'], $model->getPaginated($limit, $offset, $q, $estado));
        $total = $model->count($q, $estado);

        $this->success($items, 200, '', [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'pages' => (int)ceil($total / $limit),
        ]);
    }

    // ------------------------------------------------------------------
    // GET /api/v1/profesionales/{id}
    // ------------------------------------------------------------------
    public function show(int $id): void
    {
        $this->requireAuth();

        if ($id <= 0) {
            $this->error('ID inválido.', 400);
        }

        $row = $this->model()->getById($id);

        if ($row === null) {
            $this->error('Matriculado no encontrado.', 404);
        }

        $this->success($this->map($row));
    }

    // ------------------------------------------------------------------
    // POST /api/v1/profesionales
    // ------------------------------------------------------------------
    public function create(): void
    {
        $this->requireAuth();

        $body = $this->jsonBody();
        $data = $this->normalizeInput($body);

        $errors = $this->validate($data);
        if ($errors !== []) {
            $this->error('Datos inválidos.', 422, $errors);
        }

        $data['estado'] ??= 'Activa';
        $data['usuario_abm'] ??= 'API';

        // Foto opcional (base64 o data URL)
        if (array_key_exists('foto', $body)) {
            try {
                $data['foto'] = $this->storeFoto($body['foto']);
            } catch (Exception $e) {
                $this->error($e->getMessage(), 422);
            }
        }

        $model = $this->model();

        try {
            $id = $model->create($data);
            $this->logAudit('INSERT', 'profesionales', $id, null, $data);
            $this->success($this->map($model->getById($id)), 201, 'Matriculado creado correctamente.');
        } catch (PDOException $e) {
            if ((string)$e->getCode() === '23505') {
                $this->error('Ya existe un matriculado con el mismo DNI o número de matrícula.', 409);
            }
            error_log('API crear profesional: ' . $e->getMessage());
            $this->error('Error interno del servidor.', 500);
        } catch (Exception $e) {
            error_log('API crear profesional: ' . $e->getMessage());
            $this->error('Error interno del servidor.', 500);
        }
    }

    // ------------------------------------------------------------------
    // PUT /api/v1/profesionales/{id}  (también acepta PATCH)
    // Admite actualización parcial: solo se modifican los campos enviados.
    // ------------------------------------------------------------------
    public function update(int $id): void
    {
        $this->requireAuth();

        if ($id <= 0) {
            $this->error('ID inválido.', 400);
        }

        $model = $this->model();
        $actual = $model->getById($id);

        if ($actual === null) {
            $this->error('Matriculado no encontrado.', 404);
        }

        // PostgreSQL devuelve la columna en minúsculas; la normalizamos a la
        // clave que espera ProfesionalModel (DNI).
        if (!array_key_exists('DNI', $actual) && array_key_exists('dni', $actual)) {
            $actual['DNI'] = $actual['dni'];
            unset($actual['dni']);
        }

        $body = $this->jsonBody();
        $data = $this->normalizeInput($body);

        if ($data === []) {
            $this->error('No se enviaron campos para actualizar.', 422);
        }

        // Merge parcial sobre los datos actuales
        foreach ($data as $campo => $valor) {
            $actual[$campo] = $valor;
        }

        $actual['usuario_abm'] = 'API';

        $errors = $this->validate($actual);
        if ($errors !== []) {
            $this->error('Datos inválidos.', 422, $errors);
        }

        // Foto: null/'' la remueve; string lo reemplaza por base64
        if (array_key_exists('foto', $body)) {
            try {
                $actual['foto'] = $this->storeFoto($body['foto'], $actual['foto'] ?? null);
            } catch (Exception $e) {
                $this->error($e->getMessage(), 422);
            }
        }

        $antes = $model->getById($id);

        try {
            $model->update($id, $actual);
            $this->logAudit('UPDATE', 'profesionales', $id, $antes, $actual);
            $this->success($this->map($model->getById($id)), 200, 'Matriculado actualizado correctamente.');
        } catch (PDOException $e) {
            if ((string)$e->getCode() === '23505') {
                $this->error('Ya existe un matriculado con el mismo DNI o número de matrícula.', 409);
            }
            error_log('API actualizar profesional: ' . $e->getMessage());
            $this->error('Error interno del servidor.', 500);
        } catch (Exception $e) {
            error_log('API actualizar profesional: ' . $e->getMessage());
            $this->error('Error interno del servidor.', 500);
        }
    }

    // ------------------------------------------------------------------
    // DELETE /api/v1/profesionales/{id}
    // ------------------------------------------------------------------
    public function delete(int $id): void
    {
        $this->requireAuth();

        if ($id <= 0) {
            $this->error('ID inválido.', 400);
        }

        $model = $this->model();
        $actual = $model->getById($id);

        if ($actual === null) {
            $this->error('Matriculado no encontrado.', 404);
        }

        if (!empty($actual['foto'])) {
            Upload::delete($actual['foto']);
        }

        try {
            $model->delete($id);
            $this->logAudit('DELETE', 'profesionales', $id, $actual, null);
            $this->success(null, 200, 'Matriculado eliminado correctamente.');
        } catch (Exception $e) {
            error_log('API eliminar profesional: ' . $e->getMessage());
            $this->error('No se pudo eliminar el matriculado.', 500);
        }
    }

    // ------------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------------

    /**
     * Normaliza los campos del cuerpo JSON (camelCase o snake_case) a las
     * claves tal como las espera ProfesionalModel.
     */
    private function normalizeInput(array $input): array
    {
        $alias = [
            'nro_matricula' => ['nro_matricula', 'nroMatricula'],
            'DNI' => ['DNI', 'dni'],
            'nombre' => ['nombre'],
            'apellido' => ['apellido'],
            'email' => ['email'],
            'telefono' => ['telefono'],
            'localidad' => ['localidad'],
            'direccion' => ['direccion'],
            'estado' => ['estado'],
            'fecha_matriculacion' => ['fecha_matriculacion', 'fechaMatriculacion'],
            'observaciones' => ['observaciones', 'legajo', 'nota', 'notas'],
        ];

        $data = [];
        foreach ($alias as $key => $keys) {
            $valor = null;
            foreach ($keys as $k) {
                if (array_key_exists($k, $input)) {
                    $valor = $input[$k];
                    break;
                }
            }
            if ($valor !== null) {
                $data[$key] = is_string($valor) ? trim($valor) : $valor;
            }
        }
        return $data;
    }

    /**
     * @return array<string,string> Errores de validación por campo
     */
    private function validate(array $data): array
    {
        $errors = [];

        if (empty($data['nro_matricula'])) {
            $errors['nro_matricula'] = 'El número de matrícula es obligatorio.';
        }

        if (empty($data['DNI'])) {
            $errors['DNI'] = 'El DNI es obligatorio.';
        }

        if (empty($data['fecha_matriculacion'])) {
            $errors['fecha_matriculacion'] = 'La fecha de matriculación es obligatoria.';
        } elseif (!$this->isValidDate((string)$data['fecha_matriculacion'])) {
            $errors['fecha_matriculacion'] = 'Formato de fecha inválido. Use YYYY-MM-DD.';
        }

        if (isset($data['estado']) && $data['estado'] !== '' && !in_array($data['estado'], self::ESTADOS, true)) {
            $errors['estado'] = 'Estado inválido. Valores permitidos: ' . implode(', ', self::ESTADOS) . '.';
        }

        if (isset($data['email']) && $data['email'] !== '' && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Email con formato inválido.';
        }

        return $errors;
    }

    private function isValidDate(string $fecha): bool
    {
        $d = date_parse($fecha);
        return $d['error_count'] === 0 && $d['warning_count'] === 0;
    }

    /**
     * Recibe null (no se toca), '' (remover foto) o base64/data URL (reemplazar).
     * Devuelve la ruta relativa de la imagen almacenada o null.
     *
     * @throws Exception
     */
    private function storeFoto(mixed $foto, ?string $actual = null): ?string
    {
        if ($foto === null) {
            return $actual;
        }

        if ($foto === '') {
            if ($actual !== null) {
                Upload::delete($actual);
            }
            return null;
        }

        if (!is_string($foto)) {
            throw new Exception('El campo "foto" debe ser un string base64.');
        }

        $bin = $this->decodeFoto($foto);
        if ($bin === null) {
            throw new Exception('Imagen base64 inválida.');
        }

        if (strlen($bin) > self::FOTO_MAX_SIZE) {
            throw new Exception('La foto supera el tamaño máximo permitido (2 MB).');
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = strtolower((string)finfo_buffer($finfo, $bin));

        $extension = match ($mime) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/gif' => 'gif',
            default => null,
        };

        if ($extension === null) {
            throw new Exception('Formato de imagen no permitido. Formatos aceptados: jpg, png, webp, gif.');
        }

        $dir = ROOT_PATH . '/uploads/fotos';
        if (!is_dir($dir) && !mkdir($dir, 0755, true)) {
            throw new Exception('No se pudo crear el directorio de fotos.');
        }

        $ruta = 'uploads/fotos/' . bin2hex(random_bytes(16)) . '.' . $extension;
        if (file_put_contents(ROOT_PATH . '/' . $ruta, $bin) === false) {
            throw new Exception('No se pudo guardar la foto en el servidor.');
        }

        if ($actual !== null) {
            Upload::delete($actual);
        }

        return $ruta;
    }

    private function decodeFoto(string $foto): ?string
    {
        if (preg_match('/^data:image\/[a-zA-Z0-9+.-]+;base64,(.+)$/s', $foto, $m) === 1) {
            $bin = base64_decode($m[1], true);
        } else {
            $bin = base64_decode($foto, true);
        }
        return ($bin === false || $bin === '') ? null : $bin;
    }
}