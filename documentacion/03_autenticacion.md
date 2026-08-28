# 03 — Módulo de Autenticación

Controlador: `app/Controllers/LoginController.php`
Modelo: `app/Models/UsuarioModel.php`
Vista: `app/Views/auth/login.php`

## Rutas

| Método | Ruta | Acción | Descripción |
| :-- | :-- | :-- | :-- |
| GET | `/login` | `index` | Muestra el formulario de login (si ya hay sesión, redirige a `/dashboard`). |
| POST | `/login/autenticar` | `autenticar` | Procesa credenciales y abre sesión. |
| GET | `/login/salir` | `salir` | Cierra la sesión de forma segura. |

> `login` es el único controlador **público** permitido por el Front Controller (`$publicControllers`).

## Flujo de autenticación

```
POST /login/autenticar
   │
   ├─ ¿ya logueado?            → redirect /dashboard
   ├─ ¿método POST?            → else redirect /login
   ├─ validateCSRFToken()      → fallo ⇒ flash 'danger' → /login
   ├─ UsuarioModel::autenticar(email, password)
   │     ├─ findByEmail() + roles
   │     ├─ usuario inactivo?  → null (credenciales inválidas)
   │     ├─ password_verify()  → fallo ⇒ null
   │     ├─ password_needs_rehash(ARGON2ID)? → re-hash
   │     └─ devuelve datos sin password_hash
   │
   ├─ ¿autenticación falló?  → logAudit(LOGIN fallo) → flash → /login
   ├─ Security::setSessionUser($usuario)
   │     ├─ $_SESSION[usuario_id/nombre/email/roles]
   │     └─ regenerateSession() (antifijación)
   ├─ logAudit(LOGIN ok)
   └─ redirect /dashboard
```

## Métodos relevantes de `UsuarioModel`

| Método | Función |
| :-- | :-- |
| `findByEmail(string $email): ?array` | Busca usuario por email y adjunta la lista de nombres de sus roles. |
| `autenticar(string $email, string $password): ?array` | Valida credenciales, inactividad y re-hash; **nunca** devuelve el hash. |

## Logout (`salir`)

`Security::logout()`:
1. Limpia `$_SESSION = []`.
2. Invalida la cookie de sesión (`setcookie` con expiración pasada).
3. `session_destroy()`.
4. Redirige a `/login`.

## Consideraciones / pendiente

- El **cambio forzoso de contraseña** (cuando la clave coincide con el nombre de usuario) está previsto en la arquitectura institucional (`$_SESSION['clave_debe_cambiarse']`) pero **aún no se implementa** una pantalla dedicada para forzar el cambio tras el primer ingreso.
- El usuario por defecto del esquema es `admin@admin.com` con password hash `argon2id` (ver `09_esquema_bd.md`).
