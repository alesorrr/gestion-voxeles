<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\Usuario;

/**
 * Controlador de Usuarios del sistema. Solo accesible por administradores.
 */
final class UsuariosController extends Controller
{
    private Usuario $usuario;

    public function __construct()
    {
        $this->usuario = new Usuario();
    }

    /**
     * Lista de usuarios + formulario de alta.
     */
    public function index(): void
    {
        $this->exigirRol(['admin']);
        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);

        $this->render('usuarios/index', [
            'titulo'   => 'Usuarios',
            'usuarios' => $this->usuario->todos(),
            'flash'    => $flash,
        ]);
    }

    /**
     * Crea un usuario nuevo.
     */
    public function store(): void
    {
        $this->exigirRol(['admin']);

        $nombre   = trim((string) ($_POST['nombre'] ?? ''));
        $usuario  = trim((string) ($_POST['usuario'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        $rol      = (string) ($_POST['rol'] ?? 'operador');

        if ($nombre === '' || $usuario === '' || $password === '') {
            $this->flash('danger', 'Completá nombre, usuario y contraseña.');
            $this->redirigir('/usuarios');
            return;
        }
        if ($this->usuario->existeUsuario($usuario)) {
            $this->flash('danger', 'Ese nombre de usuario ya está en uso.');
            $this->redirigir('/usuarios');
            return;
        }

        $this->usuario->crear([
            'nombre'   => $nombre,
            'usuario'  => $usuario,
            'password' => $password,
            'rol'      => $rol,
            'activo'   => 1,
        ]);
        $this->flash('success', 'Usuario creado correctamente.');
        $this->redirigir('/usuarios');
    }

    /**
     * Actualiza un usuario existente.
     */
    public function update(int $id): void
    {
        $this->exigirRol(['admin']);

        $nombre  = trim((string) ($_POST['nombre'] ?? ''));
        $usuario = trim((string) ($_POST['usuario'] ?? ''));
        $rol     = (string) ($_POST['rol'] ?? 'operador');

        if ($nombre === '' || $usuario === '') {
            $this->flash('danger', 'El nombre y el usuario no pueden quedar vacíos.');
            $this->redirigir('/usuarios');
            return;
        }
        if ($this->usuario->existeUsuario($usuario, $id)) {
            $this->flash('danger', 'Ese nombre de usuario ya está en uso.');
            $this->redirigir('/usuarios');
            return;
        }

        $this->usuario->actualizar($id, [
            'nombre'   => $nombre,
            'usuario'  => $usuario,
            'rol'      => $rol,
            'password' => (string) ($_POST['password'] ?? ''),
        ]);
        $this->flash('success', 'Usuario actualizado.');
        $this->redirigir('/usuarios');
    }

    /**
     * Activa/desactiva un usuario.
     */
    public function toggle(int $id): void
    {
        $this->exigirRol(['admin']);

        // Evitar dejar el sistema sin administradores activos.
        $u = $this->usuario->buscar($id);
        if ($u !== null && $u['rol'] === 'admin' && (int) $u['activo'] === 1
            && $this->usuario->contarAdmins() <= 1) {
            $this->flash('danger', 'No podés desactivar al único administrador activo.');
            $this->redirigir('/usuarios');
            return;
        }

        $this->usuario->alternarActivo($id);
        $this->flash('success', 'Estado del usuario actualizado.');
        $this->redirigir('/usuarios');
    }

    /**
     * Elimina un usuario.
     */
    public function delete(int $id): void
    {
        $this->exigirRol(['admin']);

        // No permitir borrarse a sí mismo ni al último admin.
        if ($id === (int) ($_SESSION['usuario_id'] ?? 0)) {
            $this->flash('danger', 'No podés eliminar tu propio usuario.');
            $this->redirigir('/usuarios');
            return;
        }
        $u = $this->usuario->buscar($id);
        if ($u !== null && $u['rol'] === 'admin' && $this->usuario->contarAdmins() <= 1) {
            $this->flash('danger', 'No podés eliminar al único administrador.');
            $this->redirigir('/usuarios');
            return;
        }

        $this->usuario->eliminar($id);
        $this->flash('success', 'Usuario eliminado.');
        $this->redirigir('/usuarios');
    }

    /**
     * Guarda un mensaje flash en sesión.
     */
    private function flash(string $tipo, string $mensaje): void
    {
        $_SESSION['flash'] = ['tipo' => $tipo, 'mensaje' => $mensaje];
    }
}
