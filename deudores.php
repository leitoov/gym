<?php
session_start();
include 'conexion.php'; // Incluye la conexión a la base de datos

// Verificar si el administrador está autenticado
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuarios con Deudas - Gimnasio</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.4.26/dist/sweetalert2.min.css">
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

        .user-card {
            border: 1px solid #e0e0e0;
            border-radius: 15px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            padding: 20px;
            transition: all 0.3s;
            background-color: #ffffff;
        }

        .user-card:hover {
            transform: scale(1.02);
        }

        .user-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .user-details {
            flex: 1;
        }

        .user-actions {
            display: flex;
            gap: 10px;
        }

        @media (max-width: 768px) {
            .user-info {
                flex-direction: column;
                text-align: center;
            }

            .user-actions {
                margin-top: 15px;
            }
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <h2 class="text-center mb-4">Usuarios con Deudas</h2>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <a href="index.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Volver al Panel</a>
    </div>
    <div id="deudoresContainer">
        <!-- Los usuarios con deudas se cargarán dinámicamente usando AJAX -->
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.4.26/dist/sweetalert2.all.min.js"></script>

<script>
    // Cargar usuarios con deudas en el contenedor
    $(document).ready(function() {
        cargarDeudores();
    });

    function cargarDeudores() {
        $.ajax({
            url: 'api_usuarios.php?action=deudores',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    let deudores = response.deudores;
                    let deudoresContainer = '';

                    deudores.forEach(function(deudor) {
                        deudoresContainer += '<div class="user-card">';
                        deudoresContainer += '<div class="user-info">';
                        deudoresContainer += '<div class="user-details">';
                        deudoresContainer += '<h5>' + deudor.nombre + ' ' + deudor.apellido + '</h5>';
                        deudoresContainer += '<p><strong>Teléfono:</strong> ' + deudor.telefono + '</p>';
                        deudoresContainer += '<p><strong>Correo Electrónico:</strong> ' + deudor.email + '</p>';
                        deudoresContainer += '<p><strong>Plan:</strong> ' + deudor.plan + '</p>';
                        deudoresContainer += '<p><strong>Fecha de Vencimiento:</strong> ' + deudor.fecha_vencimiento + '</p>';
                        deudoresContainer += '<p><strong>Deuda:</strong> AR$ ' + deudor.deuda + '</p>';
                        deudoresContainer += '</div>';
                        deudoresContainer += '<div class="user-actions">';
                        deudoresContainer += '<a href="ver_historial.php?id=' + deudor.id_usuario + '" class="btn btn-info btn-custom"><i class="fas fa-history"></i> Ver Historial</a>';
                        deudoresContainer += '<button onclick="marcarComoPagado(' + deudor.id_usuario + ')" class="btn btn-success btn-custom"><i class="fas fa-check"></i> Marcar como Pagado</button>';
                        deudoresContainer += '</div>';
                        deudoresContainer += '</div>';
                        deudoresContainer += '</div>';
                    });

                    $('#deudoresContainer').html(deudoresContainer);
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error en la solicitud AJAX:', status, error);
            }
        });
    }

    function marcarComoPagado(id_usuario) {
        Swal.fire({
            title: '¿Estás seguro?',
            text: "Esta acción marcará la deuda como pagada.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, marcar como pagado'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: 'api_usuarios.php?action=marcar_pagado',
                    method: 'GET',
                    data: { id: id_usuario },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            Swal.fire('Éxito', 'La deuda ha sido marcada como pagada.', 'success').then(() => {
                                cargarDeudores(); // Recargar la lista de deudores
                            });
                        } else {
                            Swal.fire('Error', response.message, 'error');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error en la solicitud AJAX:', status, error);
                        Swal.fire('Error', 'Hubo un problema al marcar la deuda como pagada.', 'error');
                    }
                });
            }
        });
    }
</script>
</body>
</html>
