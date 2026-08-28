<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Detalle de Matriculado</h1>
    <div>
        <a href="/cpee/profesionales/carnet/<?= (int)$profesional['id'] ?>" target="_blank" class="d-none d-sm-inline-block btn btn-sm btn-outline-primary shadow-sm">
            <i class="fas fa-id-card fa-sm text-white-50"></i> Carnet
        </a>
        <a href="/cpee/profesionales/editar/<?= (int)$profesional['id'] ?>" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-pencil-alt fa-sm text-white-50"></i> Editar
        </a>
        <a href="/cpee/profesionales" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Volver al Listado
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-6 mb-4">
        <div class="card shadow h-100">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">Datos del Matriculado</h6>
                <?php
                $badgeClass = 'badge-success';
                if ($profesional['estado'] === 'Suspendida') $badgeClass = 'badge-warning';
                if ($profesional['estado'] === 'Inactiva') $badgeClass = 'badge-danger';
                ?>
                <span class="badge badge-pill <?= $badgeClass ?>"><?= htmlspecialchars($profesional['estado']) ?></span>
            </div>
            <div class="card-body">
                <?php if (!empty($profesional['foto'])): ?>
                    <div class="text-center mb-4">
                        <img src="/cpee/profesionales/foto/<?= (int)$profesional['id'] ?>"
                             alt="Foto del matriculado" class="img-thumbnail" style="max-height:180px">
                    </div>
                <?php endif; ?>
                <div class="form-group row">
                    <label class="col-sm-4 col-form-label text-muted small font-weight-bold">Apellido y Nombre</label>
                    <div class="col-sm-8">
                        <p class="form-control-plaintext"><?= htmlspecialchars($profesional['apellido'] . ', ' . $profesional['nombre']) ?></p>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-4 col-form-label text-muted small font-weight-bold">DNI</label>
                    <div class="col-sm-8">
                        <p class="form-control-plaintext"><?= htmlspecialchars($profesional['dni']) ?></p>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-4 col-form-label text-muted small font-weight-bold">Nro. Matrícula</label>
                    <div class="col-sm-8">
                        <p class="form-control-plaintext"><?= htmlspecialchars($profesional['nro_matricula']) ?></p>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-4 col-form-label text-muted small font-weight-bold">Fecha de Matriculación</label>
                    <div class="col-sm-8">
                        <p class="form-control-plaintext"><?= htmlspecialchars($profesional['fecha_matriculacion']) ?></p>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-4 col-form-label text-muted small font-weight-bold">Email</label>
                    <div class="col-sm-8">
                        <p class="form-control-plaintext"><?= htmlspecialchars($profesional['email'] ?? '—') ?></p>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-4 col-form-label text-muted small font-weight-bold">Teléfono</label>
                    <div class="col-sm-8">
                        <p class="form-control-plaintext"><?= htmlspecialchars($profesional['telefono'] ?? '—') ?></p>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-4 col-form-label text-muted small font-weight-bold">Localidad</label>
                    <div class="col-sm-8">
                        <p class="form-control-plaintext"><?= htmlspecialchars($profesional['localidad'] ?? '—') ?></p>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-4 col-form-label text-muted small font-weight-bold">Dirección</label>
                    <div class="col-sm-8">
                        <p class="form-control-plaintext"><?= htmlspecialchars($profesional['direccion'] ?? '—') ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6 mb-4">
        <div class="card shadow h-100">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Información Adicional</h6>
            </div>
            <div class="card-body">
                <div class="form-group mb-4">
                    <label class="text-muted small font-weight-bold d-block">Notas / Legajo</label>
                    <div class="border rounded p-3 bg-light">
                        <?= htmlspecialchars($profesional['legajo'] ?? 'Sin notas registradas.') ?>
                    </div>
                </div>
                <div class="form-group row mb-2">
                    <label class="col-sm-4 col-form-label text-muted small font-weight-bold">Registrado el</label>
                    <div class="col-sm-8">
                        <p class="form-control-plaintext"><?= htmlspecialchars($profesional['created_at'] ?? '') ?></p>
                    </div>
                </div>
                <div class="form-group row mb-2">
                    <label class="col-sm-4 col-form-label text-muted small font-weight-bold">Actualizado el</label>
                    <div class="col-sm-8">
                        <p class="form-control-plaintext"><?= htmlspecialchars($profesional['updated_at'] ?? '') ?></p>
                    </div>
                </div>

                <hr>
                <a href="/cpee/caja/crear/Ingreso/<?= (int)$profesional['id'] ?>"
                    class="btn btn-sm btn-success btn-block">
                    <i class="fas fa-coins mr-1"></i> Asentar Pago
                </a>
            </div>
        </div>
    </div>
</div>