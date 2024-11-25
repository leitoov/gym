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

            // Obtener total de deudores (usuarios con deudas pendientes en base al día de vencimiento)
            $dia_actual = date('j');
            $sql_deudores = "SELECT COUNT(DISTINCT u.id_usuario) as total FROM usuarios u 
                             LEFT JOIN deudas d ON u.id_usuario = d.id_usuario AND d.estado = 'pendiente'
                             WHERE u.dia_vencimiento <= $dia_actual AND (d.id_deuda IS NULL OR d.estado = 'pendiente')";
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
            
            // Obtener todos los usuarios y calcular la deuda acumulada considerando el día de vencimiento
            $dia_actual = date('j');
            $sql_usuarios = "SELECT u.*, 
                            (SELECT SUM(d.monto) 
                            FROM deudas d 
                            WHERE d.id_usuario = u.id_usuario AND d.estado = 'pendiente' AND u.dia_vencimiento <= $dia_actual) AS deuda_total
                            FROM usuarios u";
                             
            $usuarios = ejecutarConsulta($sql_usuarios, $conn);
        
            if (isset($usuarios['error'])) {
                $response['message'] = 'Error al obtener usuarios: ' . $usuarios['error'];
            } else {
                // Formatear la respuesta con los usuarios y su deuda total acumulada
                $response = [
                    'status' => 'success',
                    'usuarios' => array_map(function($usuario) {
                        // Asegurarnos de que la deuda sea al menos 0 si no tiene deudas
                        $usuario['deuda'] = $usuario['deuda_total'] ? floatval($usuario['deuda_total']) : 0.0;
                        unset($usuario['deuda_total']); // Remover el campo innecesario
                        return $usuario;
                    }, $usuarios)
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
                
                // Primero, generar las deudas que correspondan automáticamente
                $dia_actual = date('j');
                $mes_actual = date('n');
                $ano_actual = date('Y');
        
                // Consulta para obtener todos los usuarios que necesitan generar una deuda
                $sql_usuarios_para_generar_deuda = "SELECT id_usuario, dia_vencimiento 
                                                    FROM usuarios 
                                                    WHERE dia_vencimiento <= $dia_actual";
                $usuarios_para_generar_deuda = ejecutarConsulta($sql_usuarios_para_generar_deuda, $conn);
        
                // Recorrer usuarios para crear las deudas si es necesario
                foreach ($usuarios_para_generar_deuda as $usuario) {
                    $id_usuario = $usuario['id_usuario'];
                    $dia_vencimiento = $usuario['dia_vencimiento'];
        
                    // Verificar si ya existe una deuda para el mes actual
                    $sql_verificar_deuda = "SELECT * FROM deudas 
                                            WHERE id_usuario = $id_usuario AND MONTH(fecha_generacion) = $mes_actual AND YEAR(fecha_generacion) = $ano_actual";
                    $deuda_existente = ejecutarConsulta($sql_verificar_deuda, $conn);
        
                    // Si no hay deuda para el mes actual, crear una nueva deuda
                    if (empty($deuda_existente)) {
                        $monto = 1000; // Puedes definir un monto fijo o calcularlo según el plan
                        $sql_insertar_deuda = "INSERT INTO deudas (id_usuario, monto, fecha_generacion, estado) 
                                               VALUES ($id_usuario, $monto, '$ano_actual-$mes_actual-$dia_vencimiento', 'pendiente')";
                        if (!$conn->query($sql_insertar_deuda)) {
                            error_log("Error al generar deuda para usuario $id_usuario: " . $conn->error);
                        }
                    }
                }
        
                // Luego, obtener los usuarios con deudas pendientes
                $sql_deudores = "SELECT u.id_usuario, u.nombre, u.apellido, 
                                 COALESCE(u.telefono, 'No disponible') AS telefono, 
                                 COALESCE(u.email, 'No disponible') AS email, 
                                 p.nombre AS plan, 
                                 d.id_deuda, d.monto, d.fecha_generacion, d.estado 
                                 FROM usuarios u 
                                 LEFT JOIN deudas d ON u.id_usuario = d.id_usuario AND d.estado = 'pendiente'
                                 LEFT JOIN planes p ON u.plan = p.id_plan
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
                                'telefono' => $deuda['telefono'],
                                'email' => $deuda['email'],
                                'plan' => $deuda['plan'],
                                'deudas' => []
                            ];
                        }
                        $response['deudores'][$id_usuario]['deudas'][] = [
                            'id_deuda' => $deuda['id_deuda'],
                            'monto' => $deuda['monto'],
                            'fecha_generacion' => $deuda['fecha_generacion'],
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

    case 'anadir':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nombre = $_POST['nombre'];
            $apellido = $_POST['apellido'];
            $telefono = $_POST['telefono'];
            $email = $_POST['email'];
            $plan = $_POST['plan'];
            $dia_vencimiento = intval($_POST['dia_vencimiento']);
            $deuda = $_POST['deuda'];

            // Manejar la foto de perfil
            $foto = null;
            if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
                $target_dir = "uploads/";
                if (!file_exists($target_dir)) {
                    mkdir($target_dir, 0777, true);
                }
                $foto = $target_dir . basename($_FILES["foto"]["name"]);
                move_uploaded_file($_FILES["foto"]["tmp_name"], $foto);
            }

            $sql = "INSERT INTO usuarios (nombre, apellido, telefono, email, plan, dia_vencimiento, deuda, foto) 
                    VALUES ('$nombre', '$apellido', '$telefono', '$email', '$plan', $dia_vencimiento, $deuda, '$foto')";

            if ($conn->query($sql) === TRUE) {
                $response = [
                    'status' => 'success',
                    'message' => 'Usuario añadido correctamente'
                ];
            } else {
                error_log("Error al añadir usuario: " . $conn->error);
                $response['message'] = 'Error al añadir usuario: ' . $conn->error;
            }

            echo json_encode($response);
            die();
        }
        break;
        
    case 'actualizar':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_usuario = intval($_POST['id_usuario']);
            $nombre = $_POST['nombre'];
            $apellido = $_POST['apellido'];
            $telefono = $_POST['telefono'];
            $email = $_POST['email'];
            $plan = $_POST['plan'];
            $dia_vencimiento = intval($_POST['dia_vencimiento']);
            $deuda = floatval($_POST['deuda']);

            // Manejar la foto de perfil (si se envía una nueva)
            $foto = null;
            if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
                $target_dir = "uploads/";
                if (!file_exists($target_dir)) {
                    mkdir($target_dir, 0777, true);
                }
                $foto = $target_dir . basename($_FILES["foto"]["name"]);
                move_uploaded_file($_FILES["foto"]["tmp_name"], $foto);

                // Actualizar la consulta para incluir la foto
                $sql_actualizar = "UPDATE usuarios SET nombre = '$nombre', apellido = '$apellido', telefono = '$telefono', email = '$email', plan = '$plan', dia_vencimiento = $dia_vencimiento, deuda = $deuda, foto = '$foto' WHERE id_usuario = $id_usuario";
            } else {
                // Si no hay nueva foto, no actualizar el campo foto
                $sql_actualizar = "UPDATE usuarios SET nombre = '$nombre', apellido = '$apellido', telefono = '$telefono', email = '$email', plan = '$plan', dia_vencimiento = $dia_vencimiento, deuda = $deuda WHERE id_usuario = $id_usuario";
            }

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
        }
        break;

    case 'marcar_deuda_pagada':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = json_decode(file_get_contents("php://input"), true);
            $id_deuda = isset($data['id_deuda']) ? intval($data['id_deuda']) : null;

            if ($id_deuda !== null) {
                error_log("Ejecutando acción 'marcar_deuda_pagada' para ID de deuda: $id_deuda");
                // Marcar la deuda específica como pagada
                $fecha_pago = date('Y-m-d'); // Obtener la fecha actual
                $sql_marcar_pagada = "UPDATE deudas 
                                      SET estado = 'pagada', fecha_pago = '$fecha_pago' 
                                      WHERE id_deuda = $id_deuda AND estado = 'pendiente'";

                if ($conn->query($sql_marcar_pagada) === TRUE) {
                    $response = [
                        'status' => 'success',
                        'message' => 'Deuda marcada como pagada correctamente'
                    ];
                } else {
                    $response['message'] = 'Error al actualizar deuda: ' . $conn->error;
                    error_log($response['message']);
                }

                echo json_encode($response);
                die();
            } else {
                $response['message'] = "ID de deuda no proporcionado para la acción 'marcar_deuda_pagada'";
                error_log($response['message']);
            }
        } else {
            error_log("Método HTTP incorrecto para la acción 'marcar_deuda_pagada'");
        }
        break;

    case 'total_deuda':
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            error_log("Ejecutando acción 'total_deuda'");
            // Obtener la deuda total de todos los usuarios con deudas pendientes considerando el día de vencimiento
            $dia_actual = date('j');
            $sql_total_deuda = "SELECT SUM(d.monto) AS deuda_total FROM deudas d
                                INNER JOIN usuarios u ON u.id_usuario = d.id_usuario
                                WHERE d.estado = 'pendiente' AND u.dia_vencimiento <= $dia_actual";
            $resultado_total_deuda = ejecutarConsulta($sql_total_deuda, $conn);

            if (isset($resultado_total_deuda['error'])) {
                $response['message'] = 'Error al obtener la deuda total: ' . $resultado_total_deuda['error'];
            } else {
                $response = [
                    'status' => 'success',
                    'deuda_total' => $resultado_total_deuda[0]['deuda_total'] !== null ? $resultado_total_deuda[0]['deuda_total'] : 0
                ];
            }
            echo json_encode($response);
            die();
        } else {
            error_log("Método HTTP incorrecto para la acción 'total_deuda'");
        }
        break;

    default:
        error_log("Acción no válida o no especificada: $action");
        echo json_encode($response);
        die();
}
?>
