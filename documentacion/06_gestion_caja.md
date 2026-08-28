# 06 — Módulo de Caja y Tesorería

Controlador: `app/Controllers/CajaController.php`
Modelo: `app/Models/MovimientoCajaModel.php`
Vistas: `app/Views/caja/{index,create}.php`

## Rutas

| Método | Ruta | Acción | Descripción |
| :-- | :-- | :-- | :-- |
| GET | `/caja` | `index` | Listado + saldo por rango de fechas. |
| GET | `/caja/index/{desde}/{hasta}` | `index` | Listado con rango explícito (`YYYY-MM-DD`). |
| GET | `/caja/crear/{tipo}/{profesional_id}` | `crear` | Formulario de nuevo movimiento (con preselecciones). |
| POST | `/caja/guardar` | `guardar` | Registra el movimiento de caja (admite adjunto). |
| GET | `/caja/descargar/{id}` | `descargar` | Sirve el documento adjunto del movimiento (exige sesión). |

## Modelo — tabla `caja_movimientos`

| Campo | Tipo | Notas |
| :-- | :-- | :-- |
| `id` | SERIAL PK | — |
| `usuario_id` | FK `usuarios` | operador que registra |
| `profesional_id` | FK `profesionales` | opcional (matriculado asociado) |
| `tipo` | ENUM `tipo_movimiento` | `Ingreso` / `Egreso` |
| `concepto` | VARCHAR(255) | descripción |
| `tipo_comprobante` | VARCHAR(50) | opcional |
| `punto_venta` | VARCHAR(10) | opcional |
| `nro_comprobante` | VARCHAR(50) | opcional |
| `cuit` | VARCHAR(20) | opcional |
| `monto_neto` | NUMERIC(12,2) | — |
| `iva` | NUMERIC(12,2) | default 0 |
| `monto_total` | NUMERIC(12,2) | `neto + iva` |
| `fecha_movimiento` | TIMESTAMP | default now |
| `archivo_nombre` | VARCHAR(255) | nombre original del adjunto |
| `archivo_ruta` | VARCHAR(500) | ruta relativa del archivo (fuera del document root) |
| `archivo_tipo` | VARCHAR(100) | MIME del adjunto |
| `archivo_tamano` | BIGINT | tamaño en bytes |

## Métodos de `MovimientoCajaModel`

| Método | Función |
| :-- | :-- |
| `registrarMovimiento(array $datos): int` | Insert transaccional con `RETURNING id`. |
| `obtenerMovimientosPorFecha(string $inicio, string $fin): array` | Movimientos del rango con usuario y profesional (LEFT JOINs), orden descendente. |
| `getById(int $id): ?array` | Un movimiento y su usuario (para servir adjuntos). |

> `MatriculaController::asentarPago()` también reutiliza `registrarMovimiento()` para registrar pagos de matrícula como movimientos de tipo `Ingreso` (módulo esbozado, ver nota).

## Documento de respaldo (adjunto)

El formulario `caja/create` (`enctype="multipart/form-data"`) permite adjuntar un **PDF o imagen** como respaldo de la operación:

- **Restricciones** (`App\Core\Upload::store()`): solo `application/pdf`, `image/jpeg`, `image/png`, `image/webp`, `image/gif`; máximo **5 MB**.
- **Almacenamiento**: se guarda fuera del Document Root (carpeta `uploads/caja/`) con nombre aleatorio (`bin2hex(random_bytes(16))` + extensión), evitando path traversal y colisiones. El directorio `uploads/` está **bloqueado** del acceso web directo (regla en `.htaccess` de la raíz + `.htaccess` interno `Require all denied`).
- **Servido**: se accede únicamente vía `GET /caja/descargar/{id}`, que valida sesión, verifica que el archivo exista y lo envía con `Content-Disposition: inline` y MIME correcto. Nunca se expone la ruta real.
- **Vista previa** en cliente (imagen `<img>` o aviso para PDF) y validación de tipo/tamaño en JS.

## Lógica de `index`

1. Sanitiza/valida fechas con `preg_match('/^\d{4}-\d{2}-\d{2}$/')`; si son inválidas usa el mes corriente (`Y-m-01` a `Y-m-t`).
2. Obtiene movimientos del rango.
3. Calcula `totalIngresos`, `totalEgresos` y `saldo = ingresos - egresos` (iterando `monto_total`).
4. Renderiza la vista con los totales.

## Lógica de `guardar`

1. Exige sesión, `POST` y CSRF.
2. Sanitiza `$_POST`.
3. Valida `monto_neto > 0`.
4. Procesa el adjunto con `Upload::store($_FILES['archivo'])` (opcional; puede lanzar excepción si el formato/tamaño no es válido).
5. Calcula `monto_total = monto_neto + iva`.
6. Incluye metadatos del archivo (`archivo_nombre`, `archivo_ruta`, `archivo_tipo`, `archivo_tamano`).
7. `registrarMovimiento()` (transaccional).
8. `logAudit('INSERT', 'caja_movimientos')`.
9. `flash('success')` → redirect `/caja`.

## Diagrama de flujo

```
index ──► validar fechas ──► obtenerMovimientosPorFecha ──► calcular totales/saldo ──► vista
crear ──► profesionales (para select) ──► vista
guardar (POST+CSRF) ──► validar monto ──► Upload::store (adjunto) ──► registrarMovimiento (tx) ──► auditoría ──► index
descargar/{id} (GET, sesión) ──► getById ──► validar ruta/existencia ──► readfile (inline)
```
