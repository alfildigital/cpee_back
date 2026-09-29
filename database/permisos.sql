-- Active: 1773059763749@@127.0.0.1@5432@cpee
-- ROL
INSERT INTO
    roles (
        nombre,
        descripcion,
        usuario_abm,
        created_at
    )
VALUES (
        'root',
        'Administrador General del Sistema',
        'sistema',
        now()
    );

-- CREACION DE PERMISOS

-- novedades
INSERT INTO
    permisos (
        nombre,
        descripcion,
        usuario_abm,
        created_at
    )
VALUES (
        'novedades_crear',
        'Permite agregar registros de novedades',
        'admin',
        now()
    );

INSERT INTO
    permisos (
        nombre,
        descripcion,
        usuario_abm,
        created_at
    )
VALUES (
        'novedades_editar',
        'Permite editar registros de novedades',
        'admin',
        now()
    );

INSERT INTO
    permisos (
        nombre,
        descripcion,
        usuario_abm,
        created_at
    )
VALUES (
        'novedades_ver',
        'Permite ver registros de novedades',
        'admin',
        now()
    );

INSERT INTO
    permisos (
        nombre,
        descripcion,
        usuario_abm,
        created_at
    )
VALUES (
        'novedades_eliminar',
        'Permite eliminar registros de novedades',
        'admin',
        now()
    );

-- profesionales
INSERT INTO
    permisos (
        nombre,
        descripcion,
        usuario_abm,
        created_at
    )
VALUES (
        'profesionales_crear',
        'Permite agregar registros de Matriculados',
        'admin',
        now()
    );

INSERT INTO
    permisos (
        nombre,
        descripcion,
        usuario_abm,
        created_at
    )
VALUES (
        'profesionales_editar',
        'Permite editar registros de Matriculados',
        'admin',
        now()
    );

INSERT INTO
    permisos (
        nombre,
        descripcion,
        usuario_abm,
        created_at
    )
VALUES (
        'profesionales_ver',
        'Permite ver registros de Matriculados',
        'admin',
        now()
    );

INSERT INTO
    permisos (
        nombre,
        descripcion,
        usuario_abm,
        created_at
    )
VALUES (
        'profesionales_eliminar',
        'Permite eliminar registros de Matriculados',
        'admin',
        now()
    );

-- movimientos
INSERT INTO
    permisos (
        nombre,
        descripcion,
        usuario_abm,
        created_at
    )
VALUES (
        'movimientos_crear',
        'Permite agregar registros de Movimientos ingresos/egresos',
        'admin',
        now()
    );

INSERT INTO
    permisos (
        nombre,
        descripcion,
        usuario_abm,
        created_at
    )
VALUES (
        'movimientos_editar',
        'Permite editar registros de Movimientos ingresos/egresos',
        'admin',
        now()
    );

INSERT INTO
    permisos (
        nombre,
        descripcion,
        usuario_abm,
        created_at
    )
VALUES (
        'movimientos_ver',
        'Permite ver registros de Movimientos ingresos/egresos',
        'admin',
        now()
    );

INSERT INTO
    permisos (
        nombre,
        descripcion,
        usuario_abm,
        created_at
    )
VALUES (
        'movimientos_eliminar',
        'Permite eliminar registros de Movimientos ingresos/egresos',
        'admin',
        now()
    );

-- obra social
INSERT INTO
    permisos (
        nombre,
        descripcion,
        usuario_abm,
        created_at
    )
VALUES (
        'obra_social_crear',
        'Permite agregar registros de Obra Social',
        'admin',
        now()
    );

INSERT INTO
    permisos (
        nombre,
        descripcion,
        usuario_abm,
        created_at
    )
VALUES (
        'obra_social_editar',
        'Permite editar registros de Obra Social',
        'admin',
        now()
    );

INSERT INTO
    permisos (
        nombre,
        descripcion,
        usuario_abm,
        created_at
    )
VALUES (
        'obra_social_ver',
        'Permite ver registros de Obra Social',
        'admin',
        now()
    );

INSERT INTO
    permisos (
        nombre,
        descripcion,
        usuario_abm,
        created_at
    )
VALUES (
        'obra_social_eliminar',
        'Permite eliminar registros de Obra Social',
        'admin',
        now()
    );

-- boletin oficial
INSERT INTO
    permisos (
        nombre,
        descripcion,
        usuario_abm,
        created_at
    )
VALUES (
        'boletin_oficial_crear',
        'Permite agregar registros de Boletin  Oficial',
        'admin',
        now()
    );

INSERT INTO
    permisos (
        nombre,
        descripcion,
        usuario_abm,
        created_at
    )
VALUES (
        'boletin_oficial_editar',
        'Permite editar registros de Boletin  Oficial',
        'admin',
        now()
    );

