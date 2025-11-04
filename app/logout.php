<?php
// Obtiene la sesión
require_once 'config.php';

$_SESSION = [];

// Cerrar sesión
session_destroy();

header("Location: login.php");
exit;
?>
