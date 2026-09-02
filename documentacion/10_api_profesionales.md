# 10 — API REST de Profesionales (v1)

API REST del módulo de **matriculados/profesionales**, pensada para ser consumida por el frontend externo. Es **stateless** (sin sesión): cada petición se autentica con un token Bearer.

- **Base URL:** `/cpee/api/v1`
- **Formato:** JSON (`Content-Type: application/json; charset=UTF-8`)
- **Versión:** `v1` (se versiona en la propia URL)
- **Autenticación:** `Authorization: Bearer <API_API_KEY>`
- **Autor:** Motor de ruteo en `public/index.php` + controladores en `app/Controllers/Api/`

## 1. Autenticación

Todas las operaciones exigen el header:

```
Authorization: Bearer <API_API_KEY>
```

El token se configura en `.env`:

```
API_ENABLED=true
API_API_KEY=5d6dd9d5bf47c294b1a49222930870965bc80bd6b442669df701ee695046ba25
```

- Si falta, es inválido o `API_ENABLED` está en `false` → **HTTP 401** con header `WWW-Authenticate: Bearer`.
- La comparación se realiza con `hash_equals()` (tiempo constante).
- La API no inicia sesión PHP, no emite cookies y no redirige: responde siempre JSON.

## 2. Formato de respuesta

### Éxito
```json
{
  "success": true,
  "data": { ... } | [ ... ] | null,
  "message": "opcional, descripción corta",
  "meta": { "page": 1, "limit": 50, "total": 10, "pages": 1 }
}
```

### Error
```json
{
  "success": false,
  "message": "Descripción breve del error",
  "errors": { "campo": "Detalle de validación" }
}
```

## 3. Códigos de estado

| Código | Significado |
| :-- | :-- |
| `200 OK` | Operación de lectura/escritura exitosa. |
| `201 Created` | Recurso creado. |
| `400 Bad Request` | ID inválido o sintaxis de petición incorrecta. |
| `401 Unauthorized` | Token ausente/incorrecto o API deshabilitada. |
| `404 Not Found` | Recurso, registro o versión inexistente. |
| `405 Method Not Allowed` | Método HTTP no soportado para la ruta (incluye header `Allow`). |
| `409 Conflict` | Violación de unicidad (DNI o nro_matricula duplicado). |
| `422 Unprocessable Entity` | Errores de validación de datos. |
| `500 Internal Server Error` | Error interno (detalle solo por `error_log`). |

## 4. Endpoints

### 4.1 Listar matriculados

`GET /api/v1/profesionales`

Parámetros de consulta (todos opcionales):

| Parámetro | Tipo | Default | Descripción |
| :-- | :-- | :-- | :-- |
| `page` | int | `1` | Página actual. |
| `limit` | int | `50` | Tamaño de página (máx. `100`). |
| `q` | string | — | Búsqueda sobre nombre, apellido, DNI, nro_matricula y legajo (ILIKE). |
| `estado` | string | — | Filtro por estado: `Activa`, `Suspendida` o `Inactiva`. |

```bash
curl -H "Authorization: Bearer $API_API_KEY" \
  "http://localhost/cpee/api/v1/profesionales?page=1&limit=10&q=perez&estado=Activa"
```

```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "nro_matricula": "123456",
      "dni": "12345678",
      "nombre": "Juan",
      "apellido": "Perez",
      "email": "juan@profesional.com",
      "telefono": "123456789",
      "localidad": null,
      "direccion": null,
      "estado": "Activa",
      "fecha_matriculacion": "2022-01-01",
      "legajo": "123456",
      "foto": "uploads/fotos/8f30e243979758d78ff4c4cf46febee1.jpg",
      "created_at": "2026-08-27 12:19:18",
      "updated_at": "2026-08-28 09:11:03"
    }
  ],
  "meta": { "page": 1, "limit": 10, "total": 1, "pages": 1 }
}
```

### 4.2 Obtener un matriculado

`GET /api/v1/profesionales/{id}`

```bash
curl -H "Authorization: Bearer $API_API_KEY" \
  "http://localhost/cpee/api/v1/profesionales/1"
```

