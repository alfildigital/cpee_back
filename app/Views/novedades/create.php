<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Nueva Novedad</h1>
    <a href="/cpee/novedades" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
        <i class="fas fa-arrow-left fa-sm text-white-50"></i> Volver al Listado
    </a>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Comunicado / Noticia</h6>
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

        <form action="/cpee/novedades/guardar" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

            <div class="mb-3 text-start">
                <label for="titulo" class="form-label">Título *</label>
                <input type="text" class="form-control" id="titulo" name="titulo" maxlength="200" required>
            </div>

            <div class="mb-3 text-start">
                <label for="contenido" class="form-label">Contenido *</label>
                <textarea class="form-control" id="contenido" name="contenido" rows="6" required></textarea>
                <div class="form-text text-muted">Texto del comunicado o noticia.</div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6 text-start">
                    <label for="fecha_publicacion" class="form-label">Fecha de Publicación</label>
                    <input type="datetime-local" class="form-control" id="fecha_publicacion"
                           name="fecha_publicacion" value="<?= htmlspecialchars(date('Y-m-d\TH:i')) ?>">
                    <div class="form-text text-muted">Dejar vacío para usar la fecha y hora actual.</div>
                </div>
                <div class="col-md-6 text-start">
                    <label class="form-label d-block">Estado</label>
                    <div class="custom-control custom-switch mt-2">
                        <input type="checkbox" class="custom-control-input" id="publicado" name="publicado" checked>
                        <label class="custom-control-label" for="publicado">Publicada (visible)</label>
                    </div>
                    <div class="form-text text-muted">Desmarca para guardarla como borrador.</div>
                </div>
            </div>

            <div class="mb-3 text-start">
                <label class="form-label">Dirigida a (destinatarios por rol)</label>
                <div class="border rounded p-3 bg-light">
                    <div class="row">
                        <?php foreach ($roles as $rol): ?>
                            <div class="col-md-4">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input"
                                           id="rol_<?= (int)$rol['id'] ?>" name="roles[]" value="<?= (int)$rol['id'] ?>">
                                    <label class="custom-control-label" for="rol_<?= (int)$rol['id'] ?>">
                                        <?= htmlspecialchars($rol['nombre']) ?>
                                    </label>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="form-text text-muted mt-2">Si no seleccionas ningún rol, la novedad se dirige a todos.</div>
                </div>
            </div>

            <div class="mb-3 text-start">
                <label class="form-label">Adjunto PDF (opcional)</label>
                <input type="file" class="form-control" id="archivo" name="archivo" accept="application/pdf,.pdf"
                       onchange="cpeeNovedadPreview(this)">
                <div class="form-text text-muted">Solo PDF. Máx. 5 MB.</div>
            </div>

            <hr>
            <div class="text-right">
                <a href="/cpee/novedades" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save mr-1"></i> Guardar Novedad
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function cpeeNovedadPreview(input) {
        if (!input.files || !input.files[0]) {
            return;
        }
        const f = input.files[0];
        const MAX = 5 * 1024 * 1024;
        if (f.size > MAX) {
            alert('El PDF supera el tamaño máximo de 5 MB.');
            input.value = '';
            return;
        }
        if (f.type !== 'application/pdf') {
            alert('Solo se permiten archivos PDF.');
            input.value = '';
        }
    }
</script>
