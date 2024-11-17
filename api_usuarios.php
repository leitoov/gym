<?php 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include './config/conexion.php';
header('Content-Type: application/json');

$action = isset($_GET['action']) ? $_GET['action'] : '';
$id = isset($_GET['id']) ? intval($_GET['id']) : null;

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

if ($action === 'usuario' && $id !== null) {
    // Obtener un usuario por su ID
    $sql_usuario = "SELECT * FROM usuarios WHERE id_usuario = $id";
    $usuario = ejecutarConsulta($sql_usuario, $conn);

    if (isset($usuario['error'])) {
        $response['message'] = 'Error al obtener usuario: ' . $usuario['error'];
    } elseif (!empty($usuario)) {
        $response = [
            'status' => 'success',
            'usuario' => $usuario[0]
        ];
    } else {
        $response['message'] = 'Usuario no encontrado';
    }

    echo json_encode($response);
    exit();
}

if ($action === 'actualizar' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    // Actualizar usuario
    $id_usuario = intval($_POST['id_usuario']);
    $nombre = $conn->real_escape_string($_POST['nombre']);
    $apellido = $conn->real_escape_string($_POST['apellido']);
    $telefono = $conn->real_escape_string($_POST['telefono']);
    $email = $conn->real_escape_string($_POST['email']);
    $plan = $conn->real_escape_string($_POST['plan']);
    $fecha_vencimiento = $conn->real_escape_string($_POST['fecha_vencimiento']);
    $deuda = floatval($_POST['deuda']);

    $sql_actualizar = "UPDATE usuarios SET nombre = '$nombre', apellido = '$apellido', telefono = '$telefono', email = '$email', plan = '$plan', fecha_vencimiento = '$fecha_vencimiento', deuda = $deuda WHERE id_usuario = $id_usuario";

    if ($conn->query($sql_actualizar) === TRUE) {
        $response = [
            'status' => 'success',
            'message' => 'Usuario actualizado correctamente'
        ];
    } else {
        $response['message'] = 'Error al actualizar usuario: ' . $conn->error;
    }

    echo json_encode($response);
    exit();
}

// En caso de acción inválida
echo json_encode($response);
exit();
?>
