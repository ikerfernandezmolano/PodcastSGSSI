<?php
// Iniciar sesión (Necesario para el token)
require_once '../src/config.php';

$_SESSION = [];

// Cerrar sesión
session_destroy();

header("Location: login.php");
exit;
?>
