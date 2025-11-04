<?php
// Configuración segura de la sesión
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '',        
    'secure' => isset($_SERVER['HTTPS']),  // true si usas HTTPS
    'httponly' => true,
    'samesite' => 'Lax'
]);

// Evitar reutilización de IDs de sesión antiguos
ini_set('session.use_strict_mode', 1);

// Iniciar la sesión
session_start();
?>

