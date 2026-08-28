<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Detalle de Novedad</h1>
    <div>
        <a href="/cpee/novedades/editar/<?= (int)$novedad['id'] ?>" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-pencil-alt fa-sm text-white-50"></i> Editar
        </a>
        <a href="/cpee/novedades" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Volver al Listado
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-8 mb-4">
        <div class="card shadow h-100">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary"><?= htmlspecialchars($novedad['titulo']) ?></h6>
                <?php if (!empty($novedad['publicado'])): ?>
                    <span class="badge badge-pill badge-success">Publicada</span>
                <?php else: ?>
                    <span class="badge badge-pill badge-secondary">Borrador</span>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <div class="mb-4">
                    <p class="mb-4" style="white-space: pre-wrap;"><?= htmlspecialchars($novedad['contenido']) ?></p>
                </div>

                <?php if (!empty($novedad['archivo_ruta'])): ?>
                    <hr>
                    <h6 class="m-0 font-weight-bold text-primary mb-2">Adjunto</h6>
                    <div class="d-flex align-items-center justify-content-between border rounded p-3 bg-light">
                        <div>
                            <i class="fas fa-file-pdf text-danger mr-2"></i>
                            <strong><?= htmlspecialchars($novedad['archivo_nombre'] ?? basename($novedad['archivo_ruta'])) ?></strong>
                            <div class="small text-muted">
                                <?= $novedad['archivo_tamano'] ? number_format((int)$novedad['archivo_tamano'] / 1024, 1) . ' KB' : '' ?>
                            </div>
                        </div>
                        <a href="/cpee/novedades/descargar/<?= (int)$novedad['id'] ?>" class="btn btn-sm btn-outline-primary" target="_blank">
                            <i class="fas fa-download mr-1"></i> Descargar
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-4 mb-4">
        <div class="card shadow h-100">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Información</h6>
            </div>
            <div class="card-body">
                <div class="form-group row mb-2">
                    <label class="col-sm-5 col-form-label text-muted small font-weight-bold">Autor</label>
                    <div class="col-sm-7">
                        <p class="form-control-plaintext"><?= htmlspecialchars($novedad['autor'] ?? '—') ?></p>
                    </div>
                </div>
                <div class="form-group row mb-2">
                    <label class="col-sm-5 col-form-label text-muted small font-weight-bold">Publicación</label>
                    <div class="col-sm-7">
                        <p class="form-control-plaintext"><?= htmlspecialchars(date('d/m/Y H:i', strtotime($novedad['fecha_publicacion']))) ?></p>
                    </div>
                </div>
                <hr>
                <label class="text-muted small font-weight-bold d-block">Dirigida a</label>
                <div>
                    <?php if (empty($novedad['roles_nombres']) && empty($novedad['roles'])): ?>
                        <span class="badge badge-primary px-3 py-2 mr-2 mb-2">Todos</span>
                    <?php endif; ?>
                    <?php foreach ($roles as $rol): ?>
                        <?php if (in_array((int)$rol['id'], $novedad['roles'], true)): ?>
                            <span class="badge badge-primary px-3 py-2 mr-2 mb-2"><?= htmlspecialchars($rol['nombre']) ?></span>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>
