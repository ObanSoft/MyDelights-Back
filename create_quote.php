<?php
header("Content-Type: application/json");
require 'db.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Leer los datos de entrada
        $input = json_decode(file_get_contents('php://input'), true);

        // Validar los parámetros de entrada
        if (!isset($input['id_producto']) || !isset($input['cantidad'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing id_producto or cantidad parameter.']);
            exit;
        }

        // Obtener los parámetros de entrada
        $id_producto = intval($input['id_producto']);
        $cantidad = intval($input['cantidad']);
        $quote_id = isset($input['id_cotizacion']) ? $input['id_cotizacion'] : uniqid('COT_', true);

        // Validar que la cantidad sea mayor a 0
        if ($cantidad <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'La cantidad debe ser mayor a 0.']);
            exit;
        }

        // Comprobar si el producto existe y obtener su precio
        $stmt = $conn->prepare("SELECT precio FROM productos WHERE id = ?");
        $stmt->bind_param("i", $id_producto);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            http_response_code(404);
            echo json_encode(['error' => 'Producto no encontrado.']);
            exit;
        }

        $producto = $result->fetch_assoc();
        $precio = $producto['precio'];

        // Insertar en la tabla cotizaciones
        $stmt = $conn->prepare("INSERT INTO cotizaciones (id_cotizacion, id_producto, precio, cantidad) VALUES (?, ?, ?, ?)");
        if (!$stmt) {
            http_response_code(500);
            echo json_encode(['error' => 'Error al preparar la consulta.']);
            exit;
        }

        $stmt->bind_param("sidi", $quote_id, $id_producto, $precio, $cantidad);

        if ($stmt->execute()) {
            http_response_code(201); // Success
            echo json_encode([
                'success' => 'Producto agregado a la cotización con éxito.',
                'id_cotizacion' => $quote_id,
                'precio' => $precio,
                'cantidad' => $cantidad
            ]);
        } else {
            http_response_code(500); // Server Error
            error_log('Error al registrar la cotización: ' . $stmt->error);
            echo json_encode(['error' => 'Error al registrar el producto en la cotización.']);
        }

        $stmt->close();
        $conn->close();
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Error inesperado: ' . $e->getMessage()]);
    }
} else {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['error' => 'Invalid request method']);
}
?>