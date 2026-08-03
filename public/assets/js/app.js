/* ============================================================
   Gestión Voxeles - JavaScript del cliente
   Drag & Drop del Kanban + actualización de estado vía AJAX
   ============================================================ */

(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        inicializarKanban();
        inicializarCalculadora();
    });

    /* ========================================================
       Calculadora de costos de presupuestos (en vivo)
       Refleja la fórmula de App\Models\Presupuesto::calcular()
       ======================================================== */
    function inicializarCalculadora() {
        const form = document.getElementById('formPresupuesto');
        if (!form) {
            return;
        }

        const moneda = (typeof window.VX_MONEDA === 'string' && window.VX_MONEDA) ? window.VX_MONEDA : '$';

        const num = function (name) {
            const el = form.querySelector('[name="' + name + '"]');
            if (!el) {
                return 0;
            }
            const val = parseFloat(el.value);
            return isNaN(val) ? 0 : val;
        };

        const fmt = function (n) {
            return moneda + ' ' + Number(n).toLocaleString('es-UY', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        };

        const setTxt = function (id, valor) {
            const el = document.getElementById(id);
            if (el) {
                el.textContent = valor;
            }
        };

        function recalcular() {
            const costoKg      = num('costo_kg');
            const pesoG        = num('peso_g');
            const tImpresion   = num('tiempo_impresion_hs');
            const tManoObraMin = num('tiempo_mano_obra_min');
            const manoObraHora = num('costo_mano_obra_hora');
            const maquinaHora  = num('costo_maquina_hora');
            const potenciaW    = num('potencia_w');
            const precioKwh    = num('precio_kwh');
            const hardware     = num('costo_hardware');
            const embalaje     = num('costo_embalaje');
            let cantidad       = parseInt(num('cantidad'), 10);
            if (isNaN(cantidad) || cantidad < 1) {
                cantidad = 1;
            }
            const margen       = num('margen_porcentaje');
            const iva          = num('iva_porcentaje');

            // Por unidad
            const material     = (costoKg / 1000) * pesoG;
            const electricidad = (potenciaW / 1000) * tImpresion * precioKwh;
            const maquina      = maquinaHora * tImpresion;
            const manoObra     = manoObraHora * (tManoObraMin / 60);
            const unit         = material + electricidad + maquina + manoObra + hardware + embalaje;

            // Totales (× cantidad)
            const costoTotal   = unit * cantidad;
            const precioSinIva = costoTotal * (1 + margen / 100);
            const precioFinal  = precioSinIva * (1 + iva / 100);

            setTxt('brk_material', fmt(material * cantidad));
            setTxt('brk_electricidad', fmt(electricidad * cantidad));
            setTxt('brk_maquina', fmt(maquina * cantidad));
            setTxt('brk_mano', fmt(manoObra * cantidad));
            setTxt('brk_hardware', fmt(hardware * cantidad));
            setTxt('brk_embalaje', fmt(embalaje * cantidad));
            setTxt('brk_total', fmt(costoTotal));
            setTxt('brk_precio', fmt(precioFinal));
            setTxt('brk_precio_unit', cantidad > 1
                ? (fmt(precioFinal / cantidad) + ' por unidad · ' + cantidad + ' u.')
                : '');
        }

        // Recalcular ante cualquier cambio en los campos .calc
        form.querySelectorAll('.calc').forEach(function (input) {
            input.addEventListener('input', recalcular);
            input.addEventListener('change', recalcular);
        });

        // Botones de margen sugerido
        form.querySelectorAll('.btn-margen').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const inp = document.getElementById('inpMargen');
                if (inp) {
                    inp.value = btn.dataset.m;
                    recalcular();
                }
                form.querySelectorAll('.btn-margen').forEach(function (b) {
                    b.classList.remove('active');
                });
                btn.classList.add('active');
            });
        });

        recalcular();
    }

    /**
     * Inicializa el tablero Kanban si está presente en la página.
     */
    function inicializarKanban() {
        const tablero = document.querySelector('.kanban-board');
        if (!tablero) {
            return;
        }

        const endpoint = tablero.dataset.endpoint || '/kanban/estado';
        let tarjetaArrastrada = null;

        const tarjetas = tablero.querySelectorAll('.kanban-card');
        const zonas = tablero.querySelectorAll('.kanban-dropzone');

        // ---- Eventos de las tarjetas -------------------------
        tarjetas.forEach(function (tarjeta) {
            tarjeta.addEventListener('dragstart', function () {
                tarjetaArrastrada = tarjeta;
                setTimeout(() => tarjeta.classList.add('dragging'), 0);
            });

            tarjeta.addEventListener('dragend', function () {
                tarjeta.classList.remove('dragging');
                tarjetaArrastrada = null;
            });
        });

        // ---- Eventos de las zonas de destino -----------------
        zonas.forEach(function (zona) {
            zona.addEventListener('dragover', function (e) {
                e.preventDefault(); // necesario para permitir el drop
                zona.classList.add('drag-over');
            });

            zona.addEventListener('dragleave', function () {
                zona.classList.remove('drag-over');
            });

            zona.addEventListener('drop', function (e) {
                e.preventDefault();
                zona.classList.remove('drag-over');

                if (!tarjetaArrastrada) {
                    return;
                }

                const nuevoEstado = parseInt(zona.dataset.estadoId, 10);
                const ordenId = parseInt(tarjetaArrastrada.dataset.ordenId, 10);
                const columnaOrigen = tarjetaArrastrada.closest('.kanban-dropzone');
                const estadoOrigen = parseInt(columnaOrigen.dataset.estadoId, 10);

                // Si es la misma columna, no hacemos nada.
                if (estadoOrigen === nuevoEstado) {
                    return;
                }

                // Movemos la tarjeta visualmente (optimista).
                zona.appendChild(tarjetaArrastrada);
                actualizarContadores(tablero);

                // Enviamos el cambio al servidor.
                enviarCambioEstado(endpoint, ordenId, nuevoEstado)
                    .then(function (ok) {
                        if (!ok) {
                            // Si falla, devolvemos la tarjeta a su columna original.
                            columnaOrigen.appendChild(tarjetaArrastrada);
                            actualizarContadores(tablero);
                            mostrarError('No se pudo actualizar el estado. Intentá de nuevo.');
                        }
                    })
                    .catch(function () {
                        columnaOrigen.appendChild(tarjetaArrastrada);
                        actualizarContadores(tablero);
                        mostrarError('Error de conexión al actualizar el estado.');
                    });
            });
        });
    }

    /**
     * Envía el cambio de estado al backend mediante fetch().
     * @returns {Promise<boolean>}
     */
    function enviarCambioEstado(endpoint, ordenId, estadoId) {
        return fetch(endpoint, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ orden_id: ordenId, estado_id: estadoId })
        })
            .then(function (resp) { return resp.json(); })
            .then(function (data) { return data && data.ok === true; });
    }

    /**
     * Recalcula los contadores de tarjetas de cada columna.
     */
    function actualizarContadores(tablero) {
        tablero.querySelectorAll('.kanban-col').forEach(function (col) {
            const cantidad = col.querySelectorAll('.kanban-card').length;
            const contador = col.querySelector('.kanban-count');
            if (contador) {
                contador.textContent = String(cantidad);
            }
        });
    }

    /**
     * Muestra un aviso de error temporal.
     */
    function mostrarError(mensaje) {
        const alerta = document.createElement('div');
        alerta.className = 'alert alert-danger position-fixed top-0 start-50 translate-middle-x mt-3 shadow';
        alerta.style.zIndex = '2000';
        alerta.textContent = mensaje;
        document.body.appendChild(alerta);
        setTimeout(function () { alerta.remove(); }, 3500);
    }
})();
