<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Boletín Oficial</h1>
    <a href="/cpee/boletin-oficial/crear" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
        <i class="fas fa-plus fa-sm text-white-50"></i> Nuevo Boletín
    </a>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">Publicaciones Oficiales</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
                <thead class="thead-dark">
                    <tr>
                        <th>ID</th>
                        <th>Título</th>
                        <th>Resumen</th>
                        <th>Creado</th>
                        <th>Modificado</th>
                        <th>PDF</th>
                        <th class="no-sort">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($boletines)): ?>
                        <tr>
                            <td colspan="7" class="text-center">No hay boletines registrados.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($boletines as $boletin): ?>
                            <tr>
                                <td><?= (int)$boletin['id'] ?></td>
                                <td class="font-weight-bold"><?= htmlspecialchars($boletin['titulo']) ?></td>
                                <td class="text-muted text-truncate" style="max-width:300px;">
                                    <?= htmlspecialchars(mb_strimwidth((string)($boletin['resumen'] ?? ''), 0, 80, '…')) ?>
                                </td>
                                <td><?= htmlspecialchars(date('d/m/Y H:i', strtotime($boletin['created_at']))) ?></td>
                                <td>
                                    <?php if (!empty($boletin['updated_at']) && $boletin['updated_at'] !== $boletin['created_at']): ?>
                                        <?= htmlspecialchars(date('d/m/Y H:i', strtotime($boletin['updated_at']))) ?>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if (!empty($boletin['archivo_ruta'])): ?>
                                        <a href="/cpee/boletin-oficial/descargar/<?= (int)$boletin['id'] ?>" target="_blank"
                                           class="text-danger" title="Descargar PDF">
                                            <i class="fas fa-file-pdf fa-lg"></i>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-nowrap">
                                    <a href="/cpee/boletin-oficial/ver/<?= (int)$boletin['id'] ?>" class="btn btn-sm btn-outline-info" title="Ver detalle">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="/cpee/boletin-oficial/editar/<?= (int)$boletin['id'] ?>" class="btn btn-sm btn-outline-secondary" title="Editar">
                                        <i class="fas fa-pencil-alt"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-outline-danger btn-delete" title="Eliminar"
                                            data-url="/cpee/boletin-oficial/eliminar"
                                            data-id="<?= (int)$boletin['id'] ?>"
                                            data-nombre="<?= htmlspecialchars($boletin['titulo']) ?>">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal de confirmación de eliminación -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteModalLabel">Confirmar Eliminación</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="POST" action="/cpee/boletin-oficial/eliminar" id="deleteForm">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                <input type="hidden" name="id" id="deleteId">
                <div class="modal-body">
                    <p id="deleteMessage">¿Está seguro de que desea eliminar este boletín?</p>
                    <p class="small text-muted mb-0">Esta acción no se puede deshacer.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Sí, eliminar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        $('.btn-delete').on('click', function() {
            const id = $(this).data('id');
            const nombre = $(this).data('nombre');
            $('#deleteId').val(id);
            $('#deleteMessage').html('¿Está seguro de que desea eliminar el boletín <strong>' + (nombre || '') + '</strong>?');
            $('#deleteForm').attr('action', $(this).data('url'));
            $('#deleteModal').modal('show');
        });
        $('#dataTable').DataTable({
            "language": { "url": "/cpee/assets/vendor/datatables/Spanish.json" },
            "columnDefs": [{ "targets": "no-sort", "orderable": false }],
            "order": [[0, "desc"]]
        });
    });
</script>
