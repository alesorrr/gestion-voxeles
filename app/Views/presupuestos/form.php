<?php
declare(strict_types=1);
/**
 * Formulario de presupuesto con calculadora de costos en vivo.
 * @var array<string, mixed>|null $presupuesto
 * @var array<int, array<string, mixed>> $clientes
 */
$base = BASE_URL;
$esEdicion = $presupuesto !== null;
$accion = $esEdicion ? $base . '/presupuestos/' . (int) $presupuesto['id'] : $base . '/presupuestos';

$v = static function (string $campo, $def = '') use ($presupuesto) {
    return htmlspecialchars((string) ($presupuesto[$campo] ?? $def));
};
$materiales = ['PLA', 'PETG', 'ASA', 'ABS', 'TPU', 'Flex', 'Resina', 'Nylon', 'Otro'];
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0"><?= $esEdicion ? 'Presupuesto #' . (int) $presupuesto['id'] : 'Nuevo presupuesto' ?></h1>
    <a href="<?= $base ?>/presupuestos" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Volver
    </a>
</div>

<form method="post" action="<?= $accion ?>" class="row g-4" id="formPresupuesto">
    <div class="col-lg-8">
        <!-- Datos del proyecto -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white"><h2 class="h6 mb-0">Datos de la pieza</h2></div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-7">
                        <label class="form-label">Nombre de la pieza <span class="text-danger">*</span></label>
                        <input type="text" name="nombre_pieza" class="form-control" required value="<?= $v('nombre_pieza') ?>">
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">Cliente (opcional)</label>
                        <select name="cliente_id" class="form-select">
                            <option value="">— Sin cliente —</option>
                            <?php foreach ($clientes as $c): ?>
                                <option value="<?= (int) $c['id'] ?>"
                                    <?= ((int) ($presupuesto['cliente_id'] ?? 0) === (int) $c['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars((string) $c['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Material</label>
                        <select name="material" class="form-select">
                            <?php foreach ($materiales as $m): ?>
                                <option value="<?= $m ?>" <?= (($presupuesto['material'] ?? 'PLA') === $m) ? 'selected' : '' ?>><?= $m ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Cantidad de unidades</label>
                        <input type="number" step="1" min="1" name="cantidad" class="calc form-control" value="<?= $v('cantidad', '1') ?>">
                    </div>
                </div>
            </div>
        </div>

        <!-- Filamento y tiempos -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white"><h2 class="h6 mb-0">Filamento y tiempos (del laminador)</h2></div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Costo del filamento (<?= MONEDA ?> / kg)</label>
                        <input type="number" step="0.01" min="0" name="costo_kg" class="calc form-control" value="<?= $v('costo_kg', '0') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Peso de la pieza (g)</label>
                        <input type="number" step="0.01" min="0" name="peso_g" class="calc form-control" value="<?= $v('peso_g', '0') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Tiempo de impresión (hs)</label>
                        <input type="number" step="0.01" min="0" name="tiempo_impresion_hs" class="calc form-control" value="<?= $v('tiempo_impresion_hs', '0') ?>">
                    </div>
                </div>
            </div>
        </div>

        <!-- Máquina y energía -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white"><h2 class="h6 mb-0">Máquina y energía</h2></div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Costo de máquina (<?= MONEDA ?> / hora)</label>
                        <input type="number" step="0.01" min="0" name="costo_maquina_hora" class="calc form-control" value="<?= $v('costo_maquina_hora', '0') ?>">
                        <div class="form-text">Depreciación + mantenimiento.</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Consumo (W)</label>
                        <input type="number" step="0.01" min="0" name="potencia_w" class="calc form-control" value="<?= $v('potencia_w', '0') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Precio electricidad (<?= MONEDA ?> / kWh)</label>
                        <input type="number" step="0.0001" min="0" name="precio_kwh" class="calc form-control" value="<?= $v('precio_kwh', '0') ?>">
                    </div>
                </div>
            </div>
        </div>

        <!-- Mano de obra y extras -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white"><h2 class="h6 mb-0">Mano de obra y extras</h2></div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Tiempo de mano de obra (min)</label>
                        <input type="number" step="0.01" min="0" name="tiempo_mano_obra_min" class="calc form-control" value="<?= $v('tiempo_mano_obra_min', '0') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Costo mano de obra (<?= MONEDA ?> / hora)</label>
                        <input type="number" step="0.01" min="0" name="costo_mano_obra_hora" class="calc form-control" value="<?= $v('costo_mano_obra_hora', '0') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Costo de hardware (<?= MONEDA ?>)</label>
                        <input type="number" step="0.01" min="0" name="costo_hardware" class="calc form-control" value="<?= $v('costo_hardware', '0') ?>">
                        <div class="form-text">Imanes, tornillos, insertos…</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Costo de embalaje (<?= MONEDA ?>)</label>
                        <input type="number" step="0.01" min="0" name="costo_embalaje" class="calc form-control" value="<?= $v('costo_embalaje', '0') ?>">
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white"><h2 class="h6 mb-0">Notas</h2></div>
            <div class="card-body">
                <textarea name="notas" class="form-control" rows="2"><?= $v('notas') ?></textarea>
            </div>
        </div>
    </div>

    <!-- Panel de cálculo -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-4 calc-panel" style="top: calc(var(--vx-navbar-h) + 1.25rem);">
            <div class="card-header bg-white"><h2 class="h6 mb-0"><i class="bi bi-calculator me-1"></i>Desglose de costos</h2></div>
            <div class="card-body">
                <ul class="list-group list-group-flush small mb-3">
                    <li class="list-group-item d-flex justify-content-between px-0"><span>Material</span><span id="brk_material">—</span></li>
                    <li class="list-group-item d-flex justify-content-between px-0"><span>Electricidad</span><span id="brk_electricidad">—</span></li>
                    <li class="list-group-item d-flex justify-content-between px-0"><span>Máquina</span><span id="brk_maquina">—</span></li>
                    <li class="list-group-item d-flex justify-content-between px-0"><span>Mano de obra</span><span id="brk_mano">—</span></li>
                    <li class="list-group-item d-flex justify-content-between px-0"><span>Hardware</span><span id="brk_hardware">—</span></li>
                    <li class="list-group-item d-flex justify-content-between px-0"><span>Embalaje</span><span id="brk_embalaje">—</span></li>
                    <li class="list-group-item d-flex justify-content-between px-0 fw-semibold border-top">
                        <span>Costo total</span><span id="brk_total">—</span>
                    </li>
                </ul>

                <label class="form-label small mb-1">Margen de beneficio (%)</label>
                <input type="number" step="0.01" min="0" name="margen_porcentaje" id="inpMargen" class="calc form-control mb-2" value="<?= $v('margen_porcentaje', '40') ?>">
                <div class="d-flex flex-wrap gap-1 mb-3">
                    <button type="button" class="btn btn-sm btn-outline-secondary btn-margen" data-m="25">Competitivo 25%</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary btn-margen" data-m="40">Estándar 40%</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary btn-margen" data-m="60">Premium 60%</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary btn-margen" data-m="80">Lujo 80%</button>
                </div>

                <label class="form-label small mb-1">IVA (%)</label>
                <input type="number" step="0.01" min="0" name="iva_porcentaje" id="inpIva" class="calc form-control mb-3" value="<?= $v('iva_porcentaje', '0') ?>">

                <div class="calc-precio text-center py-3 mb-3">
                    <div class="small text-muted">Precio final sugerido</div>
                    <div class="fs-3 fw-bold text-primary" id="brk_precio">—</div>
                    <div class="small text-muted" id="brk_precio_unit"></div>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="bi bi-save me-1"></i><?= $esEdicion ? 'Guardar cambios' : 'Guardar presupuesto' ?>
                    </button>
                    <?php if ($esEdicion && empty($presupuesto['orden_id'])): ?>
                        <a href="<?= $base ?>/presupuestos/<?= (int) $presupuesto['id'] ?>/convertir"
                           class="btn btn-outline-success"
                           onclick="return confirm('¿Convertir este presupuesto en una orden de trabajo?');">
                            <i class="bi bi-arrow-right-circle me-1"></i>Convertir en orden
                        </a>
                    <?php elseif ($esEdicion && !empty($presupuesto['orden_id'])): ?>
                        <a href="<?= $base ?>/ordenes/<?= (int) $presupuesto['orden_id'] ?>/mop" class="btn btn-outline-secondary">
                            <i class="bi bi-printer me-1"></i>Ver orden #<?= (int) $presupuesto['orden_id'] ?>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
    window.VX_MONEDA = <?= json_encode(MONEDA) ?>;
</script>
