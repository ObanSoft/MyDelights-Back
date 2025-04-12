<?php
include 'db.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $result = $conn->query("SELECT * FROM productos");
    if ($result) {
        $productos = $result->fetch_all(MYSQLI_ASSOC);
        echo json_encode($productos);
    } else {
        http_response_code(500);
        echo json_encode(['message' => 'Error al obtener los productos']);
    }
} else {
    http_response_code(405);
    echo json_encode(['message' => 'Método no permitido']);
}

$conn->close();
?>