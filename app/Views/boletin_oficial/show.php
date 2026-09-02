<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Detalle de Boletín Oficial</h1>
    <a href="/cpee/boletin-oficial" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
        <i class="fas fa-arrow-left fa-sm text-white-50"></i> Volver al Listado
    </a>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-file-alt mr-1"></i><?= htmlspecialchars($boletin['titulo']) ?></h6>
        <div>
            <a href="/cpee/boletin-oficial/editar/<?= (int)$boletin['id'] ?>" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-pencil-alt mr-1"></i> Editar
            </a>
        </div>
    </div>
    <div class="card-body">
        <table class="table table-sm table-borderless">
            <tbody>
                <tr>
                    <th class="text-muted" style="width:30%;">ID</th>
                    <td><?= (int)$boletin['id'] ?></td>
                </tr>
                <tr>
                    <th class="text-muted">Título</th>
                    <td class="font-weight-bold"><?= htmlspecialchars($boletin['titulo']) ?></td>
                </tr>
                <?php if (!empty($boletin['resumen'])): ?>
                    <tr>
                        <th class="text-muted">Resumen</th>
                        <td><?= nl2br(htmlspecialchars($boletin['resumen'])) ?></td>
                    </tr>
                <?php endif; ?>
                <tr>
                    <th class="text-muted">Fecha de Creación</th>
                    <td><?= htmlspecialchars(date('d/m/Y H:i:s', strtotime($boletin['created_at']))) ?></td>
                </tr>
                <tr>
                    <th class="text-muted">Registrado por</th>
                    <td><?= htmlspecialchars($boletin['usuario_abm'] ?? '—') ?></td>
                </tr>
                <tr>
                    <th class="text-muted">Fecha de Modificación</th>
                    <td>
                        <?php if (!empty($boletin['updated_at']) && $boletin['updated_at'] !== $boletin['created_at']): ?>
                            <?= htmlspecialchars(date('d/m/Y H:i:s', strtotime($boletin['updated_at']))) ?>
                        <?php else: ?>
                            <span class="text-muted">Sin modificaciones</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <th class="text-muted">Archivo PDF</th>
                    <td>
                        <?php if (!empty($boletin['archivo_ruta'])): ?>
                            <i class="fas fa-file-pdf text-danger mr-2"></i>
                            <?= htmlspecialchars($boletin['archivo_nombre'] ?? basename($boletin['archivo_ruta'])) ?>
                            (<?= number_format((float)($boletin['archivo_tamano'] ?? 0) / 1024, 1, ',', '.') ?> KB)
                            <a href="/cpee/boletin-oficial/descargar/<?= (int)$boletin['id'] ?>" target="_blank"
                               class="btn btn-sm btn-outline-primary ml-2">
                                <i class="fas fa-download mr-1"></i> Descargar / Ver
                            </a>
                        <?php else: ?>
                            <span class="text-muted">—</span>
                        <?php endif; ?>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
