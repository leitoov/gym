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
    <title>Usuarios Registrados - Gimnasio</title>
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

        .user-photo {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 50%;
            margin-right: 20px;
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

        .total-deuda-container {
            text-align: center;
            margin-bottom: 20px;
            padding: 15px;
            border: 1px solid #e0e0e0;
            border-radius: 15px;
            background-color: #f8f9fa;
        }

        .total-deuda-container h4 {
            margin: 0;
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <h2 class="text-center mb-4">Usuarios Registrados</h2>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <a href="index.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Volver al Panel</a>
        <a href="#" class="btn btn-success" data-toggle="modal" data-target="#añadirUsuarioModal"><i class="fas fa-user-plus"></i> Añadir Usuario</a>
    </div>
    
    <!-- Contenedor de la deuda total -->
    <div id="deudaTotalContainer" class="total-deuda-container">
        <h4>Total Deuda Pendiente: AR$ <span id="deudaTotal">0.00</span></h4>
    </div>

    <div id="usuariosContainer">
        <!-- Los usuarios se cargarán dinámicamente usando AJAX -->
    </div>
</div>

<!-- Modal para añadir usuario -->
<div class="modal fade" id="añadirUsuarioModal" tabindex="-1" role="dialog" aria-labelledby="añadirUsuarioLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="añadirUsuarioLabel">Añadir Usuario</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="añadirUsuarioForm" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="añadirNombre">Nombre</label>
                        <input type="text" class="form-control" id="añadirNombre" name="nombre" required>
                    </div>
                    <div class="form-group">
                        <label for="añadirApellido">Apellido</label>
                        <input type="text" class="form-control" id="añadirApellido" name="apellido" required>
                    </div>
                    <div class="form-group">
                        <label for="añadirTelefono">Teléfono</label>
                        <input type="text" class="form-control" id="añadirTelefono" name="telefono" required>
                    </div>
                    <div class="form-group">
                        <label for="añadirEmail">Correo Electrónico</label>
                        <input type="email" class="form-control" id="añadirEmail" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="añadirPlan">Plan</label>
                        <select class="form-control" id="añadirPlan" name="plan" required>
                            <option value="Básico">Básico</option>
                            <option value="Premium">Premium</option>
                            <option value="VIP">VIP</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="añadirFechaVencimiento">Fecha de Vencimiento</label>
                        <input type="date" class="form-control" id="añadirFechaVencimiento" name="fecha_vencimiento" required>
                    </div>
                    <div class="form-group">
                        <label for="añadirDeuda">Deuda (AR$)</label>
                        <input type="number" class="form-control" id="añadirDeuda" name="deuda" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label for="añadirFoto">Foto de Perfil</label>
                        <input type="file" class="form-control-file" id="añadirFoto" name="foto" accept="image/*">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="guardarNuevoUsuario">Guardar Usuario</button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.4.26/dist/sweetalert2.all.min.js"></script>

<script>
    // Cargar usuarios y deuda total en el contenedor
    $(document).ready(function() {
        cargarUsuarios();
        cargarDeudaTotal();
    });

    function cargarUsuarios() {
        $.ajax({
            url: 'api_usuarios.php?action=usuarios',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    let usuarios = response.usuarios;
                    let usuariosContainer = '';

                    usuarios.forEach(function(usuario) {
                        usuariosContainer += '<div class="user-card">';
                        usuariosContainer += '<div class="user-info">';
                        usuariosContainer += '<img src="' + (usuario.foto ? usuario.foto : 'default-avatar.png') + '" alt="Foto de Perfil" class="user-photo">';
                        usuariosContainer += '<div class="user-details">';
                        usuariosContainer += '<h5>' + usuario.nombre + ' ' + usuario.apellido + '</h5>';
                        usuariosContainer += '<p><strong>Teléfono:</strong> ' + usuario.telefono + '</p>';
                        usuariosContainer += '<p><strong>Correo Electrónico:</strong> ' + usuario.email + '</p>';
                        usuariosContainer += '<p><strong>Plan:</strong> ' + usuario.plan + '</p>';
                        usuariosContainer += '<p><strong>Fecha de Vencimiento:</strong> ' + usuario.fecha_vencimiento + '</p>';
                        usuariosContainer += '<p><strong>Deuda:</strong> AR$ ' + usuario.deuda + '</p>';
                        usuariosContainer += '</div>';
                        usuariosContainer += '<div class="user-actions">';
                        usuariosContainer += '<button onclick="abrirModalEdicion(' + usuario.id_usuario + ')" class="btn btn-warning btn-custom"><i class="fas fa-edit"></i> Editar</button>';
                        usuariosContainer += '<a href="historial.php?id_usuario=' + usuario.id_usuario + '" class="btn btn-info btn-custom"><i class="fas fa-history"></i> Ver Historial</a>';
                        usuariosContainer += '</div>';
                        usuariosContainer += '</div>';
                        usuariosContainer += '</div>';
                    });

                    $('#usuariosContainer').html(usuariosContainer);
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error en la solicitud AJAX:', status, error);
            }
        });
    }

    function cargarDeudaTotal() {
        $.ajax({
            url: 'api_usuarios.php?action=total_deuda',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    let deudaTotal = parseFloat(response.deuda_total);
                    $('#deudaTotal').text(deudaTotal.toFixed(2));
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error en la solicitud AJAX:', status, error);
            }
        });
    }
</script>
</body>
</html>
