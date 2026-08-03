<?php
declare(strict_types=1);
/**
 * Layout de acceso (login): navbar azul superior + área centrada.
 * @var string $contenido
 * @var string $titulo
 */
$titulo = $titulo ?? APP_NAME;
$base   = BASE_URL;
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($titulo) ?> · <?= htmlspecialchars(APP_NAME) ?></title>
    <link rel="icon" type="image/png" href="<?= $base ?>/assets/img/logo_voxeles_FAVICON.png">
    <link rel="apple-touch-icon" href="<?= $base ?>/assets/img/logo_voxeles_FAVICON.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= $base ?>/assets/css/app.css" rel="stylesheet">
</head>
<body class="auth-body">

<nav class="navbar app-navbar">
    <div class="container-fluid px-3 px-lg-4">
        <a class="navbar-brand d-flex align-items-center" href="<?= $base ?>/login">
            <img src="<?= $base ?>/assets/img/logo_voxeles_W.png"
                 alt="<?= htmlspecialchars(APP_NAME) ?>" class="app-navbar-logo">
        </a>
        <div class="d-flex align-items-center gap-2 text-white ms-auto">
            <span class="small">Acceder</span>
            <span class="app-avatar"><i class="bi bi-person-fill"></i></span>
        </div>
    </div>
</nav>

<main class="auth-main d-flex align-items-center justify-content-center">
    <?= $contenido ?>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
