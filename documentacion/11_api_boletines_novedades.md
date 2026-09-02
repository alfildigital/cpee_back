# 11 — API REST de Boletines Oficiales y Novedades (v1)

API REST de **solo lectura** de los módulos de **boletines oficiales** y **novedades**, pensada para ser consumida por el frontend externo / cartelera pública. Es **stateless** (sin sesión): cada petición se autentica con un token Bearer.

- **Base URL:** `/cpee/api/v1`
- **Formato:** JSON (`Content-Type: application/json; charset=UTF-8`)
- **Versión:** `v1` (se versiona en la propia URL)
- **Autenticación:** `Authorization: Bearer <API_API_KEY>`
- **Operaciones:** solo envío de datos (GET). No expone creación, edición ni borrado.
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
| `200 OK` | Operación de lectura exitosa. |
| `400 Bad Request` | ID inválido o sintaxis de petición incorrecta. |
| `401 Unauthorized` | Token ausente/incorrecto o API deshabilitada. |
| `404 Not Found` | Recurso, registro o versión inexistente. |
| `405 Method Not Allowed` | Método HTTP no soportado (solo `GET`; incluye header `Allow`). |
| `422 Unprocessable Entity` | Parámetro de consulta con valor inválido. |
| `500 Internal Server Error` | Error interno (detalle solo por `error_log`). |

> Los recursos son de lectura, por lo que **no** se devuelven `201`, `409` ni `500` por mutaciones; cualquier método distinto de `GET` responde `405`.

## 4. Boletines oficiales

Recurso en plural: `/api/v1/boletines-oficiales`. Orden: más recientes primero (`created_at DESC, id DESC`).

### 4.1 Listar boletines

`GET /api/v1/boletines-oficiales`

Parámetros de consulta (todos opcionales):

| Parámetro | Tipo | Default | Descripción |
| :-- | :-- | :-- | :-- |
| `page` | int | `1` | Página actual. |
| `limit` | int | `50` | Tamaño de página (máx. `100`). |
| `q` | string | — | Búsqueda sobre `titulo` y `resumen` (ILIKE). |

```bash
curl -H "Authorization: Bearer $API_API_KEY" \
  "http://localhost/cpee/api/v1/boletines-oficiales?page=1&limit=10&q=resolucion"
```

```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "titulo": "Resolución N° 12/2026",
      "resumen": "Aprobación de nuevos aranceles del ejercicio 2026.",
      "archivo_nombre": "resolucion-12-2026.pdf",
      "archivo_ruta": "uploads/boletin/9f2c4e1a0b3d5c7f8a9b0c1d2e3f4a5b.pdf",
      "archivo_tipo": "application/pdf",
      "archivo_tamano": 245760,
      "usuario_abm": "admin",
      "created_at": "2026-08-27 12:19:18",
      "updated_at": "2026-08-28 09:11:03"
    }
  ],
  "meta": { "page": 1, "limit": 10, "total": 1, "pages": 1 }
}
```

### 4.2 Obtener un boletín

`GET /api/v1/boletines-oficiales/{id}`

```bash
curl -H "Authorization: Bearer $API_API_KEY" \
  "http://localhost/cpee/api/v1/boletines-oficiales/1"
```

```json
{
  "success": true,
  "data": {
    "id": 1,
    "titulo": "Resolución N° 12/2026",
    "resumen": "Aprobación de nuevos aranceles del ejercicio 2026.",
    "archivo_nombre": "resolucion-12-2026.pdf",
    "archivo_ruta": "uploads/boletin/9f2c4e1a0b3d5c7f8a9b0c1d2e3f4a5b.pdf",
    "archivo_tipo": "application/pdf",
    "archivo_tamano": 245760,
    "usuario_abm": "admin",
    "created_at": "2026-08-27 12:19:18",
    "updated_at": "2026-08-28 09:11:03"
  }
}
```

Errores: `404` si no existe, `400` si el ID es inválido.

## 5. Novedades

Recurso en plural: `/api/v1/novedades`. Orden: más recientes primero (`fecha_publicacion DESC, id DESC`). Incluye autor y roles destinatarios.

### 5.1 Listar novedades

`GET /api/v1/novedades`

