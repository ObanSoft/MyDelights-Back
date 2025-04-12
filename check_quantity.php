<?php
header("Content-Type: application/json");
require 'db.php'; 

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);

        if (!isset($input['product_id']) || !isset($input['requested_quantity'])) {
            echo json_encode(['error' => 'Missing parameters']);
            exit;
        }

        $product_id = intval($input['product_id']);
        $requested_quantity = intval($input['requested_quantity']);

        if (!$conn) {
            echo json_encode(['error' => 'Error al conectar con la base de datos']);
            exit;
        }

        $stmt = $conn->prepare("SELECT cantidad FROM productos WHERE id = ?");
        if (!$stmt) {
            echo json_encode(['error' => 'Error al preparar la consulta']);
            exit;
        }
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $product = $result->fetch_assoc();
            $available_quantity = intval($product['cantidad']);

            if ($requested_quantity > $available_quantity) {
                echo json_encode(['error' => 'Cantidad no disponible']);
            } else {
                echo json_encode(['success' => 'Cantidad disponible']);
            }
        } else {
            echo json_encode(['error' => 'Producto no encontrado']);
        }

        $stmt->close();
        $conn->close();
    } else {
        echo json_encode(['error' => 'Invalid request method']);
    }
} catch (Exception $e) {
    echo json_encode(['error' => 'Error inesperado', 'details' => $e->getMessage()]);
}
?>