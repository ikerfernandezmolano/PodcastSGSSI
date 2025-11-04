<?php
// Configuración de cookies de sesión
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '',
    'secure' => isset($_SERVER['HTTPS']),
    'httponly' => true,
    'samesite' => 'Lax'
]);
ini_set('session.use_strict_mode', 1);
session_start();

// Cabeceras HTTP de seguridad
header("X-Frame-Options: SAMEORIGIN"); // Evita clickjacking
header("X-Content-Type-Options: nosniff"); // Evita detección de tipo incorrecto
header("Referrer-Policy: strict-origin-when-cross-origin"); // Controla el envío del referrer
header("Content-Security-Policy: default-src 'self'; script-src 'self'; object-src 'none';"); // Limita scripts y recursos externos
