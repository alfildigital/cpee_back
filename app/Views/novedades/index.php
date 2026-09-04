<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Novedades</h1>
    <a href="/cpee/novedades/crear" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
        <i class="fas fa-plus fa-sm text-white-50"></i> Nueva Novedad
    </a>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">Comunicados y Noticias</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
                <thead class="thead-dark">
                    <tr>
                        <th>ID</th>
                        <th>Título</th>
                        <th>Autor</th>
                        <th>Fecha de Publicación</th>
                        <th>Estado</th>
                        <th class="no-sort">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($novedades)): ?>
                        <tr>
                            <td colspan="7" class="text-center">No hay novedades registradas.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($novedades as $novedad): ?>
                            <tr>
                                <td><?= (int)$novedad['id'] ?></td>
                                <td><?= htmlspecialchars($novedad['titulo']) ?></td>
                                <td>Secretaría General</td>
                                <td><?= htmlspecialchars(date('d/m/Y H:i', strtotime($novedad['fecha_publicacion']))) ?></td>
                                <td>
                                    <?php if (!empty($novedad['publicado'])): ?>
                                        <span class="badge badge-success">Publicada</span>
                                    <?php else: ?>
                                        <span class="badge badge-secondary">Borrador</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-nowrap">
                                    <a href="/cpee/novedades/ver/<?= (int)$novedad['id'] ?>" class="btn btn-sm btn-outline-info" title="Ver detalle">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="/cpee/novedades/editar/<?= (int)$novedad['id'] ?>" class="btn btn-sm btn-outline-secondary" title="Editar">
                                        <i class="fas fa-pencil-alt"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-outline-danger btn-delete" title="Eliminar"
                                        data-url="/cpee/novedades/eliminar"
                                        data-id="<?= (int)$novedad['id'] ?>"
                                        data-nombre="<?= htmlspecialchars($novedad['titulo']) ?>">
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
            <form method="POST" action="/cpee/novedades/eliminar" id="deleteForm">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                <input type="hidden" name="id" id="deleteId">
                <div class="modal-body">
                    <p id="deleteMessage">¿Está seguro de que desea eliminar esta novedad?</p>
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
            $('#deleteMessage').html('¿Está seguro de que desea eliminar la novedad <strong>' + (nombre || '') + '</strong>?');
            $('#deleteForm').attr('action', $(this).data('url'));
            $('#deleteModal').modal('show');
        });
        $('#dataTable').DataTable({
            "language": {
                "url": "/cpee/assets/vendor/datatables/Spanish.json"
            },
            "columnDefs": [{
                "targets": "no-sort",
                "orderable": false
            }],
            "order": [
                [0, "desc"]
            ]
        });
    });
</script>