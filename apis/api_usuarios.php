<?php 
include './config/conexion.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $response = [
        'total_usuarios' => 0,
        'total_deudores' => 0,
        'usuarios' => []
    ];

    // Obtener total de usuarios
    $sql = "SELECT COUNT(*) as total FROM usuarios";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    $response['total_usuarios'] = $row['total'];

    // Obtener total de deudores
    $sql = "SELECT COUNT(*) as total FROM usuarios WHERE deuda > 0";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    $response['total_deudores'] = $row['total'];

    // Obtener lista de usuarios
    $sql = "SELECT * FROM usuarios";
    $result = $conn->query($sql);
    while ($usuario = $result->fetch_assoc()) {
        $response['usuarios'][] = $usuario;
    }

    echo json_encode($response);
    exit();
}

?>