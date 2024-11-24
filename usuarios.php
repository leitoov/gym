<?php
session_start();
include 'conexion.php';

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
    <title>Strong Woman Factory - Usuarios</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.4.26/dist/sweetalert2.min.css">
    <style>
        :root {
            --primary-pink: #FF69B4;
            --secondary-turquoise: #40E0D0;
            --dark-bg: #000000;
            --light-bg: #FFFFFF;
            --skin-tone: #FFE4C4;
            --brown: #8B4513;
        }

        body {
            background-color: #f8f9fa;
            font-family: 'Arial', sans-serif;
        }

        .container {
            background: var(--light-bg);
            border-radius: 15px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 30px;
            max-width: 1200px;
            margin: auto;
        }

        .user-card {
            border: 2px solid var(--primary-pink);
            border-radius: 15px;
            box-shadow: 0 6px 12px rgba(255, 105, 180, 0.1);
            margin-bottom: 20px;
            padding: 20px;
            transition: all 0.3s;
            background: linear-gradient(145deg, var(--light-bg), #f8f9fa);
            display: flex;
            align-items: center;
            max-width: 800px;
            margin: 20px auto;
            position: relative;
            overflow: hidden;
        }

        .user-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(90deg, var(--primary-pink), var(--secondary-turquoise));
        }

        .user-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(255, 105, 180, 0.2);
        }

        .user-info {
            display: flex;
            align-items: center;
            flex-direction: row;
            width: 100%;
            gap: 20px;
        }

        .user-photo {
            flex: 0 0 120px;
            height: 120px;
            border-radius: 50%;
            overflow: hidden;
            border: 3px solid var(--primary-pink);
            box-shadow: 0 4px 8px rgba(255, 105, 180, 0.3);
            transition: all 0.3s;
        }

        .user-photo:hover {
            transform: scale(1.05);
        }

        .user-photo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .user-details {
            flex: 1;
            padding: 10px;
        }

        .user-details h5 {
            color: var(--primary-pink);
            font-size: 1.4rem;
            font-weight: bold;
            margin-bottom: 15px;
            text-transform: uppercase;
        }

        .user-details p {
            margin-bottom: 8px;
            font-size: 0.95rem;
            color: #555;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .user-details p strong {
            color: var(--dark-bg);
            min-width: 140px;
            display: inline-block;
        }

        .user-actions {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-left: 20px;
        }

        .btn-custom {
            width: 150px;
            border-radius: 25px;
            transition: all 0.3s ease;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
        }

        .btn-custom.btn-warning {
            background: linear-gradient(45deg, var(--primary-pink), var(--secondary-turquoise));
            border: none;
            color: white;
        }

        .btn-custom.btn-info {
            background: var(--dark-bg);
            border: none;
            color: white;
        }

        .btn-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .total-deuda-container {
            text-align: center;
            margin-bottom: 30px;
            padding: 20px;
            border-radius: 15px;
            background: linear-gradient(45deg, var(--primary-pink), var(--secondary-turquoise));
            color: white;
            box-shadow: 0 4px 15px rgba(255, 105, 180, 0.2);
        }

        .total-deuda-container h4 {
            margin: 0;
            font-size: 1.8rem;
            font-weight: 700;
            text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.2);
        }

        /* Estilos para los modales */
        .modal-content {
            border-radius: 15px;
            overflow: hidden;
        }

        .modal-header {
            background: linear-gradient(45deg, var(--primary-pink), var(--secondary-turquoise));
            color: white;
            border-bottom: none;
            padding: 1.5rem;
        }

        .modal-title {
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .modal-body {
            padding: 2rem;
        }

        .form-group label {
            color: var(--dark-bg);
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .form-control {
            border-radius: 10px;
            border: 2px solid #eee;
            padding: 0.75rem;
            transition: all 0.3s;
        }

        .form-control:focus {
            border-color: var(--primary-pink);
            box-shadow: 0 0 0 0.2rem rgba(255, 105, 180, 0.25);
        }

        .modal-footer {
            border-top: none;
            padding: 1.5rem;
        }

        .btn-success {
            background: var(--primary-pink);
            border: none;
            border-radius: 25px;
            padding: 0.5rem 1.5rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .btn-success:hover {
            background: var(--secondary-turquoise);
            transform: translateY(-2px);
        }

        @media (max-width: 768px) {
            .user-info {
                flex-direction: column;
                text-align: center;
            }

            .user-photo {
                margin: 0 auto 20px;
            }

            .user-actions {
                margin-top: 20px;
                flex-direction: row;
                justify-content: center;
                margin-left: 0;
            }

            .btn-custom {
                width: auto;
                padding: 8px 20px;
            }
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <h2 class="text-center mb-4" style="color: #343a40;">Credenciales de Usuarios del Gimnasio</h2>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <a href="index.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Volver al Panel</a>
        <a href="#" class="btn btn-success" data-toggle="modal" data-target="#anadirUsuarioModal"><i class="fas fa-user-plus"></i> Añadir Usuario</a>
    </div>
    
    <!-- Contenedor de la deuda total -->
    <div id="deudaTotalContainer" class="total-deuda-container">
        <h4>Total Deuda Pendiente: AR$ <span id="deudaTotal">0.00</span></h4>
    </div>

    <div id="usuariosContainer">
        <!-- Los usuarios se cargarán dinámicamente usando AJAX -->
    </div>
</div>

<!-- Modal para anadir usuario -->
<div class="modal fade" id="anadirUsuarioModal" tabindex="-1" role="dialog" aria-labelledby="anadirUsuarioLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="anadirUsuarioLabel">Añadir Usuario</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="anadirUsuarioForm" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="anadirNombre">Nombre</label>
                        <input type="text" class="form-control" id="anadirNombre" name="nombre" required>
                    </div>
                    <div class="form-group">
                        <label for="anadirApellido">Apellido</label>
                        <input type="text" class="form-control" id="anadirApellido" name="apellido" required>
                    </div>
                    <div class="form-group">
                        <label for="anadirTelefono">Teléfono</label>
                        <input type="text" class="form-control" id="anadirTelefono" name="telefono" required>
                    </div>
                    <div class="form-group">
                        <label for="anadirEmail">Correo Electrónico</label>
                        <input type="email" class="form-control" id="anadirEmail" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="anadirPlan">Plan</label>
                        <select class="form-control" id="anadirPlan" name="plan" required>
                            <option value="Básico">Básico</option>
                            <option value="Premium">Premium</option>
                            <option value="VIP">VIP</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="anadirFechaVencimiento">Fecha de Vencimiento</label>
                        <input type="date" class="form-control" id="anadirFechaVencimiento" name="fecha_vencimiento" required>
                    </div>
                    <div class="form-group">
                        <label for="anadirDeuda">Deuda (AR$)</label>
                        <input type="number" class="form-control" id="anadirDeuda" name="deuda" value="0.00" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label for="anadirFoto">Foto</label>
                        <input type="file" class="form-control" id="anadirFoto" name="foto" accept="image/*">
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

<!-- Modal para editar usuario -->
<div class="modal fade" id="editarUsuarioModal" tabindex="-1" role="dialog" aria-labelledby="editarUsuarioLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editarUsuarioLabel">Editar Usuario</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="editarUsuarioForm" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="editarNombre">Nombre</label>
                        <input type="text" class="form-control" id="editarNombre" name="nombre" required>
                    </div>
                    <div class="form-group">
                        <label for="editarApellido">Apellido</label>
                        <input type="text" class="form-control" id="editarApellido" name="apellido" required>
                    </div>
                    <div class="form-group">
                        <label for="editarTelefono">Teléfono</label>
                        <input type="text" class="form-control" id="editarTelefono" name="telefono" required>
                    </div>
                    <div class="form-group">
                        <label for="editarEmail">Correo Electrónico</label>
                        <input type="email" class="form-control" id="editarEmail" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="editarPlan">Plan</label>
                        <select class="form-control" id="editarPlan" name="plan" required>
                            <option value="Básico">Básico</option>
                            <option value="Premium">Premium</option>
                            <option value="VIP">VIP</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="editarFechaVencimiento">Fecha de Vencimiento</label>
                        <input type="date" class="form-control" id="editarFechaVencimiento" name="fecha_vencimiento" required>
                    </div>
                    <div class="form-group">
                        <label for="editarDeuda">Deuda (AR$)</label>
                        <input type="number" class="form-control" id="editarDeuda" name="deuda" value="0.00" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label for="editarFoto">Foto</label>
                        <input type="file" class="form-control" id="editarFoto" name="foto" accept="image/*">
                    </div>
                    <input type="hidden" id="editarIdUsuario" name="id_usuario">
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="guardarCambios">Guardar Cambios</button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.4.26/dist/sweetalert2.all.min.js"></script>

<script>
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
                        usuariosContainer += `
                            <div class="user-card">
                                <div class="user-info">
                                    <div class="user-photo">
                                        ${usuario.foto ? `<img src="${usuario.foto}" alt="Foto de ${usuario.nombre}">` : '<img src="uploads/descarga.png" alt="Foto de usuario">'}
                                    </div>
                                    <div class="user-details">
                                        <h5>${usuario.nombre} ${usuario.apellido}</h5>
                                        <p><strong>Teléfono:</strong> ${usuario.telefono}</p>
                                        <p><strong>Correo Electrónico:</strong> ${usuario.email}</p>
                                        <p><strong>Plan:</strong> ${usuario.plan}</p>
                                        <p><strong>Fecha de Vencimiento:</strong> ${usuario.fecha_vencimiento}</p>
                                        <p><strong>Deuda:</strong> AR$ ${usuario.deuda}</p>
                                    </div>
                                    <div class="user-actions">
                                        <button onclick="abrirModalEdicion(${usuario.id_usuario})" class="btn btn-warning btn-custom"><i class="fas fa-edit"></i> Editar</button>
                                        <a href="historial.php?id_usuario=${usuario.id_usuario}" class="btn btn-info btn-custom"><i class="fas fa-history"></i> Ver Historial</a>
                                    </div>
                                </div>
                            </div>
                        `;
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

    // Abrir el modal de edición de usuario
    function abrirModalEdicion(id_usuario) {
        $.ajax({
            url: 'api_usuarios.php?action=usuario&id=' + id_usuario,
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    let usuario = response.usuario;
                    $('#editarIdUsuario').val(usuario.id_usuario);
                    $('#editarNombre').val(usuario.nombre);
                    $('#editarApellido').val(usuario.apellido);
                    $('#editarTelefono').val(usuario.telefono);
                    $('#editarEmail').val(usuario.email);
                    $('#editarPlan').val(usuario.plan);
                    $('#editarFechaVencimiento').val(usuario.fecha_vencimiento);
                    $('#editarDeuda').val(usuario.deuda);
                    $('#editarUsuarioModal').modal('show');
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error en la solicitud AJAX:', status, error);
            }
        });
    }

    // Guardar el nuevo usuario
    $('#guardarNuevoUsuario').click(function() {
        let formData = new FormData($('#anadirUsuarioForm')[0]);

        $.ajax({
            url: 'api_usuarios.php?action=anadir',
            method: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    $('#anadirUsuarioModal').modal('hide');
                    Swal.fire('Éxito', 'Usuario añadido correctamente', 'success').then(() => {
                        cargarUsuarios();
                        cargarDeudaTotal();
                    });
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error en la solicitud AJAX:', status, error);
                Swal.fire('Error', 'Hubo un problema al añadir el usuario', 'error');
            }
        });
    });

    // Guardar los cambios realizados en el usuario
    $('#guardarCambios').click(function() {
        let formData = new FormData($('#editarUsuarioForm')[0]);

        $.ajax({
            url: 'api_usuarios.php?action=actualizar',
            method: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    $('#editarUsuarioModal').modal('hide');
                    Swal.fire('Éxito', 'Usuario actualizado correctamente', 'success').then(() => {
                        cargarUsuarios(); // Recargar los usuarios después de actualizar
                        cargarDeudaTotal(); // Actualizar la deuda total después de actualizar un usuario
                    });
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error en la solicitud AJAX:', status, error);
                Swal.fire('Error', 'Hubo un problema al actualizar el usuario', 'error');
            }
        });
    });
</script>
</body>
</html>
