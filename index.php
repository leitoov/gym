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
        body {
            background-color: #f0f4f8;
            color: #333;
            font-family: 'Arial', sans-serif;
        }

        .container {
            padding-top: 2rem;
        }

        .card-container {
            display: flex;
            flex-wrap: wrap;
            gap: 2rem;
            margin-top: 2rem;
            justify-content: center;
        }

        .card {
            flex: 1;
            min-width: 280px;
            max-width: 400px;
            border: none;
            border-radius: 20px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 25px rgba(0, 0, 0, 0.1);
        }

        .card-header {
            background-color: #007bff;
            color: #fff;
            padding: 15px;
            text-align: center;
            font-weight: bold;
            border-top-left-radius: 20px;
            border-top-right-radius: 20px;
        }

        .card-body {
            padding: 25px;
            text-align: center;
            background-color: #ffffff;
            border-bottom-left-radius: 20px;
            border-bottom-right-radius: 20px;
        }

        .card-body i {
            font-size: 3rem;
            color: #007bff;
            margin-bottom: 10px;
        }

        .btn-custom {
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        .btn-custom:hover {
            background-color: #0056b3;
            color: #fff;
        }

        .text-right p {
            margin: 0;
            color: #555;
        }

        @media (max-width: 768px) {
            .card-container {
                flex-direction: column;
                align-items: center;
            }
        }
    </style>
</head>
<body>
<div class="container">
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
                <i class="fas fa-users"></i>
                <h4 id="totalUsuarios">Cargando...</h4>
                <a href="usuarios.php" class="btn btn-custom">Ver Usuarios <i class="fas fa-eye"></i></a>
            </div>
        </div>

        <div class="card">
            <div class="card-header" style="background-color: #ffc107; color: #fff;">
                Usuarios con Deudas
            </div>
            <div class="card-body">
                <i class="fas fa-exclamation-circle"></i>
                <h4 id="totalDeudores">Cargando...</h4>
                <a href="deudores.php" class="btn btn-custom" style="background-color: #ffc107;">Ver Deudores <i class="fas fa-money-bill-wave"></i></a>
            </div>
        </div>

        <div class="card">
            <div class="card-header" style="background-color: #28a745; color: #fff;">
                Enviar Recordatorios
            </div>
            <div class="card-body">
                <i class="fas fa-envelope"></i>
                <h4>Notificar Usuarios Vencidos</h4>
                <a href="enviar_recordatorios.php" class="btn btn-custom" style="background-color: #28a745;">Enviar Recordatorios <i class="fas fa-paper-plane"></i></a>
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
