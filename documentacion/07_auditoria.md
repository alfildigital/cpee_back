# 07 — Módulo de Auditoría Forense

Controlador: `app/Controllers/AuditoriaController.php`
Modelo: `app/Models/AuditoriaModel.php`
Vista: `app/Views/auditoria/index.php`

## Rutas

| Método | Ruta | Acción | Descripción |
| :-- | :-- | :-- | :-- |
| GET | `/auditoria` | `index` | Listado de logs (últimos 100). |
| GET | `/auditoria/index/{tabla}/{limite}` | `index` | Filtra por tabla y cantidad de registros. |

## Propósito

Brindar **trazabilidad inmutable** de todas las operaciones sobre los datos del sistema. La escritura de logs se realiza centralizadamente con `Security::logAudit()` (ver `02_seguridad.md`) desde los controladores, y este módulo solo **lee** la información.

## Modelo — tabla `auditoria_logs`

| Campo | Tipo | Notas |
| :-- | :-- | :-- |
| `id` | SERIAL PK | — |
| `usuario_id` | INT | puede ser NULL (intentos de login sin sesión) |
| `accion` | VARCHAR(50) | `INSERT` / `UPDATE` / `DELETE` / `LOGIN` |
| `tabla_afectada` | VARCHAR(100) | nombre de la tabla |
| `registro_id` | INT | PK del registro afectado |
| `datos_anteriores` | JSONB | snapshot previo (NULL en INSERT/LOGIN) |
| `datos_nuevos` | JSONB | snapshot posterior (NULL en DELETE) |
| `ip_origen` | VARCHAR(45) | IPv4/IPv6 |
| `user_agent` | TEXT | — |
| `timestamp` | TIMESTAMP | default now |

## Métodos de `AuditoriaModel`

| Método | Función |
| :-- | :-- |
| `getAll(int $limit = 100): array` | Logs más recientes con `usuario_nombre` (LEFT JOIN `usuarios`). |
| `getByTabla(string $tabla, int $limit = 100): array` | Filtra por `tabla_afectada`. |

## Lógica de `AuditoriaController::index()`

1. Exige sesión.
2. Sanitiza `$tabla` (`[^a-zA-Z0-9_]`) y valida `$limite` numérico (default 100).
3. Si hay filtro → `getByTabla`; si no → `getAll`.
4. Renderiza la vista con los logs, el filtro aplicado y el límite.

## Inmutabilidad

- No existen endpoints para modificar o borrar logs.
- La tabla es de solo-escritura por el sistema (`Security::logAudit`).

## Índices de performance

- `idx_auditoria_logs_timestamp` → `timestamp`.
- `idx_auditoria_logs_tabla` → `tabla_afectada`.
