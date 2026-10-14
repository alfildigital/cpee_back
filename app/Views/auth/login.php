<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?= htmlspecialchars($title ?? 'Iniciar Sesión - CPEE') ?></title>

    <link rel="stylesheet" href="assets/vendor/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="assets/css/sb-admin-2.min.css">
</head>

<body class="bg-gradient-primary">

    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-xl-5 col-lg-6 col-md-8 col-sm-11">
                <div class="card o-hidden border-0 shadow-lg">
                    <div class="card-body p-4 p-sm-5">
                        <div class="text-center mb-4">
                            <img src="assets/img/logo_login.jpeg"
                                class="img-fluid mb-4 rounded"
                                alt="CPEE"
                                style="max-width: 350px; width: 100%; height: auto; object-fit: contain;">
                            <h1 class="h4 text-gray-900 mb-1 font-weight-bold">CPEE Gestión</h1>
                            <p class="text-muted small mb-0">Ingrese sus credenciales para acceder al sistema.</p>
                        </div>

                        <?php
                        $flash = $flash ?? Security::getFlash();
                        ?>

                        <?php if (!empty($flash)): ?>
                            <?php foreach ($flash as $msg): ?>
                                <div class="alert alert-<?= in_array($msg['type'], ['success', 'danger', 'warning', 'info'], true) ? $msg['type'] : 'info' ?> small py-2" role="alert">
                                    <i class="fas fa-exclamation-circle mr-1"></i>
                                    <?= htmlspecialchars($msg['message']) ?>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>

                        <form class="user" action="/cpee/login/autenticar" method="POST">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">

                            <div class="form-group">
                                <label for="email" class="small text-gray-700 font-weight-bold mb-1">Correo electrónico</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-light border-right-0">
                                            <i class="fas fa-envelope text-primary"></i>
                                        </span>
                                    </div>
                                    <input type="email" class="form-control form-control-user border-left-0"
                                        id="email" name="email" placeholder="nombre@ejemplo.com"
                                        required autofocus autocomplete="email">
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="password" class="small text-gray-700 font-weight-bold mb-1">Contraseña</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-light border-right-0">
                                            <i class="fas fa-lock text-primary"></i>
                                        </span>
                                    </div>
                                    <input type="password" class="form-control form-control-user border-left-0"
                                        id="password" name="password" placeholder="••••••••"
                                        required autocomplete="current-password">
                                </div>
                            </div>

                            <!-- <div class="form-group mb-4">
                                <div class="custom-control custom-checkbox small">
                                    <input type="checkbox" class="custom-control-input" id="olvide_clave" name="olvide_clave" value="1">
                                    <label class="custom-control-label text-gray-700" for="olvide_clave">Olvidé mi clave</label>
                                </div>
                            </div> -->

                            <button type="submit" class="btn btn-primary btn-user btn-block shadow-sm">
                                <i class="fas fa-sign-in-alt mr-1"></i> Ingresar
                            </button>
                        </form>

                        <hr class="my-4">
                        <!-- <div class="text-center small text-muted">
                            Colégio Profesional en Educación Especial
                        </div> -->
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>

</html>