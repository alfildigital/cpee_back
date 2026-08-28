<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Gestión de Matriculados</h1>
    <div>
        <a href="/cpee/profesionales/crear" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-user-plus fa-sm text-white-50"></i> Nuevo Matriculado
        </a>
        <a href="/cpee/caja/crear/Ingreso" class="d-none d-sm-inline-block btn btn-sm btn-success shadow-sm">
            <i class="fas fa-cash-register fa-sm text-white-50"></i> Asentar Pago
        </a>
    </div>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Listado de Profesionales</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
                <thead class="thead-dark">
                    <tr>
                        <th>Nro. Matrícula</th>
                        <th>DNI</th>
                        <th>Apellido y Nombre</th>
                        <th>Estado</th>
                        <th>Fecha de Mat.</th>
                        <th class="no-sort">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($profesionales)): ?>
                        <?php foreach ($profesionales as $p): ?>
                            <tr>
                                <td><?= htmlspecialchars($p['nro_matricula']) ?></td>
                                <td><?= htmlspecialchars($p['dni']) ?></td>
                                <td><?= htmlspecialchars($p['apellido'] . ', ' . $p['nombre']) ?></td>
                                <td>
                                    <?php
                                    $badgeClass = 'badge-success';
                                    if ($p['estado'] === 'Suspendida') $badgeClass = 'badge-warning';
                                    if ($p['estado'] === 'Inactiva') $badgeClass = 'badge-danger';
                                    ?>
                                    <span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($p['estado']) ?></span>
                                </td>
                                <td><?= htmlspecialchars($p['fecha_matriculacion']) ?></td>
                                <td class="text-nowrap">
                                    <a href="/cpee/profesionales/ver/<?= (int)$p['id'] ?>" class="btn btn-sm btn-outline-info" title="Ver detalle">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="/cpee/profesionales/editar/<?= (int)$p['id'] ?>" class="btn btn-sm btn-outline-secondary" title="Editar">
                                        <i class="fas fa-pencil-alt"></i>
                                    </a>
                                    <a href="/cpee/caja/crear/Ingreso/<?= (int)$p['id'] ?>" class="btn btn-sm btn-outline-success" title="Asentar pago">
                                        <i class="fas fa-coins"></i>
                                    </a>
                                    <a href="/cpee/profesionales/carnet/<?= (int)$p['id'] ?>" class="btn btn-sm btn-outline-primary" title="Generar carnet" target="_blank">
                                        <i class="fas fa-id-card"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-outline-danger btn-delete"
                                        title="Eliminar"
                                        data-url="/cpee/profesionales/eliminar"
                                        data-id="<?= (int)$p['id'] ?>"
                                        data-nombre="<?= htmlspecialchars($p['nro_matricula'] . ' - ' . $p['apellido'] . ', ' . $p['nombre']) ?>">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center">No hay profesionales registrados.</td>
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
            <form method="POST" action="/cpee/profesionales/destroy" id="deleteForm">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                <input type="hidden" name="id" id="deleteId">
                <div class="modal-body">
                    <p id="deleteMessage">¿Está seguro de que desea eliminar este matriculado?</p>
                    <p class="small text-muted mb-0">
                        Se eliminará el registro. Los movimientos de caja asociados quedarán sin vínculo.
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
        $('.btn-delete').on('click', function() {
            const id = $(this).data('id');
            const nombre = $(this).data('nombre');
            $('#deleteId').val(id);
            $('#deleteMessage').html(
                '¿Está seguro de que desea eliminar al matriculado <strong>' + (nombre || '') + '</strong>?'
            );
            $('#deleteForm').attr('action', $(this).data('url'));
            $('#deleteModal').modal('show');
        });

        $('#dataTable').DataTable({
            "language": {
                "url": "/cpee/assets/vendor/datatables/Spanish.json"
            },
            "columnDefs": [{ "targets": "no-sort", "orderable": false }],
            "order": [[4, "desc"]]
        });
    });
</script>