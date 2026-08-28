# CPEE Gestión — Índice de Documentación Técnica

Sistema administrativo web para la gestión de **C**olegios de **P**rofesionales y **E**ntidades (**E**mpleadores/**E**conómicas), construido con **PHP 8.4 puro** (frameworkless), **PostgreSQL 17** y **Bootstrap 5 / SB Admin 2**.

> Filosofía: cero dependencias externas (sin Composer, sin frameworks, sin ORMs). Código MVC nativo, tipado estricto y auditoría forense por defecto.

---

## Documentos

| N° | Archivo | Contenido |
| :-- | :-- | :-- |
| 00 | `00_indice.md` | Este índice general del proyecto. |
| 01 | `01_arquitectura.md` | Arquitectura MVC, ruteo, bootstrap e infraestructura Core. |
| 02 | `02_seguridad.md` | Modelo de seguridad: sesión, CSRF, XSS, SQLi, auditoría. |
| 03 | `03_autenticacion.md` | Módulo de autenticación (login / logout / fuerza de cambio). |
| 04 | `04_gestion_usuarios.md` | Módulo CRUD de usuarios y asignación de roles/sectores. |
| 05 | `05_gestion_profesionales.md` | Módulo CRUD de matriculados/profesionales. |
| 06 | `06_gestion_caja.md` | Módulo de caja y tesorería (ingresos/egresos). |
| 07 | `07_auditoria.md` | Módulo de auditoría forense y trazabilidad. |
| 08 | `08_dashboard_novedades.md` | Dashboard de métricas y API pública de novedades. |
| 09 | `09_gestion_roles_permisos.md` | Módulo de roles y permisos (CRUD de roles + catálogo). |
| 10 | `esquema_bd.md` | Referencia completa de tablas, tipos e índices PostgreSQL. |

---

## Mapa de módulos

```
/cpee
├── login            → Autenticación (público)
├── dashboard        → Panel principal con métricas
├── profesionales    → CRUD de matriculados
├── novedades        → CRUD de novedades con PDF adjunto (por rol)
├── caja             → Movimientos de caja / tesorería
├── obras-sociales   → CRUD de obras sociales (con logo PNG)
├── usuarios         → CRUD de usuarios del sistema
├── roles            → CRUD de roles + asignación de permisos (catálogo)
├── auditoria        → Visualización de logs de auditoría
└── api/novedades    → Endpoint JSON público de novedades (para frontend externo)
```

## Estructura de directorios

```
cpee/
├── .env                      → Variables de entorno (credenciales, sesión)
├── .htaccess                 → Rewriting + bloqueo del backend (perimetral)
├── documentacion/            → Esta documentación técnica
├── uploads/                  → Archivos subidos (fuera del root, no accesible por web)
├── public/                   → ÚNICA raíz web (Document Root)
│   ├── index.php             → Front Controller / Autoloader / Router
│   ├── .htaccess             → Rewrite de clean URLs hacia index.php
│   └── assets/               → CSS/JS/vendor locales (SB Admin 2, jQuery, DataTables)
├── app/
│   ├── Core/                 → Database, Env, Security, Upload, Pdf (infraestructura)
│   ├── Controllers/          → Controladores MVC (+ subcarpeta Api/)
│   ├── Models/               → Modelos PDO (acceso a datos)
│   └── Views/                → Vistas (layouts/, auth/, y por módulo)
└── database/
    └── schema.sql            → Script fundacional PostgreSQL
```
