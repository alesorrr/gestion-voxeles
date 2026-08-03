<?php
declare(strict_types=1);
/**
 * Layout principal con navegación Bootstrap 5.
 * Variables esperadas: $titulo (string), $contenido (string)
 * @var string $contenido
 * @var string $titulo
 */
$titulo = $titulo ?? APP_NAME;
$base   = BASE_URL;
$actual = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

/** Devuelve 'active' si la ruta coincide con el inicio del path actual. */
$activo = static function (string $ruta) use ($actual, $base): string {
    $ruta = $base . $ruta;
    if ($ruta === $base . '/') {
        return $actual === $ruta || $actual === rtrim($base, '/') . '/' ? 'active' : '';
    }
    return str_starts_with($actual, $ruta) ? 'active' : '';
};
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
<body class="app-body">

<nav class="navbar navbar-expand-lg navbar-dark app-navbar sticky-top">
    <div class="container-fluid">
        <a class="navbar-brand d-flex align-items-center" href="<?= $base ?>/">
            <img src="<?= $base ?>/assets/img/logo_voxeles_W.png"
                 alt="<?= htmlspecialchars(APP_NAME) ?>" class="app-navbar-logo">
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navMenu">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link <?= $activo('/') ?>" href="<?= $base ?>/">
                        <i class="bi bi-speedometer2 me-1"></i>Panel
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $activo('/ordenes') ?>" href="<?= $base ?>/ordenes">
                        <i class="bi bi-file-earmark-text me-1"></i>Órdenes
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $activo('/kanban') ?>" href="<?= $base ?>/kanban">
                        <i class="bi bi-kanban me-1"></i>Kanban
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $activo('/contabilidad') ?>" href="<?= $base ?>/contabilidad">
                        <i class="bi bi-cash-coin me-1"></i>Contabilidad
                    </a>
                </li>
            </ul>
            <div class="d-flex align-items-center gap-3">
                <a class="btn btn-sm btn-light" href="<?= $base ?>/ordenes/nueva">
                    <i class="bi bi-plus-lg me-1"></i>Nueva orden
                </a>
                <span class="navbar-text text-white-50 small">
                    <i class="bi bi-person-circle me-1"></i>
                    <?= htmlspecialchars($_SESSION['usuario_nombre'] ?? 'Usuario') ?>
                </span>
                <a class="btn btn-sm btn-outline-light" href="<?= $base ?>/logout">
                    <i class="bi bi-box-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>
</nav>

<main class="container-fluid py-4 px-lg-4">
    <?= $contenido ?>
</main>

<footer class="app-footer text-center text-muted small py-3">
    <?= htmlspecialchars(APP_NAME) ?> · Gestión de impresión 3D &copy; <?= date('Y') ?>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= $base ?>/assets/js/app.js"></script>
</body>
</html>
