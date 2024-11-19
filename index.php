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
            background-color: #fbeffb;
            font-family: 'Roboto', sans-serif;
        }

        .container {
            background: #ffffff;
            border-radius: 15px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            padding: 40px;
            max-width: 900px;
            margin: 50px auto;
        }

        h2 {
            font-weight: bold;
            color: #333;
            font-size: 2rem;
            margin-bottom: 1rem;
        }

        .card-container {
            display: flex;
            flex-wrap: wrap;
            gap: 2rem;
            margin-top: 2rem;
            justify-content: space-between;
        }

        .card {
            flex: 1;
            min-width: 250px;
            max-width: 300px;
            box-shadow: 0 6px 12px 0 rgba(0,0,0,0.1);
            transition: 0.4s;
            border-radius: 15px;
            background-color: #ffe6f2;
            border: none;
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }

        .card:hover {
            box-shadow: 0 12px 24px 0 rgba(0,0,0,0.15);
            transform: translateY(-8px);
        }

        .card-header {
            background-color: #f8a5c2;
            color: #fff;
            padding: 15px;
            width: 100%;
            text-align: center;
            font-weight: bold;
            border-top-left-radius: 15px;
            border-top-right-radius: 15px;
            font-size: 1.2rem;
        }

        .card-body {
            padding: 20px;
            text-align: center;
        }

        .card-body i {
            font-size: 3rem;
            color: #ff6b81;
            margin-bottom: 15px;
        }

        .btn-custom {
            margin-top: 20px;
            background-color: #f3a683;
            color: #fff;
            border: none;
            transition: background-color 0.3s;
            font-weight: bold;
            border-radius: 50px;
            padding: 10px 20px;
        }

        .btn-custom:hover {
            background-color: #f19066;
        }

        .text-right p {
            color: #555;
            font-weight: bold;
        }

        .btn-danger {
            border-radius: 50px;
            padding: 10px 20px;
            font-weight: bold;
        }

        @media (max-width: 768px) {
            .card-container {
                flex-direction: column;
                align-items: center;
            }

            .card {
                width: 100%;
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
            <div class="card-header">
                Usuarios con Deudas
            </div>
            <div class="card-body">
                <i class="fas fa-exclamation-circle"></i>
                <h4 id="totalDeudores">Cargando...</h4>
                <a href="deudores.php" class="btn btn-custom">Ver Deudores <i class="fas fa-money-bill-wave"></i></a>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                Enviar Recordatorios
            </div>
            <div class="card-body">
                <i class="fas fa-envelope"></i>
                <h4>Notificar Usuarios Vencidos</h4>
                <a href="enviar_recordatorios.php" class="btn btn-custom">Enviar Recordatorios <i class="fas fa-paper-plane"></i></a>
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
            dataType: 'json',
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
