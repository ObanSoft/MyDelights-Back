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
    echo json_encode(['message' => 'Acceso denegado. Solo los administradores pueden actualizar productos.']);
    exit();
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);
    $id = $data['id'];
    $nombre = $data['nombre'];
    $descripcion = $data['descripcion'];
    $precio = $data['precio'];
    $cantidad = $data['cantidad']; // Nuevo campo: cantidad

    if (!$id || !$nombre || !$descripcion || !$precio || !$cantidad) {
        http_response_code(400);
        echo json_encode(['message' => 'Datos incompletos']);
        exit();
    }

    $stmt = $conn->prepare("UPDATE productos SET nombre = ?, descripcion = ?, precio = ?, cantidad = ? WHERE id = ?");
    $stmt->bind_param("ssdii", $nombre, $descripcion, $precio, $cantidad, $id); // Incluimos cantidad

    if ($stmt->execute()) {
        echo json_encode(['message' => 'Producto actualizado exitosamente']);
    } else {
        http_response_code(500);
        echo json_encode(['message' => 'Error al actualizar el producto']);
    }

    $stmt->close();
} else {
    http_response_code(405);
    echo json_encode(['message' => 'Método no permitido']);
}

$conn->close();
?>