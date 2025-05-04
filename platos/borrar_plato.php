<?php

include '../config/db.php';
header('Content-Type: application/json');

$response = array('success' => false, 'message' => '');

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['nombre']) || empty($input['nombre'])) {
        $response['message'] = "El nombre del plato es requerido para eliminarlo.";
        echo json_encode($response);
        exit;
    }

    $nombre = $input['nombre'];

    $sql_verificar = "SELECT * FROM platos WHERE nombre = ?";
    $stmt_verificar = $conn->prepare($sql_verificar);
    $stmt_verificar->bind_param("s", $nombre);
    $stmt_verificar->execute();
    $result = $stmt_verificar->get_result();

    if ($result->num_rows > 0) {
        $sql_eliminar = "DELETE FROM platos WHERE nombre = ?";
        $stmt_eliminar = $conn->prepare($sql_eliminar);
        $stmt_eliminar->bind_param("s", $nombre);

        if ($stmt_eliminar->execute()) {
            $response['success'] = true;
            $response['message'] = "Plato eliminado correctamente.";
        } else {
            $response['message'] = "Error al eliminar el plato: " . $stmt_eliminar->error;
        }

        $stmt_eliminar->close();
    } else {
        $response['message'] = "No se encontró ningún plato con el nombre proporcionado.";
    }

    $stmt_verificar->close();
} else {
    http_response_code(405); 
    $response['message'] = "Método no permitido.";
}

$conn->close();
echo json_encode($response);
?>