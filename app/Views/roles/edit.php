<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Editar Rol</h1>
    <a href="/cpee/roles" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
        <i class="fas fa-arrow-left fa-sm text-white-50"></i> Volver al Listado
    </a>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Datos del Rol</h6>
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

        <form action="/cpee/roles/actualizar" method="POST">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
            <input type="hidden" name="id" value="<?= (int)$rol['id'] ?>">

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="nombre">Nombre del Rol <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nombre" name="nombre" required maxlength="50"
                            value="<?= htmlspecialchars($rol['nombre']) ?>">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="descripcion">Descripción</label>
                        <input type="text" class="form-control" id="descripcion" name="descripcion" maxlength="255"
                            value="<?= htmlspecialchars($rol['descripcion'] ?? '') ?>">
                    </div>
                </div>
            </div>

            <?php $permisosRol = array_map(static fn($p) => (int)$p, $rol['permisos'] ?? []); ?>
            <div class="form-group">
                <label>Permisos Asignados</label>
                <div class="border rounded p-3">
                    <?php if (!empty($permisos)): ?>
                        <div class="mb-2">
                            <button type="button" class="btn btn-sm btn-link pl-0" id="btnMarcarTodos">Marcar todos</button>
                            <span class="text-muted">|</span>
                            <button type="button" class="btn btn-sm btn-link" id="btnDesmarcarTodos">Desmarcar todos</button>
                        </div>
                        <?php foreach ($permisos as $permiso): ?>
                            <div class="custom-control custom-checkbox">
                                <input class="custom-control-input" type="checkbox" name="permisos[]"
                                    value="<?= (int)$permiso['id'] ?>" id="permiso_<?= (int)$permiso['id'] ?>"
                                    <?= in_array((int)$permiso['id'], $permisosRol, true) ? 'checked' : '' ?>>
                                <label class="custom-control-label" for="permiso_<?= (int)$permiso['id'] ?>">
                                    <?= htmlspecialchars($permiso['nombre']) ?> -
                                    <small class="text-muted"><?= htmlspecialchars($permiso['descripcion'] ?? '') ?></small>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted small mb-0">No hay permisos definidos en el catálogo.</p>
                    <?php endif; ?>
                </div>
            </div>

            <hr>
            <div class="text-right">
                <a href="/cpee/roles" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save mr-1"></i> Guardar Cambios
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const boxes = () => document.querySelectorAll('input[name="permisos[]"]');
        document.getElementById('btnMarcarTodos').addEventListener('click', function(e) {
            e.preventDefault();
            boxes().forEach(function(b) { b.checked = true; });
        });
        document.getElementById('btnDesmarcarTodos').addEventListener('click', function(e) {
            e.preventDefault();
            boxes().forEach(function(b) { b.checked = false; });
        });
    });
</script>
