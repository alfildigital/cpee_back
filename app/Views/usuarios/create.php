<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Alta de Usuario</h1>
    <a href="/cpee/usuarios" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
        <i class="fas fa-arrow-left fa-sm text-white-50"></i> Volver al Listado
    </a>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Datos del Usuario</h6>
    </div>
    <div class="card-body">
        <?php
        $flash = \App\Core\Security::getFlash();
        foreach ($flash as $msg):
            $tipo = in_array($msg['type'], ['success', 'danger', 'warning', 'info'], true) ? $msg['type'] : 'info';
        ?>
            <div class="alert alert-<?= $tipo ?> alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($msg['message']) ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php endforeach; ?>

        <form action="/cpee/usuarios/guardar" method="POST">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="nombre">Nombre Completo <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nombre" name="nombre" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="email">Correo Electrónico <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="password">Contraseña <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="sector_id">Sector</label>
                        <select class="form-control" id="sector_id" name="sector_id">
                            <option value="">Seleccione un sector...</option>
                            <?php foreach ($sectores as $sector): ?>
                                <option value="<?= (int)$sector['id'] ?>"><?= htmlspecialchars($sector['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label>Roles Asignados</label>
                <div class="border rounded p-3">
                    <?php foreach ($roles as $rol): ?>
                        <div class="custom-control custom-checkbox">
                            <input class="custom-control-input" type="checkbox" name="roles[]"
                                value="<?= (int)$rol['id'] ?>" id="rol_<?= (int)$rol['id'] ?>">
                            <label class="custom-control-label" for="rol_<?= (int)$rol['id'] ?>">
                                <?= htmlspecialchars($rol['nombre']) ?> -
                                <small class="text-muted"><?= htmlspecialchars($rol['descripcion'] ?? '') ?></small>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <hr>
            <div class="text-right">
                <a href="/cpee/usuarios" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save mr-1"></i> Guardar Usuario
                </button>
            </div>
        </form>
    </div>
</div>