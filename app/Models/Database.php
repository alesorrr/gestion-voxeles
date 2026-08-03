<?php
declare(strict_types=1);

namespace App\Models;

use PDO;
use PDOException;
use RuntimeException;

/**
 * Conexión única (Singleton) a la base de datos mediante PDO.
 *
 * Uso:
 *   $pdo = Database::getConnection();
 */
final class Database
{
    /** @var PDO|null Instancia única de la conexión */
    private static ?PDO $instancia = null;

    /**
     * Constructor privado para impedir instanciación externa.
     */
    private function __construct()
    {
    }

    /**
     * Devuelve la conexión PDO, creándola la primera vez.
     */
    public static function getConnection(): PDO
    {
        if (self::$instancia === null) {
            $dsn = sprintf(
                'mysql:host=%s;dbname=%s;charset=%s',
                DB_HOST,
                DB_NAME,
                DB_CHARSET
            );

            $opciones = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            try {
                self::$instancia = new PDO($dsn, DB_USER, DB_PASS, $opciones);
            } catch (PDOException $e) {
                // No exponemos credenciales; sí un mensaje claro para el operador.
                throw new RuntimeException(
                    'No se pudo conectar a la base de datos: ' . $e->getMessage(),
                    (int) $e->getCode()
                );
            }
        }

        return self::$instancia;
    }

    /**
     * Evita la clonación de la instancia.
     */
    private function __clone(): void
    {
    }
}
