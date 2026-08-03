<?php
declare(strict_types=1);

namespace App\Models;

use PDO;

/**
 * Modelo de Presupuestos (calculadora de costos de impresión 3D).
 *
 * Desglose de costos (por unidad):
 *   - Material     = costo_kg / 1000 * peso_g
 *   - Electricidad = potencia_w / 1000 * tiempo_impresion_hs * precio_kwh
 *   - Máquina      = costo_maquina_hora * tiempo_impresion_hs
 *   - Mano de obra = costo_mano_obra_hora * (tiempo_mano_obra_min / 60)
 *   - Hardware + Embalaje (valores directos)
 * Precio final = costo_total * (1 + margen%) * (1 + iva%)
 */
final class Presupuesto
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Calcula el desglose de costos a partir de los datos de entrada.
     * Los importes del desglose se devuelven ya multiplicados por la cantidad.
     *
     * @param array<string, mixed> $d
     * @return array<string, float>
     */
    public static function calcular(array $d): array
    {
        $costoKg      = (float) ($d['costo_kg'] ?? 0);
        $pesoG        = (float) ($d['peso_g'] ?? 0);
        $tImpresion   = (float) ($d['tiempo_impresion_hs'] ?? 0);
        $tManoObraMin = (float) ($d['tiempo_mano_obra_min'] ?? 0);
        $manoObraHora = (float) ($d['costo_mano_obra_hora'] ?? 0);
        $maquinaHora  = (float) ($d['costo_maquina_hora'] ?? 0);
        $potenciaW    = (float) ($d['potencia_w'] ?? 0);
        $precioKwh    = (float) ($d['precio_kwh'] ?? 0);
        $hardware     = (float) ($d['costo_hardware'] ?? 0);
        $embalaje     = (float) ($d['costo_embalaje'] ?? 0);
        $cantidad     = max(1, (int) ($d['cantidad'] ?? 1));
        $margen       = (float) ($d['margen_porcentaje'] ?? 0);
        $iva          = (float) ($d['iva_porcentaje'] ?? 0);

        // Por unidad
        $material     = ($costoKg / 1000) * $pesoG;
        $electricidad = ($potenciaW / 1000) * $tImpresion * $precioKwh;
        $maquina      = $maquinaHora * $tImpresion;
        $manoObra     = $manoObraHora * ($tManoObraMin / 60);
        $unit         = $material + $electricidad + $maquina + $manoObra + $hardware + $embalaje;

        // Totales (× cantidad)
        $costoTotal   = $unit * $cantidad;
        $precioSinIva = $costoTotal * (1 + $margen / 100);
        $precioFinal  = $precioSinIva * (1 + $iva / 100);

        return [
            'costo_material'     => round($material * $cantidad, 2),
            'costo_electricidad' => round($electricidad * $cantidad, 2),
            'costo_maquina'      => round($maquina * $cantidad, 2),
            'costo_mano_obra'    => round($manoObra * $cantidad, 2),
            'costo_hardware'     => round($hardware * $cantidad, 2),
            'costo_embalaje'     => round($embalaje * $cantidad, 2),
            'costo_total'        => round($costoTotal, 2),
            'precio_sin_iva'     => round($precioSinIva, 2),
            'precio_final'       => round($precioFinal, 2),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function todos(): array
    {
        $sql = 'SELECT p.*, c.nombre AS cliente_nombre
                  FROM presupuestos p
             LEFT JOIN clientes c ON c.id = p.cliente_id
              ORDER BY p.creado_en DESC';
        return $this->db->query($sql)->fetchAll();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function buscar(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT p.*, c.nombre AS cliente_nombre
               FROM presupuestos p
          LEFT JOIN clientes c ON c.id = p.cliente_id
              WHERE p.id = :id'
        );
        $stmt->execute(['id' => $id]);
        $fila = $stmt->fetch();
        return $fila !== false ? $fila : null;
    }

    /**
     * Crea un presupuesto y devuelve su ID.
     *
     * @param array<string, mixed> $d
     */
    public function crear(array $d): int
    {
        $p = $this->normalizar($d);
        $sql = 'INSERT INTO presupuestos
                    (nombre_pieza, cliente_id, material, costo_kg, peso_g,
                     tiempo_impresion_hs, tiempo_mano_obra_min, costo_mano_obra_hora,
                     costo_maquina_hora, potencia_w, precio_kwh, costo_hardware,
                     costo_embalaje, cantidad, margen_porcentaje, iva_porcentaje,
                     costo_material, costo_electricidad, costo_maquina, costo_mano_obra,
                     costo_total, precio_final, notas)
                VALUES
                    (:nombre_pieza, :cliente_id, :material, :costo_kg, :peso_g,
                     :tiempo_impresion_hs, :tiempo_mano_obra_min, :costo_mano_obra_hora,
                     :costo_maquina_hora, :potencia_w, :precio_kwh, :costo_hardware,
                     :costo_embalaje, :cantidad, :margen_porcentaje, :iva_porcentaje,
                     :costo_material, :costo_electricidad, :costo_maquina, :costo_mano_obra,
                     :costo_total, :precio_final, :notas)';
        $this->db->prepare($sql)->execute($p);
        return (int) $this->db->lastInsertId();
    }

    /**
     * Actualiza un presupuesto existente.
     *
     * @param array<string, mixed> $d
     */
    public function actualizar(int $id, array $d): bool
    {
        $p = $this->normalizar($d);
        $p['id'] = $id;
        $sql = 'UPDATE presupuestos SET
                    nombre_pieza = :nombre_pieza, cliente_id = :cliente_id, material = :material,
                    costo_kg = :costo_kg, peso_g = :peso_g, tiempo_impresion_hs = :tiempo_impresion_hs,
                    tiempo_mano_obra_min = :tiempo_mano_obra_min, costo_mano_obra_hora = :costo_mano_obra_hora,
                    costo_maquina_hora = :costo_maquina_hora, potencia_w = :potencia_w, precio_kwh = :precio_kwh,
                    costo_hardware = :costo_hardware, costo_embalaje = :costo_embalaje, cantidad = :cantidad,
                    margen_porcentaje = :margen_porcentaje, iva_porcentaje = :iva_porcentaje,
                    costo_material = :costo_material, costo_electricidad = :costo_electricidad,
                    costo_maquina = :costo_maquina, costo_mano_obra = :costo_mano_obra,
                    costo_total = :costo_total, precio_final = :precio_final, notas = :notas
                WHERE id = :id';
        return $this->db->prepare($sql)->execute($p);
    }

    public function eliminar(int $id): bool
    {
        return $this->db->prepare('DELETE FROM presupuestos WHERE id = :id')->execute(['id' => $id]);
    }

    /**
     * Vincula el presupuesto a la orden creada a partir de él.
     */
    public function marcarConvertido(int $id, int $ordenId): void
    {
        $this->db->prepare('UPDATE presupuestos SET orden_id = :o WHERE id = :id')
                 ->execute(['o' => $ordenId, 'id' => $id]);
    }

    /**
     * Normaliza y castea datos + recalcula el desglose (fuente de verdad server-side).
     *
     * @param array<string, mixed> $d
     * @return array<string, mixed>
     */
    private function normalizar(array $d): array
    {
        $calc = self::calcular($d);
        $clienteId = (int) ($d['cliente_id'] ?? 0);
        return [
            'nombre_pieza'         => trim((string) ($d['nombre_pieza'] ?? '')),
            'cliente_id'           => $clienteId > 0 ? $clienteId : null,
            'material'             => (string) ($d['material'] ?? 'PLA'),
            'costo_kg'             => (float) ($d['costo_kg'] ?? 0),
            'peso_g'               => (float) ($d['peso_g'] ?? 0),
            'tiempo_impresion_hs'  => (float) ($d['tiempo_impresion_hs'] ?? 0),
            'tiempo_mano_obra_min' => (float) ($d['tiempo_mano_obra_min'] ?? 0),
            'costo_mano_obra_hora' => (float) ($d['costo_mano_obra_hora'] ?? 0),
            'costo_maquina_hora'   => (float) ($d['costo_maquina_hora'] ?? 0),
            'potencia_w'           => (float) ($d['potencia_w'] ?? 0),
            'precio_kwh'           => (float) ($d['precio_kwh'] ?? 0),
            'costo_hardware'       => (float) ($d['costo_hardware'] ?? 0),
            'costo_embalaje'       => (float) ($d['costo_embalaje'] ?? 0),
            'cantidad'             => max(1, (int) ($d['cantidad'] ?? 1)),
            'margen_porcentaje'    => (float) ($d['margen_porcentaje'] ?? 0),
            'iva_porcentaje'       => (float) ($d['iva_porcentaje'] ?? 0),
            'costo_material'       => $calc['costo_material'],
            'costo_electricidad'   => $calc['costo_electricidad'],
            'costo_maquina'        => $calc['costo_maquina'],
            'costo_mano_obra'      => $calc['costo_mano_obra'],
            'costo_total'          => $calc['costo_total'],
            'precio_final'         => $calc['precio_final'],
            'notas'                => $d['notas'] ?? null,
        ];
    }
}
