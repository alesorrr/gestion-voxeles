<?php
declare(strict_types=1);

namespace App\Models;

use PDO;

/**
 * Modelo de Usuarios del sistema. CRUD + gestión de credenciales.
 */
final class Usuario
{
    private PDO $db;

    /** Roles válidos del sistema. */
    public const ROLES = ['admin', 'operador', 'ventas'];

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Devuelve todos los usuarios (sin el hash de contraseña).
     *
     * @return array<int, array<string, mixed>>
     */
    public function todos(): array
    {
        $sql = 'SELECT id, nombre, usuario, rol, activo, creado_en
                  FROM usuarios
              ORDER BY nombre ASC';
        return $this->db->query($sql)->fetchAll();
    }

    /**
     * Busca un usuario por su ID.
     *
     * @return array<string, mixed>|null
     */
    public function buscar(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT id, nombre, usuario, rol, activo, creado_en FROM usuarios WHERE id = :id'
        );
        $stmt->execute(['id' => $id]);
        $fila = $stmt->fetch();
        return $fila !== false ? $fila : null;
    }

    /**
     * Indica si un nombre de usuario ya existe (opcionalmente excluyendo un ID).
     */
    public function existeUsuario(string $usuario, int $excluirId = 0): bool
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM usuarios WHERE usuario = :u AND id <> :id'
        );
        $stmt->execute(['u' => $usuario, 'id' => $excluirId]);
        return (int) $stmt->fetchColumn() > 0;
    }

    /**
     * Crea un usuario nuevo y devuelve su ID.
     *
     * @param array<string, mixed> $d
     */
    public function crear(array $d): int
    {
        $sql = 'INSERT INTO usuarios (nombre, usuario, password_hash, rol, activo)
                VALUES (:nombre, :usuario, :password_hash, :rol, :activo)';
        $this->db->prepare($sql)->execute([
            'nombre'        => trim((string) ($d['nombre'] ?? '')),
            'usuario'       => trim((string) ($d['usuario'] ?? '')),
            'password_hash' => password_hash((string) ($d['password'] ?? ''), PASSWORD_DEFAULT),
            'rol'           => $this->rolValido((string) ($d['rol'] ?? 'operador')),
            'activo'        => (int) ($d['activo'] ?? 1),
        ]);
        return (int) $this->db->lastInsertId();
    }

    /**
     * Actualiza nombre, usuario, rol y (opcionalmente) contraseña.
     *
     * @param array<string, mixed> $d
     */
    public function actualizar(int $id, array $d): bool
    {
        $campos = [
            'nombre'  => trim((string) ($d['nombre'] ?? '')),
            'usuario' => trim((string) ($d['usuario'] ?? '')),
            'rol'     => $this->rolValido((string) ($d['rol'] ?? 'operador')),
            'id'      => $id,
        ];

        $sqlPass = '';
        $password = (string) ($d['password'] ?? '');
        if ($password !== '') {
            $sqlPass = ', password_hash = :password_hash';
            $campos['password_hash'] = password_hash($password, PASSWORD_DEFAULT);
        }

        $sql = 'UPDATE usuarios SET nombre = :nombre, usuario = :usuario, rol = :rol'
             . $sqlPass . ' WHERE id = :id';
        return $this->db->prepare($sql)->execute($campos);
    }

    /**
     * Activa o desactiva un usuario.
     */
    public function alternarActivo(int $id): bool
    {
        return $this->db->prepare(
            'UPDATE usuarios SET activo = IF(activo = 1, 0, 1) WHERE id = :id'
        )->execute(['id' => $id]);
    }

    /**
     * Elimina un usuario.
     */
    public function eliminar(int $id): bool
    {
        return $this->db->prepare('DELETE FROM usuarios WHERE id = :id')->execute(['id' => $id]);
    }

    /**
     * Cuenta cuántos administradores activos existen (para no quedarse sin ninguno).
     */
    public function contarAdmins(): int
    {
        return (int) $this->db->query(
            "SELECT COUNT(*) FROM usuarios WHERE rol = 'admin' AND activo = 1"
        )->fetchColumn();
    }

    private function rolValido(string $rol): string
    {
        return in_array($rol, self::ROLES, true) ? $rol : 'operador';
    }
}
