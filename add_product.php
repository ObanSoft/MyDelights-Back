<?php
include 'db.php';
session_start();

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['message' => 'No autorizado. Por favor, inicie sesión.']);
    exit();
}

if ($_SESSION['user_role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['message' => 'Acceso denegado. Solo los administradores pueden agregar productos.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($data['nombre']) || !isset($data['precio']) || !isset($data['cantidad'])) {
        http_response_code(400);
        echo json_encode(['message' => 'Faltan datos requeridos: nombre, precio o cantidad.']);
        exit();
    }

    $nombre = $data['nombre'];
    $descripcion = isset($data['descripcion']) ? $data['descripcion'] : null;
    $precio = $data['precio'];
    $cantidad = $data['cantidad'];

    $stmt = $conn->prepare("INSERT INTO productos (nombre, descripcion, precio, cantidad) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssdi", $nombre, $descripcion, $precio, $cantidad);

    if ($stmt->execute()) {
        echo json_encode(['message' => 'Producto agregado exitosamente']);
    } else {
        http_response_code(500);
        echo json_encode(['message' => 'Error al agregar el producto']);
    }
    $stmt->close();
} else {
    http_response_code(405);
    echo json_encode(['message' => 'Método no permitido']);
}

$conn->close();
?>