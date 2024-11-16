<?php
session_start();
include 'conexion.php'; // Archivo donde se realiza la conexión a la base de datos

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre_usuario = $_POST['nombre_usuario'];
    $contrasena = $_POST['contrasena'];

    // Consulta para verificar si el usuario existe
    $sql = "SELECT * FROM administradores WHERE nombre_usuario = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $nombre_usuario);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        $admin = $resultado->fetch_assoc();
        
        // Verificar la contraseña usando password_verify
        if (password_verify($contrasena, $admin['contrasena'])) {
            // Autenticación exitosa: almacenar información en la sesión
            $_SESSION['admin_id'] = $admin['id_admin'];
            $_SESSION['nombre_usuario'] = $admin['nombre_usuario'];
            header('Location: ./index.php'); // Redirigir al panel de administración
        } else {
            echo "<div class='alert alert-danger'>Contraseña incorrecta.</div>";
        }
    } else {
        echo "<div class='alert alert-danger'>Nombre de usuario no encontrado.</div>";
    }
} else {
    header('Location: login.php');
    exit();
}
?>
