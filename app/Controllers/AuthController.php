<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\Database;

/**
 * Controlador de autenticación: login y logout.
 */
final class AuthController extends Controller
{
    /**
     * Muestra el formulario de login (GET) o procesa el login (POST).
     */
    public function login(): void
    {
        // Si ya está logueado, al dashboard.
        if (!empty($_SESSION['usuario_id'])) {
            $this->redirigir('/');
        }

        $error = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $usuario  = trim((string) ($_POST['usuario'] ?? ''));
            $password = (string) ($_POST['password'] ?? '');

            if ($usuario === '' || $password === '') {
                $error = 'Ingresá usuario y contraseña.';
            } else {
                $db = Database::getConnection();
                $stmt = $db->prepare('SELECT * FROM usuarios WHERE usuario = :u AND activo = 1');
                $stmt->execute(['u' => $usuario]);
                $fila = $stmt->fetch();

                if ($fila !== false && password_verify($password, (string) $fila['password_hash'])) {
                    // Regeneramos el ID de sesión para evitar fijación.
                    session_regenerate_id(true);
                    $_SESSION['usuario_id']     = (int) $fila['id'];
                    $_SESSION['usuario_nombre'] = (string) $fila['nombre'];
                    $_SESSION['usuario_rol']    = (string) $fila['rol'];
                    $this->redirigir('/');
                } else {
                    $error = 'Usuario o contraseña incorrectos.';
                }
            }
        }

        $this->render('auth/login', [
            'titulo' => 'Iniciar sesión',
            'error'  => $error,
        ], 'blank');
    }

    /**
     * Cierra la sesión.
     */
    public function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
        $this->redirigir('/login');
    }
}
