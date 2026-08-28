# Let's generate a comprehensive, structured Markdown file representing the complete architectural knowledge base of the SEM System.
# We will write the python script to generate `base_conocimiento_sem.md`.

md_content = """# Base de Conocimiento Técnico y Arquitectónico: Sistema SEM

Este documento constituye la **Base de Conocimiento (Knowledge Base)** integral y oficial del **Sistema SEM (Sistema de Expedientes para Ministros)**. Está estructurado como contexto de referencia técnica de alta precisión para modelos de Inteligencia Artificial y desarrolladores.

---

## 1. Visión General del Proyecto

- **Nombre del Sistema:** SEM (Sistema de Expedientes para Ministros).
- **Propósito:** Plataforma web interna para la gestión integral, despapelización, seguimiento, firma digital y auditoría forense de expedientes administrativos y judiciales del Superior Tribunal de Justicia.
- **Filosofía de Desarrollo:** Código robusto, seguro, sin dependencias pesadas de terceros (Frameworkless PHP), con arquitectura de micro-componentes MVC, tipado estricto y auditoría forense nativa.

---

## 2. Stack Tecnológico Estándar

| Capa / Componente | Tecnología / Estándar | Especificaciones y Versión |
| :--- | :--- | :--- |
| **Lenguaje Backend** | PHP | PHP 8.4 puro (tipado estricto `declare(strict_types=1)`) |
| **Base de Datos** | PostgreSQL | PostgreSQL 17 (soporte JSONB nativo, CTEs, DISTINCT ON) |
| **Servidor Web** | Apache | Apache 2.4+ (`mod_rewrite`, `.htaccess` perimetral) |
| **Frontend UI** | HTML5 / CSS3 / JS | SB Admin 2 (Bootstrap 5 adaptado, jQuery, DataTables local) |
| **Generación PDF** | mPDF / Ghostscript | Generación de PDFs judiciales, foliación y sellado al vuelo |
| **IA / RAG Local** | Ollama / pgvector / Chunks | Modelos locales (`nomic-embed-text`, Chat RAG local en Visor) |
| **Firma Digital** | PKCS#11 / App Local | Firma por Hardware USB (SafeNet/Tokens), canal `.fdsti` |

---

## 3. Arquitectura de Software y Patrones de Diseño

### 3.1 Modelo MVC Puro (Frameworkless)
- **Patrón de Ruteo:** Clean URLs mediante Apache `.htaccess` apuntando a `public/index.php`.
- **Estructura de URLs:** `/modulo/accion/parametro` (ejemplo: `/usuarios/editar/5` invoca a `UsuariosController->editar(5)`).
- **Document Root Seguro:** Únicamente el directorio `public/` es accesible desde la web (`Options -Indexes`, desprotección nula del backend).

### 3.2 Acceso a Datos y Persistencia
- **Conexión:** Singleton PDO en `App\\Core\\Database`.
- **Inmunidad SQL Injection:** Deshabilitación explícita de `PDO::ATTR_EMULATE_PREPARES` para forcing de preparación real de sentencias en PostgreSQL.
- **Model Base Abstracto (`App\\Core\\Model`):** Encapsula métodos CRUD genéricos (`insert`, `update`, `delete`, `softDelete`, `find`, `all`).

---

## 4. Auditoría Forense Inmutable (Sello Institucional)

### 4.1 Principio Antifraude JSONB
- **Disparo Transaccional Implícito:** Cualquier operación que altere datos (`INSERT`, `UPDATE`, `DELETE`) en un modelo derivado invoca automáticamente el método privado `$this->audit()`.
- **Formato del Log:**
  - `usuario_id`: ID del operador en sesión (o `NULL` si es evento del sistema/público).
  - `ip`: Dirección IP del origen del requerimiento.
  - `tabla`: Nombre exacto de la tabla afectada.
  - `registro_id`: Primary Key del registro afectado.
  - `accion`: Identificador en mayúsculas (`INSERT`, `UPDATE`, `DELETE`, `ENVIAR_PASE`, `ACEPTAR`, etc.).
  - `estado_anterior`: Snapshot completo pre-modificación en `JSONB`.
  - `estado_nuevo`: Snapshot completo post-modificación en `JSONB`.
  - `creado_at`: Timestamp de precisión transaccional.
- **Inmutabilidad:** La tabla `auditoria` es de solo lectura; no existen endpoints de modificación ni borrado de logs.

---

## 5. Modelo de Seguridad y Control de Acceso (RBAC)

### 5.1 Seguridad de Doble Capa
1. **Capa Visual (Menú Dinámico):** `requireAuth()` evalúa si el grupo de usuario posee el módulo habilitado en `menu_permisos`. Si no, oculta la opción del Sidebar y bloquea el renderizado.
2. **Capa Lógica / Backend (`validarPermiso`):** Validación estricta por acción CRUD en el controlador:
   - `ver` $\rightarrow$ `perm_ver`
   - `crear` $\rightarrow$ `perm_crear`
   - `editar` $\rightarrow$ `perm_editar`
   - `eliminar` $\rightarrow$ `perm_eliminar`

### 5.2 Políticas de Credenciales y Perímetro
- **Generación y Reseteo:** Los administradores no pueden definir claves manuales. Al crear o resetear un usuario, la clave por defecto es igual al nombre de usuario (`usuario_id`).
- **Middleware de Cambio Forzoso:** Si `password_verify` detecta que la clave coincide con el nombre de usuario, se activa `$_SESSION['clave_debe_cambiarse'] = true`, forzando el cambio inmediato de contraseña antes de habilitar la navegación.
- **Protección CSRF:** Generación con `random_bytes()` y validación mediante `hash_equals()`.
- **Sanitización XSS:** Escape obligatorio de salidas en vistas mediante `App\\Core\\View::escape()`.

---

## 6. Lógica de Negocio de Expedientes y Pases

### 6.1 Estados del Ciclo de Vida