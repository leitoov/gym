<?php
session_start();
include './config/conexion.php'; // Incluye la conexión a la base de datos

/* Verificar si el administrador está autenticado
if (!isset($_SESSION['admin_id'])) {
    header('Location: ./login.php');
    exit();
}*/

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cambiar_contrasena'])) {
    $admin_id = $_SESSION['admin_id'];
    $contrasena_actual = $_POST['contrasena_actual'];
    $nueva_contrasena = $_POST['nueva_contrasena'];
    $confirmar_contrasena = $_POST['confirmar_contrasena'];

    // Verificar si la nueva contraseña y la confirmación coinciden
    if ($nueva_contrasena !== $confirmar_contrasena) {
        $error = "La nueva contraseña y la confirmación no coinciden.";
    } else {
        // Obtener la contraseña actual del administrador desde la base de datos
        $sql = "SELECT contrasena FROM administradores WHERE id_admin = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $admin_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $admin = $result->fetch_assoc();

        if (password_verify($contrasena_actual, $admin['contrasena'])) {
            // Actualizar la contraseña con la nueva contraseña hasheada
            $nueva_contrasena_hash = password_hash($nueva_contrasena, PASSWORD_BCRYPT);
            $sql = "UPDATE administradores SET contrasena = ? WHERE id_admin = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('si', $nueva_contrasena_hash, $admin_id);

            if ($stmt->execute()) {
                $mensaje = "Contraseña actualizada exitosamente.";
            } else {
                $error = "Error al actualizar la contraseña.";
            }
        } else {
            $error = "La contraseña actual es incorrecta.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cambiar Contraseña - Gimnasio</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <h2>Cambiar Contraseña</h2>
    <?php if (isset($error)): ?>
        <div class="alert alert-danger">
            <?= $error ?>
        </div>
    <?php endif; ?>
    <?php if (isset($mensaje)): ?>
        <div class="alert alert-success">
            <?= $mensaje ?>
        </div>
    <?php endif; ?>
    <form method="post">
        <div class="form-group">
            <label for="contrasena_actual">Contraseña Actual:</label>
            <input type="password" class="form-control" id="contrasena_actual" name="contrasena_actual" required>
        </div>
        <div class="form-group">
            <label for="nueva_contrasena">Nueva Contraseña:</label>
            <input type="password" class="form-control" id="nueva_contrasena" name="nueva_contrasena" required>
        </div>
        <div class="form-group">
            <label for="confirmar_contrasena">Confirmar Nueva Contraseña:</label>
            <input type="password" class="form-control" id="confirmar_contrasena" name="confirmar_contrasena" required>
        </div>
        <button type="submit" name="cambiar_contrasena" class="btn btn-primary">Cambiar Contraseña</button>
    </form>
    <a href="index.php" class="btn btn-secondary mt-3">Volver al Panel</a>
</div>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
