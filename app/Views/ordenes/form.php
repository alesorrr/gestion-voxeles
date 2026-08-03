<?php
declare(strict_types=1);
/**
 * Formulario de creación/edición de órdenes.
 * @var array<string, mixed>|null $orden
 * @var array<int, array<string, mixed>> $clientes
 * @var array<int, array<string, mixed>> $estados
 */
$base = BASE_URL;
$esEdicion = $orden !== null;
$accion = $esEdicion ? $base . '/ordenes/' . (int) $orden['id'] : $base . '/ordenes';

/** Helper para valores previos. */
$v = static function (string $campo, $def = '') use ($orden) {
    return htmlspecialchars((string) ($orden[$campo] ?? $def));
};

$materiales = ['PLA', 'PETG', 'ASA', 'TPU', 'Resina', 'Nylon', 'Otro'];
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0"><?= $esEdicion ? 'Editar orden #' . (int) $orden['id'] : 'Nueva orden de trabajo' ?></h1>
    <a href="<?= $base ?>/ordenes" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Volver
    </a>
</div>

<form method="post" action="<?= $accion ?>" class="row g-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white"><h2 class="h6 mb-0">Datos del proyecto</h2></div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label">Cliente <span class="text-danger">*</span></label>
                        <select name="cliente_id" class="form-select" id="selectCliente">
                            <option value="">— Seleccioná un cliente —</option>
                            <?php foreach ($clientes as $c): ?>
                                <option value="<?= (int) $c['id'] ?>"
                                    <?= ((int) ($orden['cliente_id'] ?? 0) === (int) $c['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars((string) $c['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">…o nuevo cliente</label>
                        <input type="text" name="nuevo_cliente" class="form-control" placeholder="Nombre del cliente">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Nombre del proyecto <span class="text-danger">*</span></label>
                        <input type="text" name="nombre_proyecto" class="form-control" required value="<?= $v('nombre_proyecto') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Archivo 3D / notas del modelo</label>
                        <input type="text" name="archivo_3d" class="form-control" placeholder="pieza_v3.stl" value="<?= $v('archivo_3d') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Color</label>
                        <input type="text" name="color" class="form-control" placeholder="Negro, Rojo…" value="<?= $v('color') ?>">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Notas</label>
                        <textarea name="notas" class="form-control" rows="3"><?= $v('notas') ?></textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white"><h2 class="h6 mb-0">Detalles técnicos</h2></div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Material</label>
                        <select name="material" class="form-select">
                            <?php foreach ($materiales as $m): ?>
                                <option value="<?= $m ?>" <?= (($orden['material'] ?? 'PLA') === $m) ? 'selected' : '' ?>><?= $m ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Peso estimado (g)</label>
                        <input type="number" step="0.01" min="0" name="peso_estimado_g" class="form-control" value="<?= $v('peso_estimado_g', '0') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Tiempo estimado (hs)</label>
                        <input type="number" step="0.01" min="0" name="tiempo_estimado_hs" class="form-control" value="<?= $v('tiempo_estimado_hs', '0') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Relleno / Infill (%)</label>
                        <input type="number" step="1" min="0" max="100" name="infill_porcentaje" class="form-control" value="<?= $v('infill_porcentaje', '20') ?>">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white"><h2 class="h6 mb-0">Costos y precio</h2></div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Costo de material (<?= MONEDA ?>)</label>
                    <input type="number" step="0.01" min="0" name="costo_material" class="form-control" value="<?= $v('costo_material', '0') ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Precio final (<?= MONEDA ?>)</label>
                    <input type="number" step="0.01" min="0" name="precio_final" class="form-control form-control-lg fw-bold" value="<?= $v('precio_final', '0') ?>">
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white"><h2 class="h6 mb-0">Estado y pago</h2></div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Estado</label>
                    <select name="estado_id" class="form-select">
                        <?php foreach ($estados as $e): ?>
                            <option value="<?= (int) $e['id'] ?>"
                                <?= ((int) ($orden['estado_id'] ?? 1) === (int) $e['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars((string) $e['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" role="switch" id="pagadoSwitch"
                           name="pagado" value="1" <?= ((int) ($orden['pagado'] ?? 0) === 1) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="pagadoSwitch">Marcar como pagado</label>
                </div>
                <div class="mb-0">
                    <label class="form-label">Fecha de pago</label>
                    <input type="date" name="fecha_pago" class="form-control" value="<?= $v('fecha_pago', date('Y-m-d')) ?>">
                </div>
            </div>
        </div>

        <div class="d-grid gap-2">
            <button type="submit" class="btn btn-primary btn-lg">
                <i class="bi bi-save me-1"></i><?= $esEdicion ? 'Guardar cambios' : 'Crear orden' ?>
            </button>
            <?php if ($esEdicion): ?>
                <a href="<?= $base ?>/ordenes/<?= (int) $orden['id'] ?>/mop" class="btn btn-outline-secondary">
                    <i class="bi bi-printer me-1"></i>Ver MOP imprimible
                </a>
            <?php endif; ?>
        </div>
    </div>
</form>