```json
{
  "success": true,
  "data": {
    "id": 1,
    "nro_matricula": "123456",
    "dni": "12345678",
    "nombre": "Juan",
    "apellido": "Perez",
    "email": "juan@profesional.com",
    "telefono": "123456789",
    "localidad": null,
    "direccion": null,
    "estado": "Activa",
    "fecha_matriculacion": "2022-01-01",
    "legajo": "123456",
    "foto": "uploads/fotos/8f30e243979758d78ff4c4cf46febee1.jpg",
    "created_at": "2026-08-27 12:19:18",
    "updated_at": "2026-08-28 09:11:03"
  }
}
```

Errores: `404` si no existe.

### 4.3 Crear un matriculado

`POST /api/v1/profesionales`

Body JSON. Campos **obligatorios**: `nro_matricula`, `dni`, `fecha_matriculacion` (formato `YYYY-MM-DD`). Se aceptan claves en snake_case o camelCase (`nro_matricula`/`nroMatricula`, `fecha_matriculacion`/`fechaMatriculacion`, `dni`/`DNI`). `estado` default `Activa`. `foto` opcional: base64 o data URL (`data:image/png;base64,...`), máx. 2 MB, formatos jpg/png/webp/gif.

```bash
curl -X POST -H "Authorization: Bearer $API_API_KEY" \
  -H "Content-Type: application/json" \
  -d '{
    "nro_matricula": "TEST001",
    "dni": "99999999",
    "nombre": "Ana",
    "apellido": "Gomez",
    "email": "ana@test.com",
    "telefono": "5551234",
    "estado": "Activa",
    "fecha_matriculacion": "2024-05-10",
    "localidad": "Rosario"
  }' \
  "http://localhost/cpee/api/v1/profesionales"
```

Respuesta `201 Created`:
```json
{
  "success": true,
  "data": { "id": 4, "nro_matricula": "TEST001", "dni": "99999999", "...": "..." },
  "message": "Matriculado creado correctamente."
}
```

Errores: `422` (validación), `409` (duplicado DNI/nro_matricula), `500`.

### 4.4 Actualizar un matriculado

`PUT /api/v1/profesionales/{id}` — también acepta `PATCH`. Actualización **parcial**: solo se modifican los campos enviados; los ausentes conservan su valor.

```bash
curl -X PUT -H "Authorization: Bearer $API_API_KEY" \
  -H "Content-Type: application/json" \
  -d '{"estado": "Suspendida", "telefono": "5551234"}' \
  "http://localhost/cpee/api/v1/profesionales/4"
```

Respuesta `200 OK`:
```json
{
  "success": true,
  "data": { "id": 4, "...": "..." },
  "message": "Matriculado actualizado correctamente."
}
```

Regla `foto`:
- `"foto": null` → conserva la foto actual.
- `"foto": ""` → elimina la foto actual.
- `"foto": "<base64>"` → reemplaza la foto.

Errores: `404` (no existe), `422` (validación), `409` (duplicado), `400` (ID inválido).

### 4.5 Eliminar un matriculado

`DELETE /api/v1/profesionales/{id}`

```bash
curl -X DELETE -H "Authorization: Bearer $API_API_KEY" \
  "http://localhost/cpee/api/v1/profesionales/4"
```

```json
{
  "success": true,
  "data": null,
  "message": "Matriculado eliminado correctamente."
}
```

Errores: `404` si no existe.

## 5. Errores de validación (422)

```json
{
  "success": false,
  "message": "Datos inválidos.",
  "errors": {
    "nro_matricula": "El número de matrícula es obligatorio.",
    "DNI": "El DNI es obligatorio.",
    "fecha_matriculacion": "La fecha de matriculación es obligatoria."
  }
}
```

## 6. Estructura del código

```
public/index.php                          → Router global (+ bloque API /api/v1)
app/Controllers/Api/ApiController.php     → Base abstracta: auth Bearer, JSON, auditoría
app/Controllers/Api/ProfesionalesController.php → CRUD REST de profesionales
app/Models/ProfesionalModel.php           → + getPaginated(), count() (paginación/búsqueda)
.env                                      → API_ENABLED, API_API_KEY
```

Todas las mutaciones quedan registradas en `auditoria_logs` (usuario `NULL`, accionado desde la API).

## 7. Extensiones futuras

- Agregar `GET /api/v1/profesionales/{id}/carnet` para emitir el PDF del carnet.
- Sub-versiones (`v2`) sin romper `v1`: basta crear el controlador correspondiente bajo `app/Controllers/Api/`.
- Nuevos recursos (ej. `obras-sociales`, `novedades`) junto a `profesionales` en la misma convención.