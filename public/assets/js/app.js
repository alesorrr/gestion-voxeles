/* ============================================================
   Gestión Voxeles - JavaScript del cliente
   Drag & Drop del Kanban + actualización de estado vía AJAX
   ============================================================ */

(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        inicializarKanban();
    });

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
