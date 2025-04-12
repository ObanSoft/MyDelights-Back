<?php
header("Content-Type: application/json");
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $input = json_decode(file_get_contents('php://input'), true);

        if (!isset($input['cotizaciones']) || count($input['cotizaciones']) === 0) {
            http_response_code(400);
            echo json_encode(['error' => 'Debe haber al menos una cotización para generar el pedido.']);
            exit;
        }

        $total = 0;
        foreach ($input['cotizaciones'] as $cotizacion) {
            // Validar el formato de entrada
            if (!isset($cotizacion['id_cotizacion']) || !isset($cotizacion['cantidad'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Cada cotización debe tener un id_cotizacion y una cantidad.']);
                exit;
            }

            $cotizacion_id = $cotizacion['id_cotizacion'];
            $cantidad_solicitada = $cotizacion['cantidad'];

            // Obtener información de la cotización (incluyendo id del producto)
            $stmt = $conn->prepare("SELECT id_producto, precio FROM cotizaciones WHERE id_cotizacion = ?");
            $stmt->bind_param("i", $cotizacion_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 0) {
                http_response_code(404);
                echo json_encode(['error' => 'Cotización no encontrada: ' . $cotizacion_id]);
                exit;
            }

            $cotizacion_data = $result->fetch_assoc();
            $id_producto = $cotizacion_data['id_producto'];
            $precio = $cotizacion_data['precio'];

            // Obtener información del producto
            $stmt_producto = $conn->prepare("SELECT cantidad FROM productos WHERE id = ?");
            $stmt_producto->bind_param("i", $id_producto);
            $stmt_producto->execute();
            $result_producto = $stmt_producto->get_result();

            if ($result_producto->num_rows === 0) {
                http_response_code(404);
                echo json_encode(['error' => 'Producto no encontrado para la cotización: ' . $cotizacion_id]);
                exit;
            }

            $producto_data = $result_producto->fetch_assoc();
            $cantidad_disponible = $producto_data['cantidad'];

            // Verificar si hay suficiente cantidad disponible
            if ($cantidad_solicitada > $cantidad_disponible) {
                http_response_code(400);
                echo json_encode(['error' => 'Cantidad solicitada excede la cantidad disponible para el producto: ' . $id_producto]);
                exit;
            }

            // Calcular el total acumulado
            $total += $precio * $cantidad_solicitada;

            // Descontar la cantidad solicitada del producto
            $stmt_update = $conn->prepare("UPDATE productos SET cantidad = cantidad - ? WHERE id = ?");
            if (!$stmt_update) {
                http_response_code(500);
                echo json_encode(['error' => 'Error al preparar la consulta de actualización del producto.']);
                exit;
            }

            $stmt_update->bind_param("ii", $cantidad_solicitada, $id_producto);
            if (!$stmt_update->execute()) {
                http_response_code(500);
                echo json_encode(['error' => 'Error al actualizar la cantidad del producto: ' . $id_producto]);
                exit;
            }

            $stmt_update->close();
        }

        // Registrar el pedido en la tabla pedidos
        $stmt = $conn->prepare("INSERT INTO pedidos (total) VALUES (?)");
        if (!$stmt) {
            http_response_code(500);
            echo json_encode(['error' => 'Error al preparar la consulta de pedido.']);
            exit;
        }

        $stmt->bind_param("d", $total);

        if ($stmt->execute()) {
            $pedido_id = $stmt->insert_id;

            http_response_code(201);
            echo json_encode([
                'success' => 'Pedido creado con éxito.',
                'id_pedido' => $pedido_id,
                'total' => $total
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Error al registrar el pedido.']);
        }

        $stmt->close();
        $conn->close();
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Error inesperado: ' . $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Invalid request method']);
}
?>