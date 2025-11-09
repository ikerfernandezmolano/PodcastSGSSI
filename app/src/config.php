<?php

$baseDir = dirname(__DIR__); 
$logDir = $baseDir . '/private/logs';
$logPath = $logDir . '/errores.log';

// Activar logs de PHP
ini_set('display_errors', 1);
ini_set('display_startup_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', $logPath);
error_reporting(E_ALL);

// Crear carpeta si no existe
if (!is_dir($logDir)) {
    	@mkdir($logDir, 0777, true);
}

// Intentar escribir en la carpeta
$test = @file_put_contents($logPath, "=== INICIO LOG ===\n", FILE_APPEND);

// Si falla, usar /tmp automáticamente
if ($test === false) {
    	$fallbackDir = sys_get_temp_dir();
    	$logPath = $fallbackDir . '/errores_' . basename($baseDir) . '.log';
    	@file_put_contents($logPath, "=== LOG TEMPORAL ===\n", FILE_APPEND);
}

error_log("Logger inicializado en: " . $logPath);

// Incluir el manejador personalizado de logs
require_once __DIR__ . '/logger.php';

// Configurar cookies de sesión de forma compatible
if (PHP_VERSION_ID >= 70300) {
    // PHP 7.3 o superior
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => isset($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
} else {
    // Compatibilidad con versiones anteriores
    session_set_cookie_params(0, '/; samesite=Lax', '', isset($_SERVER['HTTPS']), true);
}

// Modo estricto de sesión
ini_set('session.use_strict_mode', 1);

// Iniciar sesión
session_start();

// Cabeceras HTTP de seguridad
header_remove("X-Powered-By");
header("X-Frame-Options: SAMEORIGIN");
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: strict-origin-when-cross-origin");
header("Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self'; object-src 'none'; img-src 'self' data:; font-src 'self'; connect-src 'self'; frame-ancestors 'self'; form-action 'self';");



