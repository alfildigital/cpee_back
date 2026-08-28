<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Obras Sociales</h1>
    <a href="/cpee/obras-sociales/crear" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
        <i class="fas fa-plus fa-sm text-white-50"></i> Nueva Obra Social
    </a>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Listado de Obras Sociales</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
                <thead class="thead-dark">
                    <tr>
                        <th>ID</th>
                        <th>Logo</th>
                        <th>Nombre</th>
                        <th>Teléfono</th>
                        <th>Correo</th>
                        <th>Sitio Web</th>
                        <th class="no-sort">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($obras)): ?>
                        <?php foreach ($obras as $o): ?>
                            <tr>
                                <td><?= (int)$o['id'] ?></td>
                                <td class="text-center">
                                    <?php if (!empty($o['logo'])): ?>
                                        <img src="/cpee/obras-sociales/logo/<?= (int)$o['id'] ?>"
                                             alt="Logo" class="img-thumbnail" style="max-height:40px;max-width:60px;">
                                    <?php else: ?>
                                        <span class="text-muted small">—</span>
                                    <?php endif; ?>
                                </td>
                                <td class="font-weight-bold"><?= htmlspecialchars($o['nombre']) ?></td>
                                <td><?= htmlspecialchars($o['telefono'] ?? '—') ?></td>
                                <td><?= htmlspecialchars($o['correo'] ?? '—') ?></td>
                                <td>
                                    <?php if (!empty($o['url_sitio_web'])): ?>
                                        <a href="<?= htmlspecialchars($o['url_sitio_web']) ?>" target="_blank" rel="noopener">
                                            <i class="fas fa-external-link-alt mr-1"></i>Visitar
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-nowrap">
                                    <a href="/cpee/obras-sociales/ver/<?= (int)$o['id'] ?>" class="btn btn-sm btn-outline-info" title="Ver detalle">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="/cpee/obras-sociales/editar/<?= (int)$o['id'] ?>" class="btn btn-sm btn-outline-secondary" title="Editar">
                                        <i class="fas fa-pencil-alt"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-outline-danger btn-delete"
                                        title="Eliminar"
                                        data-url="/cpee/obras-sociales/eliminar"
                                        data-id="<?= (int)$o['id'] ?>"
                                        data-nombre="<?= htmlspecialchars($o['nombre']) ?>">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center">No hay obras sociales registradas.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

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

<!-- Modal de Confirmación de Eliminación -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fas fa-exclamation-triangle mr-2"></i>Confirmar Eliminación</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="POST" action="/cpee/obras-sociales/destroy" id="deleteForm">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                <input type="hidden" name="id" id="deleteId">
                <div class="modal-body">
                    <p id="deleteMessage">¿Está seguro de que desea eliminar esta obra social?</p>
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
            $('#deleteMessage').html(
                '¿Está seguro de que desea eliminar la obra social <strong>' + (nombre || '') + '</strong>?'
            );
            $('#deleteForm').attr('action', $(this).data('url'));
            $('#deleteModal').modal('show');
        });

        $('#dataTable').DataTable({
            "language": {
                "url": "/cpee/assets/vendor/datatables/Spanish.json"
            },
            "columnDefs": [{ "targets": "no-sort", "orderable": false }],
            "order": [[0, "asc"]]
        });
    });
</script>
