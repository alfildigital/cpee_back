# 04 — Módulo de Gestión de Usuarios

Controlador: `app/Controllers/UsuariosController.php`
Modelo: `app/Models/UsuarioModel.php`
Vistas: `app/Views/usuarios/{index,show,create,edit}.php`

## Rutas

| Método | Ruta | Acción | Descripción |
| :-- | :-- | :-- | :-- |
| GET | `/usuarios` | `index` | Listado de usuarios. |
| GET | `/usuarios/ver/{id}` | `ver` | Detalle de un usuario. |
| GET | `/usuarios/crear` | `crear` | Formulario de alta. |
| POST | `/usuarios/guardar` | `guardar` | Procesa alta (create). |
| GET | `/usuarios/editar/{id}` | `editar` | Formulario de edición. |
| POST | `/usuarios/actualizar` | `actualizar` | Procesa actualización (update). |
| POST | `/usuarios/eliminar` | `eliminar` | Elimina un usuario (delete). |

Todas las acciones exigen inicio de sesión (`requireLogin`); las de escritura además exigen `POST` y token CSRF.

## Modelo — entidades implicadas

- Tabla `usuarios` (datos + `sector_id`).
- Relación N:N `usuario_roles` ↔ `roles`.
- Tabla `sectores` (sector institucional, 1:N con usuario).

### Métodos de `UsuarioModel`

| Método | Función |
| :-- | :-- |
| `getAll(): array` | Todos los usuarios con `sector_nombre` (LEFT JOIN `sectores`). |
| `getById(int $id): ?array` | Usuario + lista `roles` (array de `rol_id`). |
| `getAllSectores(): array` | Catálogo de sectores. |
| `getAllRoles(): array` | Catálogo de roles. |
| `create(array $data, array $roles): int` | Alta con transacción + inserción de `usuario_roles`. Devuelve id. |
| `update(int $id, array $data, array $roles): bool` | Update transaccional; re-sincroniza `usuario_roles` (DELETE + INSERT). Hash opcional solo si se envía password. |
| `delete(int $id): bool` | Borra el usuario (cascada en `usuario_roles`). |

## Detalles de implementación

- El hash `password_hash($pass, PASSWORD_ARGON2ID)` se calcula en el modelo (el controlador no recibe el hash).
- **Edición:** si el campo password viene vacío, no se modifica el hash.
- **`activo`:** se calcula en el controlador a partir de `isset($_POST['activo'])` (checkbox).
- **Prevención:** el controlador impide eliminar el propio usuario (`$id === getCurrentUserId()`).
- **Auditoría:** INSERT/UPDATE/DELETE registran snapshots JSONB vía `Security::logAudit()`.

## Reglas de validación

| Campo | Obligatorio | Notas |
| :-- | :--: | :-- |
| `nombre` | ✔ | — |
| `email` | ✔ | único en BD |
| `password` | ✔ (solo al crear) | opcional al editar |
| `sector_id` | ✘ | FK a `sectores` |
| `roles` | ✘ | array multiselect |

## Diagrama de flujo CRUD

```
index ──► getAll() ──► tabla
crear ──► formulario ──► guardar (POST+CSRF) ──► create + auditoría ──► index
editar ──► getById ──► formulario ──► actualizar (POST+CSRF) ──► update + auditoría ──► index
                                          └─ getById previo (datosAnteriores)
eliminar (POST+CSRF) ──► getById ──► delete + auditoría ──► index
```
