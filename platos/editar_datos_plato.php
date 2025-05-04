<?php
include '../config/db.php';         
header('Content-Type: application/json');

$response = array('success' => false, 'message' => '');

if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['nombre']) || empty($input['nombre'])) {
        $response['message'] = "El nombre del plato es requerido para identificarlo.";
        echo json_encode($response);
        exit;
    }

    $nombre = $input['nombre'];

    $updates = [];
    if (isset($input['tipo'])) $updates[] = "tipo = '" . $conn->real_escape_string($input['tipo']) . "'";
    if (isset($input['descripcion'])) $updates[] = "descripcion = '" . $conn->real_escape_string($input['descripcion']) . "'";
    if (isset($input['ingredientes'])) $updates[] = "ingredientes = '" . $conn->real_escape_string($input['ingredientes']) . "'";
    if (isset($input['costo'])) $updates[] = "costo = " . $conn->real_escape_string($input['costo']);
    if (isset($input['imagen_url'])) $updates[] = "imagen_url = '" . $conn->real_escape_string($input['imagen_url']) . "'";

    if (empty($updates)) {
        $response['message'] = "No se enviaron campos para actualizar.";
        echo json_encode($response);
        exit;
    }

    $updates_sql = implode(", ", $updates);

    $sql = "UPDATE platos SET $updates_sql WHERE nombre = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $nombre);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            $response['success'] = true;
            $response['message'] = "Plato actualizado correctamente.";
        } else {
            $response['message'] = "No se encontró ningún plato con el nombre proporcionado.";
        }
    } else {
        $response['message'] = "Error al actualizar el plato: " . $stmt->error;
    }

    $stmt->close();
} else {
    http_response_code(405); 
    $response['message'] = "Método no permitido.";
}

$conn->close();
echo json_encode($response);
?>