<?php
header("Content-Type: application/json");
require 'db.php';

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        if (!$conn) {
            echo json_encode(['error' => 'Error al conectar con la base de datos']);
            exit;
        }

        // Consulta para obtener los pedidos
        $sql = "SELECT id_pedido, total, fecha_creacion, estado FROM pedidos";
        $result = $conn->query($sql);

        if (!$result) {
            echo json_encode(['error' => 'Error en la consulta SQL: ' . $conn->error]);
            exit;
        }

        $pedidos = [];
        while ($row = $result->fetch_assoc()) {
            $pedidos[] = [
                'id_pedido' => $row['id_pedido'],
                'total' => $row['total'],
                'fecha_creacion' => $row['fecha_creacion'],
                'estado' => $row['estado']
            ];
        }

        echo json_encode($pedidos);
    } elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
        $input = json_decode(file_get_contents('php://input'), true);

        if (!isset($input['id_pedido'], $input['estado']) || !in_array($input['estado'], ['pendiente', 'confirmado', 'rechazado'])) {
            echo json_encode(['error' => 'Datos inválidos o estado no permitido']);
            exit;
        }

        $id_pedido = $input['id_pedido'];
        $estado = $input['estado'];

        $stmt = $conn->prepare("UPDATE pedidos SET estado = ? WHERE id_pedido = ?");
        $stmt->bind_param("si", $estado, $id_pedido);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            echo json_encode(['success' => true, 'message' => 'Estado actualizado correctamente']);
        } else {
            echo json_encode(['error' => 'No se encontró el pedido o no se pudo actualizar']);
        }

        $stmt->close();
    } else {
        echo json_encode(['error' => 'Método de solicitud no válido']);
    }
} catch (Exception $e) {
    echo json_encode(['error' => 'Error inesperado: ' . $e->getMessage()]);
}
?>