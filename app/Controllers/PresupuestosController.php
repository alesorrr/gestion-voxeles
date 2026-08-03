<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\Presupuesto;
use App\Models\Cliente;
use App\Models\Orden;

/**
 * Controlador de Presupuestos (calculadora de costos de impresión 3D).
 * Accesible por administradores y usuarios de ventas.
 */
final class PresupuestosController extends Controller
{
    private Presupuesto $presupuesto;
    private Cliente $cliente;

    private const ROLES = ['admin', 'ventas'];

    public function __construct()
    {
        $this->presupuesto = new Presupuesto();
        $this->cliente     = new Cliente();
    }

    public function index(): void
    {
        $this->exigirRol(self::ROLES);
        $this->render('presupuestos/index', [
            'titulo'       => 'Presupuestos',
            'presupuestos' => $this->presupuesto->todos(),
        ]);
    }

    public function create(): void
    {
        $this->exigirRol(self::ROLES);
        $this->render('presupuestos/form', [
            'titulo'      => 'Nuevo presupuesto',
            'presupuesto' => null,
            'clientes'    => $this->cliente->todos(),
        ]);
    }

    public function store(): void
    {
        $this->exigirRol(self::ROLES);
        $id = $this->presupuesto->crear($this->datosDesdePost());
        $this->redirigir('/presupuestos/' . $id . '/editar');
    }

    public function edit(int $id): void
    {
        $this->exigirRol(self::ROLES);
        $p = $this->presupuesto->buscar($id);
        if ($p === null) {
            http_response_code(404);
            $this->render('errores/404', ['titulo' => 'No encontrado']);
            return;
        }
        $this->render('presupuestos/form', [
            'titulo'      => 'Presupuesto #' . $id,
            'presupuesto' => $p,
            'clientes'    => $this->cliente->todos(),
        ]);
    }

    public function update(int $id): void
    {
        $this->exigirRol(self::ROLES);
        $this->presupuesto->actualizar($id, $this->datosDesdePost());
        $this->redirigir('/presupuestos/' . $id . '/editar');
    }

    public function delete(int $id): void
    {
        $this->exigirRol(self::ROLES);
        $this->presupuesto->eliminar($id);
        $this->redirigir('/presupuestos');
    }

    /**
     * Convierte un presupuesto en una orden de trabajo.
     */
    public function convertir(int $id): void
    {
        $this->exigirRol(self::ROLES);
        $p = $this->presupuesto->buscar($id);
        if ($p === null) {
            http_response_code(404);
            $this->render('errores/404', ['titulo' => 'No encontrado']);
            return;
        }

        // Cliente: usa el del presupuesto o crea uno genérico si no tiene.
        $clienteId = (int) ($p['cliente_id'] ?? 0);
        if ($clienteId <= 0) {
            $clienteId = $this->cliente->crear(['nombre' => 'Cliente presupuesto #' . $id]);
        }

        $orden = new Orden();
        $ordenId = $orden->crear([
            'cliente_id'         => $clienteId,
            'estado_id'          => 1, // Pendiente / Presupuestado
            'nombre_proyecto'    => (string) $p['nombre_pieza'],
            'material'           => (string) $p['material'],
            'peso_estimado_g'    => (float) $p['peso_g'],
            'tiempo_estimado_hs' => (float) $p['tiempo_impresion_hs'],
            'cantidad_piezas'    => (int) $p['cantidad'],
            'costo_material'     => (float) $p['costo_material'],
            'precio_final'       => (float) $p['precio_final'],
            'notas'              => 'Generado desde el presupuesto #' . $id,
        ]);

        $this->presupuesto->marcarConvertido($id, $ordenId);
        $this->redirigir('/ordenes/' . $ordenId . '/editar');
    }

    /**
     * @return array<string, mixed>
     */
    private function datosDesdePost(): array
    {
        return [
            'nombre_pieza'         => (string) ($_POST['nombre_pieza'] ?? ''),
            'cliente_id'           => (int) ($_POST['cliente_id'] ?? 0),
            'material'             => (string) ($_POST['material'] ?? 'PLA'),
            'costo_kg'             => (float) ($_POST['costo_kg'] ?? 0),
            'peso_g'               => (float) ($_POST['peso_g'] ?? 0),
            'tiempo_impresion_hs'  => (float) ($_POST['tiempo_impresion_hs'] ?? 0),
            'tiempo_mano_obra_min' => (float) ($_POST['tiempo_mano_obra_min'] ?? 0),
            'costo_mano_obra_hora' => (float) ($_POST['costo_mano_obra_hora'] ?? 0),
            'costo_maquina_hora'   => (float) ($_POST['costo_maquina_hora'] ?? 0),
            'potencia_w'           => (float) ($_POST['potencia_w'] ?? 0),
            'precio_kwh'           => (float) ($_POST['precio_kwh'] ?? 0),
            'costo_hardware'       => (float) ($_POST['costo_hardware'] ?? 0),
            'costo_embalaje'       => (float) ($_POST['costo_embalaje'] ?? 0),
            'cantidad'             => (int) ($_POST['cantidad'] ?? 1),
            'margen_porcentaje'    => (float) ($_POST['margen_porcentaje'] ?? 0),
            'iva_porcentaje'       => (float) ($_POST['iva_porcentaje'] ?? 0),
            'notas'                => (string) ($_POST['notas'] ?? ''),
        ];
    }
}
