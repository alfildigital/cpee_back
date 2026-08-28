# 05 — Módulo de Gestión de Profesionales (Matriculados)

Controlador: `app/Controllers/ProfesionalesController.php`
Modelo: `app/Models/ProfesionalModel.php`
Vistas: `app/Views/profesionales/{index,show,create,edit}.php`

## Rutas

| Método | Ruta | Acción | Descripción |
| :-- | :-- | :-- | :-- |
| GET | `/profesionales` | `index` | Listado de matriculados. |
| GET | `/profesionales/ver/{id}` | `ver` | Detalle de un matriculado. |
| GET | `/profesionales/crear` | `crear` | Formulario de alta. |
| POST | `/profesionales/guardar` | `guardar` | Procesa alta (create). |
| GET | `/profesionales/editar/{id}` | `editar` | Formulario de edición. |
| POST | `/profesionales/actualizar` | `actualizar` | Procesa actualización (update). |
| POST | `/profesionales/eliminar` | `eliminar` | Elimina un matriculado (delete). |
| GET | `/profesionales/carnet/{id}` | `carnet` | Genera el PDF carnet del matriculado. |
| GET | `/profesionales/foto/{id}` | `foto` | Sirve la foto del matriculado (sesión). |

## Modelo — tabla `profesionales`

| Campo | Tipo | Notas |
| :-- | :-- | :-- |
| `id` | SERIAL PK | — |
| `nro_matricula` | VARCHAR(20) UNIQUE | N° de matrícula. |
| `DNI` | VARCHAR(20) UNIQUE | Documento. |
| `nombre` | VARCHAR(100) | — |
| `apellido` | VARCHAR(100) | — |
| `email` | VARCHAR(150) | opcional |
| `telefono` | VARCHAR(50) | opcional |
| `estado` | ENUM `estado_matricula` | `Activa` / `Suspendida` / `Inactiva`. |
| `fecha_matriculacion` | DATE | — |
| `legajo` | TEXT | opcional |
| `foto` | VARCHAR(500) | ruta relativa de la foto (opcional) |
| `created_at` / `updated_at` | TIMESTAMP | marcas de tiempo |

> El campo `estado` usa el ENUM `estado_matricula` (`Activa`/`Suspendida`/`Inactiva`), definido y aplicado en la base (ver `08_esquema_bd.md`).

## Métodos de `ProfesionalModel`

| Método | Función |
| :-- | :-- |
| `getAll(): array` | Todos, orden por `apellido, nombre`. |
| `getById(int $id): ?array` | Por ID. |
| `create(array $data): int` | Alta con `RETURNING id`. |
| `update(int $id, array $data): bool` | Actualización con `updated_at = CURRENT_TIMESTAMP`. |
| `delete(int $id): bool` | Borrado físico. |

## Reglas de validación

- Obligatorios: `DNI`, `nro_matricula`, `fecha_matriculacion`.
- `DNI` y `nro_matricula` son únicos (constraint en BD).
- `estado` por defecto `'Activa'`.
- `email`/`telefono`/`legajo` opcionales.

## Foto del profesional (`foto`)

- **Subida** (`guardar`/`actualizar`): campo `foto` tipo archivo con `enctype="multipart/form-data"` en `create.php` y `edit.php`. Se procesa con `App\Core\Upload::storeImage()`, que valida por `mime_content_type` real y guarda en `uploads/fotos/` con nombre aleatorio (`bin2hex(random_bytes(16))`), fuera del Document Root.
- **Formatos permitidos**: JPG, PNG, WEBP, GIF. Máx. **2 MB** (constante `Upload::MAX_IMAGE_SIZE`).
- **Validación cliente**: `cpeeFotoPreview()` en `create.php`/`edit.php` (tipo y tamaño + vista previa local).
- **Actualizar**: si se sube foto nueva se reemplaza la anterior (borra la vieja con `Upload::delete()`); la casilla "Quitar foto actual" (`remover_foto=1`) la elimina; si no se sube nada se conserva la existente.
- **Servir**: `uploads/` está bloqueado al acceso web directo (`.htaccess`), por lo que la foto se sirve **solo** mediante el controlador `/profesionales/foto/{id}` (requiere sesión), con `Content-Type: image/jpeg`.
- **Eliminación del matriculado**: `delete()` borra también el archivo de foto asociado.

## Diagrama CRUD

```
index ──► getAll ──► tabla
crear ──► formulario ──► guardar (POST+CSRF) ──► create + auditoría ──► index
editar ──► getById ──► formulario ──► actualizar (POST+CSRF) ──► update + auditoría ──► index
                                                  └─ getById previo (datosAnteriores)
eliminar (POST+CSRF) ──► getById ──► delete + auditoría ──► index
```

> Los matriculados se vinculan a movimientos de caja mediante `caja_movimientos.profesional_id` (ver `06_gestion_caja.md`).

## PDF Carnet (`carnet`)

El botón "Carnet" (en el listado e índice y en el detalle) genera un **PDF en tiempo real** con los datos del profesional en formato "carnet":

- Generado con `App\Core\Pdf`, un **generador PDF puro en PHP nativo (cero dependencias)**, conforme al estándar PDF 1.4 (fuentes núcleo Helvetica, WinAnsi).
- **Dimensiones estándar tarjeta**: **90 mm × 60 mm** (255.12 × 170.08 pt, apaisado), **sin bordes** (banda superior y fondo a sangre).
- **Datos que muestra**: **nombre completo** (apellido, nombre), **legajo**, **matrícula**, **fecha de alta** (fecha de matriculación), **estado** (badge coloreado: Activa=verde, Suspendida=ámbar, Inactiva=rojo) y **foto**.
- **Foto**: si el profesional tiene `foto` y el archivo existe, se incrusta a la izquierda mediante `Pdf::image()`, que normaliza JPG/PNG/WEBP/GIF a JPEG vía GD y la embebe como XObject JPEG (`/DCTDecode`). Si no hay foto, se muestra el placeholder "FOTO (sin foto)".
- Se responde con `Content-Type: application/pdf` y `Content-Disposition: inline` (nombre `carnet-{nro_matricula}.pdf`).
- Cada generación queda registrada en auditoría (`GENERAR_CARNET`).
- Botón añadido en `index.php` y `show.php` (abre en nueva pestaña).