INSERT INTO
    permisos (
        nombre,
        descripcion,
        usuario_abm,
        created_at
    )
VALUES (
        'boletin_oficial_ver',
        'Permite ver registros de Boletin  Oficial',
        'admin',
        now()
    );

INSERT INTO
    permisos (
        nombre,
        descripcion,
        usuario_abm,
        created_at
    )
VALUES (
        'boletin_oficial_eliminar',
        'Permite eliminar registros de Boletin  Oficial',
        'admin',
        now()
    );
-- ussuarios
INSERT INTO
    permisos (
        nombre,
        descripcion,
        usuario_abm,
        created_at
    )
VALUES (
        'usuarios_crear',
        'Permite agregar registros de Usuarios',
        'admin',
        now()
    );

INSERT INTO
    permisos (
        nombre,
        descripcion,
        usuario_abm,
        created_at
    )
VALUES (
        'usuarios_editar',
        'Permite editar registros de Usuarios',
        'admin',
        now()
    );

INSERT INTO
    permisos (
        nombre,
        descripcion,
        usuario_abm,
        created_at
    )
VALUES (
        'usuarios_ver',
        'Permite ver registros de Usuarios',
        'admin',
        now()
    );

INSERT INTO
    permisos (
        nombre,
        descripcion,
        usuario_abm,
        created_at
    )
VALUES (
        'usuarios_eliminar',
        'Permite eliminar registros de Usuarios',
        'admin',
        now()
    );

-- roles
INSERT INTO
    permisos (
        nombre,
        descripcion,
        usuario_abm,
        created_at
    )
VALUES (
        'roles_crear',
        'Permite agregar registros de roles',
        'admin',
        now()
    );

INSERT INTO
    permisos (
        nombre,
        descripcion,
        usuario_abm,
        created_at
    )
VALUES (
        'roles_editar',
        'Permite editar registros de roles',
        'admin',
        now()
    );

INSERT INTO
    permisos (
        nombre,
        descripcion,
        usuario_abm,
        created_at
    )
VALUES (
        'roles_ver',
        'Permite ver registros de roles',
        'admin',
        now()
    );

INSERT INTO
    permisos (
        nombre,
        descripcion,
        usuario_abm,
        created_at
    )
VALUES (
        'roles_eliminar',
        'Permite eliminar registros de roles',
        'admin',
        now()
    );
-- permisos
INSERT INTO
    permisos (
        nombre,
        descripcion,
        usuario_abm,
        created_at
    )
VALUES (
        'permisos_crear',
        'Permite agregar registros de permisos',
        'admin',
        now()
    );

INSERT INTO
    permisos (
        nombre,
        descripcion,
        usuario_abm,
        created_at
    )
VALUES (
        'permisos_editar',
        'Permite editar registros de permisos',
        'admin',
        now()
    );

INSERT INTO
    permisos (
        nombre,
        descripcion,
        usuario_abm,
        created_at
    )
VALUES (
        'permisos_ver',
        'Permite ver registros de permisos',
        'admin',
        now()
    );

INSERT INTO
    permisos (
        nombre,
        descripcion,
        usuario_abm,
        created_at
    )
VALUES (
        'permisos_eliminar',
        'Permite eliminar registros de permisos',
        'admin',
        now()
    );

-- realcion rol - permiso
INSERT INTO
    rol_permisos (
        rol_id,
        permiso_id,
        usuario_abm,
        created_at
    )
VALUES (
        (
            SELECT id
            FROM roles
            WHERE
                nombre = 'admin'
        ),
        (
            SELECT id
            FROM permisos
            WHERE
                nombre = 'usuarios_crear'
        ),
        'admin',
        now()
    );

INSERT INTO
    rol_permisos (
        rol_id,
        permiso_id,
        usuario_abm,
        created_at
    )
VALUES (
        (
            SELECT id
            FROM roles
            WHERE
                nombre = 'admin'
        ),
        (
            SELECT id
            FROM permisos
            WHERE
                nombre = 'usuarios_editar'
        ),
        'admin',
        now()
    );

INSERT INTO
    rol_permisos (
        rol_id,
        permiso_id,
        usuario_abm,
        created_at
    )
VALUES (
        (
            SELECT id
            FROM roles
            WHERE
                nombre = 'admin'
        ),
        (
            SELECT id
            FROM permisos
            WHERE
                nombre = 'roles_crear'
        ),
        'admin',
        now()
    );

INSERT INTO
    rol_permisos (
        rol_id,
        permiso_id,
        usuario_abm,
        created_at
    )
VALUES (
        (
            SELECT id
            FROM roles
            WHERE
                nombre = 'admin'
        ),
        (
            SELECT id
            FROM permisos
            WHERE
                nombre = 'roles_editar'
        ),
        'admin',
        now()
    );