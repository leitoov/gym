<?php
session_start();
include 'config/conexion.php'; // Incluye la conexión a la base de datos

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
            background-color: #f1f4f9;
            color: #333;
            font-family: 'Roboto', sans-serif;
        }

        .container {
            background: #ffffff;
            border-radius: 15px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
            padding: 30px;
            margin-top: 50px;
            max-width: 1000px;
        }

        .card-container {
            display: flex;
            flex-wrap: wrap;
            gap: 1.5rem;
            margin-top: 2rem;
            justify-content: center;
        }

        .card {
            flex: 1;
            min-width: 280px;
            max-width: 300px;
            border: none;
            border-radius: 15px;
            transition: all 0.3s ease;
            background: linear-gradient(to right, #6a11cb, #2575fc);
            color: white;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.2);
        }

        .card-header {
            text-align: center;
            font-weight: bold;
            padding: 15px;
            font-size: 1.25rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.3);
        }

        .card-body {
            padding: 20px;
            text-align: center;
        }

        .card-body i {
            font-size: 3rem;
            margin-bottom: 15px;
        }

        .btn-custom {
            margin-top: 10px;
            border-radius: 50px;
            padding: 10px 20px;
            font-weight: bold;
            transition: background 0.3s ease;
        }

        .btn-primary {
            background-color: #00c9a7;
            border: none;
        }

        .btn-primary:hover {
            background-color: #00a78f;
        }

        .btn-warning {
            background-color: #f5b700;
            border: none;
        }

        .btn-warning:hover {
            background-color: #d99a00;
        }

        .btn-success {
            background-color: #4caf50;
            border: none;
        }

        .btn-success:hover {
            background-color: #388e3c;
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
                <p><i class="fas fa-users"></i></p>
                <h4 id="totalUsuarios">Cargando...</h4>
                <a href="usuarios.php" class="btn btn-primary btn-custom">Ver Usuarios <i class="fas fa-eye"></i></a>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                Usuarios con Deudas
            </div>
            <div class="card-body">
                <p><i class="fas fa-exclamation-circle"></i></p>
                <h4 id="totalDeudores">Cargando...</h4>
                <a href="deudores.php" class="btn btn-warning btn-custom">Ver Deudores <i class="fas fa-money-bill-wave"></i></a>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                Enviar Recordatorios
            </div>
            <div class="card-body">
                <p><i class="fas fa-envelope"></i></p>
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
