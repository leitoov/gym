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
if ($action === 'usuarios') {
    $order_by = isset($_GET['order_by']) ? $_GET['order_by'] : '';
    $filter_by = isset($_GET['filter_by']) ? $_GET['filter_by'] : '';
    $filter_value = isset($_GET['filter_value']) ? $_GET['filter_value'] : '';

    // Iniciar la consulta base
    $sql_usuarios = "SELECT * FROM usuarios";

    // Aplicar filtro si corresponde
    if ($filter_by && $filter_value) {
        if ($filter_by === 'fecha_vencimiento') {
            $sql_usuarios .= " WHERE fecha_vencimiento = '$filter_value'";
        } elseif ($filter_by === 'deuda') {
            $sql_usuarios .= " WHERE deuda >= $filter_value";
        }
    }

    // Aplicar ordenamiento si corresponde
    if ($order_by) {
        if ($order_by === 'fecha_vencimiento_asc') {
            $sql_usuarios .= " ORDER BY fecha_vencimiento ASC";
        } elseif ($order_by === 'fecha_vencimiento_desc') {
            $sql_usuarios .= " ORDER BY fecha_vencimiento DESC";
        } elseif ($order_by === 'deuda_asc') {
            $sql_usuarios .= " ORDER BY deuda ASC";
        } elseif ($order_by === 'deuda_desc') {
            $sql_usuarios .= " ORDER BY deuda DESC";
        }
    }

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
    // Verificar si los parámetros requeridos existen
    $required_fields = ['id_usuario', 'nombre', 'apellido', 'telefono', 'email', 'plan', 'fecha_vencimiento', 'deuda'];
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
            $response['message'] = "Falta el campo requerido: $field";
            echo json_encode($response);
            exit();
        }
    }

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

if ($action === 'añadir' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificar si los parámetros requeridos existen
    $required_fields = ['nombre', 'apellido', 'telefono', 'email', 'plan', 'fecha_vencimiento', 'deuda'];
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
            $response['message'] = "Falta el campo requerido: $field";
            echo json_encode($response);
            exit();
        }
    }

    // Insertar nuevo usuario
    $nombre = $conn->real_escape_string($_POST['nombre']);
    $apellido = $conn->real_escape_string($_POST['apellido']);
    $telefono = $conn->real_escape_string($_POST['telefono']);
    $email = $conn->real_escape_string($_POST['email']);
    $plan = $conn->real_escape_string($_POST['plan']);
    $fecha_vencimiento = $conn->real_escape_string($_POST['fecha_vencimiento']);
    $deuda = floatval($_POST['deuda']);

    $sql_insertar = "INSERT INTO usuarios (nombre, apellido, telefono, email, plan, fecha_vencimiento, deuda) 
                     VALUES ('$nombre', '$apellido', '$telefono', '$email', '$plan', '$fecha_vencimiento', $deuda)";

    if ($conn->query($sql_insertar) === TRUE) {
        $response = [
            'status' => 'success',
            'message' => 'Usuario añadido correctamente'
        ];
    } else {
        $response['message'] = 'Error al añadir usuario: ' . $conn->error;
    }

    echo json_encode($response);
    exit();
}

// En caso de acción inválida
echo json_encode($response);
exit();
?>
