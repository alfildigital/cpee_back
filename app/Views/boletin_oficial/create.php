<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Nuevo Boletín Oficial</h1>
    <a href="/cpee/boletin-oficial" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
        <i class="fas fa-arrow-left fa-sm text-white-50"></i> Volver al Listado
    </a>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Publicación Oficial</h6>
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

        <form action="/cpee/boletin-oficial/guardar" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

            <div class="mb-3 text-start">
                <label for="titulo" class="form-label">Título *</label>
                <input type="text" class="form-control" id="titulo" name="titulo" maxlength="200" required>
            </div>

            <div class="mb-3 text-start">
                <label for="resumen" class="form-label">Resumen</label>
                <textarea class="form-control" id="resumen" name="resumen" rows="5"></textarea>
                <div class="form-text text-muted">Breve descripción del boletín.</div>
            </div>

            <div class="mb-3 text-start">
                <label class="form-label">Archivo PDF (obligatorio)</label>
                <input type="file" class="form-control" id="archivo" name="archivo" accept="application/pdf,.pdf"
                       onchange="cpeeBoletinPreview(this)" required>
                <div class="form-text text-muted">Solo PDF. Máx. 5 MB. Se guarda en uploads/boletin/.</div>
            </div>

            <hr>
            <div class="text-right">
                <a href="/cpee/boletin-oficial" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save mr-1"></i> Guardar Boletín
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
        }
    }
</script>
