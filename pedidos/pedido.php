<?php
include '../config/db.php';
header('Content-Type: application/json');

$response = array('success' => false, 'message' => '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['token']) || empty($input['token'])) {
        $response['message'] = "El token de autenticación es requerido.";
        echo json_encode($response);
        exit;
    }

    $token = $input['token'];

    $sql_token = "SELECT * FROM usuarios WHERE session_token = ?";
    $stmt_token = $conn->prepare($sql_token);
    $stmt_token->bind_param("s", $token);
    $stmt_token->execute();
    $result_token = $stmt_token->get_result();

    if ($result_token->num_rows === 0) {
        http_response_code(401); 
        $response['message'] = "Token inválido. Por favor, inicie sesión nuevamente.";
        echo json_encode($response);
        exit;
    }

    $usuario = $result_token->fetch_assoc();
    $usuario_nombre = $usuario['nombre'];
    $usuario_id = $usuario['id'];

    if (!isset($input['platos']) || !is_array($input['platos']) || empty($input['platos'])) {
        $response['message'] = "Debe proporcionar una lista de platos.";
        echo json_encode($response);
        exit;
    }

    $estado_pedido = isset($input['estado']) ? $input['estado'] : "Pendiente";
    if (!in_array($estado_pedido, ['Pendiente', 'Aceptado', 'Rechazado'])) {
        $response['message'] = "El estado del pedido no es válido. Debe ser 'Pendiente', 'Aceptado' o 'Rechazado'.";
        echo json_encode($response);
        exit;
    }

    $platos_seleccionados = $input['platos'];
    $total_pedido = 0;
    $detalle_platos = [];

    foreach ($platos_seleccionados as $plato_nombre) {
        $sql = "SELECT * FROM platos WHERE nombre = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $plato_nombre);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $plato = $result->fetch_assoc();
            $detalle_platos[] = array(
                'nombre' => $plato['nombre'],
                'costo' => $plato['costo']
            );
            $total_pedido += $plato['costo'];
        } else {
            $response['message'] = "El plato '$plato_nombre' no existe.";
            echo json_encode($response);
            exit;
        }

        $stmt->close();
    }

    $sql_pedido = "INSERT INTO pedidos (usuario_id, nombre_usuario, estado, total) VALUES (?, ?, ?, ?)";
    $stmt_pedido = $conn->prepare($sql_pedido);
    $stmt_pedido->bind_param("issd", $usuario_id, $usuario_nombre, $estado_pedido, $total_pedido);

    if ($stmt_pedido->execute()) {
        $pedido_id = $stmt_pedido->insert_id;

        foreach ($detalle_platos as $detalle) {
            $sql_detalle = "INSERT INTO detalles_pedido (pedido_id, nombre_plato, costo) VALUES (?, ?, ?)";
            $stmt_detalle = $conn->prepare($sql_detalle);
            $stmt_detalle->bind_param("isd", $pedido_id, $detalle['nombre'], $detalle['costo']);
            $stmt_detalle->execute();
            $stmt_detalle->close();
        }

        $response['success'] = true;
        $response['message'] = "Pedido creado correctamente.";
        $response['data'] = array(
            'pedido_id' => $pedido_id,
            'usuario_id' => $usuario_id,
            'nombre_usuario' => $usuario_nombre,
            'estado' => $estado_pedido,
            'total' => $total_pedido,
            'platos' => $detalle_platos
        );
    } else {
        $response['message'] = "Error al registrar el pedido: " . $stmt_pedido->error;
    }

    $stmt_pedido->close();
} else {
    http_response_code(405); 
    $response['message'] = "Método no permitido.";
}

$conn->close();
echo json_encode($response);

?>