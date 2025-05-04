<?php
include '../config/db.php';
header('Content-Type: application/json');

$response = array('success' => false, 'message' => '');

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['cedula'])) {
        $response['message'] = "El número de cédula es requerido para eliminar al cliente.";
        echo json_encode($response);
        exit;
    }

    $cedula = $input['cedula'];

    if (!preg_match('/^\d{8,}$/', $cedula)) {
        $response['message'] = "La cédula debe contener solo números y tener al menos 8 dígitos.";
        echo json_encode($response);
        exit;
    }

    $sql_verificar = "SELECT * FROM usuarios WHERE cedula = '$cedula'";
    $result = $conn->query($sql_verificar);

    if ($result->num_rows > 0) {
        $sql_eliminar = "DELETE FROM usuarios WHERE cedula = '$cedula'";
        if ($conn->query($sql_eliminar) === TRUE) {
            $response['success'] = true;
            $response['message'] = "Cliente eliminado correctamente.";
        } else {
            $response['message'] = "Error al eliminar el cliente: " . $conn->error;
        }
    } else {
        $response['message'] = "No se encontró ningún cliente con la cédula proporcionada.";
    }
} else {
    http_response_code(405); 
    $response['message'] = "Método no permitido.";
}

echo json_encode($response);
$conn->close();
?>