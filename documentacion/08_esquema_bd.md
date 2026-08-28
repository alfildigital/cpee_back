# 09 — Esquema de Base de Datos (PostgreSQL)

Script fundacional: `database/schema.sql`

## Tipos (ENUM)

```sql
CREATE TYPE estado_matricula AS ENUM ('Activa', 'Suspendida', 'Inactiva');
CREATE TYPE tipo_movimiento  AS ENUM ('Ingreso', 'Egreso');
CREATE TYPE rol_usuario      AS ENUM ('Admin', 'Tesoreria', 'Mesa de Entradas', 'Directivo');
```

> El tipo `rol_usuario` está declarado pero **no se utiliza** como tipo de columna en el esquema actual (los roles se manejan por tabla `roles`).

## Tablas

### `roles`
| Campo | Tipo | Notas |
| :-- | :-- | :-- |
| `id` | SERIAL PK | |
| `nombre` | VARCHAR(50) UNIQUE | |
| `descripcion` | TEXT | |

Seeds: `Admin`, `Tesoreria`, `Mesa de Entradas`, `Directivo`.

### `permisos`
| Campo | Tipo | Notas |
| :-- | :-- | :-- |
| `id` | SERIAL PK | |
| `nombre` | VARCHAR(100) UNIQUE | |
| `descripcion` | TEXT | |

Seeds: `crear_profesionales`, `editar_profesionales`, `eliminar_profesionales`, `ver_profesionales`.

### `rol_permisos` (N:N)
| Campo | Referencia |
| :-- | :-- |
| `rol_id` | FK `roles` (CASCADE) |
| `permiso_id` | FK `permisos` (CASCADE) |
| PK | (`rol_id`, `permiso_id`) |

Seed base: rol `Admin` (id 1) con los 4 permisos de profesionales.

### `sectores`
| Campo | Tipo | Notas |
| :-- | :-- | :-- |
| `id` | SERIAL PK | |
| `nombre` | VARCHAR(100) UNIQUE | |
| `descripcion` | TEXT | |

Seeds: `Tesoreria`, `Mesa de Entradas`.

### `usuarios`
| Campo | Tipo | Notas |
| :-- | :-- | :-- |
| `id` | SERIAL PK | |
| `sector_id` | FK `sectores` (SET NULL) | |
| `nombre` | VARCHAR(100) | |
| `email` | VARCHAR(150) UNIQUE | |
| `password_hash` | VARCHAR(255) | ARGON2ID |
| `activo` | BOOLEAN | default TRUE |
| `created_at` / `updated_at` | TIMESTAMP | |

Seed: `admin@admin.com` (hash argon2id de ejemplo).

### `usuario_roles` (N:N)
| Campo | Referencia |
| :-- | :-- |
| `usuario_id` | FK `usuarios` (CASCADE) |
| `rol_id` | FK `roles` (CASCADE) |
| PK | (`usuario_id`, `rol_id`) |

Seed: usuario admin → rol Admin.

### `profesionales`
| Campo | Tipo | Notas |
| :-- | :-- | :-- |
| `id` | SERIAL PK | |
| `nro_matricula` | VARCHAR(20) UNIQUE | |
| `DNI` | VARCHAR(20) UNIQUE | |
| `nombre` | VARCHAR(100) | |
| `apellido` | VARCHAR(100) | |
| `email` | VARCHAR(150) | |
| `telefono` | VARCHAR(50) | |
| `localidad` | VARCHAR(100) | |
| `direccion` | VARCHAR(200) | |
| `estado` | `estado_matricula` | default `'Activa'` |
| `fecha_matriculacion` | DATE | |
| `legajo` | TEXT | |
| `foto` | VARCHAR(500) | ruta relativa de la foto (fuera del root) |
| `created_at` / `updated_at` | TIMESTAMP | |

Seed: `Juan Perez`, matrícula `123456`, DNI `12345678`.

