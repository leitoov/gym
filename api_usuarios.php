<?php
// Configuración para mostrar todos los errores de PHP
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error_log.log'); // Crear el archivo error_log.log en el mismo directorio que la API
error_reporting(E_ALL);

// Incluir la conexión a la base de datos
include './config/conexion.php';
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Validar el parámetro "action" y otros que se necesiten
$action = isset($_GET['action']) ? trim($_GET['action']) : '';
$id = isset($_GET['id']) ? intval($_GET['id']) : null;

// Respuesta por defecto para errores
$response = [
    'status' => 'error',
    'message' => 'Acción no válida o falta de parámetros.'
];

// Verificar conexión con la base de datos
if (!$conn) {
    $response['message'] = 'Error en la conexión a la base de datos: ' . mysqli_connect_error();
    error_log($response['message']);
    echo json_encode($response);
    die();
}

// Función para ejecutar una consulta y retornar los resultados
function ejecutarConsulta($sql, $conn) {
    $result = $conn->query($sql);
    if ($result === false) {
        error_log("Error en la consulta: " . $conn->error);
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
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            error_log("Ejecutando acción 'totales'");
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
            } else {
                error_log("Error al obtener el total de usuarios: " . json_encode($resultado_usuarios));
            }

            // Obtener total de deudores
            $sql_deudores = "SELECT COUNT(DISTINCT id_usuario) as total FROM deudas WHERE estado = 'pendiente'";
            $resultado_deudores = ejecutarConsulta($sql_deudores, $conn);

            if (isset($resultado_deudores[0])) {
                $response['total_deudores'] = $resultado_deudores[0]['total'];
            } else {
                error_log("Error al obtener el total de deudores: " . json_encode($resultado_deudores));
            }

            echo json_encode($response);
            die();
        } else {
            error_log("Método HTTP incorrecto para la acción 'totales'");
        }
        break;

    case 'usuarios':
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            error_log("Ejecutando acción 'usuarios'");
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
            die();
        } else {
            error_log("Método HTTP incorrecto para la acción 'usuarios'");
        }
        break;

    case 'deudores':
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            error_log("Ejecutando acción 'deudores'");
            // Obtener usuarios con deudas pendientes
            $sql_deudores = "SELECT u.id_usuario, u.nombre, u.apellido, d.id_deuda, d.monto, d.fecha_generacion, d.fecha_vencimiento, d.estado 
                             FROM usuarios u 
                             INNER JOIN deudas d ON u.id_usuario = d.id_usuario 
                             WHERE d.estado = 'pendiente' 
                             ORDER BY u.id_usuario, d.fecha_generacion";
            $deudores = ejecutarConsulta($sql_deudores, $conn);

            if (isset($deudores['error'])) {
                $response['message'] = 'Error al obtener deudores: ' . $deudores['error'];
            } else {
                $response = [
                    'status' => 'success',
                    'deudores' => []
                ];

                // Agrupar las deudas por usuario
                foreach ($deudores as $deuda) {
                    $id_usuario = $deuda['id_usuario'];
                    if (!isset($response['deudores'][$id_usuario])) {
                        $response['deudores'][$id_usuario] = [
                            'id_usuario' => $deuda['id_usuario'],
                            'nombre' => $deuda['nombre'],
                            'apellido' => $deuda['apellido'],
                            'deudas' => []
                        ];
                    }
                    $response['deudores'][$id_usuario]['deudas'][] = [
                        'id_deuda' => $deuda['id_deuda'],
                        'monto' => $deuda['monto'],
                        'fecha_generacion' => $deuda['fecha_generacion'],
                        'fecha_vencimiento' => $deuda['fecha_vencimiento'],
                        'estado' => $deuda['estado']
                    ];
                }

                // Convertir el array asociativo en un array indexado
                $response['deudores'] = array_values($response['deudores']);
            }

            echo json_encode($response);
            die();
        } else {
            error_log("Método HTTP incorrecto para la acción 'deudores'");
        }
        break;

    case 'usuario':
        if ($id !== null && $_SERVER['REQUEST_METHOD'] === 'GET') {
            error_log("Ejecutando acción 'usuario' con ID: $id");
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
            die();
        } else {
            error_log("ID no proporcionado o método HTTP incorrecto para la acción 'usuario'");
        }
        break;

    case 'actualizar':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            error_log("Ejecutando acción 'actualizar'");
            $data = json_decode(file_get_contents("php://input"), true);
            $required_fields = ['id_usuario', 'nombre', 'apellido', 'telefono', 'email', 'plan', 'fecha_vencimiento', 'deuda'];

            foreach ($required_fields as $field) {
                if (!isset($data[$field]) || empty(trim($data[$field]))) {
                    $response['message'] = "Falta el campo requerido: $field";
                    echo json_encode($response);
                    die();
                }
            }

            // Actualizar usuario
            $id_usuario = intval($data['id_usuario']);
            $nombre = $conn->real_escape_string($data['nombre']);
            $apellido = $conn->real_escape_string($data['apellido']);
            $telefono = $conn->real_escape_string($data['telefono']);
            $email = $conn->real_escape_string($data['email']);
            $plan = $conn->real_escape_string($data['plan']);
            $fecha_vencimiento = $conn->real_escape_string($data['fecha_vencimiento']);
            $deuda = floatval($data['deuda']);

            $sql_actualizar = "UPDATE usuarios SET nombre = '$nombre', apellido = '$apellido', telefono = '$telefono', email = '$email', plan = '$plan', fecha_vencimiento = '$fecha_vencimiento', deuda = $deuda WHERE id_usuario = $id_usuario";

            if ($conn->query($sql_actualizar) === TRUE) {
                $response = [
                    'status' => 'success',
                    'message' => 'Usuario actualizado correctamente'
                ];
            } else {
                error_log("Error al actualizar usuario: " . $conn->error);
                $response['message'] = 'Error al actualizar usuario: ' . $conn->error;
            }

            echo json_encode($response);
            die();
        } else {
            error_log("Método HTTP incorrecto para la acción 'actualizar'");
        }
        break;

    case 'pago':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = json_decode(file_get_contents("php://input"), true);
            $id_usuario = isset($data['id']) ? intval($data['id']) : null;

            if ($id_usuario !== null) {
                error_log("Ejecutando acción 'pago' para ID: $id_usuario");
                // Marcar todas las deudas pendientes de un usuario como pagadas
                $sql_pagar = "UPDATE deudas SET estado = 'pagado' WHERE id_usuario = $id_usuario AND estado = 'pendiente'";

                if ($conn->query($sql_pagar) === TRUE) {
                    $response = [
                        'status' => 'success',
                        'message' => 'Todas las deudas pendientes han sido marcadas como pagadas'
                    ];
                } else {
                    $response['message'] = 'Error al actualizar deudas: ' . $conn->error;
                    error_log($response['message']);
                }

                echo json_encode($response);
                die();
            } else {
                $response['message'] = "ID de usuario no proporcionado para la acción 'pago'";
                error_log($response['message']);
            }
        } else {
            error_log("Método HTTP incorrecto para la acción 'pago'");
        }
        break;

    case 'eliminar_deuda':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = json_decode(file_get_contents("php://input"), true);
            $id_deuda = isset($data['id_deuda']) ? intval($data['id_deuda']) : null;

            if ($id_deuda !== null) {
                error_log("Ejecutando acción 'eliminar_deuda' para ID de deuda: $id_deuda");
                // Eliminar la deuda específica
                $sql_eliminar_deuda = "DELETE FROM deudas WHERE id_deuda = $id_deuda";

                if ($conn->query($sql_eliminar_deuda) === TRUE) {
                    $response = [
                        'status' => 'success',
                        'message' => 'Deuda eliminada correctamente'
                    ];
                } else {
                    $response['message'] = 'Error al eliminar deuda: ' . $conn->error;
                    error_log($response['message']);
                }

                echo json_encode($response);
                die();
            } else {
                $response['message'] = "ID de deuda no proporcionado para la acción 'eliminar_deuda'";
                error_log($response['message']);
            }
        } else {
            error_log("Método HTTP incorrecto para la acción 'eliminar_deuda'");
        }
        break;

    default:
        error_log("Acción no válida o no especificada: $action");
        echo json_encode($response);
        die();
}
?>
