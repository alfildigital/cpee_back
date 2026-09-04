<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Editar Novedad</h1>
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

        <form action="/cpee/novedades/actualizar" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
            <input type="hidden" name="id" value="<?= (int)$novedad['id'] ?>">

            <div class="mb-3 text-start">
                <label for="titulo" class="form-label">Título *</label>
                <input type="text" class="form-control" id="titulo" name="titulo" maxlength="200" required
                    value="<?= htmlspecialchars($novedad['titulo']) ?>">
            </div>

            <div class="mb-3 text-start">
                <label for="contenido" class="form-label">Contenido *</label>
                <textarea class="form-control" id="contenido" name="contenido" rows="6" required><?= htmlspecialchars($novedad['contenido']) ?></textarea>
                <div class="form-text text-muted"><small><b><i>Texto del comunicado o noticia.</i></b></small></div>
            </div>

            <div class="row mb-3">
                <div class="col-md-4 text-start">
                    <label for="fecha_publicacion" class="form-label">Fecha de Publicación</label>
                    <input type="datetime-local" class="form-control" id="fecha_publicacion"
                        name="fecha_publicacion"
                        value="<?= htmlspecialchars(date('Y-m-d\TH:i', strtotime($novedad['fecha_publicacion']))) ?>">
                    <div class="form-text text-muted"><small><b><i>Dejar vacío para usar la fecha y hora actual.</i></b></small></div>
                </div>
                <div class="col-md-4 text-start">
                    <label class="form-label d-block">Estado</label>
                    <div class="custom-control custom-switch mt-2">
                        <input type="checkbox" class="custom-control-input" id="publicado" name="publicado"
                            <?= !empty($novedad['publicado']) ? 'checked' : '' ?>>
                        <label class="custom-control-label" for="publicado">Publicada (visible)</label>
                    </div>
                    <div class="form-text text-muted"><small><b><i>Desmarca para guardarla como borrador.</i></b></small></div>
                </div>
                <div class="col-md-4 text-start">
                    <label class="form-label">Adjunto PDF</label>
                    <?php if (!empty($novedad['archivo_ruta'])): ?>
                        <div class="border rounded p-2 bg-light mb-2 d-flex align-items-center justify-content-between">
                            <div class="text-truncate pr-2">
                                <i class="fas fa-file-pdf text-danger mr-2"></i>
                                <?= htmlspecialchars($novedad['archivo_nombre'] ?? basename($novedad['archivo_ruta'])) ?>
                            </div>
                            <a href="/cpee/novedades/descargar/<?= (int)$novedad['id'] ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-download"></i>
                            </a>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="remover_archivo" name="remover_archivo" value="1">
                            <label class="form-check-label text-danger" for="remover_archivo">Quitar adjunto actual</label>
                        </div>
                    <?php endif; ?>
                    <input type="file" class="form-control" id="archivo" name="archivo" accept="application/pdf,.pdf"
                        onchange="cpeeNovedadPreview(this)">
                    <div class="form-text text-muted"><small><b><i>Solo PDF. Máx. 5 MB. Dejar vacío para conservar el actual.</i></b></small></div>
                </div>
            </div>



            <hr>
            <div class="text-right">
                <a href="/cpee/novedades" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save mr-1"></i> Guardar Cambios
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
            return;
        }
        const cb = document.getElementById('remover_archivo');
        if (cb) {
            cb.checked = false;
        }
    }
</script>