<?php

declare(strict_types=1);

namespace App\Core;

final class Env
{
    /**
     * Carga las variables de un archivo .env en el entorno del proceso
     * (getenv / $_ENV / $_SERVER) sin depender de librerías externas.
     */
    public static function load(string $path): void
    {
        if (!is_file($path)) {
            return;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            return;
        }

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
                continue;
            }

            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);

            if ($key === '' || $value === '' || !preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $key)) {
                continue;
            }

            if (strlen($value) >= 2) {
                $first = $value[0];
                $last = $value[strlen($value) - 1];
                if (($first === '"' && $last === '"') || ($first === "'" && $last === "'")) {
                    $value = substr($value, 1, -1);
                }
            }

            // No sobrescribir variables ya definidas en el entorno real.
            if (getenv($key) === false) {
                putenv("$key=$value");
            }
            $_ENV[$key] ??= $value;
            $_SERVER[$key] ??= $value;
        }
    }
}