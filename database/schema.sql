CREATE TYPE estado_matricula AS ENUM ('Activa', 'Suspendida', 'Inactiva');

CREATE TYPE tipo_movimiento AS ENUM ('Ingreso', 'Egreso');

CREATE TYPE rol_usuario AS ENUM ('Admin', 'Tesoreria', 'Mesa de Entradas', 'Directivo');

CREATE TABLE roles (
    id SERIAL PRIMARY KEY,
    nombre VARCHAR(50) UNIQUE NOT NULL,
    descripcion TEXT,
    usuario_abm VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO
    roles (nombre, descripcion)
VALUES (
        'Admin',
        'Administrador del sistema'
    );

INSERT INTO
    roles (nombre, descripcion)
VALUES (
        'Tesoreria',
        'Tesoreria del sistema'
    );

INSERT INTO
    roles (nombre, descripcion)
VALUES (
        'Mesa de Entradas',
        'Mesa de Entradas del sistema'
    );

INSERT INTO
    roles (nombre, descripcion)
VALUES (
        'Directivo',
        'Directivo del sistema'
    );

CREATE TABLE permisos (
    id SERIAL PRIMARY KEY,
    nombre VARCHAR(100) UNIQUE NOT NULL,
    descripcion TEXT,
    usuario_abm VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO
    permisos (nombre, descripcion)
VALUES (
        'crear_profesionales',
        'Permite crear profesionales'
    );

INSERT INTO
    permisos (nombre, descripcion)
VALUES (
        'editar_profesionales',
        'Permite editar profesionales'
    );

INSERT INTO
    permisos (nombre, descripcion)
VALUES (
        'eliminar_profesionales',
        'Permite eliminar profesionales'
    );

INSERT INTO
    permisos (nombre, descripcion)
VALUES (
        'ver_profesionales',
        'Permite ver profesionales'
    );

CREATE TABLE rol_permisos (
    rol_id INT REFERENCES roles (id) ON DELETE CASCADE,
    permiso_id INT REFERENCES permisos (id) ON DELETE CASCADE,
    usuario_abm VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (rol_id, permiso_id)
);

INSERT INTO rol_permisos (rol_id, permiso_id) VALUES (1, 1);

INSERT INTO rol_permisos (rol_id, permiso_id) VALUES (1, 2);

INSERT INTO rol_permisos (rol_id, permiso_id) VALUES (1, 3);

INSERT INTO rol_permisos (rol_id, permiso_id) VALUES (1, 4);

CREATE TABLE sectores (
    id SERIAL PRIMARY KEY,
    nombre VARCHAR(100) UNIQUE NOT NULL,
    descripcion TEXT,
    usuario_abm VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO
    sectores (nombre, descripcion)
VALUES (
        'Tesoreria',
        'Tesoreria del sistema'
    );

INSERT INTO
    sectores (nombre, descripcion)
VALUES (
        'Mesa de Entradas',
        'Mesa de Entradas del sistema'
    );

CREATE TABLE usuarios (
    id SERIAL PRIMARY KEY,
    sector_id INT REFERENCES sectores (id) ON DELETE SET NULL,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    activo BOOLEAN DEFAULT TRUE,
    usuario_abm VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO
    usuarios (
        sector_id,
        nombre,
        email,
        password_hash,
        activo
    )
VALUES (
        1,
        'admin',
        'admin@admin.com',
        '$argon2id$v=19$m=65536,t=4,p=1$cmtEdkRzdFR6RC9aeVovVw$Q4aoOtqXVQmdIxIqoTldKu/KfENaJU8vGqOt43ec13U',
        TRUE
    );

CREATE TABLE usuario_roles (
    usuario_id INT REFERENCES usuarios (id) ON DELETE CASCADE,
    rol_id INT REFERENCES roles (id) ON DELETE CASCADE,
    usuario_abm VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (usuario_id, rol_id)
);

INSERT INTO usuario_roles (usuario_id, rol_id) VALUES (1, 1);

CREATE TABLE profesionales (
    id SERIAL PRIMARY KEY,
    nro_matricula VARCHAR(20) UNIQUE NOT NULL,
    DNI VARCHAR(20) UNIQUE NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    email VARCHAR(150),
    telefono VARCHAR(50),
    localidad VARCHAR(100),
    direccion VARCHAR(200),
    estado estado_matricula DEFAULT 'Activa',
    fecha_matriculacion DATE NOT NULL,
    observaciones TEXT,
    foto VARCHAR(500),
    usuario_abm VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO
    profesionales (
        nro_matricula,
        DNI,
        nombre,
        apellido,
        email,
        telefono,
        estado,
        fecha_matriculacion,
        observaciones
    )
VALUES (
        '123456',
        '12345678',
        'Juan',
        'Perez',
        'juan@profesional.com',
        '123456789',
        'Activa',
        '2022-01-01',
        '123456'
    );

CREATE TABLE caja_movimientos (
    id SERIAL PRIMARY KEY,
    usuario_id INT REFERENCES usuarios (id) ON DELETE SET NULL,
    profesional_id INT references profesionales (id) ON DELETE SET NULL,
    tipo tipo_movimiento NOT NULL,
    concepto VARCHAR(255) NOT NULL,
    tipo_comprobante VARCHAR(50),
    punto_venta VARCHAR(10),
    nro_comprobante VARCHAR(50),
    cuit VARCHAR(20),
    monto_neto NUMERIC(12, 2) NOT NULL,
    iva NUMERIC(12, 2) DEFAULT 0,
    monto_total NUMERIC(12, 2) NOT NULL,
    fecha_movimiento TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    archivo_nombre VARCHAR(255),
    archivo_ruta VARCHAR(500),
    archivo_tipo VARCHAR(100),
    archivo_tamano BIGINT,
    usuario_abm VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE obras_sociales (
    id SERIAL PRIMARY KEY,
    nombre VARCHAR(100) UNIQUE NOT NULL,
    descripcion TEXT,
    telefono VARCHAR(50),
    correo VARCHAR(150),
    url_sitio_web VARCHAR(255),
    logo VARCHAR(500),
    usuario_abm VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE boletines_oficiales (
    id SERIAL PRIMARY KEY,
    titulo VARCHAR(200) NOT NULL,
    resumen TEXT,
    archivo_nombre VARCHAR(255),
    archivo_ruta VARCHAR(500),
    archivo_tipo VARCHAR(100),
    archivo_tamano BIGINT,
    usuario_abm VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE novedades (
    id SERIAL PRIMARY KEY,
    usuario_id INT REFERENCES usuarios (id) ON DELETE SET NULL,
    titulo VARCHAR(200) NOT NULL,
    contenido TEXT NOT NULL,
    publicado BOOLEAN DEFAULT TRUE,
    fecha_publicacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    archivo_nombre VARCHAR(255),
    archivo_ruta VARCHAR(500),
    archivo_tipo VARCHAR(100),
    archivo_tamano BIGINT,
    usuario_abm VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE novedad_roles (
    novedad_id INT REFERENCES novedades (id) ON DELETE CASCADE,
    rol_id INT REFERENCES roles (id) ON DELETE CASCADE,
    usuario_abm VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (novedad_id, rol_id)
);

CREATE TABLE auditoria_logs (
    id SERIAL PRIMARY KEY,
    usuario_id INT, -- Can be null for login attempts
    accion VARCHAR(50) NOT NULL,
    tabla_afectada VARCHAR(100),
    registro_id INT,
    datos_anteriores JSONB,
    datos_nuevos JSONB,
    ip_origen VARCHAR(45),
    user_agent TEXT,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    usuario_abm VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Indexes for performance
CREATE INDEX idx_usuarios_email ON usuarios (email);

CREATE INDEX idx_profesionales_nro_matricula ON profesionales (nro_matricula);

CREATE INDEX idx_caja_movimientos_fecha ON caja_movimientos (fecha_movimiento);

CREATE INDEX idx_auditoria_logs_timestamp ON auditoria_logs (timestamp);

CREATE INDEX idx_auditoria_logs_tabla ON auditoria_logs (tabla_afectada);