<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\Orden;
use App\Models\Gasto;

/**
 * Controlador de Contabilidad: ingresos, gastos y balance.
 */
final class ContabilidadController extends Controller
{
    private Orden $orden;
    private Gasto $gasto;

    public function __construct()
    {
        $this->orden = new Orden();
        $this->gasto = new Gasto();
    }

    /**
     * Dashboard financiero con filtros por rango de fechas.
     */
    public function index(): void
    {
        $this->exigirAuth();

        // Filtros: por defecto, el mes actual.
        $desde = (string) ($_GET['desde'] ?? date('Y-m-01'));
        $hasta = (string) ($_GET['hasta'] ?? date('Y-m-t'));

        $ingresos = $this->orden->getIngresosResumen($desde, $hasta);
        $resumenGastos = $this->gasto->getResumen($desde, $hasta);

        $totalIngresos = $ingresos['total'];
        $totalGastos   = $resumenGastos['total'];

        $this->render('contabilidad/index', [
            'titulo'          => 'Contabilidad',
            'desde'           => $desde,
            'hasta'           => $hasta,
            'totalIngresos'   => $totalIngresos,
            'totalGastos'     => $totalGastos,
            'balance'         => $totalIngresos - $totalGastos,
            'porCategoria'    => $resumenGastos['por_categoria'],
            'gastos'          => $this->gasto->todos($desde, $hasta),
            'ingresos'        => $this->orden->ingresosRecientes($desde, $hasta),
            'categorias'      => Gasto::categorias(),
        ]);
    }

    /**
     * Registra un nuevo gasto.
     */
    public function storeGasto(): void
    {
        $this->exigirAuth();

        $this->gasto->crear([
            'categoria'   => (string) ($_POST['categoria'] ?? 'Otro'),
            'descripcion' => (string) ($_POST['descripcion'] ?? ''),
            'monto'       => (float) ($_POST['monto'] ?? 0),
            'fecha'       => (string) ($_POST['fecha'] ?? date('Y-m-d')),
        ]);

        $this->redirigir('/contabilidad');
    }

    /**
     * Elimina un gasto.
     */
    public function deleteGasto(int $id): void
    {
        $this->exigirAuth();
        $this->gasto->eliminar($id);
        $this->redirigir('/contabilidad');
    }
}
