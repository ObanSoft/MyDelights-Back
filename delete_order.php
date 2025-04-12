<?php
include 'db.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['message' => 'No autorizado. Por favor, inicie sesión.']);
    exit();
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'DELETE') {
    $data = json_decode(file_get_contents('php://input'), true);
    $id_pedido = $data['id'];
    $id_usuario = $_SESSION['user_id'];
    $user_role = $_SESSION['user_role'];

    $stmt_check = $conn->prepare("SELECT id_usuario FROM pedidos WHERE id = ?");
    $stmt_check->bind_param("i", $id_pedido);
    $stmt_check->execute();
    $stmt_check->bind_result($pedido_user_id);
    $stmt_check->fetch();
    $stmt_check->close();

    if ($pedido_user_id != $id_usuario && $user_role != 'admin') {
        http_response_code(403);
        echo json_encode(['message' => 'Acceso denegado. Solo el creador del pedido o un administrador pueden cancelar el pedido.']);
        exit();
    }

    $stmt = $conn->prepare("UPDATE pedidos SET estado = 'cancelado' WHERE id = ?");
    if (!$stmt) {
        http_response_code(500);
        echo json_encode(['message' => 'Error en la preparación de la consulta: ' . $conn->error]);
        exit();
    }

    $stmt->bind_param("i", $id_pedido);
    if ($stmt->execute()) {
        $stmt_update_productos = $conn->prepare("UPDATE pedidos_productos SET estado = 'cancelado' WHERE id_pedido = ?");
        $stmt_update_productos->bind_param("i", $id_pedido);
        $stmt_update_productos->execute();
        $stmt_update_productos->close();

        echo json_encode(['message' => 'Pedido cancelado exitosamente']);
    } else {
        http_response_code(500);
        echo json_encode(['message' => 'Error al cancelar el pedido: ' . $stmt->error]);
    }
    $stmt->close();
} else {
    http_response_code(405);
    echo json_encode(['message' => 'Método no permitido']);
}

$conn->close();
?>