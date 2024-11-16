<?php 
include 'conexion.php';
header('Content-Type: application/json');

$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($action === 'totales') {
    $response = [
        'total_usuarios' => 0,
        'total_deudores' => 0
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

    echo json_encode($response);
    exit();
}

if ($action === 'usuarios') {
    $usuarios = [];
    $sql = "SELECT * FROM usuarios";
    $result = $conn->query($sql);
    while ($usuario = $result->fetch_assoc()) {
        $usuarios[] = $usuario;
    }
    echo json_encode($usuarios);
    exit();
}

if ($action === 'deudores') {
    $deudores = [];
    $sql = "SELECT * FROM usuarios WHERE deuda > 0";
    $result = $conn->query($sql);
    while ($deudor = $result->fetch_assoc()) {
        $deudores[] = $deudor;
    }
    echo json_encode($deudores);
    exit();
}

?>