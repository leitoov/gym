<?php
// No debe haber ningún espacio o línea en blanco antes de <?php
ob_start(); // Iniciar el buffer de salida para prevenir cualquier salida accidental

session_start();
include 'config/conexion.php'; // Incluye la conexión a la base de datos

// Desactivar la salida de errores para evitar problemas de cabeceras
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', 'error_log.txt'); // Guardar errores en un archivo de log

// Verificar si el administrador está autenticado
if (!isset($_SESSION['admin_id'])) {
    header('Location: index.html');
    exit();
}

// Verificar conexión a la base de datos
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Configurar la localización en español
setlocale(LC_TIME, 'es_ES.UTF-8', 'es_ES', 'esp'); // Intentar diferentes configuraciones para garantizar compatibilidad

// Obtener ID de usuario
$id_usuario = isset($_GET['id_usuario']) ? intval($_GET['id_usuario']) : null;
if ($id_usuario === null) {
    echo "<p>Error: ID de usuario no proporcionado.</p>";
    exit();
}

// Obtener el historial de deudas
$sql = "SELECT * FROM deudas WHERE id_usuario = $id_usuario ORDER BY fecha_generacion DESC";
$result = $conn->query($sql);

if ($result === false) {
    echo "<p>Error al ejecutar la consulta: " . $conn->error . "</p>";
    exit();
}

ob_end_flush(); // Finalizar el buffer de salida antes de comenzar el HTML

// Obtener la URL de referencia para determinar de dónde se llegó
$origen = isset($_GET['origen']) ? $_GET['origen'] : null;
$referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';

$volver_a = "index.php"; // Valor por defecto
if ($origen === "usuarios") {
    $volver_a = "usuarios.php";
} elseif ($origen === "deudores") {
    $volver_a = "deudores.php";
} elseif (strpos($referer, 'usuarios.php') !== false) {
    $volver_a = "usuarios.php";
} elseif (strpos($referer, 'deudores.php') !== false) {
    $volver_a = "deudores.php";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Deudas - Gimnasio</title>
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

        .debt-card {
            border: 1px solid #e0e0e0;
            border-radius: 15px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            padding: 20px;
            transition: all 0.3s;
            background-color: #ffffff;
        }

        .debt-card:hover {
            transform: scale(1.02);
        }

        /* Colores por estado */
        .estado-pagado {
            border-left: 5px solid #28a745; /* Verde */
        }

        .estado-pendiente {
            border-left: 5px solid #ffc107; /* Amarillo */
        }

        .estado-vencida {
            border-left: 5px solid #dc3545; /* Rojo */
        }

        .estado-otro {
            border-left: 5px solid #6c757d; /* Gris */
        }

        /* Estilo para el empty state */
        .empty-state {
            text-align: center;
            padding: 40px;
            background-color: #f0f0f0;
            border-radius: 15px;
            color: #6c757d;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .empty-state i {
            font-size: 50px;
            margin-bottom: 20px;
            color: #adb5bd;
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <h2 class="text-center mb-4">Historial de Deudas</h2>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <a href="<?php echo htmlspecialchars($volver_a); ?>" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Volver</a>
    </div>
    <div id="historialContainer">
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <?php
                // Determinar la clase de color en función del estado
                $estadoClase = '';
                switch (strtolower($row['estado'])) {
                    case 'pagada':
                        $estadoClase = 'estado-pagado';
                        break;
                    case 'pendiente':
                        $estadoClase = 'estado-pendiente';
                        break;
                    case 'vencida':
                        $estadoClase = 'estado-vencida';
                        break;
                    default:
                        $estadoClase = 'estado-otro';
                        break;
                }

                // Usar DateTime para manejar la fecha de generación y obtener el mes en español
                $fecha_generacion = new DateTime($row['fecha_generacion']);
                $mes_en_espanol = strftime('%B %Y', $fecha_generacion->getTimestamp());
                ?>
                <div class="debt-card <?php echo $estadoClase; ?>">
                    <h5><strong>Mes de la Deuda:</strong> <?php echo ucfirst($mes_en_espanol); ?></h5>
                    <p><strong>Monto:</strong> AR$ <?php echo number_format($row['monto'], 2); ?></p>
                    <p><strong>Fecha de Pago:</strong> <?php echo $row['fecha_pago'] ? htmlspecialchars($row['fecha_pago']) : 'No Pagado'; ?></p>
                    <p><strong>Estado:</strong> <?php echo htmlspecialchars($row['estado']); ?></p>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-folder-open"></i>
                <h4>Sin historial de deudas</h4>
                <p>Este usuario no tiene historial de deudas registrado.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
