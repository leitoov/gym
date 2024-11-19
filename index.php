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
            background-color: #fdf2f8;
            color: #5a5a5a;
        }

        .container {
            background: #ffffff;
            border-radius: 15px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 30px;
            margin-top: 30px;
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
            min-width: 300px;
            max-width: 350px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s;
            border-radius: 15px;
            border: none;
        }

        .card:hover {
            transform: translateY(-5px);
        }

        .card-header {
            background-color: #ffccd5;
            color: #5a5a5a;
            padding: 15px;
            text-align: center;
            font-weight: bold;
            border-top-left-radius: 15px;
            border-top-right-radius: 15px;
        }

        .card-body {
            padding: 25px;
            background-color: #ffe4e6;
        }

        .btn-custom {
            margin: 5px;
            background-color: #fbb1bd;
            border: none;
            color: white;
            font-weight: bold;
        }

        .btn-custom:hover {
            background-color: #f9869b;
        }

        .text-right p {
            color: #7f8c8d;
            font-size: 1rem;
        }

        .btn-danger {
            background-color: #ff6b6b;
            border: none;
        }

        .btn-danger:hover {
            background-color: #e74c3c;
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
            <div class="card-body text-center">
                <p><i class="fas fa-users fa-3x" style="color: #ffa69e;"></i></p>
                <h4 id="totalUsuarios">Cargando...</h4>
                <a href="usuarios.php" class="btn btn-custom">Ver Usuarios <i class="fas fa-eye"></i></a>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                Usuarios con Deudas
            </div>
            <div class="card-body text-center">
                <p><i class="fas fa-exclamation-circle fa-3x" style="color: #ffc09f;"></i></p>
                <h4 id="totalDeudores">Cargando...</h4>
                <a href="deudores.php" class="btn btn-custom">Ver Deudores <i class="fas fa-money-bill-wave"></i></a>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                Enviar Recordatorios
            </div>
            <div class="card-body text-center">
                <p><i class="fas fa-envelope fa-3x" style="color: #ffabab;"></i></p>
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
