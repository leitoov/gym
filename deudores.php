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
    // Leer el tipo de deudas desde la URL
    const urlParams = new URLSearchParams(window.location.search);
    const tipo = urlParams.get('tipo') || 'todas'; // Por defecto, mostrar ambas

    $(document).ready(function() {
        cargarDeudores(tipo);
    });

    function cargarDeudores(tipo) {
        $.ajax({
            url: `api_usuarios.php?action=deudores&tipo=${tipo}`,
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    let deudores = response.deudores;
                    let deudasHtml = '';

                    if (deudores.length === 0) {
                        deudasHtml = `
                            <div class="empty-state">
                                <i class="fas fa-smile-beam"></i>
                                <h4>¡No hay deudas ${tipo === 'manuales' ? 'manuales' : tipo === 'automaticas' ? 'automáticas' : ''} en este momento!</h4>
                            </div>`;
                    } else {
                        deudores.forEach(function(deudor) {
                            deudor.deudas.forEach(function(deuda) {
                                deudasHtml += `
                                    <div class="user-card">
                                        <div class="user-info">
                                            <div class="user-details">
                                                <h5>${deudor.nombre} ${deudor.apellido}</h5>
                                                <p><strong>Teléfono:</strong> ${deudor.telefono}</p>
                                                <p><strong>Correo Electrónico:</strong> ${deudor.email}</p>
                                                <p><strong>Monto:</strong> AR$ ${deuda.monto}</p>
                                                <p><strong>Vencimiento:</strong> ${deuda.fecha_vencimiento === '--' ? 'Sin vencimiento' : deuda.fecha_vencimiento}</p>
                                            </div>
                                            <div class="user-actions">
                                                <button onclick="marcarDeudaComoPagada(${deuda.id_deuda === 'manual' ? "'manual', " + deudor.id_usuario : deuda.id_deuda})" class="btn btn-success btn-sm">
                                                    <i class="fas fa-check"></i> Marcar como Pagada
                                                </button>
                                            </div>
                                        </div>
                                    </div>`;
                            });
                        });
                    }

                    $('#deudoresContainer').html(deudasHtml);
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

    function marcarDeudaComoPagada(id_deuda, id_usuario = null) {
    const isManual = id_deuda === 'manual';

    Swal.fire({
        title: '¿Estás seguro?',
        text: isManual
            ? "Esta acción marcará la deuda manual como pagada."
            : "Esta acción marcará esta deuda automática como pagada.",
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
                data: JSON.stringify({
                    id_deuda: isManual ? null : id_deuda,
                    id_usuario: id_usuario // Siempre incluir id_usuario
                }),
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        Swal.fire('Éxito', 'La deuda ha sido marcada como pagada.', 'success').then(() => {
                            cargarDeudores(tipo); // Recargar la lista de deudores
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
