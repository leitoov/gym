<?php
session_start();
include 'conexion.php'; // Incluye la conexión a la base de datos

// Verificar si el administrador está autenticado
if (!isset($_SESSION['admin_id'])) {
    header('Location: index.html');
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración - Gimnasio</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        .card-container {
            display: flex;
            flex-wrap: wrap;
            gap: 1.5rem;
            margin-top: 2rem;
        }

        .card {
            flex: 1;
            min-width: 300px;
            box-shadow: 0 4px 8px 0 rgba(0,0,0,0.2);
            transition: 0.3s;
            border-radius: 10px;
        }

        .card:hover {
            box-shadow: 0 8px 16px 0 rgba(0,0,0,0.2);
        }

        .card-header {
            background-color: #007bff;
            color: #fff;
            padding: 10px;
            text-align: center;
            font-weight: bold;
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
        }

        .card-body {
            padding: 20px;
        }

        .btn-custom {
            margin: 5px;
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <h2 class="text-center">Panel de Administración del Gimnasio</h2>
    <div class="text-right mb-3">
        <p>Administrador: <?= $_SESSION['nombre_usuario'] ?></p>
        <a href="logout.php" class="btn btn-danger"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a>
    </div>

    <div class="card-container">
        <div class="card">
            <div class="card-header">
                Usuarios Registrados
            </div>
            <div class="card-body">
                <p><i class="fas fa-users fa-3x"></i></p>
                <h4 id="totalUsuarios">Cargando...</h4>
                <a href="usuarios.php" class="btn btn-primary btn-custom">Ver Usuarios <i class="fas fa-eye"></i></a>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                Usuarios con Deudas
            </div>
            <div class="card-body">
                <p><i class="fas fa-exclamation-circle fa-3x"></i></p>
                <h4 id="totalDeudores">Cargando...</h4>
                <a href="deudores.php" class="btn btn-warning btn-custom">Ver Deudores <i class="fas fa-money-bill-wave"></i></a>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                Enviar Recordatorios
            </div>
            <div class="card-body">
                <p><i class="fas fa-envelope fa-3x"></i></p>
                <h4>Notificar Usuarios Vencidos</h4>
                <a href="enviar_recordatorios.php" class="btn btn-success btn-custom">Enviar Recordatorios <i class="fas fa-paper-plane"></i></a>
            </div>
        </div>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script>
    $(document).ready(function() {
        $.ajax({
            url: 'api_usuarios.php?action=totales',
            method: 'GET',
            dataType: 'json', // Asegúrate de especificar el tipo de dato
            success: function(response) {
                if (response.status === 'success') {
                    let totalUsuarios = response.total_usuarios;
                    let totalDeudores = response.total_deudores;

                    $('#totalUsuarios').text(totalUsuarios + ' Usuarios');
                    $('#totalDeudores').text(totalDeudores + ' Deudores');
                } else {
                    console.error('Error en la respuesta de la API:', response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error en la solicitud AJAX:', status, error);
            }
        });
    });
</script>
</body>
</html>
