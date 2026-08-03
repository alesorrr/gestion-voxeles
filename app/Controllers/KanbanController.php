<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\Orden;

/**
 * Controlador del tablero Kanban.
 */
final class KanbanController extends Controller
{
    private Orden $orden;

    public function __construct()
    {
        $this->orden = new Orden();
    }

    /**
     * Muestra el tablero con las órdenes agrupadas por estado.
     */
    public function index(): void
    {
        $this->exigirAuth();
        $this->render('kanban/index', [
            'titulo'   => 'Tablero Kanban',
            'estados'  => $this->orden->estados(),
            'columnas' => $this->orden->porEstado(),
        ]);
    }

    /**
     * Endpoint AJAX: actualiza el estado de una orden.
     * Espera JSON: { "orden_id": int, "estado_id": int }
     */
    public function updateEstado(): void
    {
        $this->exigirAuth();

        $entrada = json_decode(file_get_contents('php://input') ?: '[]', true);
        if (!is_array($entrada)) {
            $entrada = [];
        }

        $ordenId  = (int) ($entrada['orden_id'] ?? $_POST['orden_id'] ?? 0);
        $estadoId = (int) ($entrada['estado_id'] ?? $_POST['estado_id'] ?? 0);

        if ($ordenId <= 0 || $estadoId <= 0) {
            $this->json(['ok' => false, 'error' => 'Parámetros inválidos'], 400);
        }

        $ok = $this->orden->actualizarEstado($ordenId, $estadoId);
        $this->json(['ok' => $ok]);
    }
}
