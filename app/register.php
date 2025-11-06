<?php

// Iniciar sesión (Necesario para el token)
require_once 'config.php';
// Conexión con la base de datos
require_once 'db_connect.php';
//rate-limit--> máximo 3 registros cada 30 segundos por IP
require_once 'rate_limit.php';
rate_limit_or_exit('register', 3, 30); 

$message = "";
$message_color = "red";

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
	if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        	die("Error de seguridad: token CSRF inválido.");
    	}
        $user       = trim($_POST['user'] ?? '');
        $name       = trim($_POST['name'] ?? '');
        $surnames   = trim($_POST['surnames'] ?? '');
        $dni        = strtoupper(trim($_POST['dni'] ?? ''));
        $email      = trim($_POST['email'] ?? '');
        $tlfn       = trim($_POST['tlfn'] ?? '');
        $fNcto      = trim($_POST['fNcto'] ?? '');
        $passwd     = $_POST['passwd'] ?? '';
        $passwd_repeat = $_POST['passwd_repeat'] ?? '';
        
        $errores = [];
        
    // VALIDACIÓN: campos vacíos
    if ($user === '' || $name === '' || $surnames === '' ||
        $dni === '' || $email === '' || $tlfn === '' || $fNcto === '' || $passwd === '') {
        $errores[] = "Por favor, completa todos los campos obligatorios.";
    }

    // VALIDACIÓN: longitudes máximas
    if (strlen($user) > 30) $errores[] = "El usuario no puede tener más de 30 caracteres.";
    if (strlen($name) > 50) $errores[] = "El nombre no puede tener más de 50 caracteres.";
    if (strlen($surnames) > 100) $errores[] = "Los apellidos no pueden tener más de 100 caracteres.";
    if (strlen($email) > 100) $errores[] = "El correo no puede tener más de 100 caracteres.";
    if (strlen($dni) > 9) $errores[] = "El DNI no puede tener más de 9 caracteres.";
    
     // VALIDACIÓN: formatos
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errores[] = "El correo electrónico no es válido.";
    if (!preg_match('/^[0-9]{8}[A-Z]$/', $dni)) $errores[] = "El DNI debe tener 8 números y una letra (mayúscula).";
    if (!preg_match('/^[679][0-9]{8}$/', $tlfn)) $errores[] = "El teléfono debe empezar por 6, 7 o 9 y tener 9 dígitos.";
    if (strlen($passwd) < 8) $errores[] = "La contraseña debe tener al menos 8 caracteres.";
    if ($passwd !== $passwd_repeat) $errores[] = "Las contraseñas no coinciden.";

    // VALIDACIÓN: fecha (no permitir futuras)
    if (strtotime($fNcto) > time()) $errores[] = "La fecha de nacimiento no puede ser futura.";

    if (!empty($errores)) {
        $message = implode("<br>", $errores);   
        } else {
            // Insercción del usuario en la base de datos
            $sql = "INSERT INTO usuario 
                    (user, dni, nombre, apellidos, correo, contrasena, telefono, fecha_nacimiento)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conexion->prepare($sql);

            if (!$stmt) {
                $message = "Error al preparar la consulta: " . $conexion->error;
            } else {
                // Hashear la contraseña (usa BYCRYPT)
                $passwd_hashed = password_hash($passwd, PASSWORD_BCRYPT);
                $stmt->bind_param(
                    "ssssssss",
                    $user, $dni, $name, $surnames, $email, $passwd_hashed, $tlfn, $fNcto
                );

                if ($stmt->execute()) {
                    $message = "Usuario registrado correctamente.";
                    $message_color = "green";
                    $_POST = []; // limpiar el formulario
                } else {
                    if ($stmt->errno === 1062) {
                        $errorText = $stmt->error;
                        if (strpos($errorText, "dni") !== false) {
                            $message = "El DNI ya está registrado.";
                        } elseif (strpos($errorText, "correo") !== false) {
                            $message = "El correo electrónico ya está registrado.";
                        } elseif (strpos($errorText, "telefono") !== false) {
                            $message = "El teléfono ya está registrado.";
                        } else {
                            $message = "El nombre de usuario ya está registrado.";
                        }
                    } else {
                        $message = "Error al registrar el usuario: " . $stmt->error;
                    }
                }

                $stmt->close();
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
    <title>Registrarse</title>
    <link rel="stylesheet" href="css/register.css">
    <link rel="stylesheet" href="css/fontawesome-libreriaexterna.css">
    <script src="js/register.js" defer></script>
</head>
<body>

<div class="bar">
    <div class="volver_button">
        <a href="index.php" title="Volver al inicio">
            <i class="fa-solid fa-house"></i>
        </a>
    </div>
    <h1>REGISTRARSE</h1>
</div>

<div class="container">
    <div class="content">
        <?php if ($message !== ""): ?>
            <p style="color: <?= $message_color ?>; font-weight: bold; margin-bottom: 15px;">
                <?= htmlspecialchars($message) ?>
            </p>
        <?php endif; ?>
        <div class="rellenar">
            <form id="register_form" action="" method="post" class="labels">
                <label for="user">Usuario</label>
                <input type="text" id="user" name="user" required value="<?= htmlspecialchars($_POST['user'] ?? '') ?>">

                <label for="name">Nombre</label>
                <input type="text" id="name" name="name" required value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">

                <label for="surnames">Apellidos</label>
                <input type="text" id="surnames" name="surnames" required value="<?= htmlspecialchars($_POST['surnames'] ?? '') ?>">

                <label for="dni">DNI (Ejemplo: 12345678Z)</label>
                <input type="text" id="dni" name="dni" required value="<?= htmlspecialchars($_POST['dni'] ?? '') ?>">

                <label for="email">Correo (usuario@servidor.extension)</label>
                <input type="email" id="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">

                <label for="tlfn">Teléfono (6, 7 o 9 + 8 dígitos)</label>
                <input type="text" id="tlfn" name="tlfn" required value="<?= htmlspecialchars($_POST['tlfn'] ?? '') ?>">

                <label for="fNcto">Fecha de Nacimiento</label>
                <input type="date" id="fNcto" name="fNcto" required value="<?= htmlspecialchars($_POST['fNcto'] ?? '') ?>">

                <label for="passwd">Contraseña (mínimo 8 caracteres) </label>
                <input type="password" id="passwd" name="passwd"  required value="<?= htmlspecialchars($_POST['passwd'] ?? '') ?>">

                <label for="passwd_repeat">Repetir Contraseña</label>
                <input type="password" id="passwd_repeat" name="passwd_repeat" required>
                
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

                <button type="submit" id="register_submit">Confirmar</button>
            </form>
        </div>
    </div>
</div>

</body>
</html>

