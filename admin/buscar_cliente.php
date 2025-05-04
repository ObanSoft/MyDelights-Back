<?php

include '../config/db.php';
header('Content-Type: application/json');

function validar_cedula($cedula) {
    if (!preg_match('/^\d{8,}$/', $cedula)) {
        return "La cédula debe contener solo números y tener al menos 8 dígitos.";
    }
    return true;
}

$response = array('success' => false, 'message' => '');

            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (!isset($_GET['cedula'])) {
        $response['message'] = "El número de cédula es requerido.";
        echo json_encode($response);
        exit;
    }

    $cedula = $_GET['cedula'];

    $resultado_cedula = validar_cedula($cedula);
    if ($resultado_cedula !== true) {
        $response['message'] = $resultado_cedula;
        echo json_encode($response);
        exit;
    }

    $sql = "SELECT nombre, email, cedula, sexo, fecha_nacimiento, direccion, telefono, tipo_usuario, creado_en 
            FROM usuarios 
            WHERE cedula = '$cedula'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $cliente = $result->fetch_assoc();
        $response['success'] = true;
        $response['message'] = "Cliente encontrado.";
        $response['data'] = $cliente; 
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