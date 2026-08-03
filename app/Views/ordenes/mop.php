<?php
declare(strict_types=1);
/**
 * Vista MOP imprimible (Orden de Trabajo).
 * @var array<string, mixed> $orden
 */
$fmt = static fn (float $n): string => MONEDA . ' ' . number_format($n, 2, ',', '.');
$margen = (float) $orden['precio_final'] - (float) $orden['costo_material'];

/** Formatea una fecha YYYY-MM-DD a d/m/Y, o '—' si está vacía. */
$fFecha = static function ($f): string {
    if (empty($f) || $f === '0000-00-00') {
        return '—';
    }
    $ts = strtotime((string) $f);
    return $ts !== false ? date('d/m/Y', $ts) : '—';
};

// Días pactados: entre la creación y la fecha límite.
$diasPactados = null;
if (!empty($orden['fecha_limite']) && $orden['fecha_limite'] !== '0000-00-00') {
    try {
        $desde = new DateTime(date('Y-m-d', strtotime((string) $orden['creado_en'])));
        $hasta = new DateTime((string) $orden['fecha_limite']);
        $diff  = (int) $desde->diff($hasta)->format('%r%a');
        $diasPactados = max(0, $diff);
    } catch (\Throwable $e) {
        $diasPactados = null;
    }
}
?>
<div class="mop-doc">

    <!-- Encabezado -->
    <div class="mop-header d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-3">
            <img src="<?= BASE_URL ?>/assets/img/logo_voxeles_web.png"
                 alt="<?= htmlspecialchars(APP_NAME) ?>" class="mop-logo-img">
            <div>
                <div class="mop-doc-title">ORDEN DE TRABAJO</div>
                <p class="text-muted mb-0 small">Voxeles 3DLab · Impresión 3D</p>
            </div>
        </div>
        <div class="text-end">
            <div class="mop-numero">O. T. #<?= str_pad((string) $orden['id'], 4, '0', STR_PAD_LEFT) ?></div>
            <div class="small text-muted">Fecha Creación: <?= date('d/m/Y', strtotime((string) $orden['creado_en'])) ?></div>
            <div class="mt-1">
                <span class="badge" style="background-color: <?= htmlspecialchars((string) $orden['estado_color']) ?>">
                    <?= htmlspecialchars((string) $orden['estado_nombre']) ?>
                </span>
            </div>
        </div>
    </div>

    <hr class="mop-divider">

    <!-- Caja de datos generales -->
    <div class="mop-infobox mb-4">
        <div class="row g-2">
            <div class="col-md-6 mop-info-item">
                <span class="mop-info-label">Nombre del proyecto</span>
                <span class="mop-info-value"><?= htmlspecialchars((string) $orden['nombre_proyecto']) ?></span>
            </div>
            <div class="col-md-6 mop-info-item">
                <span class="mop-info-label">Cliente</span>
                <span class="mop-info-value">
                    <?= htmlspecialchars((string) $orden['cliente_nombre']) ?>
                    <?php if (!empty($orden['cliente_empresa'])): ?>
                        <span class="text-muted">· <?= htmlspecialchars((string) $orden['cliente_empresa']) ?></span>
                    <?php endif; ?>
                </span>
            </div>
            <div class="col-md-6 mop-info-item">
                <span class="mop-info-label">Método de contacto</span>
                <span class="mop-info-value">
                    <?= htmlspecialchars((string) ($orden['metodo_contacto'] ?: '—')) ?>
                    <?php if (!empty($orden['cliente_telefono'])): ?>
                        <span class="text-muted">· <?= htmlspecialchars((string) $orden['cliente_telefono']) ?></span>
                    <?php endif; ?>
                    <?php if (!empty($orden['cliente_email'])): ?>
                        <span class="text-muted">· <?= htmlspecialchars((string) $orden['cliente_email']) ?></span>
                    <?php endif; ?>
                </span>
            </div>
            <div class="col-md-6 mop-info-item">
                <span class="mop-info-label">Archivo 3D</span>
                <span class="mop-info-value"><?= htmlspecialchars((string) ($orden['archivo_3d'] ?: '—')) ?></span>
            </div>
            <div class="col-md-4 mop-info-item">
                <span class="mop-info-label">Fecha estimada</span>
                <span class="mop-info-value"><?= $fFecha($orden['fecha_estimada'] ?? null) ?></span>
            </div>
            <div class="col-md-4 mop-info-item">
                <span class="mop-info-label">Fecha límite</span>
                <span class="mop-info-value"><?= $fFecha($orden['fecha_limite'] ?? null) ?></span>
            </div>
            <div class="col-md-4 mop-info-item">
                <span class="mop-info-label">Días pactados</span>
                <span class="mop-info-value"><?= $diasPactados !== null ? $diasPactados . ' días' : '—' ?></span>
            </div>
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
                <th>Altura de capa</th><td><?= number_format((float) ($orden['altura_capa'] ?? 0), 2, ',', '.') ?> mm</td>
                <th>Cantidad de piezas</th><td><?= (int) ($orden['cantidad_piezas'] ?? 1) ?></td>
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
