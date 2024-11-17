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
    <title>Usuarios Registrados - Gimnasio</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.4.26/dist/sweetalert2.min.css">
</head>
<body>
<div class="container mt-5">
    <h2 class="text-center">Usuarios Registrados</h2>
    <div class="text-right mb-3">
        <a href="index.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Volver al Panel</a>
    </div>
    <table class="table table-bordered">
        <thead>
        <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Apellido</th>
            <th>Teléfono</th>
            <th>Correo Electrónico</th>
            <th>Plan</th>
            <th>Fecha de Vencimiento</th>
            <th>Deuda (AR$)</th>
            <th>Avisos Enviados</th>
            <th>Acciones</th>
        </tr>
        </thead>
        <tbody id="tablaUsuarios">
            <!-- Los usuarios se cargarán dinámicamente usando AJAX -->
        </tbody>
    </table>
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
                <form id="editarUsuarioForm">
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
                        <input type="text" class="form-control" id="editarPlan" name="plan" required>
                    </div>
                    <div class="form-group">
                        <label for="editarFechaVencimiento">Fecha de Vencimiento</label>
                        <input type="date" class="form-control" id="editarFechaVencimiento" name="fecha_vencimiento" required>
                    </div>
                    <div class="form-group">
                        <label for="editarDeuda">Deuda (AR$)</label>
                        <input type="number" class="form-control" id="editarDeuda" name="deuda" step="0.01" required>
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
    // Cargar usuarios en la tabla
    $(document).ready(function() {
        $.ajax({
            url: 'api_usuarios.php?action=usuarios',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    let usuarios = response.usuarios;
                    let tablaUsuarios = '';

                    usuarios.forEach(function(usuario) {
                        tablaUsuarios += '<tr>';
                        tablaUsuarios += '<td>' + usuario.id_usuario + '</td>';
                        tablaUsuarios += '<td>' + usuario.nombre + '</td>';
                        tablaUsuarios += '<td>' + usuario.apellido + '</td>';
                        tablaUsuarios += '<td>' + usuario.telefono + '</td>';
                        tablaUsuarios += '<td>' + usuario.email + '</td>';
                        tablaUsuarios += '<td>' + usuario.plan + '</td>';
                        tablaUsuarios += '<td>' + usuario.fecha_vencimiento + '</td>';
                        tablaUsuarios += '<td>AR$ ' + usuario.deuda + '</td>';
                        tablaUsuarios += '<td>' + usuario.avisos + '</td>';
                        tablaUsuarios += '<td>';
                        tablaUsuarios += '<a href="#" onclick="abrirModalEdicion(' + usuario.id_usuario + ')" class="btn btn-warning btn-custom"><i class="fas fa-edit"></i> Editar</a> ';
                        tablaUsuarios += '<a href="ver_historial.php?id=' + usuario.id_usuario + '" class="btn btn-info btn-custom"><i class="fas fa-history"></i> Ver Historial</a>';
                        tablaUsuarios += '</td>';
                        tablaUsuarios += '</tr>';
                    });

                    $('#tablaUsuarios').html(tablaUsuarios);
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error en la solicitud AJAX:', status, error);
            }
        });
    });

    // Abrir el modal de edición de usuario
    function abrirModalEdicion(id_usuario) {
        // Obtener los datos del usuario y mostrarlos en el modal
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

    // Guardar los cambios realizados en el usuario
    $('#guardarCambios').click(function() {
        let formData = $('#editarUsuarioForm').serialize();

        $.ajax({
            url: 'api_usuarios.php?action=actualizar',
            method: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    $('#editarUsuarioModal').modal('hide');
                    Swal.fire('Exito', 'Usuario actualizado correctamente', 'success').then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire('Error', response.message, 'error');
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
