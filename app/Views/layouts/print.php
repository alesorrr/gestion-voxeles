<?php
declare(strict_types=1);
/**
 * Layout limpio para impresión de la MOP.
 * @var string $contenido
 * @var string $titulo
 */
$titulo = $titulo ?? 'MOP';
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
<body class="print-body">

<!-- Barra de acciones (se oculta al imprimir) -->
<div class="no-print bg-light border-bottom py-2 mb-3">
    <div class="container d-flex justify-content-between align-items-center">
        <a href="<?= $base ?>/ordenes" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Volver
        </a>
        <button onclick="window.print()" class="btn btn-sm btn-primary">
            <i class="bi bi-printer me-1"></i>Imprimir MOP
        </button>
    </div>
</div>

<div class="container print-container">
    <?= $contenido ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
