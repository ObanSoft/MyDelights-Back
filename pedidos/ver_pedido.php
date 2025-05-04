<?php
include '../config/db.php';
header('Content-Type: application/json');

$response = array('success' => false, 'message' => '');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $headers = getallheaders();
    if (!isset($headers['Authorization']) || empty($headers['Authorization'])) {
        http_response_code(401); 
        $response['message'] = "El token de autenticación es requerido.";
        echo json_encode($response);
        exit;
    }

    $token = $headers['Authorization'];

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
    $usuario_id = $usuario['id'];

    $sql_pedidos = "SELECT * FROM pedidos WHERE usuario_id = ?";
    $stmt_pedidos = $conn->prepare($sql_pedidos);
    $stmt_pedidos->bind_param("i", $usuario_id);
    $stmt_pedidos->execute();
    $result_pedidos = $stmt_pedidos->get_result();

    $pedidos = [];
    while ($pedido = $result_pedidos->fetch_assoc()) {
        $sql_detalles = "SELECT nombre_plato, costo FROM detalles_pedido WHERE pedido_id = ?";
        $stmt_detalles = $conn->prepare($sql_detalles);
        $stmt_detalles->bind_param("i", $pedido['id']);
        $stmt_detalles->execute();
        $result_detalles = $stmt_detalles->get_result();

        $detalle_platos = [];
        while ($detalle = $result_detalles->fetch_assoc()) {
            $detalle_platos[] = $detalle;
        }

        $stmt_detalles->close();

        $pedidos[] = array(
            'pedido_id' => $pedido['id'],
            'estado' => $pedido['estado'],
            'total' => $pedido['total'],
            'creado_en' => $pedido['creado_en'],
            'platos' => $detalle_platos
        );
    }

    $stmt_pedidos->close();

    $response['success'] = true;
    $response['message'] = "Pedidos obtenidos correctamente.";
    $response['data'] = $pedidos;

} else {
    http_response_code(405); 
    $response['message'] = "Método no permitido.";
}

$conn->close();
echo json_encode($response);

?>