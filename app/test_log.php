<?php
require_once 'config.php';

// Genera una advertencia
trigger_error("Advertencia de prueba desde test_log.php", E_USER_WARNING);

// Genera una excepción no capturada
throw new Exception("Excepción de prueba desde test_log.php");
