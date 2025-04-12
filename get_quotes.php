<?php
header("Content-Type: application/json");
require 'db.php';

try {
    $stmt = $conn->prepare("
        SELECT 
            c.id_cotizacion, 
            c.id_producto, 
            c.precio, 
            c.cantidad, 
            c.creado_en, 
            p.nombre AS nombre_producto,
            p.descripcion
        FROM cotizaciones AS c
        INNER JOIN productos AS p ON c.id_producto = p.id
    ");

    if (!$stmt) {
        http_response_code(500);
        echo json_encode(['error' => 'Error en la preparación de la consulta.', 'details' => $conn->error]);
        exit;
    }

    if (!$stmt->execute()) {
        http_response_code(500);
        echo json_encode(['error' => 'Error al ejecutar la consulta.', 'details' => $stmt->error]);
        exit;
    }
    $result = $stmt->get_result();

    $quotes = [];
    while ($row = $result->fetch_assoc()) {
        $quotes[] = $row;
    }

    echo json_encode($quotes);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al obtener las cotizaciones.', 'details' => $e->getMessage()]);
}
?>