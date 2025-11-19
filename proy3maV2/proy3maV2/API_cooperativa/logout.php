<?php
session_start();

// Destruir todas las variables de sesión
$_SESSION = [];

// Destruir la sesión
session_destroy();

// Redirigir al inicio de sesión o página principal
header("Location: ../Front/login.html");
exit;
