<?php
// Configuración para mostrar todos los errores de PHP
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Incluir la conexión a la base de datos
include './config/conexion.php';
header('Content-Type: application/json');

// Validar el parámetro "action" y otros que se necesiten
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
switch ($action) {
    case 'totales':
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

    case 'usuarios':
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

    case 'deudores':
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

    case 'usuario':
        if ($id !== null) {
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
        }

        echo json_encode($response);
        exit();

    case 'actualizar':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
        break;

    case 'anadir':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Verificar si los parámetros requeridos existen para añadir un usuario
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

            $sql_insertar = "INSERT INTO usuarios (nombre, apellido, telefono, email, plan, fecha_vencimiento, deuda) VALUES ('$nombre', '$apellido', '$telefono', '$email', '$plan', '$fecha_vencimiento', $deuda)";

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
        break;

    case 'pago':
        if ($id !== null && $_SERVER['REQUEST_METHOD'] === 'POST') {
            // Marcar la deuda de un usuario como pagada
            $sql_pagar = "UPDATE usuarios SET deuda = 0 WHERE id_usuario = $id";

            if ($conn->query($sql_pagar) === TRUE) {
                $response = [
                    'status' => 'success',
                    'message' => 'Deuda marcada como pagada'
                ];
            } else {
                $response['message'] = 'Error al actualizar deuda: ' . $conn->error;
            }

            echo json_encode($response);
            exit();
        }
        break;

    default:
        // En caso de acción inválida
        echo json_encode($response);
        exit();
}

?>
