<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Gestión de Usuarios</h1>
    <a href="/cpee/usuarios/crear" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
        <i class="fas fa-user-plus fa-sm text-white-50"></i> Nuevo Usuario
    </a>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Listado de Usuarios del Sistema</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
                <thead class="thead-dark">
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Sector</th>
                        <th>Estado</th>
                        <th class="no-sort">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($usuarios)): ?>
                        <?php foreach ($usuarios as $u): ?>
                            <tr>
                                <td><?= htmlspecialchars((string)$u['id']) ?></td>
                                <td><?= htmlspecialchars($u['nombre']) ?></td>
                                <td><?= htmlspecialchars($u['email']) ?></td>
                                <td><?= htmlspecialchars($u['sector_nombre'] ?? 'Sin sector') ?></td>
                                <td>
                                    <?php if ($u['activo']): ?>
                                        <span class="badge badge-success">Activo</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger">Inactivo</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-nowrap">
                                    <a href="/cpee/usuarios/ver/<?= (int)$u['id'] ?>" class="btn btn-sm btn-outline-info" title="Ver detalle">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="/cpee/usuarios/editar/<?= (int)$u['id'] ?>" class="btn btn-sm btn-outline-secondary" title="Editar">
                                        <i class="fas fa-pencil-alt"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-outline-danger btn-delete"
                                        title="Eliminar"
                                        data-url="/cpee/usuarios/eliminar"
                                        data-id="<?= (int)$u['id'] ?>"
                                        data-nombre="<?= htmlspecialchars($u['nombre']) ?>">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center">No hay usuarios registrados.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

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
            <form method="POST" action="/cpee/usuarios/destroy" id="deleteForm">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                <input type="hidden" name="id" id="deleteId">
                <div class="modal-body">
                    <p id="deleteMessage">¿Está seguro de que desea eliminar este usuario?</p>
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
                '¿Está seguro de que desea eliminar a <strong>' + (nombre || 'este usuario') + '</strong>?'
            );
            $('#deleteForm').attr('action', $(this).data('url'));
            $('#deleteModal').modal('show');
        });

        $('#dataTable').DataTable({
            "language": {
                "url": "/cpee/assets/vendor/datatables/Spanish.json"
            },
            "columnDefs": [{ "targets": "no-sort", "orderable": false }],
            "order": [[0, "desc"]]
        });
    });
</script>