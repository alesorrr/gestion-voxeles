<?php
declare(strict_types=1);
/**
 * Panel principal.
 * @var float $totalIngresos
 * @var float $totalGastos
 * @var float $balance
 * @var int $ordenesActivas
 * @var array<int, array<string, mixed>> $ultimasOrdenes
 * @var string $mesNombre
 */
$base = BASE_URL;
$fmt = static fn (float $n): string => MONEDA . ' ' . number_format($n, 2, ',', '.');
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0">Panel principal</h1>
        <p class="text-muted mb-0">Resumen de <?= htmlspecialchars($mesNombre) ?></p>
    </div>
    <a href="<?= $base ?>/ordenes/nueva" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i>Nueva orden
    </a>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="card stat-card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="stat-icon bg-success-subtle text-success"><i class="bi bi-arrow-down-circle"></i></div>
                <p class="stat-label">Ingresos del mes</p>
                <p class="stat-value text-success"><?= $fmt($totalIngresos) ?></p>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card stat-card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="stat-icon bg-danger-subtle text-danger"><i class="bi bi-arrow-up-circle"></i></div>
                <p class="stat-label">Gastos del mes</p>
                <p class="stat-value text-danger"><?= $fmt($totalGastos) ?></p>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card stat-card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="stat-icon bg-primary-subtle text-primary"><i class="bi bi-wallet2"></i></div>
                <p class="stat-label">Balance neto</p>
                <p class="stat-value <?= $balance >= 0 ? 'text-primary' : 'text-danger' ?>"><?= $fmt($balance) ?></p>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card stat-card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="stat-icon bg-warning-subtle text-warning"><i class="bi bi-gear-wide-connected"></i></div>
                <p class="stat-label">Órdenes activas</p>
                <p class="stat-value"><?= (int) $ordenesActivas ?></p>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h2 class="h6 mb-0"><i class="bi bi-clock-history me-1"></i>Últimas órdenes</h2>
        <a href="<?= $base ?>/ordenes" class="small">Ver todas</a>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Proyecto</th>
                    <th>Cliente</th>
                    <th>Estado</th>
                    <th class="text-end">Precio</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($ultimasOrdenes)): ?>
                <tr><td colspan="6" class="text-center text-muted py-4">Todavía no hay órdenes cargadas.</td></tr>
            <?php else: ?>
                <?php foreach ($ultimasOrdenes as $o): ?>
                    <tr>
                        <td class="text-muted">#<?= (int) $o['id'] ?></td>
                        <td class="fw-medium"><?= htmlspecialchars((string) $o['nombre_proyecto']) ?></td>
                        <td><?= htmlspecialchars((string) $o['cliente_nombre']) ?></td>
                        <td>
                            <span class="badge" style="background-color: <?= htmlspecialchars((string) $o['estado_color']) ?>">
                                <?= htmlspecialchars((string) $o['estado_nombre']) ?>
                            </span>
                        </td>
                        <td class="text-end"><?= $fmt((float) $o['precio_final']) ?></td>
                        <td class="text-end">
                            <a href="<?= $base ?>/ordenes/<?= (int) $o['id'] ?>/mop" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
