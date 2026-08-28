<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Catálogo de Permisos</h1>
    <div>
        <button type="button" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm" id="btnNuevoPermiso">
            <i class="fas fa-plus fa-sm text-white-50"></i> Nuevo Permiso
        </button>
        <a href="/cpee/roles" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Volver a Roles
        </a>
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

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Permisos definidos en el sistema</h6>
    </div>
    <div class="card-body">
        <div class="alert alert-info small">
            <i class="fas fa-info-circle mr-1"></i>
            Crea y edita los permisos desde esta sección. Los permisos se asignan luego a cada rol desde la
            opción <strong>Editar</strong> del rol correspondiente en "Roles y Permisos".
        </div>
        <div class="table-responsive">
            <table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
                <thead class="thead-dark">
                    <tr>
                        <th>ID</th>
                        <th>Permiso (clave)</th>
                        <th>Descripción</th>
                        <th class="no-sort">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($permisos)): ?>
                        <?php foreach ($permisos as $permiso): ?>
                            <tr>
                                <td><?= (int)$permiso['id'] ?></td>
                                <td><code><?= htmlspecialchars($permiso['nombre']) ?></code></td>
                                <td><?= htmlspecialchars($permiso['descripcion'] ?? '') ?></td>
                                <td class="text-nowrap">
                                    <button type="button" class="btn btn-sm btn-outline-secondary btn-edit-permiso"
                                        title="Editar"
                                        data-id="<?= (int)$permiso['id'] ?>"
                                        data-nombre="<?= htmlspecialchars($permiso['nombre']) ?>"
                                        data-descripcion="<?= htmlspecialchars($permiso['descripcion'] ?? '') ?>">
                                        <i class="fas fa-pencil-alt"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-danger btn-delete-permiso"
                                        title="Eliminar"
                                        data-id="<?= (int)$permiso['id'] ?>"
                                        data-nombre="<?= htmlspecialchars($permiso['nombre']) ?>">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center">No hay permisos definidos.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal de Crear / Editar Permiso -->
<div class="modal fade" id="permisoModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="permisoModalTitle">Nuevo Permiso</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="POST" id="permisoForm" action="/cpee/roles/guardarPermiso">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                <input type="hidden" name="id" id="permisoId">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="permisoNombre">Permiso (clave) <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="permisoNombre" name="nombre" required maxlength="100"
                               placeholder="ej: crear_obras_sociales">
                        <small class="form-text text-muted">Clave única utilizada en la lógica de autorización.</small>
                    </div>
                    <div class="form-group mb-0">
                        <label for="permisoDescripcion">Descripción</label>
                        <textarea class="form-control" id="permisoDescripcion" name="descripcion" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i> Guardar
                    </button>
                </div>
            </form>
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
            <form method="POST" action="/cpee/roles/eliminarPermiso" id="deleteForm">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                <input type="hidden" name="id" id="deletePermisoId">
                <div class="modal-body">
                    <p id="deleteMessage">¿Está seguro de que desea eliminar este permiso?</p>
                    <p class="small text-danger mb-0">
                        <i class="fas fa-exclamation-circle mr-1"></i>Si el permiso está asignado a algún rol, se desvinculará.
                    </p>
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
        const modal = $('#permisoModal');
        const form = document.getElementById('permisoForm');

        $('#btnNuevoPermiso').on('click', function() {
            document.getElementById('permisoModalTitle').textContent = 'Nuevo Permiso';
            document.getElementById('permisoForm').action = '/cpee/roles/guardarPermiso';
            document.getElementById('permisoId').value = '';
            document.getElementById('permisoNombre').value = '';
            document.getElementById('permisoDescripcion').value = '';
            modal.modal('show');
        });

        $('.btn-edit-permiso').on('click', function() {
            const id = $(this).data('id');
            document.getElementById('permisoModalTitle').textContent = 'Editar Permiso';
            document.getElementById('permisoForm').action = '/cpee/roles/actualizarPermiso';
            document.getElementById('permisoId').value = id;
            document.getElementById('permisoNombre').value = $(this).data('nombre');
            document.getElementById('permisoDescripcion').value = $(this).data('descripcion');
            modal.modal('show');
        });

        $('.btn-delete-permiso').on('click', function() {
            $('#deletePermisoId').val($(this).data('id'));
            $('#deleteMessage').html(
                '¿Está seguro de que desea eliminar el permiso <strong><code>' +
                ($(this).data('nombre') || '') + '</code></strong>?'
            );
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
