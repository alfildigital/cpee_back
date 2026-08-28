# 01 — Arquitectura del Sistema

## 1. Resumen

CPEE es una **aplicación MVC pura (frameworkless)** escrita en PHP 8.4 nativo, con PostgreSQL 17 y frontend Bootstrap 5 / SB Admin 2. No utiliza Composer, frameworks ni ORMs: **todas las dependencias se resuelven con código PHP nativo**.

## 2. Patrón de Ruteo (Clean URLs)

El flujo de una petición es:

```
Navegador
   │  GET /cpee/usuarios/editar/5
   ▼
.htaccess (raíz)            → reescribe hacia public/
   ▼
public/.htaccess            → reescribe a  public/index.php?url=usuarios/editar/5
   ▼
public/index.php            → Front Controller
   ├─ define ROOT_PATH
   ├─ spl_autoload_register → autoloader de la namespace App\
   ├─ Env::load()           → carga .env
   ├─ Security::startSession()
   └─ Router                → resuelve controlador/acción
         ▼
\App\Controllers\UsuariosController->editar('5')
```

### Convención de URL

`/modulo/accion/parametro...`

- `modulo`  → nombre del controlador (ej. `usuarios` → `UsuariosController`)
- `accion`  → método a invocar (ej. `editar`)
- `parametro...` → argumentos posicionales del método (ej. `5`)

**`public/index.php`** (`getUrl`): los segmentos se sanitizan con `preg_replace('/[^a-zA-Z0-9_]/', '')` antes de construir el nombre de clase `\App\Controllers\Ucfirst($controller).Controller` y de invocar la acción con `call_user_func_array()`.

### Tabla ruteo → controlador

| Ruta | Controlador | Acción |
| :-- | :-- | :-- |
| `/login` | `LoginController` | `index` |
| `/login/autenticar` | `LoginController` | `autenticar` |
| `/login/salir` | `LoginController` | `salir` |
| `/dashboard` | `DashboardController` | `index` |
| `/profesionales` | `ProfesionalesController` | `index` |
| `/profesionales/ver/{id}` | `ProfesionalesController` | `ver` |
| `/profesionales/crear` | `ProfesionalesController` | `crear` |
| `/profesionales/guardar` | `ProfesionalesController` | `guardar` (POST) |
| `/profesionales/editar/{id}` | `ProfesionalesController` | `editar` |
| `/profesionales/actualizar` | `ProfesionalesController` | `actualizar` (POST) |
| `/profesionales/eliminar` | `ProfesionalesController` | `eliminar` (POST) |
| `/caja` | `CajaController` | `index` |
| `/caja/crear/{tipo}/{profesional}` | `CajaController` | `crear` |
| `/caja/guardar` | `CajaController` | `guardar` (POST) |
| `/usuarios` | `UsuariosController` | `index` |
| `/usuarios/ver/{id}` | `UsuariosController` | `ver` |
| `/usuarios/crear` | `UsuariosController` | `crear` |
| `/usuarios/guardar` | `UsuariosController` | `guardar` (POST) |
| `/usuarios/editar/{id}` | `UsuariosController` | `editar` |
| `/usuarios/actualizar` | `UsuariosController` | `actualizar` (POST) |
| `/usuarios/eliminar` | `UsuariosController` | `eliminar` (POST) |
| `/auditoria` | `AuditoriaController` | `index` |
| `/api/novedades/getPublicadas` | `Api\NovedadesController` | `getPublicadas` |

> **Nota de consistencia**: el ruteo por convención de `index.php` construye `\App\Controllers\Ucfirst(modulo)Controller`. Por eso el endpoint de novedades se resuelve con la URL `api/novedades/getPublicadas` → `Api\NovedadesController` solo es accesible si el autoloader y la ruta coinciden; el directorio `app/Controllers/Api/` existe y la clase está en la sub-namespace `App\Controllers\Api`.

## 3. Front Controller — `public/index.php`

Responsabilidades:
1. **Autoloader manual** con `spl_autoload_register()` que mapea `App\` → `app/`.
2. Carga de entorno `\App\Core\Env::load()`.
3. Inicio seguro de sesión `\App\Core\Security::startSession()`.
4. **Guard de autenticación**: los módulos no listados en `$publicControllers = ['login']` exigen `$_SESSION['usuario_id']`; si no, se redirige a `/cpee/login`.
5. **Dispatch**: construye la clase y acciona el método, con *fallback* a `index` y respuesta 404 para controladores/acciones inexistentes.

## 4. Infraestructura Core (`app/Core/`)

### `Database` (Singleton PDO)
```php
App\Core\Database::getInstance()->getConnection(): PDO
```
- Conexión única `pgsql` a partir de variables de entorno.
- `PDO::ERRMODE_EXCEPTION`, `FETCH_ASSOC`, **`ATTR_EMULATE_PREPARES = false`** (preparación real, inmunidad a SQLi).
- En caso de fallo solo se loguea internamente (`error_log`); al usuario se le muestra un mensaje genérico.
- Previene clonación y serialización (`__clone`, `__wakeup`).

### `Env`
- Carga `.env` a `getenv()/$_ENV/$_SERVER` sin sobrescribir variables ya definidas.
- Ignora comentarios (`#`) y líneas sin `=`.
- Soporta valores entre comillas simples/dobles.

### `Security`
Clase estática de seguridad global. Ver documento `02_seguridad.md`.

## 5. BaseController (`app/Controllers/BaseController.php`)

Clase abstracta que centraliza utilidades comunes de los controladores:

| Método | Función |
| :-- | :-- |
| `getCurrentUserId(): int` | Devuelve `$_SESSION['usuario_id']` (0 si no existe). |
| `requireLogin(): void` | Redirige a `/cpee/login` si no hay sesión. |
| `requirePost(): void` | Exige método `POST`, si no redirige a `HTTP_REFERER`. |
| `requireCsrf(): void` | Valida el token CSRF de `$_POST`; si falla, redirige. |
| `redirect(string $url): void` | Emite `Location:` y termina. |
| `render(view, title, data)` | Extrae datos, captura la vista parcial y la inyecta en `layouts/base.php`. |

## 6. Manejo de vistas (`render`)

`BaseController::render()`:
1. `extract($data)` → variables disponibles en la vista.
2. `ob_start()` + `require` de la vista parcial (`app/Views/{modulo}/{vista}.php`).
3. Se captura el HTML en `$content`.
4. `require` del layout maestro `app/Views/layouts/base.php`, que imprime el sidebar, topbar, mensajes flash y `$content`.

## 7. Document Root seguro

Solo `public/` es accesible desde la web. El `.htaccess` de la raíz:

- Bloquea `app/`, `database/`, `documentacion/`, `.env`, `.git`, `.agent` y dotfiles.
- Redirige todo el tráfico hacia `public/`.

## 8. Buenas prácticas aplicadas

- `declare(strict_types=1);` al inicio de la mayoría de los archivos PHP.
- Tipado fuerte en propiedades y métodos (`int`, `string`, `?array`, `PDO`).
- Prepared statements en todos los accesos a datos.
- Mensajes de error genéricos al usuario, detalles por `error_log`.
