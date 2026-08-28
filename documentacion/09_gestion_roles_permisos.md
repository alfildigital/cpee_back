# 09 — Módulo de Roles y Permisos

Controlador: `app/Controllers/RolesController.php`
Modelo: `app/Models/RolPermisoModel.php`
Vistas: `app/Views/roles/{index,show,create,edit,permisos}.php`

## Rutas

| Método | Ruta | Acción | Descripción |
| :-- | :-- | :-- | :-- |
| GET | `/roles` | `index` | Listado de roles (con cant. de permisos y usuarios). |
| GET | `/roles/ver/{id}` | `ver` | Detalle de un rol y sus permisos asignados. |
| GET | `/roles/crear` | `crear` | Formulario de alta de rol. |
| POST | `/roles/guardar` | `guardar` | Procesa alta (create) + asignación de permisos. |
| GET | `/roles/editar/{id}` | `editar` | Formulario de edición con checkboxes de permisos. |
| POST | `/roles/actualizar` | `actualizar` | Procesa actualización (update) + re-asignación de permisos. |
| POST | `/roles/eliminar` | `eliminar` | Elimina un rol (cascada en `rol_permisos` y `novedad_roles`). |
| GET | `/roles/permisos` | `permisos` | Catálogo de permisos (solo lectura). |

Todas las acciones exigen inicio de sesión (`requireLogin`); las de escritura además exigen `POST` y token CSRF.

## Modelo — entidades implicadas

- Tabla `roles` (nombre + descripción).
- Tabla `permisos` (catálogo de permisos, solo lectura).
- Relación N:N `rol_permisos` ↔ `permisos`.

### Métodos de `RolPermisoModel`

| Método | Función |
| :-- | :-- |
| `getAll(): array` | Roles con `permisos_count` y `usuarios_count` (subqueries agregadas). |
| `getById(int $id): ?array` | Rol + lista `permisos` (array de `permiso_id`). |
| `getAllPermisos(): array` | Catálogo completo de permisos (solo lectura). |
| `create(array $data, array $permisosIds): int` | Alta con transacción + inserción en `rol_permisos`. Devuelve id. |
| `update(int $id, array $data, array $permisosIds): bool` | Update transaccional; re-sincroniza `rol_permisos` (DELETE + INSERT). |
| `delete(int $id): bool` | Borra el rol (cascada en `rol_permisos`, `novedad_roles`, `usuario_roles`). |

## Detalles de implementación

- La asignación de permisos se realiza mediante **checkboxes** en los formularios de alta/edición (`name="permisos[]"`). El modelo re-sincroniza la tabla `rol_permisos` borrando y reinsertando, dentro de una transacción.
- El **catálogo de permisos** (`/roles/permisos`) es una lista de solo lectura (`id`, clave `nombre`, `descripción`). Los permisos no se crean desde la UI: se definen a nivel de semilla/BD.
- Los botones de "Marcar todos / Desmarcar todos" son helpers de UI que alternan los checkboxes de permisos.
- El modal de eliminación advierte si el rol está asignado a uno o más usuarios (`usuarios_count > 0`) antes de confirmar.
- En el listado se muestra la cantidad de permisos y de usuarios vinculados a cada rol.

## Estado del módulo

- El módulo implementa el **CRUD de roles** y la **asignación de permisos**. La validación granular de permisos dentro de cada controlador queda pendiente (ver `02_seguridad.md` — RBAC esbozado); actualmente todas las rutas usan `requireLogin()`, la autorización fina es un paso siguiente.
