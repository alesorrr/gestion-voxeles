<?php
declare(strict_types=1);
/**
 * Listado de presupuestos.
 * @var array<int, array<string, mixed>> $presupuestos
 */
$base = BASE_URL;
$fmt = static fn (float $n): string => MONEDA . ' ' . number_format($n, 2, ',', '.');
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0">Presupuestos</h1>
        <p class="text-muted mb-0"><?= count($presupuestos) ?> presupuesto(s) guardado(s)</p>
    </div>
    <a href="<?= $base ?>/presupuestos/nuevo" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i>Nuevo presupuesto
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Pieza</th>
                    <th>Cliente</th>
                    <th>Material</th>
                    <th class="text-end">Cant.</th>
                    <th class="text-end">Costo</th>
                    <th class="text-end">Precio final</th>
                    <th>Estado</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($presupuestos)): ?>
                <tr><td colspan="9" class="text-center text-muted py-5">
                    No hay presupuestos todavía.
                    <a href="<?= $base ?>/presupuestos/nuevo">Creá el primero</a>.
                </td></tr>
            <?php else: ?>
                <?php foreach ($presupuestos as $p): ?>
                    <tr>
                        <td class="text-muted">#<?= (int) $p['id'] ?></td>
                        <td class="fw-medium"><?= htmlspecialchars((string) $p['nombre_pieza']) ?></td>
                        <td><?= htmlspecialchars((string) ($p['cliente_nombre'] ?? '—')) ?></td>
                        <td><span class="badge bg-secondary-subtle text-secondary-emphasis"><?= htmlspecialchars((string) $p['material']) ?></span></td>
                        <td class="text-end"><?= (int) $p['cantidad'] ?></td>
                        <td class="text-end text-muted"><?= $fmt((float) $p['costo_total']) ?></td>
                        <td class="text-end fw-semibold"><?= $fmt((float) $p['precio_final']) ?></td>
                        <td>
                            <?php if (!empty($p['orden_id'])): ?>
                                <a href="<?= $base ?>/ordenes/<?= (int) $p['orden_id'] ?>/mop" class="badge bg-success text-decoration-none">
                                    <i class="bi bi-check-lg"></i> Orden #<?= (int) $p['orden_id'] ?>
                                </a>
                            <?php else: ?>
                                <span class="badge bg-light text-muted border">Presupuesto</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end text-nowrap">
                            <a href="<?= $base ?>/presupuestos/<?= (int) $p['id'] ?>/editar" class="btn btn-sm btn-outline-primary" title="Editar">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <?php if (empty($p['orden_id'])): ?>
                                <a href="<?= $base ?>/presupuestos/<?= (int) $p['id'] ?>/convertir" class="btn btn-sm btn-outline-success"
                                   title="Convertir en orden" onclick="return confirm('¿Convertir el presupuesto #<?= (int) $p['id'] ?> en una orden de trabajo?');">
                                    <i class="bi bi-arrow-right-circle"></i>
                                </a>
                            <?php endif; ?>
                            <a href="<?= $base ?>/presupuestos/<?= (int) $p['id'] ?>/eliminar" class="btn btn-sm btn-outline-danger"
                               title="Eliminar" onclick="return confirm('¿Eliminar el presupuesto #<?= (int) $p['id'] ?>?');">
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
