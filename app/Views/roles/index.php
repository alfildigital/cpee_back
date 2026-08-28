<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Roles y Permisos</h1>
    <div>
        <a href="/cpee/roles/permisos" class="d-none d-sm-inline-block btn btn-sm btn-outline-secondary shadow-sm mr-2">
            <i class="fas fa-list fa-sm text-white-50"></i> Catálogo de Permisos
        </a>
        <a href="/cpee/roles/crear" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-plus fa-sm text-white-50"></i> Nuevo Rol
        </a>
    </div>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Listado de Roles del Sistema</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
                <thead class="thead-dark">
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Descripción</th>
                        <th>Permisos</th>
                        <th>Usuarios</th>
                        <th class="no-sort">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($roles)): ?>
                        <?php foreach ($roles as $rol): ?>
                            <tr>
                                <td><?= htmlspecialchars((string)$rol['id']) ?></td>
                                <td class="font-weight-bold"><?= htmlspecialchars($rol['nombre']) ?></td>
                                <td><?= htmlspecialchars($rol['descripcion'] ?? '') ?></td>
                                <td>
                                    <span class="badge badge-primary"><?= (int)$rol['permisos_count'] ?></span>
                                </td>
                                <td>
                                    <span class="badge badge-secondary"><?= (int)$rol['usuarios_count'] ?></span>
                                </td>
                                <td class="text-nowrap">
                                    <a href="/cpee/roles/ver/<?= (int)$rol['id'] ?>" class="btn btn-sm btn-outline-info" title="Ver detalle">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="/cpee/roles/editar/<?= (int)$rol['id'] ?>" class="btn btn-sm btn-outline-secondary" title="Editar / Asignar permisos">
                                        <i class="fas fa-pencil-alt"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-outline-danger btn-delete"
                                        title="Eliminar"
                                        data-url="/cpee/roles/eliminar"
                                        data-id="<?= (int)$rol['id'] ?>"
                                        data-nombre="<?= htmlspecialchars($rol['nombre']) ?>"
                                        data-usuarios="<?= (int)$rol['usuarios_count'] ?>">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center">No hay roles registrados.</td>
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
            <form method="POST" action="/cpee/roles/destroy" id="deleteForm">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                <input type="hidden" name="id" id="deleteId">
                <div class="modal-body">
                    <p id="deleteMessage">¿Está seguro de que desea eliminar este rol?</p>
                    <p class="small text-danger mb-0" id="deleteWarning" style="display:none;">
                        <i class="fas fa-exclamation-circle mr-1"></i>Este rol está asignado a uno o más usuarios.
                    </p>
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
            const usuarios = parseInt($(this).data('usuarios')) || 0;
            $('#deleteId').val(id);
            $('#deleteMessage').html(
                '¿Está seguro de que desea eliminar el rol <strong>' + (nombre || '') + '</strong>?'
            );
            $('#deleteWarning').toggle(usuarios > 0);
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
