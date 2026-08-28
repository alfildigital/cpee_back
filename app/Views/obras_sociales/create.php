<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Nueva Obra Social</h1>
    <a href="/cpee/obras-sociales" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
        <i class="fas fa-arrow-left fa-sm text-white-50"></i> Volver al Listado
    </a>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Datos de la Obra Social</h6>
    </div>
    <div class="card-body">
        <form action="/cpee/obras-sociales/guardar" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

            <div class="row mb-3">
                <div class="col-md-6 text-start">
                    <label for="nombre" class="form-label">Nombre *</label>
                    <input type="text" class="form-control" id="nombre" name="nombre" required maxlength="100">
                </div>
                <div class="col-md-6 text-start">
                    <label for="telefono" class="form-label">Teléfono</label>
                    <input type="text" class="form-control" id="telefono" name="telefono" maxlength="50">
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6 text-start">
                    <label for="correo" class="form-label">Correo Electrónico</label>
                    <input type="email" class="form-control" id="correo" name="correo" maxlength="150">
                </div>
                <div class="col-md-6 text-start">
                    <label for="url_sitio_web" class="form-label">URL Sitio Web</label>
                    <input type="url" class="form-control" id="url_sitio_web" name="url_sitio_web" maxlength="255"
                           placeholder="https://...">
                </div>
            </div>

            <div class="mb-3 text-start">
                <label for="descripcion" class="form-label">Descripción</label>
                <textarea class="form-control" id="descripcion" name="descripcion" rows="3"></textarea>
            </div>

            <div class="mb-3 text-start">
                <label for="logo" class="form-label">Logo (PNG)</label>
                <input type="file" class="form-control" id="logo" name="logo" accept="image/png"
                       onchange="cpeeLogoPreview(this)">
                <div class="form-text text-muted">Solo formato PNG. Opcional.</div>
                <div class="mt-2 d-none" id="logo_preview_wrap">
                    <img id="logo_preview" alt="Vista previa" class="img-thumbnail" style="max-height:100px;">
                </div>
            </div>

            <hr>
            <div class="text-right">
                <a href="/cpee/obras-sociales" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save mr-1"></i> Registrar Obra Social
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function cpeeLogoPreview(input) {
        const wrap = document.getElementById('logo_preview_wrap');
        const img = document.getElementById('logo_preview');
        if (!input.files || !input.files[0]) {
            wrap.classList.add('d-none');
            return;
        }
        const f = input.files[0];
        if (f.type !== 'image/png') {
            alert('Formato no permitido. Solo se aceptan archivos PNG.');
            input.value = '';
            wrap.classList.add('d-none');
            return;
        }
        img.src = URL.createObjectURL(f);
        wrap.classList.remove('d-none');
    }
</script>
