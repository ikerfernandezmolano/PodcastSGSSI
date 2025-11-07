<?php
// logger.php
// Captura errores, excepciones y errores fatales. Redacta claves sensibles.
ini_set('display_errors', 0);
ini_set('log_errors', 1);
// Claves a redacted si aparecen en arrays (POST, GET...)
$SENSITIVE_KEYS = ['password','passwd','contrasena','token','csrf','auth','bearer'];

/** Redacta recursivamente claves sensibles en arrays */
function redact_array($arr) {
    global $SENSITIVE_KEYS;
    if (!is_array($arr)) return $arr;
    $out = [];
    foreach ($arr as $k => $v) {
        if (in_array(strtolower($k), $SENSITIVE_KEYS, true)) {
            $out[$k] = '[REDACTED]';
        } elseif (is_array($v)) {
            $out[$k] = redact_array($v);
        } else {
            if (is_string($v) && strlen($v) > 1000) {
                $out[$k] = substr($v, 0, 1000) . '...[TRUNC]';
            } else {
                $out[$k] = $v;
            }
        }
    }
    return $out;
}
/** Escribe una entrada JSON en el log (una línea por evento) */
function app_log($level, $message, $context = []) {
    $timestamp = date('c');
    $entry = [
        'time' => $timestamp,
        'level' => strtoupper($level),
        'message' => $message,
        'context' => redact_array($context)
    ];
    // error_log usa la ruta definida en ini_set('error_log')
    error_log(json_encode($entry, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL);
}

/** Handler de errores (warnings, notices, etc.) */
function app_error_handler($errno, $errstr, $errfile, $errline) {
    // Si el error está suprimido con @, no hacer nada
    if (error_reporting() === 0) {
        return false;
    }
    app_log('error', $errstr, ['file' => $errfile, 'line' => $errline]);
    // Devolver true evita que PHP también muestre el mensaje
    return true;
}

/** Shutdown handler para errores fatales */
function app_shutdown_handler() {
    $err = error_get_last();
    if ($err && in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        app_log('fatal', $err['message'], ['file' => ($err['file'] ?? null), 'line' => ($err['line'] ?? null)]);
        if (!headers_sent()) http_response_code(500);
        echo "Ha ocurrido un error grave en el servidor.";
    }
}

// Manejador de excepciones personalizadas
function app_exception_handler($exception) {
    error_log("Excepción capturada: " . $exception->getMessage());
}

/** Registrar los handlers */
set_error_handler('app_error_handler');
set_exception_handler('app_exception_handler');
register_shutdown_function('app_shutdown_handler');
error_log("Logger cargado correctamente");
