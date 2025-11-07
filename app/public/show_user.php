<?php

// Iniciar sesión (Necesario para el token)
require_once '../src/config.php';
// Conexión con la base de datos
require_once '../src/db_connect.php';

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}

// Se obtiene el usuario al que hacemos referencia
$userKey = isset($_GET['user']) ? trim($_GET['user']) : '';

if ($_SESSION['usuario'] !== $userKey) {
	die("No tienes permisos para acceder a este usuario.");
} else {
	$sql = "SELECT user, dni, nombre, apellidos, correo, telefono, fecha_nacimiento 
		FROM usuario 
		WHERE user = ?";
	$stmt = $conexion->prepare($sql);
	if (!$stmt) {
		die("Error al preparar la consulta: " . mysqli_error($conexion));
	}
	$stmt->bind_param("s", $_SESSION['usuario']);
	if ($stmt->execute()) {
		$stmt->store_result();
		$stmt->bind_result($user,$dni,$nombre,$apellidos,$correo,$telefono,$fecha_nacimiento);
		$stmt->fetch();
	} else {
		die("Error al ejecutar la consulta: " . mysqli_stmt_error($stmt));
	}
}

// Usuario no existente
if (!$user) {
    echo "Usuario no encontrado.";
    exit;
}

// Se cierra la conexión con la base de datos
$stmt->close();
$conexion->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Datos del Usuario</title>
    <link rel="stylesheet" href="css/show_user.css">
    <link rel="stylesheet" href="css/fontawesome-libreriaexterna.css">
</head>
<body>

<div class="bar">
    <div class="volver_button">
        <a href="items.php" title="Volver al inicio">
            <i class="fa-solid fa-house"></i>
        </a>
    </div>
    <h1>DATOS DEL USUARIO</h1>
    <a href="modify_user.php?user=<?= urlencode($_SESSION['usuario']) ?>">
        <button>Modificar</button>
    </a>
</div>

<div class="container">
    <div class="content">
        <div class="rellenar">
            <p><strong>Usuario:</strong> <?= htmlspecialchars($user) ?></p>
            <p><strong>Nombre:</strong> <?= htmlspecialchars($nombre) ?></p>
            <p><strong>Apellidos:</strong> <?= htmlspecialchars($apellidos) ?></p>
            <p><strong>Correo:</strong> <?= htmlspecialchars($correo) ?></p>
            <p><strong>DNI:</strong> <?= htmlspecialchars($dni) ?></p>
            <p><strong>Teléfono:</strong> <?= htmlspecialchars($telefono) ?></p>
            <p><strong>Fecha de nacimiento:</strong> <?= htmlspecialchars($fecha_nacimiento) ?></p>
        </div>
    </div>
</div>

</body>
</html>
