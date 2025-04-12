<?php
header("Content-Type: application/json");
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    try {
        $stmt = $conn->prepare("DELETE FROM cotizaciones");

        if (!$stmt) {
            http_response_code(500);
            echo json_encode(['error' => 'Error al preparar la consulta.', 'details' => $conn->error]);
            exit;
        }

        if ($stmt->execute()) {
            http_response_code(200); 
            echo json_encode(['success' => 'Todas las cotizaciones han sido eliminadas con éxito.']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Error al ejecutar la consulta.', 'details' => $stmt->error]);
        }

        $stmt->close();
        $conn->close();
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Error inesperado al eliminar las cotizaciones.', 'details' => $e->getMessage()]);
    }
} else {
    http_response_code(405); 
    echo json_encode(['error' => 'Método no permitido.']);
}
?>