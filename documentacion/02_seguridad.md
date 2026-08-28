# 02 — Modelo de Seguridad

CPEE implementa una **seguridad de doble capa** (visual + lógica) y mitigaciones manuales nativas para las vulnerabilidades web clásicas, sin librerías externas.

## 1. Capas de control de acceso

1. **Capa visual (menú/sidebar):** se ocultan opciones de módulos no habilitados.
2. **Capa lógica (controladores):** `requireLogin()` en cada acción protege el backend.

> En el estado actual la validación por **permisos granular** (`perm_ver`, `perm_crear`, etc.) quedó **esbozada** en `MatriculaController::checkPermissions()` pero **no está conectada a tablas por contralor en los demás módulos**. Persiste la base de datos `permisos / rol_permisos` lista para implementar el RBAC completo (ver `09_esquema_bd.md`).

## 2. Sessión segura — `Security::startSession()`

- Nombre de sesión desde `SESSION_NAME` (`.env` → `CPEE_SECURE_SESSION`).
- Cookies de sesión con atributos:
  - `lifetime` (7200s por defecto),
  - `httponly=true`,
  - `samesite=Strict`,
  - `secure=true` solo si la petición es HTTPS.
- **Antifijación de sesión:** `regenerateSession()` (con borrado) tras el login correcto.

## 3. CSRF (Cross-Site Request Forgery)

- **Generación:** `Security::generateCSRFToken()` usa `bin2hex(random_bytes(32))`, guardado en `$_SESSION['csrf_token']`.
- **Validación:** `Security::validateCSRFToken()` compara con `===` (equivalente a `hash_equals` para cadenas de igual longitud).
- **Uso:** cada formulario POST incluye un `hidden` `csrf_token`; los controladores invocan `requireCsrf()` antes de procesar.
- Ver ejemplos en `index.php` de cada vista de creación/edición.

## 4. XSS (Cross-Site Scripting)

- **Entrada:** `Security::sanitizeInput()` aplica recursivamente `htmlspecialchars(strip_tags(trim()), ENT_QUOTES, 'UTF-8')` sobre `$_POST`.
- **Salida:** las vistas escapan los datos con `htmlspecialchars()` (ver `app/Views/layouts/base.php` y las vistas de los módulos).
- **Headers:** `Security::setSecurityHeaders()` emite `X-Frame-Options: DENY`, `X-XSS-Protection`, `X-Content-Type-Options: nosniff` y una `Content-Security-Policy` restrictiva.

## 5. SQL Injection

- Todas las consultas usan **Prepared Statements PDO** con placeholders.
- `Database` fuerza `PDO::ATTR_EMULATE_PREPARES = false`, garantizando preparación nativa contra el motor PostgreSQL.
- Los parámetros de ruteo se sanitizan (alfanuméricos) antes de usarse en comparaciones o de construir clases.

## 6. Contraseñas

- **Hash:** `password_hash($pass, PASSWORD_ARGON2ID)`.
- **Verificación:** `password_verify()`.
- **Re-hash automático:** `password_needs_rehash()` actualiza el hash si el algoritmo objetivo cambia.
- **Creación de usuarios:** la política institucional indica que los administradores **no definen claves manuales**; la clave por defecto es igual al nombre de usuario (`usuario_id`), lo que fuerza el cambio posterior (ver `03_autenticacion.md`).

## 7. Auditoría de operaciones — `Security::logAudit()`

Toda operación que muta datos (`INSERT`, `UPDATE`, `DELETE`, `LOGIN`) registra trazabilidad:

```php
Security::logAudit(
    ?int $usuarioId,
    string $accion,          // INSERT / UPDATE / DELETE / LOGIN
    string $tablaAfectada,   // nombre de tabla
    ?int $registroId,
    ?array $datosAnteriores, // snapshot JSONB previo
    ?array $datosNuevos      // snapshot JSONB posterior
);
```

- Captura `REMOTE_ADDR` (IP) y `HTTP_USER_AGENT`.
- Serializa los snapshots con `json_encode()` a columnas `JSONB`.
- `datosAnteriores` es `NULL` en `INSERT`/`LOGIN`; `datosNuevos` es `NULL` en `DELETE`.
- **Inmutabilidad:** no existen endpoints de modificación ni borrado de logs de auditoría; la tabla `auditoria_logs` es de solo-escritura por el sistema.

## 8. Mensajes flash

`Security::flash($tipo, $mensaje)` guarda en `$_SESSION['flash']` mensajes `success/danger/warning/info`; `getFlash()` los consume una sola vez. Se renderizan en el layout base.

## 9. Resumen de controles por operación

| Control | Login | CRUD módulos |
| :-- | :--: | :--: |
| Validación CSRF | ✔ | ✔ (`requireCsrf`) |
| Requiere sesión | — | ✔ (`requireLogin`) |
| Método POST forzado | ✔ | ✔ (`requirePost`) |
| Sanitización de entrada | ✔ | ✔ |
| Escape de salida | ✔ | ✔ |
| Log de auditoría | ✔ (LOGIN) | ✔ (INSERT/UPDATE/DELETE) |
