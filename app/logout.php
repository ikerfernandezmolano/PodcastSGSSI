<?php
// Obtiene la sesión
require_once 'start.php';

$_SESSION = [];

// Cerrar sesión
session_destroy();

header("Location: login.php");
exit;
?>
