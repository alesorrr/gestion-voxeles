<?php
declare(strict_types=1);

/**
 * Front Controller de "Gestión Voxeles".
 *
 * Punto de entrada único de la aplicación. Carga la configuración,
 * registra un autoloader simple, inicia la sesión y despacha la
 * petición al controlador y acción correspondientes.
 */

// ------------------------------------------------------------
//  Configuración y constantes
// ------------------------------------------------------------
// La ubicación de config/ y app/ puede variar según cómo se haya
// subido el proyecto al hosting:
//   - Estructura plana (InfinityFree): index.php está en htdocs/ (raíz),
//     junto a config/ y app/.
//   - Estructura con public/: index.php está en public/, y config/ y app/
//     están un nivel más arriba (en la raíz del proyecto).
// Para que funcione en ambos casos, buscamos la carpeta base que contenga
// config/config.php probando varias rutas candidatas.
$basesCandidatas = [
    __DIR__,               // config/ junto a index.php (estructura plana)
    dirname(__DIR__),      // config/ un nivel arriba (index.php dentro de public/)
];

$baseDir     = null;
$rutaConfig  = null;
foreach ($basesCandidatas as $base) {
    if (is_file($base . '/config/config.php')) {
        $baseDir    = $base;
        $rutaConfig = $base . '/config/config.php';
        break;
    }
}

if ($rutaConfig === null) {
    http_response_code(500);
    exit(
        'Falta el archivo config/config.php. '
        . 'Copiá config/config.example.php a config/config.php (en la MISMA carpeta '
        . 'donde está este index.php, o un nivel arriba si usás la carpeta public/) '
        . 'y completá los datos de conexión a la base de datos.'
    );
}
require $rutaConfig;

// ------------------------------------------------------------
//  Autoloader PSR-4 simple para el namespace App\
// ------------------------------------------------------------
spl_autoload_register(static function (string $clase) use ($baseDir): void {
    $prefijo = 'App\\';
    if (!str_starts_with($clase, $prefijo)) {
        return;
    }
    $relativa = substr($clase, strlen($prefijo));
    $archivo  = $baseDir . '/app/' . str_replace('\\', '/', $relativa) . '.php';
    if (is_file($archivo)) {
        require $archivo;
    }
});

// ------------------------------------------------------------
//  Sesión
// ------------------------------------------------------------
session_start();

// ------------------------------------------------------------
//  Parseo de la URL
// ------------------------------------------------------------
$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

// Quitamos el BASE_URL si está configurado.
if (BASE_URL !== '' && str_starts_with($uri, BASE_URL)) {
    $uri = substr($uri, strlen(BASE_URL));
}
$uri = '/' . trim($uri, '/');   // normalizamos
$metodo = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// Segmentos de la ruta.
$segmentos = $uri === '/' ? [] : explode('/', trim($uri, '/'));

// ------------------------------------------------------------
//  Enrutamiento
// ------------------------------------------------------------
use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\OrdenesController;
use App\Controllers\KanbanController;
use App\Controllers\ContabilidadController;
use App\Controllers\PresupuestosController;
use App\Controllers\UsuariosController;

try {
    despachar($segmentos, $metodo);
} catch (\Throwable $e) {
    http_response_code(500);
    // Mensaje amigable; el detalle solo se muestra si está activado el modo debug.
    echo '<!doctype html><html lang="es"><head><meta charset="utf-8">'
       . '<title>Error</title></head><body style="font-family:sans-serif;padding:2rem">'
       . '<h1>Ocurrió un error</h1><p>' . htmlspecialchars($e->getMessage()) . '</p>'
       . '</body></html>';
}

/**
 * Despacha la petición al controlador adecuado.
 *
 * @param array<int, string> $s Segmentos de la URL
 */
function despachar(array $s, string $metodo): void
{
    // Raíz -> Dashboard
    if ($s === []) {
        (new DashboardController())->index();
        return;
    }

    switch ($s[0]) {

        // -------- Autenticación --------
        case 'login':
            (new AuthController())->login();
            return;

        case 'logout':
            (new AuthController())->logout();
            return;

        // -------- Órdenes de trabajo --------
        case 'ordenes':
            enrutarOrdenes($s, $metodo);
            return;

        // -------- Presupuestos --------
        case 'presupuestos':
            enrutarPresupuestos($s, $metodo);
            return;

        // -------- Usuarios --------
        case 'usuarios':
            enrutarUsuarios($s, $metodo);
            return;

        // -------- Kanban --------
        case 'kanban':
            $ctrl = new KanbanController();
            if (isset($s[1]) && $s[1] === 'estado' && $metodo === 'POST') {
                $ctrl->updateEstado();
            } else {
                $ctrl->index();
            }
            return;

        // -------- Contabilidad --------
        case 'contabilidad':
            $ctrl = new ContabilidadController();
            if (isset($s[1]) && $s[1] === 'gastos') {
                if (isset($s[2]) && $s[2] === 'eliminar' && isset($s[3])) {
                    $ctrl->deleteGasto((int) $s[3]);
                } elseif ($metodo === 'POST') {
                    $ctrl->storeGasto();
                } else {
                    $ctrl->index();
                }
            } else {
                $ctrl->index();
            }
            return;

        default:
            http_response_code(404);
            echo '<h1>404 - Página no encontrada</h1>';
            return;
    }
}