### `caja_movimientos`
| Campo | Tipo | Notas |
| :-- | :-- | :-- |
| `id` | SERIAL PK | |
| `usuario_id` | FK `usuarios` (SET NULL) | |
| `profesional_id` | FK `profesionales` (SET NULL) | |
| `tipo` | `tipo_movimiento` | `Ingreso`/`Egreso` |
| `concepto` | VARCHAR(255) | |
| `tipo_comprobante` | VARCHAR(50) | |
| `punto_venta` | VARCHAR(10) | |
| `nro_comprobante` | VARCHAR(50) | |
| `cuit` | VARCHAR(20) | |
| `monto_neto` | NUMERIC(12,2) | |
| `iva` | NUMERIC(12,2) | default 0 |
| `monto_total` | NUMERIC(12,2) | |
| `fecha_movimiento` | TIMESTAMP | default now |
| `created_at` | TIMESTAMP | |
| `archivo_nombre` | VARCHAR(255) | adjunto: nombre original |
| `archivo_ruta` | VARCHAR(500) | adjunto: ruta relativa (fuera del root) |
| `archivo_tipo` | VARCHAR(100) | adjunto: MIME |
| `archivo_tamano` | BIGINT | adjunto: tamaño en bytes |

### `obras_sociales`
| Campo | Tipo | Notas |
| :-- | :-- | :-- |
| `id` | SERIAL PK | |
| `nombre` | VARCHAR(100) UNIQUE | |
| `descripcion` | TEXT | |
| `telefono` | VARCHAR(50) | |
| `correo` | VARCHAR(150) | |
| `url_sitio_web` | VARCHAR(255) | |
| `logo` | VARCHAR(500) | ruta relativa del PNG (fuera del root) |
| `created_at` / `updated_at` | TIMESTAMP | |

### `novedades`
| Campo | Tipo | Notas |
| :-- | :-- | :-- |
| `id` | SERIAL PK | |
| `usuario_id` | FK `usuarios` (SET NULL) | autor |
| `titulo` | VARCHAR(200) | |
| `contenido` | TEXT | |
| `publicado` | BOOLEAN | default TRUE |
| `fecha_publicacion` | TIMESTAMP | default now |
| `archivo_nombre` | VARCHAR(255) | adjunto PDF: nombre original |
| `archivo_ruta` | VARCHAR(500) | adjunto PDF: ruta relativa (fuera del root) |
| `archivo_tipo` | VARCHAR(100) | adjunto PDF: MIME |
| `archivo_tamano` | BIGINT | adjunto PDF: tamaño en bytes |

### `novedad_roles` (N:N)
| Campo | Referencia |
| :-- | :-- |
| `novedad_id` | FK `novedades` (CASCADE) |
| `rol_id` | FK `roles` (CASCADE) |
| PK | (`novedad_id`, `rol_id`) |

### `auditoria_logs`
| Campo | Tipo | Notas |
| :-- | :-- | :-- |
| `id` | SERIAL PK | |
| `usuario_id` | INT | NULL en intentos de login |
| `accion` | VARCHAR(50) | INSERT/UPDATE/DELETE/LOGIN |
| `tabla_afectada` | VARCHAR(100) | |
| `registro_id` | INT | |
| `datos_anteriores` | JSONB | snapshot previo |
| `datos_nuevos` | JSONB | snapshot posterior |
| `ip_origen` | VARCHAR(45) | |
| `user_agent` | TEXT | |
| `timestamp` | TIMESTAMP | default now |

## Índices

```sql
CREATE INDEX idx_usuarios_email              ON usuarios (email);
CREATE INDEX idx_profesionales_nro_matricula ON profesionales (nro_matricula);
CREATE INDEX idx_caja_movimientos_fecha      ON caja_movimientos (fecha_movimiento);
CREATE INDEX idx_auditoria_logs_timestamp    ON auditoria_logs (timestamp);
CREATE INDEX idx_auditoria_logs_tabla        ON auditoria_logs (tabla_afectada);
```

## Estado real de la BD (verificado)

