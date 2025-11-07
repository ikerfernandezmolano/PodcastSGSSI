<?php

// Iniciar sesión (Necesario para el token)
require_once '../src/config.php';
// Conexión con la base de datos
require_once '../src/db_connect.php';
//rate-limit--> máximo 10 acciones cada minuto por IP
require_once '../src/rate_limit.php';
rate_limit_or_exit('modify_user', 15, 30); 

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Función para consultas SQL, para reutilizar
function prepare_or_die($conexion, $sql, $ctx = '') {
    $stmt = mysqli_prepare($conexion, $sql);
    if (!$stmt) {
        die("Error en prepare() {$ctx}: " . mysqli_error($conexion) . " — SQL: {$sql}");
    }
    return $stmt;
}

// Se obtiene el usuario al que hacemos referencia
$userKey = isset($_GET['user']) ? trim($_GET['user']) : '';
$usuario = null;

if ($_SESSION['usuario'] !== $userKey) {
	die("No tienes permisos para acceder a este usuario.");
} else {
	$sql = "SELECT * FROM `usuario` WHERE user = ? LIMIT 1";
	$stmt = prepare_or_die($conexion, $sql, 'SELECT usuario');
	$stmt->bind_param("s", $_SESSION['usuario']);
	$stmt->execute();
	$res = mysqli_stmt_get_result($stmt);
	$usuario = mysqli_fetch_assoc($res);
	$stmt->close();
}

// Usuario no existente
if (!$usuario) {
    echo "Usuario no encontrado.";
    exit;
}

$successMsg = "";
$errorMsg = "";
$message_color = "red";

// Procesar envío del formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Error de seguridad: token CSRF inválido.");
    }
    $user_post   = $usuario['user'];
    $dni_post    = trim($_POST['dni'] ?? '');
    $nombre_post = trim($_POST['nombre'] ?? '');
    $apellidos   = trim($_POST['apellidos'] ?? '');
    $correo      = trim($_POST['correo'] ?? '');
    $telefono    = trim($_POST['telefono'] ?? '');
    $fecha_nac   = trim($_POST['fecha_nacimiento'] ?? '');
    $passwd      = $_POST['contrasena'] ?? '';
    $passwd_r    = $_POST['contrasena_repeat'] ?? '';

    // Comprobación de duplicados
    $dup_sql = "SELECT user, correo, dni, telefono 
                FROM usuario 
                WHERE (correo = ? OR dni = ? OR telefono = ?) AND user <> ?";
    $dup_stmt = prepare_or_die($conexion, $dup_sql, 'SELECT duplicados');
    $dup_stmt->bind_param("ssss", $correo, $dni_post, $telefono, $user_post);
    $dup_stmt->execute();
    $dup_res = mysqli_stmt_get_result($dup_stmt);

    if ($dup_user = mysqli_fetch_assoc($dup_res)) {
        if ($dup_user['correo'] === $correo) {
            $errorMsg = "El correo ya está registrado por otro usuario.";
        } elseif ($dup_user['dni'] === $dni_post) {
            $errorMsg = "El DNI ya está registrado por otro usuario.";
        } elseif ($dup_user['telefono'] === $telefono) {
            $errorMsg = "El teléfono ya está registrado por otro usuario.";
        }
        $dup_stmt->close();
    } else {
        $dup_stmt->close();

	// Validaciones de campos 
        if ($nombre_post === '' || $apellidos === '' || $correo === '' || 
            $dni_post === '' || $telefono === '' || $fecha_nac === '') {
            $errorMsg = "Por favor, completa todos los campos obligatorios.";
        } elseif (strlen($nombre_post) > 50 || strlen($apellidos) > 100 ||
                  strlen($correo) > 100 || strlen($dni_post) > 9) {
            $errorMsg = "Alguno de los campos excede la longitud máxima permitida.";
        } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            $errorMsg = "El correo no es válido.";
        } elseif (!preg_match('/^[0-9]{8}[A-Z]$/', strtoupper($dni_post))) {
            $errorMsg = "El DNI debe tener 8 números y una letra mayúscula.";
        } elseif (!preg_match('/^[679][0-9]{8}$/', $telefono)) {
            $errorMsg = "El teléfono debe empezar por 6, 7 o 9 y tener 9 dígitos.";
        } elseif (strtotime($fecha_nac) > time()) {
            $errorMsg = "La fecha de nacimiento no puede ser futura.";
        } elseif ($passwd !== '' && strlen($passwd) < 8) {
            $errorMsg = "La nueva contraseña debe tener al menos 8 caracteres.";
        }

        // Solo continuar si no hay error de validación
	if (!$errorMsg) {
		// Actualización (con o sin contraseña)
		if ($passwd !== '') {
		    if ($passwd !== $passwd_r) {
			$errorMsg = "Las contraseñas no coinciden.";
		    } 
		    else {
			$sql = "UPDATE `usuario` 
			        SET `nombre`=?, `apellidos`=?, `dni`=?, `correo`=?, 
			            `telefono`=?, `fecha_nacimiento`=?, `contrasena`=? 
			        WHERE `user`=?";
			$stmt = prepare_or_die($conexion, $sql, 'UPDATE con contrasena');
			
			// Hashear la contraseña (usa BYCRYPT)
			$passwd_hashed = password_hash($passwd, PASSWORD_BCRYPT);
			
			$stmt->bind_param("ssssssss", 
			    $nombre_post, $apellidos, $dni_post, $correo, 
			    $telefono, $fecha_nac, $passwd_hashed, $user_post
			);
			$stmt->execute();
			$stmt->close();
			$successMsg = "Datos actualizados (contraseña incluida).";
			$message_color = "green";
		    }
		} 
		else {
		    $sql = "UPDATE `usuario` 
			    SET `nombre`=?, `apellidos`=?, `dni`=?, `correo`=?, 
			        `telefono`=?, `fecha_nacimiento`=? 
			    WHERE `user`=?";
		    $stmt = prepare_or_die($conexion, $sql, 'UPDATE sin contrasena');
		    $stmt->bind_param("sssssss", 
			$nombre_post, $apellidos, $dni_post, $correo, 
			$telefono, $fecha_nac, $user_post
		    );
		    $stmt->execute();
		    $stmt->close();
		    $successMsg = "Datos actualizados.";
           	 $message_color = "green";
       		 }

		// Recargar datos actualizados
		$sql = "SELECT * FROM `usuario` WHERE user = ?";
		$stmt = prepare_or_die($conexion, $sql, 'SELECT recarga');
		$stmt->bind_param("s", $user_post);
		$stmt->execute();
		$res = mysqli_stmt_get_result($stmt);
		$usuario = mysqli_fetch_assoc($res);
		$stmt->close();
	}
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modificar Usuario</title>
    <link rel="stylesheet" href="css/modify_user.css">
    <link rel="stylesheet" href="css/fontawesome-libreriaexterna.css">
    <script src="js/modify_user.js" defer></script>
