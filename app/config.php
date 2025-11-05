<?php

// No mostrar errores en pantalla
ini_set('display_errors', 0);
// Activar el registro de errores
ini_set('log_errors', 1);
// Definir el archivo de log donde se guardarán los errores
ini_set('error_log', __DIR__ . '/logErrores.log');
error_reporting(E_ALL);

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
//header("Content-Security-Policy: default-src 'self'; script-src 'self'; object-src 'none';");

