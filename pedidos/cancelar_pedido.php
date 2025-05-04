<?php
include '../config/db.php'; 
header('Content-Type: application/json');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$response = array('success' => false, 'message' => '');

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['id']) || empty($input['id'])) {
        $response['message'] = "El ID del pedido es requerido.";
        echo json_encode($response);
        exit;
    }

    $pedido_id = intval($input['id']); 

    $sql_check = "SELECT id FROM pedidos WHERE id = ?";
    $stmt_check = $conn->prepare($sql_check);

    if (!$stmt_check) {
        $response['message'] = "Error al preparar la consulta: " . $conn->error;
        echo json_encode($response);
        exit;
    }

    $stmt_check->bind_param("i", $pedido_id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows === 0) {
        $response['message'] = "El pedido no existe.";
        echo json_encode($response);
        $stmt_check->close();
        exit;
    }

    $stmt_check->close();

    $sql_delete = "DELETE FROM pedidos WHERE id = ?";
    $stmt_delete = $conn->prepare($sql_delete);

    if (!$stmt_delete) {
        $response['message'] = "Error al preparar la consulta de eliminación: " . $conn->error;
        echo json_encode($response);
        exit;
    }

    $stmt_delete->bind_param("i", $pedido_id);
    if ($stmt_delete->execute()) {
        if ($stmt_delete->affected_rows > 0) {
            $response['success'] = true;
            $response['message'] = "El pedido se canceló correctamente.";
        } else {
            $response['message'] = "No se pudo cancelar el pedido. Intente nuevamente.";
        }
    } else {
        $response['message'] = "Error al cancelar el pedido: " . $stmt_delete->error;
    }

    $stmt_delete->close();
} else {
    http_response_code(405);
    $response['message'] = "Método no permitido.";
}

$conn->close();
echo json_encode($response);

?>