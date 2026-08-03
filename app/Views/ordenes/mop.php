<?php
declare(strict_types=1);
/**
 * Vista MOP imprimible (Orden de Trabajo).
 * @var array<string, mixed> $orden
 */
$fmt = static fn (float $n): string => MONEDA . ' ' . number_format($n, 2, ',', '.');
$margen = (float) $orden['precio_final'] - (float) $orden['costo_material'];
?>
<div class="mop-doc">

    <!-- Encabezado -->
    <div class="mop-header d-flex justify-content-between align-items-start">
        <div class="d-flex align-items-center gap-3">
            <div class="mop-logo"><i class="bi bi-printer-fill"></i></div>
            <div>
                <h1 class="mop-title mb-0"><?= htmlspecialchars(APP_NAME) ?></h1>
                <p class="text-muted mb-0 small">Orden de Trabajo · Impresión 3D</p>
            </div>
        </div>
        <div class="text-end">
            <div class="mop-numero">MOP #<?= str_pad((string) $orden['id'], 4, '0', STR_PAD_LEFT) ?></div>
            <div class="small text-muted">Fecha: <?= date('d/m/Y', strtotime((string) $orden['creado_en'])) ?></div>
        </div>
    </div>

    <hr class="mop-divider">

    <!-- Cliente y proyecto -->
    <div class="row mb-3">
        <div class="col-6">
            <h2 class="mop-section-title">Cliente</h2>
            <p class="mb-1"><strong><?= htmlspecialchars((string) $orden['cliente_nombre']) ?></strong></p>
            <?php if (!empty($orden['cliente_empresa'])): ?>
                <p class="mb-1 small"><?= htmlspecialchars((string) $orden['cliente_empresa']) ?></p>
            <?php endif; ?>
            <?php if (!empty($orden['cliente_email'])): ?>
                <p class="mb-1 small"><i class="bi bi-envelope me-1"></i><?= htmlspecialchars((string) $orden['cliente_email']) ?></p>
            <?php endif; ?>
            <?php if (!empty($orden['cliente_telefono'])): ?>
                <p class="mb-0 small"><i class="bi bi-telephone me-1"></i><?= htmlspecialchars((string) $orden['cliente_telefono']) ?></p>
            <?php endif; ?>
        </div>
        <div class="col-6">
            <h2 class="mop-section-title">Proyecto</h2>
            <p class="mb-1"><strong><?= htmlspecialchars((string) $orden['nombre_proyecto']) ?></strong></p>
            <?php if (!empty($orden['archivo_3d'])): ?>
                <p class="mb-1 small"><i class="bi bi-file-earmark-binary me-1"></i><?= htmlspecialchars((string) $orden['archivo_3d']) ?></p>
            <?php endif; ?>
            <p class="mb-0 small">
                Estado:
                <span class="badge" style="background-color: <?= htmlspecialchars((string) $orden['estado_color']) ?>">
                    <?= htmlspecialchars((string) $orden['estado_nombre']) ?>
                </span>
            </p>
        </div>
    </div>

    <!-- Detalles técnicos -->
    <h2 class="mop-section-title">Detalles técnicos</h2>
    <table class="table table-sm mop-table mb-4">
        <tbody>
            <tr>
                <th>Material</th><td><?= htmlspecialchars((string) $orden['material']) ?></td>
                <th>Color</th><td><?= htmlspecialchars((string) ($orden['color'] ?: '—')) ?></td>
            </tr>
            <tr>
                <th>Peso estimado</th><td><?= number_format((float) $orden['peso_estimado_g'], 2, ',', '.') ?> g</td>
                <th>Relleno (infill)</th><td><?= (int) $orden['infill_porcentaje'] ?> %</td>
            </tr>
            <tr>
                <th>Tiempo estimado</th><td colspan="3"><?= number_format((float) $orden['tiempo_estimado_hs'], 2, ',', '.') ?> horas</td>
            </tr>
        </tbody>
    </table>

    <!-- Costos -->
    <h2 class="mop-section-title">Costos</h2>
    <table class="table table-sm mop-table mop-costos mb-4">
        <tbody>
            <tr>
                <th style="width:70%">Costo de material</th>
                <td class="text-end"><?= $fmt((float) $orden['costo_material']) ?></td>
            </tr>
            <tr>
                <th>Margen / mano de obra</th>
                <td class="text-end"><?= $fmt($margen) ?></td>
            </tr>
            <tr class="mop-total">
                <th>PRECIO FINAL</th>
                <td class="text-end fw-bold"><?= $fmt((float) $orden['precio_final']) ?></td>
            </tr>
        </tbody>
    </table>

    <?php if (!empty($orden['notas'])): ?>
        <h2 class="mop-section-title">Notas</h2>
        <p class="mop-notas"><?= nl2br(htmlspecialchars((string) $orden['notas'])) ?></p>
    <?php endif; ?>

    <!-- Firmas -->
    <div class="row mop-firmas mt-5">
        <div class="col-6 text-center">
            <div class="mop-firma-linea"></div>
            <p class="small text-muted mb-0">Responsable del taller</p>
        </div>
        <div class="col-6 text-center">
            <div class="mop-firma-linea"></div>
            <p class="small text-muted mb-0">Conformidad del cliente</p>
        </div>
    </div>

    <p class="text-center text-muted small mt-4 mop-pie">
        <?= htmlspecialchars(APP_NAME) ?> · Documento generado el <?= date('d/m/Y H:i') ?>
    </p>
</div>
