<?php
declare(strict_types=1);
/**
 * Tablero Kanban con drag & drop.
 * @var array<int, array<string, mixed>> $estados
 * @var array<int, array<int, array<string, mixed>>> $columnas
 */
$base = BASE_URL;
$fmt = static fn (float $n): string => MONEDA . ' ' . number_format($n, 2, ',', '.');
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0">Tablero Kanban</h1>
        <p class="text-muted mb-0">Arrastrá las tarjetas para cambiar el estado de cada orden</p>
    </div>
    <a href="<?= $base ?>/ordenes/nueva" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i>Nueva orden
    </a>
</div>

<div class="kanban-board" data-endpoint="<?= $base ?>/kanban/estado">
    <?php foreach ($estados as $estado): ?>
        <?php $ordenesCol = $columnas[(int) $estado['id']] ?? []; ?>
        <div class="kanban-col" data-estado-id="<?= (int) $estado['id'] ?>">
            <div class="kanban-col-header" style="border-top-color: <?= htmlspecialchars((string) $estado['color']) ?>">
                <span class="fw-semibold"><?= htmlspecialchars((string) $estado['nombre']) ?></span>
                <span class="badge rounded-pill kanban-count"><?= count($ordenesCol) ?></span>
            </div>
            <div class="kanban-dropzone" data-estado-id="<?= (int) $estado['id'] ?>">
                <?php foreach ($ordenesCol as $o): ?>
                    <div class="kanban-card" draggable="true"
                         data-orden-id="<?= (int) $o['id'] ?>"
                         style="border-left-color: <?= htmlspecialchars((string) $estado['color']) ?>">
                        <div class="d-flex justify-content-between align-items-start">
                            <span class="kanban-card-titulo"><?= htmlspecialchars((string) $o['nombre_proyecto']) ?></span>
                            <span class="text-muted small">#<?= (int) $o['id'] ?></span>
                        </div>
                        <p class="kanban-card-cliente mb-2">
                            <i class="bi bi-person me-1"></i><?= htmlspecialchars((string) $o['cliente_nombre']) ?>
                        </p>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="chip-material"><?= htmlspecialchars((string) $o['material']) ?></span>
                            <span class="kanban-card-precio"><?= $fmt((float) $o['precio_final']) ?></span>
                        </div>
                        <div class="kanban-card-acciones mt-2">
                            <a href="<?= $base ?>/ordenes/<?= (int) $o['id'] ?>/mop" class="btn btn-sm btn-light" title="Ver MOP">
                                <i class="bi bi-printer"></i>
                            </a>
                            <a href="<?= $base ?>/ordenes/<?= (int) $o['id'] ?>/editar" class="btn btn-sm btn-light" title="Editar">
                                <i class="bi bi-pencil"></i>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>
