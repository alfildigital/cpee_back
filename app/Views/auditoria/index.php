<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Registro de Auditoría</h2>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form action="/cpee/auditoria" method="GET" class="row g-3 align-items-center">
                <div class="col-auto">
                    <label for="tabla" class="col-form-label">Filtrar por Tabla:</label>
                </div>
                <div class="col-auto">
                    <input type="text" id="tabla" name="tabla" class="form-control" placeholder="Ej: usuarios" value="<?= htmlspecialchars($_GET['tabla'] ?? '') ?>">
                </div>
                <div class="col-auto">
                    <label for="limit" class="col-form-label">Mostrar:</label>
                </div>
                <div class="col-auto">
                    <select name="limit" id="limit" class="form-select">
                        <option value="50" <?= (isset($_GET['limit']) && $_GET['limit'] == 50) ? 'selected' : '' ?>>50</option>
                        <option value="100" <?= (!isset($_GET['limit']) || $_GET['limit'] == 100) ? 'selected' : '' ?>>100</option>
                        <option value="500" <?= (isset($_GET['limit']) && $_GET['limit'] == 500) ? 'selected' : '' ?>>500</option>
                    </select>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary">Filtrar</button>
                    <a href="/cpee/auditoria" class="btn btn-secondary">Limpiar</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-bordered align-middle" style="font-size: 0.9rem;">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Timestamp</th>
                            <th>Usuario</th>
                            <th>Acción</th>
                            <th>Tabla</th>
                            <th>Reg. ID</th>
                            <th>Datos Anteriores</th>
                            <th>Datos Nuevos</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($logs as $log): ?>
                            <tr>
                                <td><?= htmlspecialchars($log['id']) ?></td>
                                <td><?= htmlspecialchars($log['timestamp']) ?></td>
                                <td><?= htmlspecialchars($log['usuario_nombre'] ?? 'Sistema/Desconocido') ?> (<?= htmlspecialchars($log['usuario_id']) ?>)</td>
                                <td>
                                    <?php
                                    $badgeClass = 'bg-secondary';
                                    if ($log['accion'] === 'INSERT') $badgeClass = 'bg-success';
                                    if ($log['accion'] === 'UPDATE') $badgeClass = 'bg-warning text-dark';
                                    if ($log['accion'] === 'DELETE') $badgeClass = 'bg-danger';
                                    if ($log['accion'] === 'LOGIN') $badgeClass = 'bg-info text-dark';
                                    ?>
                                    <span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($log['accion']) ?></span>
                                </td>
                                <td><?= htmlspecialchars($log['tabla_afectada']) ?></td>
                                <td><?= htmlspecialchars($log['registro_id']) ?></td>
                                <td>
                                    <?php if ($log['datos_anteriores']): ?>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" data-toggle="modal" data-target="#datosModal<?= $log['id'] ?>_ant">Ver</button>
                                        <!-- Modal -->
                                        <div class="modal fade" id="datosModal<?= $log['id'] ?>_ant" tabindex="-1" role="dialog" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Datos Anteriores (Log ID: <?= $log['id'] ?>)</h5>
                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                    </div>
                                                    <div class="modal-body pre-scrollable" style="max-height:70vh;overflow:auto;">
                                                        <pre><code><?= htmlspecialchars(json_encode(json_decode($log['datos_anteriores']), JSON_PRETTY_PRINT)) ?></code></pre>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($log['datos_nuevos']): ?>
                                        <button type="button" class="btn btn-sm btn-outline-primary" data-toggle="modal" data-target="#datosModal<?= $log['id'] ?>_nuevos">Ver</button>
                                        <!-- Modal -->
                                        <div class="modal fade" id="datosModal<?= $log['id'] ?>_nuevos" tabindex="-1" role="dialog" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Datos Nuevos (Log ID: <?= $log['id'] ?>)</h5>
                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                    </div>
                                                    <div class="modal-body pre-scrollable" style="max-height:70vh;overflow:auto;">
                                                        <pre><code><?= htmlspecialchars(json_encode(json_decode($log['datos_nuevos']), JSON_PRETTY_PRINT)) ?></code></pre>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($logs)): ?>
                            <tr>
                                <td colspan="8" class="text-center">No hay registros de auditoría.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>