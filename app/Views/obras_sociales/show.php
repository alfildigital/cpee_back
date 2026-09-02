<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Detalle de Obra Social</h1>
    <a href="/cpee/obras-sociales" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
        <i class="fas fa-arrow-left fa-sm text-white-50"></i> Volver al Listado
    </a>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-building mr-1"></i><?= htmlspecialchars($obra['nombre']) ?></h6>
        <div>
            <a href="/cpee/obras-sociales/editar/<?= (int)$obra['id'] ?>" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-pencil-alt mr-1"></i> Editar
            </a>
        </div>
    </div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-3 text-center">
                <?php if (!empty($obra['logo'])): ?>
                    <img src="/cpee/obras-sociales/logo/<?= (int)$obra['id'] ?>" alt="Logo"
                         class="img-thumbnail" style="max-height:150px;max-width:200px;">
                <?php else: ?>
                    <div class="text-muted p-4 border rounded">
                        <i class="fas fa-image fa-3x mb-2"></i>
                        <div>Sin logo</div>
                    </div>
                <?php endif; ?>
            </div>
            <div class="col-md-9">
                <table class="table table-sm table-borderless">
                    <tbody>
                        <tr>
                            <th class="text-muted" style="width:30%;">ID</th>
                            <td><?= (int)$obra['id'] ?></td>
                        </tr>
                        <tr>
                            <th class="text-muted">Nombre</th>
                            <td class="font-weight-bold"><?= htmlspecialchars($obra['nombre']) ?></td>
                        </tr>
                        <?php if (!empty($obra['descripcion'])): ?>
                            <tr>
                                <th class="text-muted">Descripción</th>
                                <td><?= nl2br(htmlspecialchars($obra['descripcion'])) ?></td>
                            </tr>
                        <?php endif; ?>
                        <tr>
                            <th class="text-muted">Teléfono</th>
                            <td>
                                <?php if (!empty($obra['telefono'])): ?>
                                    <i class="fas fa-phone mr-1"></i><?= htmlspecialchars($obra['telefono']) ?>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th class="text-muted">Correo</th>
                            <td>
                                <?php if (!empty($obra['correo'])): ?>
                                    <i class="fas fa-envelope mr-1"></i>
                                    <a href="mailto:<?= htmlspecialchars($obra['correo']) ?>"><?= htmlspecialchars($obra['correo']) ?></a>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th class="text-muted">Sitio Web</th>
                            <td>
                                <?php if (!empty($obra['url_sitio_web'])): ?>
                                    <i class="fas fa-globe mr-1"></i>
                                    <a href="<?= htmlspecialchars($obra['url_sitio_web']) ?>" target="_blank" rel="noopener">
                                        <?= htmlspecialchars($obra['url_sitio_web']) ?>
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th class="text-muted">Registrado por</th>
                            <td><?= htmlspecialchars($obra['usuario_abm'] ?? '—') ?></td>
                        </tr>
                        <tr>
                            <th class="text-muted">Registrado el</th>
                            <td><?= htmlspecialchars($obra['created_at'] ?? '—') ?></td>
                        </tr>
                        <tr>
                            <th class="text-muted">Actualizado el</th>
                            <td><?= htmlspecialchars($obra['updated_at'] ?? '—') ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
