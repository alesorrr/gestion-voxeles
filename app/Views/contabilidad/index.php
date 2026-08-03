<?php
declare(strict_types=1);
/**
 * Dashboard de contabilidad.
 * @var string $desde
 * @var string $hasta
 * @var float $totalIngresos
 * @var float $totalGastos
 * @var float $balance
 * @var array<int, array<string, mixed>> $porCategoria
 * @var array<int, array<string, mixed>> $gastos
 * @var array<int, array<string, mixed>> $ingresos
 * @var array<int, string> $categorias
 */
$base = BASE_URL;
$fmt = static fn (float $n): string => MONEDA . ' ' . number_format($n, 2, ',', '.');
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Contabilidad</h1>
</div>

<!-- Filtro por rango de fechas -->
<form method="get" action="<?= $base ?>/contabilidad" class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <div class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label small">Desde</label>
                <input type="date" name="desde" class="form-control" value="<?= htmlspecialchars($desde) ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label small">Hasta</label>
                <input type="date" name="hasta" class="form-control" value="<?= htmlspecialchars($hasta) ?>">
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-funnel me-1"></i>Filtrar
                </button>
                <a href="<?= $base ?>/contabilidad" class="btn btn-outline-secondary">Mes actual</a>
            </div>
        </div>
    </div>
</form>

<!-- Cards de resumen -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card stat-card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="stat-icon bg-success-subtle text-success"><i class="bi bi-arrow-down-circle"></i></div>
                <p class="stat-label">Ingresos</p>
                <p class="stat-value text-success"><?= $fmt($totalIngresos) ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card stat-card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="stat-icon bg-danger-subtle text-danger"><i class="bi bi-arrow-up-circle"></i></div>
                <p class="stat-label">Gastos</p>
                <p class="stat-value text-danger"><?= $fmt($totalGastos) ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card stat-card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="stat-icon bg-primary-subtle text-primary"><i class="bi bi-wallet2"></i></div>
                <p class="stat-label">Balance neto</p>
                <p class="stat-value <?= $balance >= 0 ? 'text-primary' : 'text-danger' ?>"><?= $fmt($balance) ?></p>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Nuevo gasto -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white"><h2 class="h6 mb-0">Registrar gasto</h2></div>
            <div class="card-body">
                <form method="post" action="<?= $base ?>/contabilidad/gastos">
                    <div class="mb-3">
                        <label class="form-label">Categoría</label>
                        <select name="categoria" class="form-select">
                            <?php foreach ($categorias as $cat): ?>
                                <option value="<?= htmlspecialchars($cat) ?>"><?= htmlspecialchars($cat) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Descripción</label>
                        <input type="text" name="descripcion" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Monto (<?= MONEDA ?>)</label>
                        <input type="number" step="0.01" min="0" name="monto" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Fecha</label>
                        <input type="date" name="fecha" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-plus-lg me-1"></i>Agregar gasto
                    </button>
                </form>
            </div>
        </div>

        <!-- Desglose por categoría -->
        <div class="card border-0 shadow-sm mt-4">
            <div class="card-header bg-white"><h2 class="h6 mb-0">Gastos por categoría</h2></div>
            <ul class="list-group list-group-flush">
                <?php if (empty($porCategoria)): ?>
                    <li class="list-group-item text-muted small text-center py-3">Sin gastos en el período.</li>
                <?php else: ?>
                    <?php foreach ($porCategoria as $pc): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><?= htmlspecialchars((string) $pc['categoria']) ?></span>
                            <span class="fw-medium text-danger"><?= $fmt((float) $pc['total']) ?></span>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
        </div>
    </div>

    <!-- Movimientos -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white"><h2 class="h6 mb-0"><i class="bi bi-arrow-up-circle text-danger me-1"></i>Gastos del período</h2></div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr><th>Fecha</th><th>Categoría</th><th>Descripción</th><th class="text-end">Monto</th><th></th></tr>
                    </thead>
                    <tbody>
                    <?php if (empty($gastos)): ?>
                        <tr><td colspan="5" class="text-center text-muted py-4">Sin gastos registrados.</td></tr>
                    <?php else: ?>
                        <?php foreach ($gastos as $g): ?>
                            <tr>
                                <td class="small"><?= date('d/m/Y', strtotime((string) $g['fecha'])) ?></td>
                                <td><span class="badge bg-secondary-subtle text-secondary-emphasis"><?= htmlspecialchars((string) $g['categoria']) ?></span></td>
                                <td><?= htmlspecialchars((string) $g['descripcion']) ?></td>
                                <td class="text-end text-danger"><?= $fmt((float) $g['monto']) ?></td>
                                <td class="text-end">
                                    <a href="<?= $base ?>/contabilidad/gastos/eliminar/<?= (int) $g['id'] ?>"
                                       class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Eliminar este gasto?');">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white"><h2 class="h6 mb-0"><i class="bi bi-arrow-down-circle text-success me-1"></i>Ingresos del período</h2></div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr><th>Fecha</th><th>Descripción</th><th class="text-end">Monto</th></tr>
                    </thead>
                    <tbody>
                    <?php if (empty($ingresos)): ?>
                        <tr><td colspan="3" class="text-center text-muted py-4">Sin ingresos en el período.</td></tr>
                    <?php else: ?>
                        <?php foreach ($ingresos as $i): ?>
                            <tr>
                                <td class="small"><?= date('d/m/Y', strtotime((string) $i['fecha'])) ?></td>
                                <td><?= htmlspecialchars((string) $i['descripcion']) ?></td>
                                <td class="text-end text-success"><?= $fmt((float) $i['monto']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
