<?php
include '../../config/db.php';
header('Content-Type: application/json');


$response = array('success' => false, 'message' => '');

if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['id']) || empty($input['id'])) {
        $response['message'] = "El ID del pedido es requerido.";
        echo json_encode($response);
        exit;
    }

    if (!isset($input['estado']) || empty($input['estado'])) {
        $response['message'] = "El nuevo estado es requerido.";
        echo json_encode($response);
        exit;
    }

    $pedido_id = intval($input['id']); 
    $nuevo_estado = $input['estado']; 

    $estados_validos = ['Pendiente', 'Aceptado', 'Rechazado'];
    if (!in_array($nuevo_estado, $estados_validos)) {
        $response['message'] = "El estado proporcionado no es válido. Los estados válidos son: 'Pendiente', 'Aceptado', 'Rechazado'.";
        echo json_encode($response);
        exit;
    }

    $sql_update = "UPDATE pedidos SET estado = ? WHERE id = ?";
    $stmt_update = $conn->prepare($sql_update);

    if (!$stmt_update) {
        $response['message'] = "Error al preparar la consulta: " . $conn->error;
        echo json_encode($response);
        exit;
    }

    $stmt_update->bind_param("si", $nuevo_estado, $pedido_id);
    if ($stmt_update->execute()) {
        if ($stmt_update->affected_rows > 0) {
            $response['success'] = true;
            $response['message'] = "El estado del pedido se actualizó correctamente.";
        } else {
            $response['message'] = "No se encontró el pedido con el ID proporcionado.";
        }
    } else {
        $response['message'] = "Error al actualizar el estado: " . $stmt_update->error;
    }

    $stmt_update->close();
} else {
    http_response_code(405); 
    $response['message'] = "Método no permitido.";
}

$conn->close();
echo json_encode($response);

?>