Parámetros de consulta (todos opcionales):

| Parámetro | Tipo | Default | Descripción |
| :-- | :-- | :-- | :-- |
| `page` | int | `1` | Página actual. |
| `limit` | int | `50` | Tamaño de página (máx. `100`). |
| `q` | string | — | Búsqueda sobre `titulo` y `contenido` (ILIKE). |
| `publicada` | bool | — | Filtro por estado de publicación: `true` o `false`. |

```bash
curl -H "Authorization: Bearer $API_API_KEY" \
  "http://localhost/cpee/api/v1/novedades?page=1&limit=10&publicada=true"
```

```json
{
  "success": true,
  "data": [
    {
      "id": 3,
      "usuario_id": 2,
      "titulo": "Nuevos horarios de atención",
      "contenido": "Desde el 1/9 la oficina atiende de 8 a 14 hs.",
      "publicado": true,
      "fecha_publicacion": "2026-08-30 10:00:00",
      "archivo_nombre": null,
      "archivo_ruta": null,
      "archivo_tipo": null,
      "archivo_tamano": null,
      "autor": "Ana Gómez",
      "roles_nombres": "Todos",
      "roles": [1, 2, 3],
      "usuario_abm": "admin",
      "created_at": "2026-08-30 10:00:00",
      "updated_at": "2026-08-30 10:00:00"
    }
  ],
  "meta": { "page": 1, "limit": 10, "total": 1, "pages": 1 }
}
```

> En el listado `roles` es `null`; en el detalle (5.2) se incluye la lista de IDs de roles destinatarios.

### 5.2 Obtener una novedad

`GET /api/v1/novedades/{id}`

```bash
curl -H "Authorization: Bearer $API_API_KEY" \
  "http://localhost/cpee/api/v1/novedades/3"
```

```json
{
  "success": true,
  "data": {
    "id": 3,
    "usuario_id": 2,
    "titulo": "Nuevos horarios de atención",
    "contenido": "Desde el 1/9 la oficina atiende de 8 a 14 hs.",
    "publicado": true,
    "fecha_publicacion": "2026-08-30 10:00:00",
    "archivo_nombre": null,
    "archivo_ruta": null,
    "archivo_tipo": null,
    "archivo_tamano": null,
    "autor": "Ana Gómez",
    "roles_nombres": "Todos",
    "roles": [1, 2, 3],
    "usuario_abm": "admin",
    "created_at": "2026-08-30 10:00:00",
    "updated_at": "2026-08-30 10:00:00"
  }
}
```

Errores: `404` si no existe, `400` si el ID es inválido.

## 6. Ejemplo de error (422)

Parámetro `publicada` inválido:

```json
{
  "success": false,
  "message": "Parámetro \"publicada\" inválido.",
  "errors": {
    "publicada": "Valores permitidos: true o false."
  }
}
```

## 7. Estructura del código

```
public/index.php                                      → Router global (+ bloque API /api/v1)
app/Controllers/Api/ApiController.php                 → Base abstracta: auth Bearer, JSON, auditoría
app/Controllers/Api/BoletinesOficialesController.php  → GET index/show de boletines oficiales
app/Controllers/Api/NovedadesController.php           → GET index/show de novedades
app/Models/BoletinOficialModel.php                    → + getPaginated(), count() (paginación/búsqueda)
app/Models/NovedadModel.php                           → + getPaginated(), count() (paginación/filtros)
.env                                                  → API_ENABLED, API_API_KEY
```

Los `archivo_ruta` apuntan a archivos en `uploads/` (fuera del Document Root, no accesibles por web). La descarga pública de los PDF se resuelve por los endpoints internos `/cpee/boletin-oficial/descargar/{id}` y `/cpee/novedades/descargar/{id}` (con sesión), no por la API.

## 8. Extensiones futuras

- Endpoint de descarga de PDF por la API: `GET /api/v1/boletines-oficiales/{id}/archivo` y `GET /api/v1/novedades/{id}/archivo`.
- Sub-versiones (`v2`) sin romper `v1`: basta crear el controlador correspondiente bajo `app/Controllers/Api/`.
- Nuevos recursos (ej. `obras-sociales`) junto a los existentes en la misma convención.
