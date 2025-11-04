<?php
$hostname = "db";
$username = "admin";
$password = "test";
$dbname   = "database";

$conexion = mysqli_init();

// Activar SSL (ruta dentro del contenedor del servicio web)
mysqli_ssl_set($conexion, NULL, NULL, "/etc/ssl/certs/ca-cert.pem", NULL, NULL);

// Intentar conexión segura
if (!mysqli_real_connect($conexion, $hostname, $username, $password, $dbname, 3306, NULL, MYSQLI_CLIENT_SSL)) {
    die("❌ Error de conexión SSL: " . mysqli_connect_error());
}
