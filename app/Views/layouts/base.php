<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?= htmlspecialchars($title ?? 'CPEE Gestión') ?></title>

    <!-- Hojas de estilo locales (sin CDN) -->
    <link rel="stylesheet" href="/cpee/assets/vendor/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="/cpee/assets/css/sb-admin-2.min.css">
    <link rel="stylesheet" href="/cpee/assets/vendor/datatables/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="/cpee/assets/vendor/datatables/responsive.bootstrap4.min.css">
</head>

<body id="page-top">
    <div id="wrapper">
        <!-- Sidebar -->
        <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="/cpee/dashboard">
                <div class="sidebar-brand-icon">
                    <i class="fas fa-university"></i>
                </div>
                <div class="sidebar-brand-text mx-3">CPEE <sup>Manager</sup></div>
            </a>
            <hr class="sidebar-divider my-0">
            <li class="nav-item">
                <a class="nav-link" href="/cpee/dashboard">
                    <i class="fas fa-fw fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <hr class="sidebar-divider">
            <div class="sidebar-heading">Módulos</div>
            <li class="nav-item">
                <a class="nav-link" href="/cpee/profesionales">
                    <i class="fas fa-fw fa-users"></i>
                    <span>Matriculados</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="/cpee/caja">
                    <i class="fas fa-fw fa-cash-register"></i>
                    <span>Caja / Tesorería</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="/cpee/novedades">
                    <i class="fas fa-fw fa-exclamation-circle"></i>
                    <span>Novedades</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="/cpee/obras-sociales">
                    <i class="fas fa-fw fa-building"></i>
                    <span>Obras Sociales</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="/cpee/boletin-oficial">
                    <i class="fas fa-fw fa-file-alt"></i>
                    <span>Boletín Oficial</span>
                </a>
            </li>
            <hr class="sidebar-divider">
            <div class="sidebar-heading">Sistema</div>
            <li class="nav-item">
                <a class="nav-link" href="/cpee/usuarios">
                    <i class="fas fa-fw fa-user-cog"></i>
                    <span>Usuarios</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="/cpee/roles">
                    <i class="fas fa-fw fa-user-shield"></i>
                    <span>Roles y Permisos</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="/cpee/auditoria">
                    <i class="fas fa-fw fa-clipboard-list"></i>
                    <span>Auditoría</span>
                </a>
            </li>
            <hr class="sidebar-divider">
            <div class="text-center d-none d-md-inline">
                <button class="rounded-circle border-0" id="sidebarToggle"></button>
            </div>
        </ul>

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <!-- Topbar -->
                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                        <i class="fa fa-bars"></i>
                    </button>

                    <ul class="navbar-nav ml-auto">
                        <li class="nav-item dropdown no-arrow">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="mr-2 d-none d-lg-inline text-gray-600 small">
                                    <?= htmlspecialchars($_SESSION['usuario_nombre'] ?? 'Usuario') ?>
                                </span>
                                <i class="fas fa-user-circle fa-lg text-gray-400"></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">
                                <a class="dropdown-item" href="/cpee/login/salir">
                                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Cerrar sesión
                                </a>
                            </div>
                        </li>
                    </ul>
                </nav>

                <!-- Page Content -->
                <div class="container-fluid">
                    <?php $flash = \App\Core\Security::getFlash(); ?>
                    <?php foreach ($flash as $msg): ?>
                        <?php
                        $tipo = in_array($msg['type'], ['success', 'danger', 'warning', 'info'], true) ? $msg['type'] : 'info';
                        $icono = match ($msg['type']) {
                            'success' => 'check-circle',
                            'danger' => 'exclamation-circle',
                            'warning' => 'exclamation-triangle',
                            default => 'info-circle',
                        };
                        ?>
                        <div class="alert alert-<?= $tipo ?> alert-dismissible fade show" role="alert">
                            <i class="fas fa-<?= $icono ?> mr-1"></i>
                            <?= htmlspecialchars($msg['message']) ?>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    <?php endforeach; ?>

                    <?= $content ?? '' ?>
                </div>
            </div>

            <!-- Footer -->
            <footer class="sticky-footer bg-white">
                <div class="container my-auto">
                    <div class="copyright text-center my-auto">
                        <span>Copyright &copy; CPEE <?= date('Y') ?></span>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    <!-- Scripts Locales -->
    <script src="/cpee/assets/vendor/jquery/jquery.min.js"></script>
    <script src="/cpee/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="/cpee/assets/vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="/cpee/assets/vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="/cpee/assets/vendor/datatables/dataTables.bootstrap4.min.js"></script>
    <script src="/cpee/assets/vendor/datatables/dataTables.responsive.min.js"></script>
    <script src="/cpee/assets/js/sb-admin-2.min.js"></script>
</body>

</html>