</head>
<body>

<div class="bar">
    <div class="volver_button">
        <a href="show_user.php?user=<?= urlencode($_SESSION['usuario']) ?>" title="Volver al inicio">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
    </div>
    <h1>MODIFICAR USUARIO</h1>
</div>

<div class="container">
    <div class="content">
        <?php if ($errorMsg): ?>
            <p style="color: red; font-weight: bold;"><?= htmlspecialchars($errorMsg) ?></p>
        <?php endif; ?>
        <?php if ($successMsg): ?>
            <p style="color: green; font-weight: bold;"><?= htmlspecialchars($successMsg) ?></p>
        <?php endif; ?>

        <div class="rellenar">
            <form id="user_modify_form" action="modify_user.php?user=<?= urlencode($_SESSION['usuario']) ?>" method="post" class="labels">
                <div class="readonly-field">
                    <label for="user_display">Usuario</label>
                    <input type="text" id="user_display" value="<?= htmlspecialchars($usuario['user']) ?>" readonly class="input-readonly">
                </div>

                <label for="nombre">Nombre</label>
                <input type="text" id="nombre" name="nombre" value="<?= htmlspecialchars($usuario['nombre']) ?>" required>

                <label for="apellidos">Apellidos</label>
                <input type="text" id="apellidos" name="apellidos" value="<?= htmlspecialchars($usuario['apellidos']) ?>" required>

                <label for="correo">Correo (usuario@servidor.extension)</label>
                <input type="email" id="correo" name="correo" value="<?= htmlspecialchars($usuario['correo']) ?>" required>

                <label for="dni">DNI (Ejemplo: 12345678Z)</label>
                <input type="text" id="dni" name="dni" value="<?= htmlspecialchars($usuario['dni']) ?>" required>

                <label for="telefono">Teléfono (6, 7 o 9 + 8 dígitos)</label>
                <input type="text" id="telefono" name="telefono" value="<?= htmlspecialchars($usuario['telefono']) ?>" required>

                <label for="fecha_nacimiento">Fecha de Nacimiento</label>
                <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" value="<?= htmlspecialchars($usuario['fecha_nacimiento']) ?>" required>

                <details>
                    <summary>Cambiar contraseña</summary>
                    <label for="contrasena">Contraseña (Mínimo 8 cáracteres) </label>
                    <input type="password" id="contrasena" name="contrasena"value="<?= htmlspecialchars($usuario['contrasena']) ?>" required>

                    <label for="contrasena_repeat">Repetir Contraseña</label>
                    <input type="password" id="contrasena_repeat" name="contrasena_repeat">
                </details>
                
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

                <button type="submit" id="user_modify_submit">Guardar cambios</button>
            </form>
        </div>
    </div>
</div>

</body>
</html>

