<?php

$logPath = __DIR__ . '/../private/logs/errores.log';

$ENV = getenv('APP_ENV') ?: 'development';

// Mostrar o no los errores en pantalla según entorno
if ($ENV === 'development') {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(E_ALL);
}

// Definir el archivo de log 
ini_set('log_errors', 1);
ini_set('error_log', $logPath);


// Crear el archivo si no existe
if (!file_exists($logPath)) {
    @file_put_contents($logPath, "=== INICIO DEL LOG ===\n");
    @chmod($logPath, 0666);
}

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
header("X-Frame-Options: SAMEORIGIN");
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: strict-origin-when-cross-origin");
header("Content-Security-Policy: default-src 'self'; script-src 'self'; object-src 'none';");

