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

// Generar deudas automáticas
function generarDeudasAutomaticas($conn) {
    // Obtener usuarios con planes y deudas
    $sql_usuarios = "SELECT u.id_usuario, u.fecha_registro, u.dia_vencimiento, p.precio AS monto_cuota
                     FROM usuarios u
                     INNER JOIN planes p ON u.plan = p.nombre";
    $usuarios = ejecutarConsulta($sql_usuarios, $conn);
    if (isset($usuarios['error'])) {
        error_log("Error al obtener usuarios: " . $usuarios['error']);
        return;
    }

    $fecha_actual = date('Y-m-d');
    $mes_actual = date('Y-m');

    foreach ($usuarios as $usuario) {
        $id_usuario = $usuario['id_usuario'];
        $fecha_registro = $usuario['fecha_registro'];
        $dia_vencimiento = $usuario['dia_vencimiento'];
        $monto_cuota = $usuario['monto_cuota'];

        // Calcular el primer mes de vencimiento (mes siguiente al registro)
        $mes_inicio = date('Y-m', strtotime("$fecha_registro +1 month"));
        $mes_vencimiento = $mes_inicio;

        // Revisar historial de pagos para omitir meses ya pagados
        $sql_ultimo_pago = "SELECT MAX(mes_pagado) AS ultimo_mes_pagado 
                            FROM historial_pagos 
                            WHERE id_usuario = $id_usuario";
        $ultimo_pago = ejecutarConsulta($sql_ultimo_pago, $conn);
        if (!empty($ultimo_pago[0]['ultimo_mes_pagado'])) {
            $mes_vencimiento = date('Y-m', strtotime($ultimo_pago[0]['ultimo_mes_pagado'] . ' +1 month'));
        }

        // Generar deudas hasta el mes actual
        while ($mes_vencimiento <= $mes_actual) {
            $fecha_vencimiento = date('Y-m-d', strtotime("$mes_vencimiento-$dia_vencimiento"));

            // Verificar si ya existe una deuda para esta fecha
            $sql_verificar = "SELECT COUNT(*) AS existe 
                              FROM deudas 
                              WHERE id_usuario = $id_usuario 
                              AND fecha_vencimiento = '$fecha_vencimiento'";
            $existe = ejecutarConsulta($sql_verificar, $conn)[0]['existe'];

            if (!$existe) {
                // Insertar la deuda
                $sql_insertar = "INSERT INTO deudas (id_usuario, monto, fecha_generacion, fecha_vencimiento, estado) 
                                 VALUES ($id_usuario, $monto_cuota, '$fecha_actual', '$fecha_vencimiento', 'pendiente')";
                $conn->query($sql_insertar);
            }

            // Avanzar al siguiente mes
            $mes_vencimiento = date('Y-m', strtotime("$mes_vencimiento +1 month"));
        }
    }
}

