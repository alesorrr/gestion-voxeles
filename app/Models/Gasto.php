<?php
declare(strict_types=1);

namespace App\Models;

use PDO;

/**
 * Modelo de Gastos (egresos manuales). CRUD + resúmenes por rango de fechas.
 */
final class Gasto
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Lista de gastos, opcionalmente filtrada por rango de fechas.
     *
     * @return array<int, array<string, mixed>>
     */
    public function todos(?string $desde = null, ?string $hasta = null, int $limite = 100): array
    {
        $sql = 'SELECT * FROM gastos WHERE 1=1';
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

    /**
     * Busca un gasto por ID.
     *
     * @return array<string, mixed>|null
     */
    public function buscar(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM gastos WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $fila = $stmt->fetch();
        return $fila !== false ? $fila : null;
    }

    /**
     * Crea un gasto y devuelve su ID.
     *
     * @param array<string, mixed> $d
     */
    public function crear(array $d): int
    {
        $sql = 'INSERT INTO gastos (categoria, descripcion, monto, fecha)
                VALUES (:categoria, :descripcion, :monto, :fecha)';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'categoria'   => (string) ($d['categoria'] ?? 'Otro'),
            'descripcion' => trim((string) ($d['descripcion'] ?? '')),
            'monto'       => (float) ($d['monto'] ?? 0),
            'fecha'       => (string) ($d['fecha'] ?? date('Y-m-d')),
        ]);
        return (int) $this->db->lastInsertId();
    }

    /**
     * Elimina un gasto.
     */
    public function eliminar(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM gastos WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Resumen de gastos en un rango de fechas.
     *
     * @return array{total: float, cantidad: int, por_categoria: array<int, array<string, mixed>>}
     */
    public function getResumen(?string $fechaDesde = null, ?string $fechaHasta = null): array
    {
        $where = ' WHERE 1=1';
        $params = [];
        if ($fechaDesde !== null && $fechaDesde !== '') {
            $where .= ' AND fecha >= :desde';
            $params['desde'] = $fechaDesde;
        }
        if ($fechaHasta !== null && $fechaHasta !== '') {
            $where .= ' AND fecha <= :hasta';
            $params['hasta'] = $fechaHasta;
        }

        // Totales generales
        $stmt = $this->db->prepare('SELECT COALESCE(SUM(monto),0) AS total, COUNT(*) AS cantidad FROM gastos' . $where);
        $stmt->execute($params);
        $tot = $stmt->fetch();

        // Desglose por categoría
        $stmt2 = $this->db->prepare(
            'SELECT categoria, COALESCE(SUM(monto),0) AS total, COUNT(*) AS cantidad
               FROM gastos' . $where . '
           GROUP BY categoria
           ORDER BY total DESC'
        );
        $stmt2->execute($params);

        return [
            'total'         => (float) ($tot['total'] ?? 0),
            'cantidad'      => (int) ($tot['cantidad'] ?? 0),
            'por_categoria' => $stmt2->fetchAll(),
        ];
    }

    /**
     * Categorías disponibles (según el ENUM del esquema).
     *
     * @return array<int, string>
     */
    public static function categorias(): array
    {
        return ['Materiales', 'Repuestos', 'Electricidad', 'Herramientas', 'Marketing', 'Envios', 'Impuestos', 'Otro'];
    }
}
