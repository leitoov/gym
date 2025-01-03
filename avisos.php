<?php
session_start();
include 'config/conexion.php'; // Incluye la conexión a la base de datos

// Configurar el manejo de errores para depuración
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error_log.txt'); // Crear archivo error_log.txt para guardar errores

// Verificar si el administrador está autenticado
if (!isset($_SESSION['admin_id'])) {
    header('Location: index.html');
    exit();
}

// Verificar conexión con la base de datos
if ($conn->connect_error) {
    error_log("Error de conexión a la base de datos: " . $conn->connect_error);
    die("Error de conexión a la base de datos");
}



// Obtener la lista de usuarios con deudas y sumar el total de sus deudas
$sql_deudores = "SELECT u.id_usuario, u.nombre, u.apellido, u.telefono, SUM(d.monto) AS total_deuda
                 FROM usuarios u
                 INNER JOIN deudas d ON u.id_usuario = d.id_usuario
                 WHERE d.estado = 'pendiente'
                 GROUP BY u.id_usuario";
$deudores = $conn->query($sql_deudores);

if ($deudores === false) {
    error_log("Error al ejecutar la consulta SQL de deudores: " . $conn->error);
    echo "<p>Error al ejecutar la consulta. Ver el archivo de registro de errores para más detalles.</p>";
    exit();
}

// Mostrar notificaciones en pantalla
echo "<div class='container mt-5'>";
echo "<h2 class='text-center mb-4'>Notificaciones de Deuda por WhatsApp</h2>";
echo "<div class='notificaciones-list'>";

while ($deudor = $deudores->fetch_assoc()) {
    $id_usuario = $deudor['id_usuario'];
    $nombre = htmlspecialchars($deudor['nombre']);
    $apellido = htmlspecialchars($deudor['apellido']);
    $telefono = htmlspecialchars($deudor['telefono']);
    $monto = number_format($deudor['total_deuda'], 2);
    
    // Crear mensaje de WhatsApp
    $mensaje = "Hola, $nombre $apellido, ¿cómo estás? Este es un mensaje automático para avisarte que tenés una deuda pendiente por la cuota del gym con un total de AR$ $monto.";
    $whatsapp_url = "https://api.whatsapp.com/send/?phone=549$telefono&text=" . urlencode($mensaje) . "&type=phone_number&app_absent=0";

    // Mostrar enlace para enviar notificación por WhatsApp
    echo "<div class='notificacion card mb-3 p-3 shadow-sm'>";
    echo "<div class='d-flex justify-content-between align-items-center'>";
    echo "<div class='info'>";
    echo "<h5>Notificación para: $nombre $apellido</h5>";
    echo "<p>Monto pendiente: AR$ $monto</p>";
    echo "</div>";
    echo "<form method='post' action=''>";
    echo "<input type='hidden' name='id_usuario' value='$id_usuario'>";
    echo "<input type='hidden' name='telefono' value='$telefono'>";
    echo "<input type='hidden' name='nombre' value='$nombre'>";
    echo "<input type='hidden' name='apellido' value='$apellido'>";
    echo "<input type='hidden' name='monto' value='$monto'>";
    echo "<button type='submit' name='enviar_notificacion' class='btn btn-success'><i class='fas fa-whatsapp'></i> Enviar Notificación por WhatsApp</button>";
    echo "</form>";
    echo "</div>";
    echo "</div>";
}

echo "</div>"; // Cerrar notificaciones-list

// Botón para volver al panel
echo "<div class='text-center mt-4'>";
echo "<a href='index.php' class='btn btn-secondary'><i class='fas fa-arrow-left'></i> Volver al Panel</a>";
echo "</div>";

// Procesar el envío de la notificación si se presiona el botón
if (isset($_POST['enviar_notificacion'])) {
    $id_usuario = $_POST['id_usuario'];
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $telefono = $_POST['telefono'];
    $monto = $_POST['monto'];

    // Guardar en la tabla historial_avisos
    $fecha_actual = date('Y-m-d H:i:s');
    $sql_historial = "INSERT INTO historial_avisos (id_usuario, fecha, accion, monto) VALUES ($id_usuario, '$fecha_actual','whatsapp', '$monto')";
    if ($conn->query($sql_historial) === false) {
        error_log("Error al guardar el aviso en el historial para el usuario $id_usuario: " . $conn->error);
        echo "<p>Error al guardar el aviso en el historial. Ver el archivo de registro de errores para más detalles.</p>";
    } else {
        // Redirigir a la URL de WhatsApp
        $mensaje = "Hola, $nombre $apellido, ¿cómo estás? Este es un mensaje automático para avisarte que tenés una deuda pendiente por la cuota del gym con un total de AR$ $monto.";
        $whatsapp_url = "https://api.whatsapp.com/send/?phone=549$telefono&text=" . urlencode($mensaje) . "&type=phone_number&app_absent=0";
        echo "<script>window.open('$whatsapp_url', '_blank');</script>";
    }
}

// Mostrar cantidad de avisos enviados por usuario
$sql_avisos = "SELECT u.id_usuario, u.nombre, u.apellido, 
                      COUNT(h.id_historial) AS total_avisos 
               FROM usuarios u
               LEFT JOIN historial_avisos h 
               ON u.id_usuario = h.id_usuario
               WHERE h.id_historial IS NOT NULL
               GROUP BY u.id_usuario";
$avisos = $conn->query($sql_avisos);




if ($avisos === false) {
    error_log("Error al obtener el historial de avisos: " . $conn->error);
    echo "<p>Error al obtener el historial de avisos. Ver el archivo de registro de errores para más detalles.</p>";
    exit();
}

// Mostrar resumen de avisos enviados
echo "<div class='resumen-avisos card mt-5 p-4 shadow-sm'>";
echo "<h3 class='text-center'>Resumen de Avisos Enviados</h3>";
echo "<ul class='list-group list-group-flush'>";
while ($aviso = $avisos->fetch_assoc()) {
    echo "<li class='list-group-item'>Usuario: " . htmlspecialchars($aviso['nombre']) . " " . htmlspecialchars($aviso['apellido']) . " - Avisos Enviados: " . $aviso['total_avisos'] . "</li>";
}
echo "</ul>";
echo "</div>";

echo "</div>"; // Cerrar container
?>

<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
<style>
    body {
        background-color: #f8f9fa;
    }

    .container {
        background: #ffffff;
        border-radius: 15px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        padding: 30px;
    }

    .notificacion {
        border: 1px solid #e0e0e0;
        border-radius: 15px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        transition: all 0.3s;
        background-color: #ffffff;
    }

    .notificacion:hover {
        transform: scale(1.02);
    }

    .resumen-avisos {
        background-color: #ffffff;
        border-radius: 15px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .list-group-item {
        background-color: transparent;
    }

    .btn-secondary {
        margin-top: 20px;
    }
</style>
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
