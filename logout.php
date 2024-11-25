<?php
session_start(); // Iniciar la sesión

// Destruir todas las variables de sesión
$_SESSION = array();

// Destruir la sesión
session_destroy();

// Redireccionar al usuario a la página de inicio (index.html)
header("Location: index.html");
exit();
?>
