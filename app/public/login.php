<?php
// Iniciar sesión (Necesario para el token)
require_once '../src/config.php';

// Conexión con la base de datos
require_once '../src/db_connect.php';
//rate-limit--> máximo 10 acciones cada minuto por IP
require_once '../src/rate_limit.php';
rate_limit_or_exit('login', 5, 30); 


$message = "";
$message_color = "red";

// Crear token CSRF si no existe
if (!isset($_SESSION['csrf_token'])) {
	$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Inicializar intentos y bloqueo
if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 3;
}
if (!isset($_SESSION['lockout_time'])) {
    $_SESSION['lockout_time'] = 0;
}

$current_time=time();

// Si está bloqueado, comprobamos si ya pasaron 3 minutos
if ($_SESSION['lockout_time'] > 0) {
    $elapsed = $current_time - $_SESSION['lockout_time'];
    if ($elapsed < 180) {  // Bloqueo de 180s = 3 minutos
        $remaining = 180 - $elapsed;
        $message = "Demasiados intentos fallidos. Espera " . ceil($remaining / 60) . " minuto(s) para volver a intentarlo.";
    } else {
        // Bloqueo expirado → reset
        $_SESSION['login_attempts'] = 3;
        $_SESSION['lockout_time'] = 0;
    }
}

//Si no está bloqueado
if ($_SESSION['lockout_time'] === 0) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $email = $_POST['email'] ?? '';
        $passwd = $_POST['passwd'] ?? '';

	// Preparar consulta para buscar usuario
        $stmt = $conexion->prepare("SELECT contrasena, user FROM usuario WHERE correo = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();


        if ($stmt->num_rows === 0) {
            $message = "Correo no registrado.";
            $_SESSION['login_attempts']--;
        } else {
            $stmt->bind_result($db_pass, $user);
            $stmt->fetch();
            if (password_verify($passwd, $db_pass)) {
                session_regenerate_id(true);
                $_SESSION['usuario'] = $user;
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                $_SESSION['login_attempts'] = 3;
                $_SESSION['lockout_time'] = 0;
                $message = "Login correcto. Redirigiendo...";
                $message_color = "green";
                header("Location: items.php");
                exit;
            } else {
                $message = "Contraseña incorrecta.";
                $_SESSION['login_attempts']--;
            }
        }
        $stmt->close();
		
		// Si llega a 3 intentos, bloquear
  		if ($_SESSION['login_attempts'] <= 0) {
        	$_SESSION['lockout_time'] = time();
        	$message = "Has superado el número máximo de intentos. Espera 3 minutos antes de volver a intentarlo.";
        }
    }
}

// Se cierra la conexión con la base de datos
$conexion->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión</title>
    <link rel="stylesheet" href="css/login.css">
    <link rel="stylesheet" href="css/fontawesome-libreriaexterna.css">
    <script src="js/index.js" defer></script>
    <script src="js/login.js" defer></script>
</head>
<body>

<div class="bar">
    <div class="volver_button">
        <a href="index.php" title="Volver al inicio">
            <i class="fa-solid fa-house"></i>
        </a>
    </div>
    <h1>INICIAR SESIÓN</h1>
</div>

<div class="container">
    <div class="content">

        <?php if ($message !== ""): ?>
            <p style="color: <?= $message_color ?>; font-weight:bold; margin-bottom:15px;">
                <?= htmlspecialchars($message) ?>
            </p>
        <?php endif; ?>

        <div class="rellenar">
            <form id="login_form" action="" method="post" class="labels">
                <label for="email">Correo (usuario@servidor.extension)</label>
                <input type="text" id="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">

                <label for="passwd">Contraseña</label>
                <input type="password" id="passwd" name="passwd" required>
                
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

                <button type="submit" id="login_submit">Confirmar</button>
            </form>
        </div>
    </div>
</div>

</body>
</html>

