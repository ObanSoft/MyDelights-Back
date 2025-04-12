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

if ($method === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);
    $id_pedido = $data['id'];
    $productos = $data['productos'];
    $estado = isset($data['estado']) ? $data['estado'] : 'pendiente';
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
        echo json_encode(['message' => 'Acceso denegado. Solo el creador del pedido o un administrador pueden actualizar el pedido.']);
        exit();
    }

    $total = 0;
    foreach ($productos as $producto) {
        $id_producto = $producto['id'];
        $cantidad = $producto['cantidad'];

        $stmt_producto = $conn->prepare("SELECT precio FROM productos WHERE id = ?");
        $stmt_producto->bind_param("i", $id_producto);
        $stmt_producto->execute();
        $stmt_producto->bind_result($precio);
        $stmt_producto->fetch();
        $stmt_producto->close();

        $subtotal = $precio * $cantidad;
        $total += $subtotal;
    }

    $stmt = $conn->prepare("UPDATE pedidos SET total = ?, estado = ? WHERE id = ?");
    $stmt->bind_param("dsi", $total, $estado, $id_pedido);
    if ($stmt->execute()) {
        $stmt_delete = $conn->prepare("DELETE FROM pedidos_productos WHERE id_pedido = ?");
        $stmt_delete->bind_param("i", $id_pedido);
        $stmt_delete->execute();
        $stmt_delete->close();

        foreach ($productos as $producto) {
            $id_producto = $producto['id'];
            $cantidad = $producto['cantidad'];
            $estado_producto = $estado;

            $stmt_producto = $conn->prepare("INSERT INTO pedidos_productos (id_pedido, id_producto, cantidad, estado) VALUES (?, ?, ?, ?)");
            $stmt_producto->bind_param("iiis", $id_pedido, $id_producto, $cantidad, $estado_producto);
            $stmt_producto->execute();
            $stmt_producto->close();
        }
        echo json_encode(['message' => 'Pedido actualizado exitosamente']);
    } else {
        http_response_code(500);
        echo json_encode(['message' => 'Error al actualizar el pedido']);
    }
    $stmt->close();
} else {
    http_response_code(405);
    echo json_encode(['message' => 'Método no permitido']);
}

$conn->close();
?>