<?php
include '../../config/db.php'; 

header('Content-Type: application/json');
$response = array('success' => false, 'message' => '');

if (!isset($conn)) {
    $response['message'] = "No se pudo establecer la conexión con la base de datos.";
    echo json_encode($response);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        $response['message'] = "El ID del pedido es requerido.";
        echo json_encode($response);
        exit;
    }

    $pedido_id = intval($_GET['id']); 

    $sql_pedido = "SELECT * FROM pedidos WHERE id = ?";
    $stmt_pedido = $conn->prepare($sql_pedido);

    if (!$stmt_pedido) {
        $response['message'] = "Error al preparar la consulta: " . $conn->error;
        echo json_encode($response);
        exit;
    }

    $stmt_pedido->bind_param("i", $pedido_id);
    $stmt_pedido->execute();
    $result_pedido = $stmt_pedido->get_result();

    if ($result_pedido->num_rows === 0) {
        $response['message'] = "El pedido no existe.";
        echo json_encode($response);
        exit;
    }

    $pedido = $result_pedido->fetch_assoc();

    $sql_detalles = "SELECT nombre_plato, costo FROM detalles_pedido WHERE pedido_id = ?";
    $stmt_detalles = $conn->prepare($sql_detalles);

    if (!$stmt_detalles) {
        $response['message'] = "Error al preparar la consulta de detalles: " . $conn->error;
        echo json_encode($response);
        exit;
    }

    $stmt_detalles->bind_param("i", $pedido_id);
    $stmt_detalles->execute();
    $result_detalles = $stmt_detalles->get_result();

    $detalle_platos = [];
    while ($detalle = $result_detalles->fetch_assoc()) {
        $detalle_platos[] = $detalle;
    }

    $stmt_detalles->close();

    $response['success'] = true;
    $response['message'] = "Pedido obtenido correctamente.";
    $response['data'] = array(
        'pedido_id' => $pedido['id'],
        'usuario_id' => $pedido['usuario_id'],
        'nombre_usuario' => $pedido['nombre_usuario'],
        'estado' => $pedido['estado'],
        'total' => $pedido['total'],
        'creado_en' => $pedido['creado_en'],
        'platos' => $detalle_platos
    );

} else {
    http_response_code(405); 
    $response['message'] = "Método no permitido.";
}

$conn->close();
echo json_encode($response);

?>