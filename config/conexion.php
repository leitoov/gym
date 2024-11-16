<?php
// archivo: conexion.php
// Conexión a la base de datos
$servername = "localhost";
$username = "c2620852_gym";
$password = "ravoSEku18";
$database = "c2620852_gym";

// Crear conexión
$conn = new mysqli($servername, $username, $password, $database);

// Verificar conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}
?>