/**
 * Sub-enrutador para el módulo de órdenes.
 *
 * Rutas soportadas:
 *   GET  /ordenes                 -> index
 *   GET  /ordenes/nueva           -> create
 *   POST /ordenes                 -> store
 *   GET  /ordenes/{id}/editar     -> edit
 *   POST /ordenes/{id}            -> update
 *   GET  /ordenes/{id}/eliminar   -> delete
 *   GET  /ordenes/{id}/mop        -> mop
 *
 * @param array<int, string> $s
 */
function enrutarOrdenes(array $s, string $metodo): void
{
    $ctrl = new OrdenesController();

    // /ordenes
    if (!isset($s[1])) {
        if ($metodo === 'POST') {
            $ctrl->store();
        } else {
            $ctrl->index();
        }
        return;
    }

    // /ordenes/nueva
    if ($s[1] === 'nueva') {
        $ctrl->create();
        return;
    }

    // /ordenes/{id}/...
    $id = (int) $s[1];
    if ($id <= 0) {
        http_response_code(404);
        echo '<h1>404 - Orden no encontrada</h1>';
        return;
    }

    $accion = $s[2] ?? '';
    switch ($accion) {
        case 'editar':
            $ctrl->edit($id);
            return;
        case 'eliminar':
            $ctrl->delete($id);
            return;
        case 'mop':
            $ctrl->mop($id);
            return;
        case '':
            // POST /ordenes/{id} -> update
            if ($metodo === 'POST') {
                $ctrl->update($id);
            } else {
                $ctrl->mop($id);
            }
            return;
        default:
            http_response_code(404);
            echo '<h1>404 - Acción no encontrada</h1>';
    }
}

/**
 * Sub-enrutador para el módulo de presupuestos.
 *
 * Rutas soportadas:
 *   GET  /presupuestos                 -> index
 *   POST /presupuestos                 -> store
 *   GET  /presupuestos/nuevo           -> create
 *   GET  /presupuestos/{id}/editar     -> edit
 *   POST /presupuestos/{id}            -> update
 *   GET  /presupuestos/{id}/eliminar   -> delete
 *   GET  /presupuestos/{id}/convertir  -> convertir
 *
 * @param array<int, string> $s
 */
function enrutarPresupuestos(array $s, string $metodo): void
{
    $ctrl = new PresupuestosController();

    // /presupuestos
    if (!isset($s[1])) {
        if ($metodo === 'POST') {
            $ctrl->store();
        } else {
            $ctrl->index();
        }
        return;
    }

    // /presupuestos/nuevo
    if ($s[1] === 'nuevo') {
        $ctrl->create();
        return;
    }

    // /presupuestos/{id}/...
    $id = (int) $s[1];
    if ($id <= 0) {
        http_response_code(404);
        echo '<h1>404 - Presupuesto no encontrado</h1>';
        return;
    }

    $accion = $s[2] ?? '';
    switch ($accion) {
        case 'editar':
            $ctrl->edit($id);
            return;
        case 'eliminar':
            $ctrl->delete($id);
            return;
        case 'convertir':
            $ctrl->convertir($id);
            return;
        case '':
            if ($metodo === 'POST') {
                $ctrl->update($id);
            } else {
                $ctrl->edit($id);
            }
            return;
        default:
            http_response_code(404);
            echo '<h1>404 - Acción no encontrada</h1>';
    }
}

/**
 * Sub-enrutador para el módulo de usuarios (solo admin).
 *
 * Rutas soportadas:
 *   GET  /usuarios                   -> index (lista + formulario)
 *   POST /usuarios                   -> store
 *   POST /usuarios/{id}              -> update
 *   GET  /usuarios/{id}/eliminar     -> delete
 *   GET  /usuarios/{id}/estado       -> toggle activo
 *
 * @param array<int, string> $s
 */
function enrutarUsuarios(array $s, string $metodo): void
{
    $ctrl = new UsuariosController();

    // /usuarios
    if (!isset($s[1])) {
        if ($metodo === 'POST') {
            $ctrl->store();
        } else {
            $ctrl->index();
        }
        return;
    }

    // /usuarios/{id}/...
    $id = (int) $s[1];
    if ($id <= 0) {
        http_response_code(404);
        echo '<h1>404 - Usuario no encontrado</h1>';
        return;
    }

    $accion = $s[2] ?? '';
    switch ($accion) {
        case 'eliminar':
            $ctrl->delete($id);
            return;
        case 'estado':
            $ctrl->toggle($id);
            return;
        case '':
            if ($metodo === 'POST') {
                $ctrl->update($id);
            } else {
                $ctrl->index();
            }
            return;
        default:
            http_response_code(404);
            echo '<h1>404 - Acción no encontrada</h1>';
    }
}
