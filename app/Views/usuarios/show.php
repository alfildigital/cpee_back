<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Detalle de Usuario</h1>
    <div>
        <a href="/cpee/usuarios/editar/<?= (int)$usuario['id'] ?>" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-pencil-alt fa-sm text-white-50"></i> Editar
        </a>
        <a href="/cpee/usuarios" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Volver al Listado
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-6 mb-4">
        <div class="card shadow h-100">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Datos Personales</h6>
            </div>
            <div class="card-body">
                <div class="form-group row">
                    <label class="col-sm-4 col-form-label text-muted small font-weight-bold">ID</label>
                    <div class="col-sm-8">
                        <p class="form-control-plaintext"><?= (int)$usuario['id'] ?></p>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-4 col-form-label text-muted small font-weight-bold">Nombre</label>
                    <div class="col-sm-8">
                        <p class="form-control-plaintext"><?= htmlspecialchars($usuario['nombre']) ?></p>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-4 col-form-label text-muted small font-weight-bold">Email</label>
                    <div class="col-sm-8">
                        <p class="form-control-plaintext"><?= htmlspecialchars($usuario['email']) ?></p>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-4 col-form-label text-muted small font-weight-bold">Sector</label>
                    <div class="col-sm-8">
                        <p class="form-control-plaintext">
                            <?= htmlspecialchars($usuario['sector_nombre'] ?? 'Sin sector') ?>
                        </p>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-4 col-form-label text-muted small font-weight-bold">Estado</label>
                    <div class="col-sm-8">
                        <?php if ($usuario['activo']): ?>
                            <span class="badge badge-success">Activo</span>
                        <?php else: ?>
                            <span class="badge badge-danger">Inactivo</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6 mb-4">
        <div class="card shadow h-100">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Roles Asignados</h6>
            </div>
            <div class="card-body">
                <?php
                $rolIds = array_map(static fn($r) => (int)$r, $usuario['roles'] ?? []);
                $asignados = array_filter($roles, static fn($rol) => in_array((int)$rol['id'], $rolIds, true));
                ?>
                <?php if ($asignados): ?>
                    <?php foreach ($asignados as $rol): ?>
                        <span class="badge badge-primary px-3 py-2 mr-2 mb-2">
                            <i class="fas fa-user-tag mr-1"></i><?= htmlspecialchars($rol['nombre']) ?>
                        </span>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-muted small mb-0">El usuario no tiene roles asignados.</p>
                <?php endif; ?>

                <hr>
                <div class="form-group row mb-0">
                    <label class="col-sm-4 col-form-label text-muted small font-weight-bold">Registrado por</label>
                    <div class="col-sm-8">
                        <p class="form-control-plaintext"><?= htmlspecialchars($usuario['usuario_abm'] ?? '—') ?></p>
                    </div>
                </div>
                <div class="form-group row mb-0">
                    <label class="col-sm-4 col-form-label text-muted small font-weight-bold">Registrado el</label>
                    <div class="col-sm-8">
                        <p class="form-control-plaintext"><?= htmlspecialchars($usuario['created_at'] ?? '') ?></p>
                    </div>
                </div>
                <div class="form-group row mb-0">
                    <label class="col-sm-4 col-form-label text-muted small font-weight-bold">Actualizado el</label>
                    <div class="col-sm-8">
                        <p class="form-control-plaintext"><?= htmlspecialchars($usuario['updated_at'] ?? '') ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>