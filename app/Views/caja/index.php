<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Caja y Tesorería</h1>
    <a href="/cpee/caja/crear" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
        <i class="fas fa-plus fa-sm text-white-50"></i> Nuevo Movimiento
    </a>
</div>

<!-- Filtros de Fecha (URLs amigables: /caja/index/{desde}/{hasta}) -->
<div class="card shadow mb-4">
    <div class="card-body">
        <form id="filtroCaja" class="row align-items-center">
            <div class="col-auto">
                <label>Desde:</label>
                <input type="date" id="fecha_inicio" class="form-control form-control-sm" value="<?= htmlspecialchars($desde) ?>">
            </div>
            <div class="col-auto">
                <label>Hasta:</label>
                <input type="date" id="fecha_fin" class="form-control form-control-sm" value="<?= htmlspecialchars($hasta) ?>">
            </div>
            <div class="col-auto mt-4">
                <button type="submit" class="btn btn-sm btn-secondary"><i class="fas fa-filter mr-1"></i>Filtrar</button>
                <a href="/cpee/caja" class="btn btn-sm btn-outline-secondary">Limpiar</a>
            </div>
        </form>
    </div>
</div>

<!-- Resumen -->
<div class="row">
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Ingresos</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">$ <?= number_format($totalIngresos, 2, ',', '.') ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-arrow-up fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card border-left-danger shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Total Egresos</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">$ <?= number_format($totalEgresos, 2, ',', '.') ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-arrow-down fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Saldo del Periodo</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">$ <?= number_format($saldo, 2, ',', '.') ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-wallet fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Listado Movimientos -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Detalle de Movimientos</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
                <thead class="thead-dark">
                    <tr>
                        <th>Fecha</th>
                        <th>Tipo</th>
                        <th>Concepto / Comprobante</th>
                        <th>Matriculado (Si aplica)</th>
                        <th>Monto Neto</th>
                        <th>IVA</th>
                        <th>Total</th>
                        <th>Adjunto</th>
                        <th>Usuario Op.</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($movimientos)): ?>
                        <?php foreach ($movimientos as $m): ?>
                            <tr>
                                <td><?= date('d/m/Y H:i', strtotime($m['fecha_movimiento'])) ?></td>
                                <td>
                                    <?php if ($m['tipo'] == 'Ingreso'): ?>
                                        <span class="badge badge-success">Ingreso</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger">Egreso</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($m['concepto']) ?></strong><br>
                                    <small class="text-muted">
                                        <?= htmlspecialchars(trim(($m['tipo_comprobante'] ?? '') . ' ' . ($m['punto_venta'] ?? '') . '-' . ($m['nro_comprobante'] ?? ''))) ?>
                                        <?= $m['cuit'] ? ' | CUIT: ' . htmlspecialchars($m['cuit']) : '' ?>
                                    </small>
                                </td>
                                <td><?= $m['profesional_id'] ? htmlspecialchars($m['prof_apellido'] . ', ' . $m['prof_nombre']) : '-' ?></td>
                                <td>$ <?= number_format($m['monto_neto'], 2, ',', '.') ?></td>
                                <td>$ <?= number_format($m['iva'], 2, ',', '.') ?></td>
                                <td class="font-weight-bold">$ <?= number_format($m['monto_total'], 2, ',', '.') ?></td>
                                <td class="text-center">
                                    <?php if (!empty($m['archivo_ruta'])): ?>
                                        <a href="/cpee/caja/descargar/<?= (int)$m['id'] ?>" class="btn btn-sm btn-outline-secondary" title="Ver documento adjunto" target="_blank">
                                            <i class="fas fa-paperclip"></i>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($m['usuario'] ?? 'Sistema') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="text-center">No hay movimientos en este periodo.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const form = document.getElementById('filtroCaja');
        if (form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                const desde = document.getElementById('fecha_inicio').value;
                const hasta = document.getElementById('fecha_fin').value;
                window.location.href = '/cpee/caja/index/' + desde + '/' + hasta;
            });
        }

        $('#dataTable').DataTable({
            "language": {
                "url": "/cpee/assets/vendor/datatables/Spanish.json"
            },
            "order": [[0, "desc"]]
        });
    });
</script>