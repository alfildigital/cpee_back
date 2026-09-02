<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Editar Boletín Oficial</h1>
    <a href="/cpee/boletin-oficial" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
        <i class="fas fa-arrow-left fa-sm text-white-50"></i> Volver al Listado
    </a>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Publicación Oficial #<?= (int)$boletin['id'] ?></h6>
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

        <form action="/cpee/boletin-oficial/actualizar" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
            <input type="hidden" name="id" value="<?= (int)$boletin['id'] ?>">

            <div class="mb-3 text-start">
                <label for="titulo" class="form-label">Título *</label>
                <input type="text" class="form-control" id="titulo" name="titulo" maxlength="200" required
                       value="<?= htmlspecialchars($boletin['titulo']) ?>">
            </div>

            <div class="mb-3 text-start">
                <label for="resumen" class="form-label">Resumen</label>
                <textarea class="form-control" id="resumen" name="resumen" rows="5"><?= htmlspecialchars($boletin['resumen'] ?? '') ?></textarea>
                <div class="form-text text-muted">Breve descripción del boletín.</div>
            </div>

            <div class="mb-3 text-start">
                <label class="form-label">Archivo PDF</label>
                <?php if (!empty($boletin['archivo_ruta'])): ?>
                    <div class="border rounded p-2 bg-light mb-2 d-flex align-items-center justify-content-between">
                        <div class="text-truncate pr-2">
                            <i class="fas fa-file-pdf text-danger mr-2"></i>
                            <?= htmlspecialchars($boletin['archivo_nombre'] ?? basename($boletin['archivo_ruta'])) ?>
                        </div>
                        <a href="/cpee/boletin-oficial/descargar/<?= (int)$boletin['id'] ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-download"></i>
                        </a>
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" id="remover_archivo" name="remover_archivo" value="1">
                        <label class="form-check-label text-danger" for="remover_archivo">Quitar adjunto actual</label>
                    </div>
                <?php endif; ?>
                <input type="file" class="form-control" id="archivo" name="archivo" accept="application/pdf,.pdf"
                       onchange="cpeeBoletinPreview(this)">
                <div class="form-text text-muted">Solo PDF. Máx. 5 MB. Dejar vacío para conservar el actual.</div>
            </div>

            <hr>
            <div class="text-right">
                <a href="/cpee/boletin-oficial" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save mr-1"></i> Guardar Cambios
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function cpeeBoletinPreview(input) {
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
        if (cb) { cb.checked = false; }
    }
</script>
