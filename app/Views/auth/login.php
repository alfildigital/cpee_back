<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?= htmlspecialchars($title ?? 'Iniciar Sesión - CPEE') ?></title>

    <link rel="stylesheet" href="/cpee/assets/vendor/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="/cpee/assets/css/sb-admin-2.min.css">
</head>

<body class="bg-gradient-primary">

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xl-10 col-lg-12 col-md-9">
                <div class="card o-hidden border-0 shadow-lg my-5">
                    <div class="card-body p-0">
                        <div class="row">
                            <div class="col-lg-6 d-none d-lg-block bg-login-image"></div>
                            <div class="col-lg-6">
                                <div class="p-5">
                                    <div class="text-center">
                                        <h1 class="h4 text-gray-900 mb-4">
                                            <i class="fas fa-university text-primary mr-2"></i>CPEE Gestión
                                        </h1>
                                        <p class="mb-4">Ingrese sus credenciales para acceder al sistema.</p>
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
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                                </div>
                                                <input type="email" class="form-control form-control-user"
                                                    id="email" name="email" placeholder="Correo electrónico"
                                                    required autofocus autocomplete="email">
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="fas fa-key"></i></span>
                                                </div>
                                                <input type="password" class="form-control form-control-user"
                                                    id="password" name="password" placeholder="Contraseña"
                                                    required autocomplete="current-password">
                                            </div>
                                        </div>

                                        <button type="submit" class="btn btn-primary btn-user btn-block">
                                            <i class="fas fa-sign-in-alt mr-1"></i> Ingresar
                                        </button>
                                    </form>

                                    <hr>
                                    <div class="text-center small text-muted">
                                        Colegio Público de la Ingeniería
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>

</html>