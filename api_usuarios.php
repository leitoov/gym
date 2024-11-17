<?php 
include 'conexion.php';
header('Content-Type: application/json');

// Acción solicitada (totales, usuarios, deudores, etc.)
$action = isset($_GET['action']) ? $_GET['action'] : '';

// Respuesta por defecto para errores
$response = [
    'status' => 'error',
    'message' => 'Acción no válida o falta de parámetros.'
];

// Función para ejecutar una consulta y retornar los resultados
function ejecutarConsulta($sql, $conn) {
    $result = $conn->query($sql);
    if ($result === false) {
        return ['error' => $conn->error];
    }
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    return $data;
}

// Lógica de las acciones disponibles
if ($action === 'totales') {
    $response = [
        'status' => 'success',
        'total_usuarios' => 0,
        'total_deudores' => 0
    ];

    // Obtener total de usuarios
    $sql_usuarios = "SELECT COUNT(*) as total FROM usuarios";
    $resultado_usuarios = ejecutarConsulta($sql_usuarios, $conn);
    
    if (isset($resultado_usuarios[0])) {
        $response['total_usuarios'] = $resultado_usuarios[0]['total'];
    }

    // Obtener total de deudores
    $sql_deudores = "SELECT COUNT(*) as total FROM usuarios WHERE deuda > 0";
    $resultado_deudores = ejecutarConsulta($sql_deudores, $conn);
    
    if (isset($resultado_deudores[0])) {
        $response['total_deudores'] = $resultado_deudores[0]['total'];
    }

    echo json_encode($response);
    exit();
}

if ($action === 'usuarios') {
    // Obtener todos los usuarios
    $sql_usuarios = "SELECT * FROM usuarios";
    $usuarios = ejecutarConsulta($sql_usuarios, $conn);
    
    if (isset($usuarios['error'])) {
        $response['message'] = 'Error al obtener usuarios: ' . $usuarios['error'];
    } else {
        $response = [
            'status' => 'success',
            'usuarios' => $usuarios
        ];
    }

    echo json_encode($response);
    exit();
}

if ($action === 'deudores') {
    // Obtener usuarios con deudas
    $sql_deudores = "SELECT * FROM usuarios WHERE deuda > 0";
    $deudores = ejecutarConsulta($sql_deudores, $conn);

    if (isset($deudores['error'])) {
        $response['message'] = 'Error al obtener deudores: ' . $deudores['error'];
    } else {
        $response = [
            'status' => 'success',
            'deudores' => $deudores
        ];
    }

    echo json_encode($response);
    exit();
}

// En caso de acción inválida
echo json_encode($response);
exit();
?>
