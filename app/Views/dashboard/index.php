<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
</div>

<div class="alert alert-primary" role="alert">
    <i class="fas fa-hand-sparkles mr-2"></i>
    Bienvenido/a, <strong><?= htmlspecialchars($usuario) ?></strong>. Resumen general del sistema.
</div>

<div class="row">
    <div class="col-xl-3 col-md-6 mb-4">
        <a href="/cpee/profesionales" class="text-decoration-none">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Profesionales Matriculados</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= (int)$counts['profesionales'] ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <a href="/cpee/caja" class="text-decoration-none">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Movimientos de Caja</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= (int)$counts['caja'] ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-cash-register fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <a href="/cpee/usuarios" class="text-decoration-none">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Usuarios del Sistema</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= (int)$counts['usuarios'] ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-cog fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <!-- <div class="col-xl-3 col-md-6 mb-4">
        <a href="/cpee/auditoria" class="text-decoration-none">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Eventos Auditados</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"> (int)$counts['auditoria'] </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div> -->
</div>

<div class="row">
    <div class="col-lg-6 mb-4">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Accesos Rápidos</h6>
            </div>
            <div class="card-body">
                <a href="/cpee/profesionales/crear" class="btn btn-primary btn-sm shadow-sm mb-2">
                    <i class="fas fa-user-plus mr-1"></i> Nuevo Matriculado
                </a>
                <a href="/cpee/caja/crear" class="btn btn-success btn-sm shadow-sm mb-2">
                    <i class="fas fa-cash-register mr-1"></i> Nuevo Movimiento de Caja
                </a>
                <a href="/cpee/usuarios/crear" class="btn btn-info btn-sm shadow-sm mb-2">
                    <i class="fas fa-user-cog mr-1"></i> Nuevo Usuario
                </a>
            </div>
        </div>
    </div>
</div>