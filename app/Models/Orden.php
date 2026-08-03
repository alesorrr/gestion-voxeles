<?php
declare(strict_types=1);

namespace App\Models;

use PDO;

/**
 * Modelo de Órdenes de Trabajo (MOP).
 * CRUD completo + soporte para Kanban y resúmenes de ingresos.
 */
final class Orden
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Lista todas las órdenes con datos del cliente y estado.
     *
     * @return array<int, array<string, mixed>>
     */
    public function todas(): array
    {
        $sql = 'SELECT o.*, c.nombre AS cliente_nombre,
                       e.nombre AS estado_nombre, e.color AS estado_color, e.slug AS estado_slug
                  FROM ordenes_trabajo o
                  JOIN clientes c       ON c.id = o.cliente_id
                  JOIN estados_orden e  ON e.id = o.estado_id
              ORDER BY o.creado_en DESC';
        return $this->db->query($sql)->fetchAll();
    }

    /**
     * Devuelve las últimas N órdenes (para el dashboard).
     *
     * @return array<int, array<string, mixed>>
     */
    public function ultimas(int $limite = 5): array
    {
        $sql = 'SELECT o.*, c.nombre AS cliente_nombre,
                       e.nombre AS estado_nombre, e.color AS estado_color
                  FROM ordenes_trabajo o
                  JOIN clientes c      ON c.id = o.cliente_id
                  JOIN estados_orden e ON e.id = o.estado_id
              ORDER BY o.creado_en DESC
                 LIMIT :limite';
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue('limite', $limite, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Busca una orden por ID (con datos completos del cliente y estado).
     *
     * @return array<string, mixed>|null
     */
    public function buscar(int $id): ?array
    {
        $sql = 'SELECT o.*, c.nombre AS cliente_nombre, c.email AS cliente_email,
                       c.telefono AS cliente_telefono, c.empresa AS cliente_empresa,
                       e.nombre AS estado_nombre, e.color AS estado_color, e.slug AS estado_slug
                  FROM ordenes_trabajo o
                  JOIN clientes c      ON c.id = o.cliente_id
                  JOIN estados_orden e ON e.id = o.estado_id
                 WHERE o.id = :id';
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        $fila = $stmt->fetch();

        return $fila !== false ? $fila : null;
    }

    /**
     * Crea una nueva orden y devuelve su ID.
     *
     * @param array<string, mixed> $d
     */
    public function crear(array $d): int
    {
        $sql = 'INSERT INTO ordenes_trabajo
                    (cliente_id, estado_id, nombre_proyecto, archivo_3d, material, color,
                     peso_estimado_g, tiempo_estimado_hs, infill_porcentaje,
                     altura_capa, cantidad_piezas, metodo_contacto, fecha_estimada, fecha_limite,
                     costo_material, precio_final, pagado, fecha_pago, notas)
                VALUES
                    (:cliente_id, :estado_id, :nombre_proyecto, :archivo_3d, :material, :color,
                     :peso_estimado_g, :tiempo_estimado_hs, :infill_porcentaje,
                     :altura_capa, :cantidad_piezas, :metodo_contacto, :fecha_estimada, :fecha_limite,
                     :costo_material, :precio_final, :pagado, :fecha_pago, :notas)';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($this->normalizar($d));

        $id = (int) $this->db->lastInsertId();

        // Si se crea ya pagada, registrar ingreso.
        if ((int) ($d['pagado'] ?? 0) === 1) {
            $this->registrarIngreso($id, (float) ($d['precio_final'] ?? 0), (string) ($d['nombre_proyecto'] ?? ''));
        }

        return $id;
    }

    /**
     * Actualiza una orden existente.
     *
     * @param array<string, mixed> $d
     */
    public function actualizar(int $id, array $d): bool
    {
        // Estado de pago previo para detectar transición a "pagado".
        $previa = $this->buscar($id);
        $pagadoAntes = $previa !== null ? (int) $previa['pagado'] : 0;

        $sql = 'UPDATE ordenes_trabajo SET
                    cliente_id = :cliente_id, estado_id = :estado_id,
                    nombre_proyecto = :nombre_proyecto, archivo_3d = :archivo_3d,
                    material = :material, color = :color,
                    peso_estimado_g = :peso_estimado_g, tiempo_estimado_hs = :tiempo_estimado_hs,
                    infill_porcentaje = :infill_porcentaje, altura_capa = :altura_capa,
                    cantidad_piezas = :cantidad_piezas, metodo_contacto = :metodo_contacto,
                    fecha_estimada = :fecha_estimada, fecha_limite = :fecha_limite,
                    costo_material = :costo_material,
                    precio_final = :precio_final, pagado = :pagado,
                    fecha_pago = :fecha_pago, notas = :notas
                WHERE id = :id';
        $stmt = $this->db->prepare($sql);
        $params = $this->normalizar($d);
        $params['id'] = $id;
        $ok = $stmt->execute($params);

        // Si pasó a pagado y antes no lo estaba, registrar ingreso.
        if ($ok && $pagadoAntes === 0 && (int) ($d['pagado'] ?? 0) === 1) {
            $this->registrarIngreso($id, (float) ($d['precio_final'] ?? 0), (string) ($d['nombre_proyecto'] ?? ''));
        }

        return $ok;
    }

    /**
     * Elimina una orden.
     */
    public function eliminar(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM ordenes_trabajo WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }

    // --------------------------------------------------------
    //  Kanban
    // --------------------------------------------------------

    /**
     * Devuelve todos los estados (columnas del Kanban) ordenados.
     *
     * @return array<int, array<string, mixed>>
     */
    public function estados(): array
    {
        return $this->db->query('SELECT * FROM estados_orden ORDER BY orden ASC')->fetchAll();
    }

    /**
     * Devuelve las órdenes agrupadas por estado (id de estado => lista de órdenes).
     *
     * @return array<int, array<int, array<string, mixed>>>
     */
    public function porEstado(): array
    {
        $sql = 'SELECT o.*, c.nombre AS cliente_nombre
                  FROM ordenes_trabajo o
                  JOIN clientes c ON c.id = o.cliente_id
              ORDER BY o.actualizado_en DESC';
        $filas = $this->db->query($sql)->fetchAll();

        $agrupadas = [];
        foreach ($filas as $fila) {
            $agrupadas[(int) $fila['estado_id']][] = $fila;
        }

        return $agrupadas;
    }

    /**
     * Actualiza el estado de una orden (usado por el Kanban vía AJAX).
     * Si el nuevo estado es final, marca la orden como pagada y registra ingreso.
     */
    public function actualizarEstado(int $id, int $estadoId): bool
    {
        $stmt = $this->db->prepare('UPDATE ordenes_trabajo SET estado_id = :estado_id WHERE id = :id');
        $ok = $stmt->execute(['estado_id' => $estadoId, 'id' => $id]);

        if (!$ok) {
            return false;
        }

        // ¿Es un estado final (Completado / Pagado)?
        $estStmt = $this->db->prepare('SELECT es_final FROM estados_orden WHERE id = :id');
        $estStmt->execute(['id' => $estadoId]);
        $esFinal = (int) ($estStmt->fetchColumn() ?: 0);

        if ($esFinal === 1) {
            $orden = $this->buscar($id);
            if ($orden !== null && (int) $orden['pagado'] === 0) {
                $this->db->prepare(
                    'UPDATE ordenes_trabajo SET pagado = 1, fecha_pago = CURDATE() WHERE id = :id'
                )->execute(['id' => $id]);
                $this->registrarIngreso($id, (float) $orden['precio_final'], (string) $orden['nombre_proyecto']);
            }
        }

        return true;
    }

    // --------------------------------------------------------
    //  Ingresos / resúmenes
    // --------------------------------------------------------

    /**
     * Registra un ingreso ligado a una orden (evita duplicados por orden).
     */
    public function registrarIngreso(int $ordenId, float $monto, string $descripcion): void
    {
        // Evitar registrar dos veces el ingreso de la misma orden.
        $chk = $this->db->prepare('SELECT COUNT(*) FROM ingresos WHERE orden_id = :id');
        $chk->execute(['id' => $ordenId]);
        if ((int) $chk->fetchColumn() > 0) {
            return;
        }

        $sql = 'INSERT INTO ingresos (orden_id, descripcion, monto, fecha)
                VALUES (:orden_id, :descripcion, :monto, CURDATE())';
        $this->db->prepare($sql)->execute([
            'orden_id'    => $ordenId,
            'descripcion' => 'Orden pagada: ' . $descripcion,
            'monto'       => $monto,
        ]);
    }

    /**
     * Resumen de ingresos en un rango de fechas.
     *
     * @return array{total: float, cantidad: int}
     */
    public function getIngresosResumen(?string $desde = null, ?string $hasta = null): array
    {
        $sql = 'SELECT COALESCE(SUM(monto), 0) AS total, COUNT(*) AS cantidad FROM ingresos WHERE 1=1';
        $params = [];

        if ($desde !== null && $desde !== '') {
            $sql .= ' AND fecha >= :desde';
            $params['desde'] = $desde;
        }
        if ($hasta !== null && $hasta !== '') {
            $sql .= ' AND fecha <= :hasta';
            $params['hasta'] = $hasta;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $fila = $stmt->fetch();

        return [
            'total'    => (float) ($fila['total'] ?? 0),
            'cantidad' => (int) ($fila['cantidad'] ?? 0),
        ];
    }

    /**
     * Cuenta las órdenes activas (no finalizadas).
     */
    public function contarActivas(): int
    {
        $sql = 'SELECT COUNT(*)
                  FROM ordenes_trabajo o
                  JOIN estados_orden e ON e.id = o.estado_id
                 WHERE e.es_final = 0';
        return (int) $this->db->query($sql)->fetchColumn();
    }

    /**
     * Lista de ingresos recientes (para contabilidad).
     *
     * @return array<int, array<string, mixed>>
     */
    public function ingresosRecientes(?string $desde = null, ?string $hasta = null, int $limite = 50): array
    {
        $sql = 'SELECT * FROM ingresos WHERE 1=1';
        $params = [];
        if ($desde !== null && $desde !== '') {
            $sql .= ' AND fecha >= :desde';
            $params['desde'] = $desde;
        }
        if ($hasta !== null && $hasta !== '') {
            $sql .= ' AND fecha <= :hasta';
            $params['hasta'] = $hasta;
        }
        $sql .= ' ORDER BY fecha DESC, id DESC LIMIT :limite';

        $stmt = $this->db->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue('limite', $limite, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // --------------------------------------------------------
    //  Helpers
    // --------------------------------------------------------

    /**
     * Normaliza y castea los datos entrantes para persistencia.
     *
     * @param array<string, mixed> $d
     * @return array<string, mixed>
     */
    private function normalizar(array $d): array
    {
        $pagado = (int) ($d['pagado'] ?? 0);
        return [
            'cliente_id'         => (int) ($d['cliente_id'] ?? 0),
            'estado_id'          => (int) ($d['estado_id'] ?? 1),
            'nombre_proyecto'    => trim((string) ($d['nombre_proyecto'] ?? '')),
            'archivo_3d'         => $d['archivo_3d'] ?? null,
            'material'           => (string) ($d['material'] ?? 'PLA'),
            'color'              => $d['color'] ?? null,
            'peso_estimado_g'    => (float) ($d['peso_estimado_g'] ?? 0),
            'tiempo_estimado_hs' => (float) ($d['tiempo_estimado_hs'] ?? 0),
            'infill_porcentaje'  => (int) ($d['infill_porcentaje'] ?? 20),
            'altura_capa'        => (float) ($d['altura_capa'] ?? 0.20),
            'cantidad_piezas'    => max(1, (int) ($d['cantidad_piezas'] ?? 1)),
            'metodo_contacto'    => ($d['metodo_contacto'] ?? null) !== '' ? ($d['metodo_contacto'] ?? null) : null,
            'fecha_estimada'     => !empty($d['fecha_estimada']) ? (string) $d['fecha_estimada'] : null,
            'fecha_limite'       => !empty($d['fecha_limite']) ? (string) $d['fecha_limite'] : null,
            'costo_material'     => (float) ($d['costo_material'] ?? 0),
            'precio_final'       => (float) ($d['precio_final'] ?? 0),
            'pagado'             => $pagado,
            'fecha_pago'         => ($pagado === 1)
                ? ((string) ($d['fecha_pago'] ?? date('Y-m-d')) ?: date('Y-m-d'))
                : null,
            'notas'              => $d['notas'] ?? null,
        ];
    }
}
