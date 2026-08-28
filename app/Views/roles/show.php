<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Detalle de Rol</h1>
    <div>
        <a href="/cpee/roles/editar/<?= (int)$rol['id'] ?>" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-pencil-alt fa-sm text-white-50"></i> Editar / Asignar Permisos
        </a>
        <a href="/cpee/roles" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Volver al Listado
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-6 mb-4">
        <div class="card shadow h-100">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Datos del Rol</h6>
            </div>
            <div class="card-body">
                <div class="form-group row">
                    <label class="col-sm-4 col-form-label text-muted small font-weight-bold">ID</label>
                    <div class="col-sm-8">
                        <p class="form-control-plaintext"><?= (int)$rol['id'] ?></p>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-4 col-form-label text-muted small font-weight-bold">Nombre</label>
                    <div class="col-sm-8">
                        <p class="form-control-plaintext font-weight-bold"><?= htmlspecialchars($rol['nombre']) ?></p>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-4 col-form-label text-muted small font-weight-bold">Descripción</label>
                    <div class="col-sm-8">
                        <p class="form-control-plaintext"><?= htmlspecialchars($rol['descripcion'] ?? '—') ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6 mb-4">
        <div class="card shadow h-100">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Permisos Asignados</h6>
            </div>
            <div class="card-body">
                <?php
                $permisosRol = array_map(static fn($p) => (int)$p, $rol['permisos'] ?? []);
                $asignados = array_filter($permisos, static fn($p) => in_array((int)$p['id'], $permisosRol, true));
                ?>
                <?php if ($asignados): ?>
                    <?php foreach ($asignados as $permiso): ?>
                        <span class="badge badge-success px-3 py-2 mr-2 mb-2">
                            <i class="fas fa-check-circle mr-1"></i><?= htmlspecialchars($permiso['nombre']) ?>
                        </span>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-muted small mb-0">El rol no tiene permisos asignados.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
