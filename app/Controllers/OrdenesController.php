<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\Orden;
use App\Models\Cliente;

/**
 * Controlador de Órdenes de Trabajo (MOP).
 */
final class OrdenesController extends Controller
{
    private Orden $orden;
    private Cliente $cliente;

    public function __construct()
    {
        $this->orden   = new Orden();
        $this->cliente = new Cliente();
    }

    /**
     * Lista todas las órdenes.
     */
    public function index(): void
    {
        $this->exigirAuth();
        $this->render('ordenes/index', [
            'titulo'  => 'Órdenes de trabajo',
            'ordenes' => $this->orden->todas(),
        ]);
    }

    /**
     * Formulario de creación.
     */
    public function create(): void
    {
        $this->exigirAuth();
        $this->render('ordenes/form', [
            'titulo'   => 'Nueva orden',
            'orden'    => null,
            'clientes' => $this->cliente->todos(),
            'estados'  => $this->orden->estados(),
        ]);
    }

    /**
     * Guarda una orden nueva.
     */
    public function store(): void
    {
        $this->exigirAuth();
        $datos = $this->datosDesdePost();

        // Si se ingresó un nuevo cliente, lo creamos primero.
        $nuevoCliente = trim((string) ($_POST['nuevo_cliente'] ?? ''));
        if ($nuevoCliente !== '') {
            $datos['cliente_id'] = $this->cliente->crear(['nombre' => $nuevoCliente]);
        }

        $id = $this->orden->crear($datos);
        $this->redirigir('/ordenes/' . $id . '/mop');
    }

    /**
     * Formulario de edición.
     */
    public function edit(int $id): void
    {
        $this->exigirAuth();
        $orden = $this->orden->buscar($id);
        if ($orden === null) {
            http_response_code(404);
            $this->render('errores/404', ['titulo' => 'No encontrado']);
            return;
        }
        $this->render('ordenes/form', [
            'titulo'   => 'Editar orden #' . $id,
            'orden'    => $orden,
            'clientes' => $this->cliente->todos(),
            'estados'  => $this->orden->estados(),
        ]);
    }

    /**
     * Actualiza una orden existente.
     */
    public function update(int $id): void
    {
        $this->exigirAuth();
        $datos = $this->datosDesdePost();
        $this->orden->actualizar($id, $datos);
        $this->redirigir('/ordenes/' . $id . '/mop');
    }

    /**
     * Elimina una orden.
     */
    public function delete(int $id): void
    {
        $this->exigirAuth();
        $this->orden->eliminar($id);
        $this->redirigir('/ordenes');
    }

    /**
     * Vista MOP imprimible.
     */
    public function mop(int $id): void
    {
        $this->exigirAuth();
        $orden = $this->orden->buscar($id);
        if ($orden === null) {
            http_response_code(404);
            $this->render('errores/404', ['titulo' => 'No encontrado']);
            return;
        }
        $this->render('ordenes/mop', [
            'titulo' => 'MOP #' . $id,
            'orden'  => $orden,
        ], 'print');
    }

    /**
     * Extrae y sanitiza los datos del formulario.
     *
     * @return array<string, mixed>
     */
    private function datosDesdePost(): array
    {
        return [
            'cliente_id'         => (int) ($_POST['cliente_id'] ?? 0),
            'estado_id'          => (int) ($_POST['estado_id'] ?? 1),
            'nombre_proyecto'    => (string) ($_POST['nombre_proyecto'] ?? ''),
            'archivo_3d'         => (string) ($_POST['archivo_3d'] ?? ''),
            'material'           => (string) ($_POST['material'] ?? 'PLA'),
            'color'              => (string) ($_POST['color'] ?? ''),
            'peso_estimado_g'    => (float) ($_POST['peso_estimado_g'] ?? 0),
            'tiempo_estimado_hs' => (float) ($_POST['tiempo_estimado_hs'] ?? 0),
            'infill_porcentaje'  => (int) ($_POST['infill_porcentaje'] ?? 20),
            'altura_capa'        => (float) ($_POST['altura_capa'] ?? 0.20),
            'cantidad_piezas'    => (int) ($_POST['cantidad_piezas'] ?? 1),
            'metodo_contacto'    => (string) ($_POST['metodo_contacto'] ?? ''),
            'fecha_estimada'     => (string) ($_POST['fecha_estimada'] ?? ''),
            'fecha_limite'       => (string) ($_POST['fecha_limite'] ?? ''),
            'costo_material'     => (float) ($_POST['costo_material'] ?? 0),
            'precio_final'       => (float) ($_POST['precio_final'] ?? 0),
            'pagado'             => (int) ($_POST['pagado'] ?? 0),
            'fecha_pago'         => (string) ($_POST['fecha_pago'] ?? ''),
            'notas'              => (string) ($_POST['notas'] ?? ''),
        ];
    }
}
