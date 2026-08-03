<?php
declare(strict_types=1);

/**
 * Configuración global de la aplicación "Gestión Voxeles".
 *
 * IMPORTANTE: Este archivo contiene credenciales reales y NO debe versionarse.
 * Está incluido en .gitignore. Copiá config.example.php a config.php y
 * completá los valores según tu entorno.
 */

// ------------------------------------------------------------
//  Credenciales de la base de datos
// ------------------------------------------------------------
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'gestion_voxeles');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// ------------------------------------------------------------
//  Aplicación
// ------------------------------------------------------------
define('APP_NAME', 'Gestión Voxeles');

/**
 * URL base de la aplicación (sin barra final).
 * Si la app corre en la raíz del dominio, dejá cadena vacía ''.
 * Si corre en un subdirectorio, por ejemplo /gestion-voxeles/public,
 * poné ese path aquí.
 */
define('BASE_URL', '');

// Símbolo de moneda usado en toda la UI
define('MONEDA', '$');

// Zona horaria
date_default_timezone_set('America/Montevideo');
