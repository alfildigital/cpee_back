<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Alta de Matriculado</h1>
    <a href="/cpee/profesionales" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
        <i class="fas fa-arrow-left fa-sm text-white-50"></i> Volver al Listado
    </a>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Datos del Profesional</h6>
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

        <form action="/cpee/profesionales/guardar" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

            <div class="row mb-3">
                <div class="col-md-6 text-start">
                    <label for="nro_matricula" class="form-label">Nro. Matrícula *</label>
                    <input type="text" class="form-control" id="nro_matricula" name="nro_matricula" required>
                </div>
                <div class="col-md-6 text-start">
                    <label for="DNI" class="form-label">DNI *</label>
                    <input type="text" class="form-control" id="DNI" name="DNI" required pattern="\d{7,8}">
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6 text-start">
                    <label for="nombre" class="form-label">Nombres *</label>
                    <input type="text" class="form-control" id="nombre" name="nombre" required>
                </div>
                <div class="col-md-6 text-start">
                    <label for="apellido" class="form-label">Apellidos *</label>
                    <input type="text" class="form-control" id="apellido" name="apellido" required>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6 text-start">
                    <label for="email" class="form-label">Correo Electrónico</label>
                    <input type="email" class="form-control" id="email" name="email">
                </div>
                <div class="col-md-6 text-start">
                    <label for="telefono" class="form-label">Teléfono de Contacto</label>
                    <input type="text" class="form-control" id="telefono" name="telefono">
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6 text-start">
                    <label for="localidad" class="form-label">Localidad</label>
                    <input type="text" class="form-control" id="localidad" name="localidad">
                </div>
                <div class="col-md-6 text-start">
                    <label for="direccion" class="form-label">Dirección</label>
                    <input type="text" class="form-control" id="direccion" name="direccion">
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6 text-start">
                    <label for="fecha_matriculacion" class="form-label">Fecha de Matriculación *</label>
                    <input type="date" class="form-control" id="fecha_matriculacion" name="fecha_matriculacion" required>
                </div>
                <div class="col-md-6 text-start">
                    <label for="estado" class="form-label">Estado Inicial</label>
                    <select class="form-control" id="estado" name="estado">
                        <option value="Activa">Activa</option>
                        <option value="Suspendida">Suspendida</option>
                        <option value="Inactiva">Inactiva</option>
                    </select>
                </div>
            </div>

            <div class="mb-3 text-start">
                <label for="legajo" class="form-label">Notas Adicionales (Legajo)</label>
                <textarea class="form-control" id="legajo" name="legajo" rows="3"></textarea>
            </div>

            <div class="mb-3 text-start">
                <label for="foto" class="form-label">Foto (para el carnet)</label>
                <input type="file" class="form-control" id="foto" name="foto" accept="image/*"
                       onchange="cpeeFotoPreview(this)">
                <div class="form-text text-muted">Formatos: JPG, PNG, WEBP o GIF. Máx. 2 MB. Opcional.</div>
                <div class="mt-2 d-none" id="foto_preview_wrap">
                    <img id="foto_preview" alt="Vista previa" class="img-thumbnail" style="max-height:150px;">
                </div>
            </div>

            <hr>
            <div class="text-right">
                <a href="/cpee/profesionales" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save mr-1"></i> Registrar Profesional
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function cpeeFotoPreview(input) {
        const wrap = document.getElementById('foto_preview_wrap');
        const img = document.getElementById('foto_preview');
        if (!input.files || !input.files[0]) {
            wrap.classList.add('d-none');
            return;
        }
        const f = input.files[0];
        const MAX = 2 * 1024 * 1024;
        const TIPOS = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        if (f.size > MAX) {
            alert('La foto supera el tamaño máximo de 2 MB.');
            input.value = '';
            wrap.classList.add('d-none');
            return;
        }
        if (!TIPOS.includes(f.type)) {
            alert('Formato no permitido. Solo JPG, PNG, WEBP o GIF.');
            input.value = '';
            wrap.classList.add('d-none');
            return;
        }
        img.src = URL.createObjectURL(f);
        wrap.classList.remove('d-none');
    }
</script>