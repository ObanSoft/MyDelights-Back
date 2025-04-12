<?php
include 'db.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['message' => 'No autorizado. Por favor, inicie sesión.']);
    exit();
}

if ($_SESSION['user_role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['message' => 'Acceso denegado. Solo los administradores pueden eliminar productos.']);
    exit();
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'DELETE') {
    $data = json_decode(file_get_contents('php://input'), true);
    $id = $data['id'];

    if (!$id) {
        http_response_code(400);
        echo json_encode(['message' => 'ID del producto no proporcionado']);
        exit();
    }

    $stmt = $conn->prepare("SELECT cantidad FROM productos WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($cantidad);
    $stmt->fetch();
    $stmt->close();

    if ($cantidad === null) {
        http_response_code(404);
        echo json_encode(['message' => 'Producto no encontrado']);
        exit();
    }

    // Eliminar el producto
    $stmt = $conn->prepare("DELETE FROM productos WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo json_encode(['message' => "Producto eliminado exitosamente. Cantidad eliminada: $cantidad"]);
    } else {
        http_response_code(500);
        echo json_encode(['message' => 'Error al eliminar el producto']);
    }

    $stmt->close();
} else {
    http_response_code(405);
    echo json_encode(['message' => 'Método no permitido']);
}

$conn->close();
?>