<?php
/**
 * Script de diagnóstico TEMPORAL para Gestión Voxeles.
 *
 * Subilo a la MISMA carpeta donde está index.php (en InfinityFree: htdocs/)
 * y abrilo en el navegador:  https://TU-DOMINIO/diagnostico.php
 *
 * Te dice si encuentra config/config.php y qué hay en las carpetas.
 * ⚠️ BORRALO del hosting cuando termines de diagnosticar (por seguridad).
 */
header('Content-Type: text/html; charset=utf-8');

function fila(string $etiqueta, string $valor, ?bool $ok = null): void
{
    $color = $ok === null ? '#334' : ($ok ? '#0a7d28' : '#b00020');
    $icono = $ok === null ? '' : ($ok ? '✔ ' : '✗ ');
    echo '<tr><td style="padding:6px 12px;font-weight:600;vertical-align:top">'
        . htmlspecialchars($etiqueta) . '</td>'
        . '<td style="padding:6px 12px;color:' . $color . ';font-family:monospace">'
        . $icono . htmlspecialchars($valor) . '</td></tr>';
}

function listar(string $dir): string
{
    if (!is_dir($dir)) {
        return '(no es una carpeta o no existe)';
    }
    $items = @scandir($dir);
    if ($items === false) {
        return '(no se pudo leer la carpeta)';
    }
    $items = array_values(array_filter($items, fn ($i) => $i !== '.' && $i !== '..'));
    return $items ? implode('   ', $items) : '(carpeta vacía)';
}

$dir       = __DIR__;
$padre     = dirname(__DIR__);
$candidatos = [
    $dir . '/config/config.php'   => 'Junto a index.php (estructura plana / InfinityFree)',
    $padre . '/config/config.php' => 'Un nivel arriba (si index.php está dentro de public/)',
];
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Diagnóstico · Gestión Voxeles</title>
</head>
<body style="font-family:system-ui,sans-serif;max-width:900px;margin:2rem auto;padding:0 1rem;color:#222">
<h1 style="color:#1b4f9c">🔎 Diagnóstico de Gestión Voxeles</h1>
<p>Este script ayuda a encontrar por qué aparece <em>"Falta el archivo config/config.php"</em>.</p>

<h2 style="color:#1b4f9c;border-bottom:2px solid #eef2f8;padding-bottom:.3rem">1. Rutas</h2>
<table style="border-collapse:collapse;width:100%">
<?php
fila('Carpeta de este archivo (__DIR__)', $dir);
fila('Carpeta un nivel arriba', $padre);
fila('Versión de PHP', PHP_VERSION, version_compare(PHP_VERSION, '8.0.0', '>='));
?>
</table>

<h2 style="color:#1b4f9c;border-bottom:2px solid #eef2f8;padding-bottom:.3rem">2. ¿Existe config/config.php?</h2>
<table style="border-collapse:collapse;width:100%">
<?php
$encontrado = false;
foreach ($candidatos as $ruta => $desc) {
    $existe = is_file($ruta);
    $encontrado = $encontrado || $existe;
    fila($desc, $ruta . ($existe ? '  →  ENCONTRADO' : '  →  no está'), $existe);
}
?>
</table>
<p style="padding:.8rem 1rem;border-radius:8px;background:<?= $encontrado ? '#e6f4ea' : '#fce8e6' ?>">
<?php if ($encontrado): ?>
✔ <strong>config.php fue encontrado.</strong> Si la app ya funciona, borrá este archivo.
Si aún ves un error, probablemente sea de <strong>conexión a la base de datos</strong>
(revisá DB_HOST, DB_NAME, DB_USER, DB_PASS en config.php).
<?php else: ?>
✗ <strong>No se encontró config.php en ninguna ubicación.</strong>
Mirá abajo el contenido de las carpetas para ver dónde quedó realmente el archivo
y con qué nombre exacto (ojo con mayúsculas o extensiones ocultas como <code>.txt</code>).
<?php endif; ?>
</p>

<h2 style="color:#1b4f9c;border-bottom:2px solid #eef2f8;padding-bottom:.3rem">3. Contenido de las carpetas</h2>
<table style="border-collapse:collapse;width:100%">
<?php
fila('Archivos en la carpeta de index.php', listar($dir));
fila('¿Existe carpeta config/ junto a index.php?', is_dir($dir . '/config') ? 'sí' : 'no', is_dir($dir . '/config'));
fila('Contenido de config/ (junto a index.php)', listar($dir . '/config'));
fila('¿Existe carpeta app/ junto a index.php?', is_dir($dir . '/app') ? 'sí' : 'no', is_dir($dir . '/app'));
fila('Archivos un nivel arriba', listar($padre));
fila('Contenido de config/ (un nivel arriba)', listar($padre . '/config'));
?>
</table>

<p style="margin-top:2rem;padding:.8rem 1rem;border-radius:8px;background:#fff3cd;border:1px solid #ffe69c">
⚠️ <strong>Importante:</strong> borrá <code>diagnostico.php</code> del hosting cuando termines,
para no exponer información de tu servidor.
</p>
</body>
</html>