// Lógica de las acciones disponibles
switch ($action) {
    case 'totales':
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            error_log("Ejecutando acción 'totales'");
    
            $response = [
                'status' => 'success',
                'total_usuarios' => 0,
                'deudas_manuales' => 0,
                'deudas_cuotas' => 0
            ];
    
            // Total de usuarios
            $sql_usuarios = "SELECT COUNT(*) as total FROM usuarios";
            $resultado_usuarios = ejecutarConsulta($sql_usuarios, $conn);
            if (isset($resultado_usuarios[0])) {
                $response['total_usuarios'] = $resultado_usuarios[0]['total'];
            } else {
                error_log("Error al obtener el total de usuarios: " . json_encode($resultado_usuarios));
            }
    
            // Total de usuarios con deudas manuales
            $sql_deudas_manuales = "SELECT COUNT(*) as total FROM usuarios WHERE deuda > 0";
            $resultado_manuales = ejecutarConsulta($sql_deudas_manuales, $conn);
            if (isset($resultado_manuales[0])) {
                $response['deudas_manuales'] = $resultado_manuales[0]['total'];
            } else {
                error_log("Error al obtener el total de deudas manuales: " . json_encode($resultado_manuales));
            }
    
            // Total de usuarios con deudas de cuotas vencidas
            $sql_deudas_cuotas = "SELECT COUNT(DISTINCT d.id_usuario) as total 
                                  FROM deudas d 
                                  WHERE d.estado = 'pendiente'";
            $resultado_cuotas = ejecutarConsulta($sql_deudas_cuotas, $conn);
            if (isset($resultado_cuotas[0])) {
                $response['deudas_cuotas'] = $resultado_cuotas[0]['total'];
            } else {
                error_log("Error al obtener el total de deudas de cuotas: " . json_encode($resultado_cuotas));
            }
    
            echo json_encode($response);
            die();
        }
    break;
    case 'usuarios':
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            generarDeudasAutomaticas($conn);

            $sql_usuarios = "SELECT u.*, p.precio AS monto_cuota
                             FROM usuarios u
                             INNER JOIN planes p ON u.plan = p.nombre";
            $usuarios = ejecutarConsulta($sql_usuarios, $conn);
            if (isset($usuarios['error'])) {
                $response['message'] = 'Error al obtener usuarios: ' . $usuarios['error'];
                echo json_encode($response);
                die();
            }

            echo json_encode(['status' => 'success', 'usuarios' => $usuarios]);
            die();
        }
        break;
        case 'deudores':
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                $tipo = isset($_GET['tipo']) ? $_GET['tipo'] : 'todas';
        
                // Consulta base para deudores
                $sql_base = "SELECT u.id_usuario, u.nombre, u.apellido, 
                                    COALESCE(u.telefono, 'No disponible') AS telefono, 
                                    COALESCE(u.email, 'No disponible') AS email, 
                                    u.deuda AS deuda_manual, 
                                    d.id_deuda, d.monto, d.fecha_generacion, d.fecha_vencimiento, d.estado
                             FROM usuarios u
                             LEFT JOIN deudas d ON u.id_usuario = d.id_usuario AND d.estado = 'pendiente'";
        
                if ($tipo === 'manuales') {
                    // Filtro para deudas manuales
                    $sql_deudores = "$sql_base WHERE u.deuda IS NOT NULL AND u.deuda > 0";
                } elseif ($tipo === 'automaticas') {
                    // Filtro para deudas automáticas
                    $sql_deudores = "$sql_base WHERE d.id_deuda IS NOT NULL";
                } else {
                    // Todos los deudores
                    $sql_deudores = "$sql_base WHERE (u.deuda > 0 OR d.id_deuda IS NOT NULL)";
                }
        
                // Ejecutar consulta
                $result = ejecutarConsulta($sql_deudores, $conn);
        
                if (isset($result['error'])) {
                    echo json_encode(['status' => 'error', 'message' => $result['error']]);
                    die();
                }
        
                $response = ['status' => 'success', 'deudores' => []];
                $usuarios = [];
        
                foreach ($result as $row) {
                    $id_usuario = $row['id_usuario'];
                    if (!isset($usuarios[$id_usuario])) {
                        $usuarios[$id_usuario] = [
                            'id_usuario' => $id_usuario,
                            'nombre' => $row['nombre'],
                            'apellido' => $row['apellido'],
                            'telefono' => $row['telefono'],
                            'email' => $row['email'],
                            'deudas' => [] // Siempre inicializa como un array vacío
                        ];
                    }
        
                    // Agregar deuda si existe
                    if ($row['id_deuda'] !== null) {
                        $usuarios[$id_usuario]['deudas'][] = [
                            'id_deuda' => $row['id_deuda'],
                            'monto' => $row['monto'],
                            'fecha_generacion' => $row['fecha_generacion'],
                            'fecha_vencimiento' => $row['fecha_vencimiento'],
                            'estado' => $row['estado']
                        ];
                    }
        
                    // Agregar deuda manual como deuda especial
                    if ($row['deuda_manual'] > 0 && empty($usuarios[$id_usuario]['deudas'])) {
                        $usuarios[$id_usuario]['deudas'][] = [
                            'id_deuda' => 'manual',
                            'monto' => $row['deuda_manual'],
                            'fecha_generacion' => '--',
                            'fecha_vencimiento' => '--',
                            'estado' => 'pendiente'
                        ];
                    }
                }
        
                $response['deudores'] = array_values($usuarios);
                echo json_encode($response);
                die();
            }
                 

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
                $nombre = $conn->real_escape_string($_POST['nombre']);
                $apellido = $conn->real_escape_string($_POST['apellido']);
                $telefono = $conn->real_escape_string($_POST['telefono']);
                $email = $conn->real_escape_string($_POST['email']);
                $plan = $conn->real_escape_string($_POST['plan']);
                $dia_vencimiento = intval($_POST['dia_vencimiento']);
                $deuda = floatval($_POST['deuda']);
        
                // Verificar si el email ya está registrado
                $sql_verificar_email = "SELECT COUNT(*) as total FROM usuarios WHERE email = '$email'";
                $resultado_verificar = ejecutarConsulta($sql_verificar_email, $conn);
        
                if (isset($resultado_verificar[0]) && intval($resultado_verificar[0]['total']) > 0) {
                    // Si el correo ya existe, devolver mensaje de error y no continuar
                    $response = [
                        'status' => 'error',
                        'message' => 'El correo electrónico ya está registrado. Por favor, usa otro.'
                    ];
                    echo json_encode($response);
                    die();
                }
        
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
        
                // Insertar el nuevo usuario
                $sql = "INSERT INTO usuarios (nombre, apellido, telefono, email, plan, dia_vencimiento, deuda, foto) 
                        VALUES ('$nombre', '$apellido', '$telefono', '$email', '$plan', $dia_vencimiento, $deuda, '$foto')";
        
                if ($conn->query($sql) === TRUE) {
                    $response = [
                        'status' => 'success',
                        'message' => 'Usuario añadido correctamente'
                    ];
                } else {
                    error_log("Error al añadir usuario: " . $conn->error);
                    $response['status'] = 'error';
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
                $id_deuda = isset($data['id_deuda']) ? $data['id_deuda'] : null;
                $id_usuario = isset($data['id_usuario']) ? intval($data['id_usuario']) : null;
        
                // Validar que se haya proporcionado el ID del usuario
                if ($id_usuario === null) {
                    $response['message'] = "ID de usuario no proporcionado";
                    echo json_encode($response);
                    die();
                }
        
                // Caso: Deuda Manual
                if ($id_deuda === null || $id_deuda === 'manual') {
                    $sql_actualizar_deuda_manual = "UPDATE usuarios SET deuda = 0 WHERE id_usuario = $id_usuario";
        
                    if ($conn->query($sql_actualizar_deuda_manual) === TRUE) {
                        $response = [
                            'status' => 'success',
                            'message' => 'Deuda manual marcada como pagada correctamente'
                        ];
                    } else {
                        $response['message'] = 'Error al actualizar deuda manual: ' . $conn->error;
                        error_log($response['message']);
                    }
                } 
                // Caso: Deuda Automática
                elseif ($id_deuda !== null) {
                    $fecha_pago = date('Y-m-d');
                    $sql_marcar_pagada = "UPDATE deudas 
                                          SET estado = 'pagada', fecha_pago = '$fecha_pago' 
                                          WHERE id_deuda = $id_deuda AND estado = 'pendiente'";
        
                    if ($conn->query($sql_marcar_pagada) === TRUE) {
                        $response = [
                            'status' => 'success',
                            'message' => 'Deuda automática marcada como pagada correctamente'
                        ];
                    } else {
                        $response['message'] = 'Error al actualizar deuda automática: ' . $conn->error;
                        error_log($response['message']);
                    }
                } else {
                    $response['message'] = "ID de deuda no proporcionado o inválido";
                    error_log($response['message']);
                }
        
                echo json_encode($response);
                die();
        }
    break;
        
        
         

    case 'total_deuda':
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $sql_total_deudas_mensuales = "SELECT SUM(monto) AS total_mensual FROM deudas WHERE estado = 'pendiente'";
            $total_mensual = ejecutarConsulta($sql_total_deudas_mensuales, $conn);
    
            $sql_total_deudas_manuales = "SELECT SUM(deuda) AS total_manual FROM usuarios WHERE deuda > 0";
            $total_manual = ejecutarConsulta($sql_total_deudas_manuales, $conn);
    
            $total_mensual = $total_mensual[0]['total_mensual'] ?? 0;
            $total_manual = $total_manual[0]['total_manual'] ?? 0;
    
            echo json_encode([
                'status' => 'success',
                'deuda_total' => $total_mensual + $total_manual,
                'deuda_mensual' => $total_mensual,
                'deuda_manual' => $total_manual,
            ]);
            die();
        }
        break;
    
    case 'buscar':
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                $termino = isset($_GET['termino']) ? trim($_GET['termino']) : '';
        
                if ($termino === '') {
                    echo json_encode(['status' => 'error', 'message' => 'Término de búsqueda vacío']);
                    die();
                }
        
                $sql_buscar = "SELECT * FROM usuarios 
                               WHERE nombre LIKE '%$termino%' 
                                  OR email LIKE '%$termino%' 
                                  OR telefono LIKE '%$termino%'";
                $resultados = ejecutarConsulta($sql_buscar, $conn);
        
                if (isset($resultados['error'])) {
                    echo json_encode(['status' => 'error', 'message' => 'Error al buscar usuarios: ' . $resultados['error']]);
                } else {
                    echo json_encode(['status' => 'success', 'usuarios' => $resultados]);
                }
                die();
        }
    break;
    default:
        error_log("Acción no válida o no especificada: $action");
        echo json_encode($response);
        die();
}
?>
