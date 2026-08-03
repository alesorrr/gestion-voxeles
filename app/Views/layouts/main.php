<?php
declare(strict_types=1);
/**
 * Layout principal: navbar azul superior + sidebar lateral con menú por rol.
 * Variables esperadas: $titulo (string), $contenido (string)
 * @var string $contenido
 * @var string $titulo
 */
$titulo = $titulo ?? APP_NAME;
$base   = BASE_URL;
$actual = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

$rol    = (string) ($_SESSION['usuario_rol'] ?? '');
$nombre = (string) ($_SESSION['usuario_nombre'] ?? 'Usuario');

// Etiqueta legible del rol.
$rolLabel = [
    'admin'    => 'Administrador',
    'operador' => 'Operario',
    'ventas'   => 'Usuario Ventas',
][$rol] ?? 'Usuario';

/** Devuelve 'active' si la ruta coincide con el inicio del path actual. */
$activo = static function (string $ruta) use ($actual, $base): string {
    $r = rtrim($base, '/') . $ruta;
    if ($ruta === '/') {
        return ($actual === $r || $actual === $r . '/' || $actual === rtrim($base, '/') . '/') ? 'active' : '';
    }
    return str_starts_with($actual, $r) ? 'active' : '';
};

// Definición del menú con visibilidad por rol.
$menu = [
    ['ruta' => '/',             'label' => 'Panel',              'icon' => 'bi-speedometer2',       'roles' => ['admin', 'operador', 'ventas']],
    ['ruta' => '/ordenes',      'label' => 'Ordenes De Trabajo', 'icon' => 'bi-file-earmark-text',  'roles' => ['admin', 'ventas']],
    ['ruta' => '/kanban',       'label' => 'Tablero Kanban',     'icon' => 'bi-kanban',             'roles' => ['admin', 'operador', 'ventas']],
    ['ruta' => '/contabilidad', 'label' => 'Contabilidad',       'icon' => 'bi-cash-coin',          'roles' => ['admin']],
    ['ruta' => '/presupuestos', 'label' => 'Presupuestos',       'icon' => 'bi-calculator',         'roles' => ['admin', 'ventas']],
    ['ruta' => '/usuarios',     'label' => 'Usuarios',           'icon' => 'bi-people',             'roles' => ['admin']],
];
$menuVisible = array_filter($menu, static fn (array $m): bool => in_array($rol, $m['roles'], true));
$puedeCrearOrden = in_array($rol, ['admin', 'ventas'], true);
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

<!-- ===================== NAVBAR SUPERIOR ===================== -->
<nav class="navbar app-navbar sticky-top">
    <div class="container-fluid px-3 px-lg-4">
        <a class="navbar-brand d-flex align-items-center" href="<?= $base ?>/">
            <img src="<?= $base ?>/assets/img/logo_voxeles_W.png"
                 alt="<?= htmlspecialchars(APP_NAME) ?>" class="app-navbar-logo">
        </a>

        <!-- Info de usuario (escritorio) -->
        <div class="d-none d-lg-flex align-items-center gap-2 text-white ms-auto">
            <span class="small"><?= htmlspecialchars($nombre) ?></span>
            <span class="app-avatar"><i class="bi bi-person-fill"></i></span>
        </div>

        <!-- Botón hamburguesa (móvil) -->
        <button class="navbar-toggler d-lg-none" type="button"
                data-bs-toggle="collapse" data-bs-target="#menuMovil"
                aria-controls="menuMovil" aria-expanded="false" aria-label="Menú">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Menú móvil desplegable -->
        <div class="collapse navbar-collapse app-nav-mobile d-lg-none" id="menuMovil">
            <ul class="navbar-nav w-100 pt-2">
                <?php foreach ($menuVisible as $m): ?>
                    <li class="nav-item">
                        <a class="nav-link <?= $activo($m['ruta']) ?>" href="<?= $base . $m['ruta'] ?>">
                            <i class="bi <?= $m['icon'] ?> me-2"></i><?= htmlspecialchars($m['label']) ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
            <div class="d-flex align-items-center justify-content-between py-2 border-top border-light border-opacity-25">
                <?php if ($puedeCrearOrden): ?>
                    <a class="btn btn-light btn-sm" href="<?= $base ?>/ordenes/nueva">
                        <i class="bi bi-plus-lg me-1"></i>Nueva Orden
                    </a>
                <?php else: ?><span></span><?php endif; ?>
                <a class="btn btn-outline-light btn-sm" href="<?= $base ?>/logout" title="Cerrar sesión">
                    <i class="bi bi-box-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>
</nav>

<!-- ===================== CUERPO: SIDEBAR + CONTENIDO ===================== -->
<div class="app-shell">

    <!-- Sidebar (escritorio) -->
    <aside class="app-sidebar d-none d-lg-flex">
        <div class="app-role-badge"><?= htmlspecialchars($rolLabel) ?></div>
        <nav class="app-sidebar-menu">
            <?php foreach ($menuVisible as $m): ?>
                <a class="app-menu-item <?= $activo($m['ruta']) ?>" href="<?= $base . $m['ruta'] ?>">
                    <i class="bi <?= $m['icon'] ?> app-menu-icon"></i>
                    <span><?= htmlspecialchars($m['label']) ?></span>
                </a>
            <?php endforeach; ?>
        </nav>
        <div class="app-sidebar-footer">
            <a class="btn btn-outline-secondary btn-sm w-100" href="<?= $base ?>/logout">
                <i class="bi bi-box-arrow-right me-1"></i>Cerrar sesión
            </a>
        </div>
    </aside>

    <!-- Contenido -->
    <main class="app-content">
        <?php if ($puedeCrearOrden): ?>
            <div class="d-none d-lg-flex justify-content-end mb-2">
                <a class="btn app-btn-nueva btn-sm" href="<?= $base ?>/ordenes/nueva">
                    <i class="bi bi-plus-lg me-1"></i>Nueva orden
                </a>
            </div>
        <?php endif; ?>

        <?= $contenido ?>

        <footer class="app-footer text-center text-muted small py-4 mt-3">
            Voxeles 3DLab · Gestión de impresión 3D &copy; <?= date('Y') ?>
        </footer>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= $base ?>/assets/js/app.js"></script>
</body>
</html>
