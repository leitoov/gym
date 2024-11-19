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

        /* Estilo para la lista de deudas */
        .debt-list {
            margin-top: 10px;
            border-radius: 10px;
            padding: 15px;
        }

        .debt-item {
            border-bottom: 1px solid #e0e0e0;
            padding: 10px 0;
        }

        .debt-item:last-child {
            border-bottom: none;
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

                    if (deudores.length === 0) {
                        // Mostrar un empty state si no hay deudores
                        deudoresContainer = `
                            <div class="empty-state">
                                <i class="fas fa-smile-beam"></i>
                                <h4>¡No hay deudores en este momento!</h4>
                                <p>Todos los usuarios están al día con sus pagos. ¡Buen trabajo!</p>
                            </div>
                        `;
                    } else {
                        // Mostrar la lista de deudores
                        deudores.forEach(function(deudor) {
                            if (deudor.deudas.length > 1) {
                                deudoresContainer += '<div class="user-card" style="background-color: #f8f9fa;">';
                                deudoresContainer += '<div class="user-info">';
                                deudoresContainer += '<div class="user-details">';
                                deudoresContainer += '<h5>' + deudor.nombre + ' ' + deudor.apellido + '</h5>';
                                deudoresContainer += '<p><strong>Teléfono:</strong> ' + deudor.telefono + '</p>';
                                deudoresContainer += '<p><strong>Correo Electrónico:</strong> ' + deudor.email + '</p>';
                                deudoresContainer += '<p><strong>Plan:</strong> ' + deudor.plan + '</p>';

                                // Mostrar las deudas del usuario con fondo gris
                                deudoresContainer += '<div class="debt-list" style="background-color: #f8f9fa;">';
                            } else {
                                deudoresContainer += '<div class="user-card">';
                                deudoresContainer += '<div class="user-info">';
                                deudoresContainer += '<div class="user-details">';
                                deudoresContainer += '<h5>' + deudor.nombre + ' ' + deudor.apellido + '</h5>';
                                deudoresContainer += '<p><strong>Teléfono:</strong> ' + deudor.telefono + '</p>';
                                deudoresContainer += '<p><strong>Correo Electrónico:</strong> ' + deudor.email + '</p>';
                                deudoresContainer += '<p><strong>Plan:</strong> ' + deudor.plan + '</p>';

                                // Mostrar las deudas del usuario con fondo blanco
                                deudoresContainer += '<div class="debt-list" style="background-color: #ffffff;">';
                            }

                            deudor.deudas.forEach(function(deuda) {
                                deudoresContainer += '<div class="debt-item">';
                                deudoresContainer += '<p><strong>Monto:</strong> AR$ ' + deuda.monto + '</p>';
                                deudoresContainer += '<p><strong>Mes de la Deuda:</strong> ' + deuda.fecha_generacion + '</p>';
                                deudoresContainer += '<button onclick="marcarDeudaComoPagada(' + deuda.id_deuda + ')" class="btn btn-success btn-sm"><i class="fas fa-check"></i> Marcar como Pagada</button>';
                                deudoresContainer += '</div>';
                            });
                            deudoresContainer += '</div>'; // Cerrar la lista de deudas

                            deudoresContainer += '</div>'; // Cerrar los detalles del usuario
                            deudoresContainer += '<div class="user-actions">';
                            deudoresContainer += '<a href="ver_historial.php?id=' + deudor.id_usuario + '" class="btn btn-info btn-custom"><i class="fas fa-history"></i> Ver Historial</a>';
                            deudoresContainer += '</div>';
                            deudoresContainer += '</div>'; // Cerrar la información del usuario
                            deudoresContainer += '</div>'; // Cerrar la tarjeta del usuario
                        });
                    }

                    $('#deudoresContainer').html(deudoresContainer);
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error en la solicitud AJAX:', status, error);
                Swal.fire('Error', 'No se pudo cargar la lista de deudores. Por favor intenta nuevamente.', 'error');
            }
        });
    }

    function marcarDeudaComoPagada(id_deuda) {
        Swal.fire({
            title: '¿Estás seguro?',
            text: "Esta acción marcará esta deuda específica como pagada.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, marcar como pagada'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: 'api_usuarios.php?action=marcar_deuda_pagada',
                    method: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify({ id_deuda: id_deuda }),
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
