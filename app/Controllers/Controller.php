<?php
declare(strict_types=1);

namespace App\Controllers;

/**
 * Controlador base. Provee helpers comunes de render y redirección.
 */
abstract class Controller
{
    /**
     * Renderiza una vista dentro de un layout.
     *
     * @param string               $vista  Ruta relativa de la vista, ej: 'dashboard/index'
     * @param array<string, mixed> $datos  Variables disponibles en la vista
     * @param string               $layout Layout a usar ('main' o 'print')
     */
    protected function render(string $vista, array $datos = [], string $layout = 'main'): void
    {
        // Extrae las variables para que estén disponibles en la vista.
        extract($datos, EXTR_SKIP);

        // Capturamos el contenido de la vista en $contenido.
        ob_start();
        $rutaVista = dirname(__DIR__) . '/Views/' . $vista . '.php';
        if (!is_file($rutaVista)) {
            http_response_code(500);
            echo 'Vista no encontrada: ' . htmlspecialchars($vista);
            return;
        }
        require $rutaVista;
        $contenido = (string) ob_get_clean();

        // Incluimos el layout, que usará $contenido.
        require dirname(__DIR__) . '/Views/layouts/' . $layout . '.php';
    }

    /**
     * Redirige a una ruta interna (relativa a BASE_URL).
     */
    protected function redirigir(string $ruta): void
    {
        header('Location: ' . BASE_URL . $ruta);
        exit;
    }

    /**
     * Devuelve una respuesta JSON y termina la ejecución.
     *
     * @param array<string, mixed> $datos
     */
    protected function json(array $datos, int $codigo = 200): void
    {
        http_response_code($codigo);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($datos, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Exige que el usuario esté autenticado; si no, redirige al login.
     */
    protected function exigirAuth(): void
    {
        if (empty($_SESSION['usuario_id'])) {
            $this->redirigir('/login');
        }
    }
}
