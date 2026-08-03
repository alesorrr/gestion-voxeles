<?php
declare(strict_types=1);
/**
 * Listado de órdenes de trabajo.
 * @var array<int, array<string, mixed>> $ordenes
 */
$base = BASE_URL;
$fmt = static fn (float $n): string => MONEDA . ' ' . number_format($n, 2, ',', '.');
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0">Órdenes de trabajo</h1>
        <p class="text-muted mb-0"><?= count($ordenes) ?> orden(es) registradas</p>
    </div>
    <a href="<?= $base ?>/ordenes/nueva" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i>Nueva orden
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Proyecto</th>
                    <th>Cliente</th>
                    <th>Material</th>
                    <th>Estado</th>
                    <th class="text-end">Precio</th>
                    <th>Pago</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($ordenes)): ?>
                <tr><td colspan="8" class="text-center text-muted py-5">
                    No hay órdenes todavía.
                    <a href="<?= $base ?>/ordenes/nueva">Creá la primera</a>.
                </td></tr>
            <?php else: ?>
                <?php foreach ($ordenes as $o): ?>
                    <tr>
                        <td class="text-muted">#<?= (int) $o['id'] ?></td>
                        <td class="fw-medium"><?= htmlspecialchars((string) $o['nombre_proyecto']) ?></td>
                        <td><?= htmlspecialchars((string) $o['cliente_nombre']) ?></td>
                        <td><span class="badge bg-secondary-subtle text-secondary-emphasis"><?= htmlspecialchars((string) $o['material']) ?></span></td>
                        <td>
                            <span class="badge" style="background-color: <?= htmlspecialchars((string) $o['estado_color']) ?>">
                                <?= htmlspecialchars((string) $o['estado_nombre']) ?>
                            </span>
                        </td>
                        <td class="text-end"><?= $fmt((float) $o['precio_final']) ?></td>
                        <td>
                            <?php if ((int) $o['pagado'] === 1): ?>
                                <span class="badge bg-success"><i class="bi bi-check-lg"></i> Pagado</span>
                            <?php else: ?>
                                <span class="badge bg-light text-muted border">Pendiente</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end text-nowrap">
                            <a href="<?= $base ?>/ordenes/<?= (int) $o['id'] ?>/mop" class="btn btn-sm btn-outline-secondary" title="Ver MOP">
                                <i class="bi bi-printer"></i>
                            </a>
                            <a href="<?= $base ?>/ordenes/<?= (int) $o['id'] ?>/editar" class="btn btn-sm btn-outline-primary" title="Editar">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <a href="<?= $base ?>/ordenes/<?= (int) $o['id'] ?>/eliminar" class="btn btn-sm btn-outline-danger"
                               title="Eliminar" onclick="return confirm('¿Eliminar la orden #<?= (int) $o['id'] ?>?');">
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
