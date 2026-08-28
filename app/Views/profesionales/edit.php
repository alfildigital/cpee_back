<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Editar Matriculado</h1>
    <a href="/cpee/profesionales" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
        <i class="fas fa-arrow-left fa-sm text-white-50"></i> Volver al Listado
    </a>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Modificar Datos - <?= htmlspecialchars($profesional['nombre'] . ' ' . $profesional['apellido']) ?></h6>
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

        <form action="/cpee/profesionales/actualizar" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
            <input type="hidden" name="id" value="<?= htmlspecialchars($profesional['id']) ?>">

            <div class="row mb-3">
                <div class="col-md-6 text-start">
                    <label for="nro_matricula" class="form-label">Nro. Matrícula *</label>
                    <input type="text" class="form-control" id="nro_matricula" name="nro_matricula" value="<?= htmlspecialchars($profesional['nro_matricula']) ?>" required>
                </div>
                <div class="col-md-6 text-start">
                    <label for="DNI" class="form-label">DNI *</label>
                    <input type="text" class="form-control" id="DNI" name="DNI" value="<?= htmlspecialchars($profesional['dni']) ?>" required pattern="\d{7,8}">
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6 text-start">
                    <label for="nombre" class="form-label">Nombres *</label>
                    <input type="text" class="form-control" id="nombre" name="nombre" value="<?= htmlspecialchars($profesional['nombre']) ?>" required>
                </div>
                <div class="col-md-6 text-start">
                    <label for="apellido" class="form-label">Apellidos *</label>
                    <input type="text" class="form-control" id="apellido" name="apellido" value="<?= htmlspecialchars($profesional['apellido']) ?>" required>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6 text-start">
                    <label for="email" class="form-label">Correo Electrónico</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($profesional['email']) ?>">
                </div>
                <div class="col-md-6 text-start">
                    <label for="telefono" class="form-label">Teléfono de Contacto</label>
                    <input type="text" class="form-control" id="telefono" name="telefono" value="<?= htmlspecialchars($profesional['telefono']) ?>">
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6 text-start">
                    <label for="localidad" class="form-label">Localidad</label>
                    <input type="text" class="form-control" id="localidad" name="localidad" value="<?= htmlspecialchars($profesional['localidad'] ?? '') ?>">
                </div>
                <div class="col-md-6 text-start">
                    <label for="direccion" class="form-label">Dirección</label>
                    <input type="text" class="form-control" id="direccion" name="direccion" value="<?= htmlspecialchars($profesional['direccion'] ?? '') ?>">
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6 text-start">
                    <label for="fecha_matriculacion" class="form-label">Fecha de Matriculación *</label>
                    <input type="date" class="form-control" id="fecha_matriculacion" name="fecha_matriculacion" value="<?= htmlspecialchars($profesional['fecha_matriculacion']) ?>" required>
                </div>
                <div class="col-md-6 text-start">
                    <label for="estado" class="form-label">Estado</label>
                    <select class="form-control" id="estado" name="estado">
                        <option value="Activa" <?= $profesional['estado'] === 'Activa' ? 'selected' : '' ?>>Activa</option>
                        <option value="Suspendida" <?= $profesional['estado'] === 'Suspendida' ? 'selected' : '' ?>>Suspendida</option>
                        <option value="Inactiva" <?= $profesional['estado'] === 'Inactiva' ? 'selected' : '' ?>>Inactiva</option>
                    </select>
                </div>
            </div>

            <div class="mb-3 text-start">
                <label for="legajo" class="form-label">Notas Adicionales (Legajo)</label>
                <textarea class="form-control" id="legajo" name="legajo" rows="3"><?= htmlspecialchars($profesional['legajo']) ?></textarea>
            </div>

            <div class="mb-3 text-start">
                <label for="foto" class="form-label">Foto (para el carnet)</label>
                <div class="d-flex align-items-start">
                    <div class="mr-3">
                        <?php if (!empty($profesional['foto'])): ?>
                            <img src="/cpee/profesionales/foto/<?= (int)$profesional['id'] ?>"
                                 alt="Foto actual" class="img-thumbnail" style="max-height:120px">
                        <?php else: ?>
                            <div class="border rounded d-flex align-items-center justify-content-center text-muted"
                                 style="width:100px;height:120px">Sin foto</div>
                        <?php endif; ?>
                        <div class="mt-2 d-none" id="foto_preview_wrap">
                            <img id="foto_preview" alt="Nueva foto" class="img-thumbnail" style="max-height:120px">
                        </div>
                    </div>
                    <div>
                        <input type="file" class="form-control" id="foto" name="foto" accept="image/*"
                               onchange="cpeeFotoPreview(this)">
                        <div class="form-text text-muted">Formatos: JPG, PNG, WEBP o GIF. Máx. 2 MB. Dejar vacío para conservar la actual.</div>
                        <?php if (!empty($profesional['foto'])): ?>
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="checkbox" id="remover_foto" name="remover_foto" value="1">
                                <label class="form-check-label text-danger" for="remover_foto">Quitar foto actual</label>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <hr>
            <div class="text-right">
                <a href="/cpee/profesionales" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save mr-1"></i> Actualizar Profesional
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
        const cb = document.getElementById('remover_foto');
        if (cb) { cb.checked = false; }
        img.src = URL.createObjectURL(f);
        wrap.classList.remove('d-none');
    }
</script>