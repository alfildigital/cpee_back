<?php

declare(strict_types=1);

namespace App\Core;

use Exception;

/**
 * Utilidad nativa para el manejo seguro de subida de archivos.
 * Almacena los archivos fuera del Document Root (uploads/) y devuelve
 * un arreglo de metadatos para persistir en la base de datos.
 */
final class Upload
{
    /** Tamaño máximo para cualquier adjunto (5 MB). */
    public const MAX_SIZE = 5242880;

    /** Tamaño máximo para fotos de matriculados (2 MB). */
    public const MAX_IMAGE_SIZE = 2097152;

    /** Extensiones permitidas para respaldo documental: PDF o imagen. */
    private const MIME_MAP = [
        'application/pdf' => 'pdf',
        'image/jpeg'      => 'jpg',
        'image/png'       => 'png',
        'image/webp'      => 'webp',
        'image/gif'       => 'gif',
    ];

    /** MIME permitidos para fotos (solo imágenes raster — nada de PDF). */
    private const IMAGE_MIME_MAP = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        'image/gif'  => 'gif',
    ];

    /**
     * @return array{nombre:string,ruta:string,tipo:string,tamano:int}|null
     * @throws Exception
     */
    public static function store(array $file, string $subdir = 'caja', ?array $allowed = null): ?array
    {
        if (!isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
            return null; // No se adjuntó archivo (campo opcional)
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('Error al subir el archivo (código ' . $file['error'] . ').');
        }

        if ($file['size'] > self::MAX_SIZE) {
            throw new Exception('El archivo supera el tamaño máximo permitido (5 MB).');
        }

        $map = $allowed ?? self::MIME_MAP;

        $tipoReal = (string)(mime_content_type($file['tmp_name']) ?: '');
        $tipoReal = strtolower($tipoReal);

        if (!isset($map[$tipoReal])) {
            $permitidos = implode('/', array_keys($map));
            throw new Exception('Formato no permitido. Formatos aceptados: ' . $permitidos . '.');
        }

        $extension = $map[$tipoReal];

        // Nombre único en disco (evita path traversal y colisiones)
        $nombreDisco = bin2hex(random_bytes(16)) . '.' . $extension;

        $dirDestino = ROOT_PATH . '/uploads/' . trim($subdir, '/');
        if (!is_dir($dirDestino) && !mkdir($dirDestino, 0755, true)) {
            throw new Exception('No se pudo crear el directorio de destino.');
        }

        $rutaAbsoluta = $dirDestino . '/' . $nombreDisco;
        if (!move_uploaded_file($file['tmp_name'], $rutaAbsoluta)) {
            throw new Exception('No se pudo guardar el archivo en el servidor.');
        }

        return [
            'nombre' => basename($file['name']),
            'ruta'   => 'uploads/' . trim($subdir, '/') . '/' . $nombreDisco, // ruta relativa (fuera del root)
            'tipo'   => $tipoReal,
            'tamano' => (int)$file['size'],
        ];
    }

    /**
     * Sube una imagen (foto de matriculado) con restricciones propias.
     *
     * @return array{nombre:string,ruta:string,tipo:string,tamano:int}|null
     * @throws Exception
     */
    public static function storeImage(array $file, string $subdir = 'fotos'): ?array
    {
        if (!isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        if ($file['size'] > self::MAX_IMAGE_SIZE) {
            throw new Exception('La foto supera el tamaño máximo permitido (2 MB).');
        }
        return self::store($file, $subdir, self::IMAGE_MIME_MAP);
    }

    /** Elimina un archivo ya almacenado (si existe). Ignora paths inválidos. */
    public static function delete(string $rutaRelativa): void
    {
        if ($rutaRelativa === '' || str_contains($rutaRelativa, '..')) {
            return;
        }
        $ruta = ROOT_PATH . '/' . ltrim($rutaRelativa, '/');
        if (is_file($ruta)) {
            @unlink($ruta);
        }
    }
}
