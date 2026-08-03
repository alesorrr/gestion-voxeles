<?php
declare(strict_types=1);

namespace App\Models;

use PDO;

/**
 * Modelo de Clientes. CRUD básico sobre la tabla `clientes`.
 */
final class Cliente
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Devuelve todos los clientes ordenados por nombre.
     *
     * @return array<int, array<string, mixed>>
     */
    public function todos(): array
    {
        $sql = 'SELECT * FROM clientes ORDER BY nombre ASC';
        return $this->db->query($sql)->fetchAll();
    }

    /**
     * Busca un cliente por su ID.
     *
     * @return array<string, mixed>|null
     */
    public function buscar(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM clientes WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $fila = $stmt->fetch();

        return $fila !== false ? $fila : null;
    }

    /**
     * Crea un nuevo cliente y devuelve su ID.
     *
     * @param array<string, mixed> $datos
     */
    public function crear(array $datos): int
    {
        $sql = 'INSERT INTO clientes (nombre, email, telefono, empresa, notas)
                VALUES (:nombre, :email, :telefono, :empresa, :notas)';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'nombre'   => trim((string) ($datos['nombre'] ?? '')),
            'email'    => $datos['email']    ?? null,
            'telefono' => $datos['telefono'] ?? null,
            'empresa'  => $datos['empresa']  ?? null,
            'notas'    => $datos['notas']    ?? null,
        ]);

        return (int) $this->db->lastInsertId();
    }

    /**
     * Actualiza un cliente existente.
     *
     * @param array<string, mixed> $datos
     */
    public function actualizar(int $id, array $datos): bool
    {
        $sql = 'UPDATE clientes
                   SET nombre = :nombre, email = :email, telefono = :telefono,
                       empresa = :empresa, notas = :notas
                 WHERE id = :id';
        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'id'       => $id,
            'nombre'   => trim((string) ($datos['nombre'] ?? '')),
            'email'    => $datos['email']    ?? null,
            'telefono' => $datos['telefono'] ?? null,
            'empresa'  => $datos['empresa']  ?? null,
            'notas'    => $datos['notas']    ?? null,
        ]);
    }

    /**
     * Elimina un cliente por ID.
     */
    public function eliminar(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM clientes WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }
}
