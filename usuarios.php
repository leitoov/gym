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
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script>
    // Cargar usuarios en la tabla
    $(document).ready(function() {
        $.ajax({
            url: 'api_usuarios.php?action=usuarios',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                let usuarios = response;
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
            }
        });
    });
</script>
</body>
</html>