Tablas existentes en `cpee` (2026-08): `auditoria_logs`, `caja_movimientos`, `novedad_roles`, `novedades`, `obras_sociales`, `permisos`, `profesionales`, `rol_permisos`, `roles`, `sectores`, `usuario_roles`, `usuarios`.

### Verificación de tipos

- El tipo `estado_matricula` **está creado** en la BD, y la columna `profesionales.estado` es de tipo `USER-DEFINED` (enum). El esquema está aplicado correctamente para esta tabla.

### Nota pendiente

- El tipo `rol_usuario` está declarado en `schema.sql` pero **no se utiliza** como tipo de columna (los roles se gestionan por tabla `roles`). Inofensivo; solo conviene documentarlo o retirarlo si no se usará.
- Los permisos granulares (`permisos`/`rol_permisos`) están sembrados (crear/editar/eliminar/ver profesionales) pero **aún no se consumen** en la lógica de los controladores (ver `02_seguridad.md` — RBAC esbozado).

## Migraciones aplicadas (2026-08)

Se aplicaron columnas adicionales a `caja_movimientos` para el respaldo documental:

```sql
ALTER TABLE caja_movimientos
  ADD COLUMN IF NOT EXISTS archivo_nombre VARCHAR(255),
  ADD COLUMN IF NOT EXISTS archivo_ruta  VARCHAR(500),
  ADD COLUMN IF NOT EXISTS archivo_tipo  VARCHAR(100),
  ADD COLUMN IF NOT EXISTS archivo_tamano BIGINT;
```

> El script `database/schema.sql` ya incluye estas columnas para instalaciones nuevas. Los archivos subidos se almacenan en la carpeta `uploads/` (fuera del Document Root) y se sirven por controlador; ver `06_gestion_caja.md`.

### `novedades.archivo_*` (PDF adjunto)

```sql
ALTER TABLE novedades
  ADD COLUMN IF NOT EXISTS archivo_nombre VARCHAR(255),
  ADD COLUMN IF NOT EXISTS archivo_ruta  VARCHAR(500),
  ADD COLUMN IF NOT EXISTS archivo_tipo  VARCHAR(100),
  ADD COLUMN IF NOT EXISTS archivo_tamano BIGINT;
```

> Permite adjuntar un PDF a cada novedad. El archivo se guarda en `uploads/novedades/` (fuera del Document Root) y se sirve por controlador (`/cpee/novedades/descargar/{id}`). `database/schema.sql` la incluye para instalaciones nuevas.

### `profesionales.foto`

```sql
ALTER TABLE profesionales ADD COLUMN IF NOT EXISTS foto VARCHAR(500);
```

> La columna `foto` guarda la **ruta relativa** de la foto del matriculado (p. ej. `uploads/fotos/<hex>.jpg`), almacenada fuera del Document Root y servida por controlador (ver `05_gestion_profesionales.md`). `database/schema.sql` la incluye para instalaciones nuevas.

### `profesionales.localidad` / `profesionales.direccion`

```sql
ALTER TABLE profesionales
  ADD COLUMN IF NOT EXISTS localidad VARCHAR(100),
  ADD COLUMN IF NOT EXISTS direccion VARCHAR(200);
```

> Datos de contacto/dirección del matriculado. `database/schema.sql` los incluye para instalaciones nuevas.

### Tabla `obras_sociales`

```sql
CREATE TABLE IF NOT EXISTS obras_sociales (
    id             SERIAL PRIMARY KEY,
    nombre         VARCHAR(100) NOT NULL UNIQUE,
    descripcion    TEXT,
    telefono       VARCHAR(50),
    correo         VARCHAR(150),
    url_sitio_web  VARCHAR(255),
    logo           VARCHAR(500),
    created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

> Módulo de Obra Social (CRUD). El campo `logo` guarda la ruta relativa de un **PNG** (p. ej. `uploads/logos/<hex>.png`), almacenado fuera del Document Root y servido por controlador (`/cpee/obras-sociales/logo/{id}`). `database/schema.sql` ya crea esta tabla.
