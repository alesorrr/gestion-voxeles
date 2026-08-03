<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\Orden;
use App\Models\Gasto;

/**
 * Controlador del panel principal (Dashboard).
 */
final class DashboardController extends Controller
{
    /**
     * Muestra totales del mes actual y las últimas órdenes.
     */
    public function index(): void
    {
        $this->exigirAuth();

        $orden = new Orden();
        $gasto = new Gasto();

        $desde = date('Y-m-01');           // primer día del mes
        $hasta = date('Y-m-t');            // último día del mes

        $ingresos = $orden->getIngresosResumen($desde, $hasta);
        $gastos   = $gasto->getResumen($desde, $hasta);

        $totalIngresos = $ingresos['total'];
        $totalGastos   = $gastos['total'];

        $this->render('dashboard/index', [
            'titulo'         => 'Panel principal',
            'totalIngresos'  => $totalIngresos,
            'totalGastos'    => $totalGastos,
            'balance'        => $totalIngresos - $totalGastos,
            'ordenesActivas' => $orden->contarActivas(),
            'ultimasOrdenes' => $orden->ultimas(5),
            'mesNombre'      => $this->nombreMes((int) date('n')) . ' ' . date('Y'),
        ]);
    }

    private function nombreMes(int $n): string
    {
        $meses = [1 => 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
                  'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
        return $meses[$n] ?? '';
    }
}
