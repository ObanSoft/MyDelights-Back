<?php

include '../config/db.php';
header('Content-Type: application/json');

$response = array('success' => false, 'message' => '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['nombre']) || empty($input['nombre'])) {
        $response['message'] = "El nombre del plato es requerido.";
        echo json_encode($response);
        exit;
    }

    $nombre = $input['nombre'];
    $tipo = isset($input['tipo']) ? $input['tipo'] : null;
    $descripcion = isset($input['descripcion']) ? $input['descripcion'] : null;
    $ingredientes = isset($input['ingredientes']) ? $input['ingredientes'] : null;
    $costo = isset($input['costo']) ? $input['costo'] : null;
    $imagen_url = isset($input['imagen_url']) ? $input['imagen_url'] : null;

    $sql_verificar = "SELECT * FROM platos WHERE nombre = ?";
    $stmt_verificar = $conn->prepare($sql_verificar);
    $stmt_verificar->bind_param("s", $nombre);
    $stmt_verificar->execute();
    $result = $stmt_verificar->get_result();

    if ($result->num_rows > 0) {
        $response['message'] = "El plato con el nombre '$nombre' ya existe.";
        echo json_encode($response);
        exit;
    }

    $stmt_verificar->close();

    $sql_insertar = "INSERT INTO platos (nombre, tipo, descripcion, ingredientes, costo, imagen_url) 
                    VALUES (?, ?, ?, ?, ?, ?)";
    $stmt_insertar = $conn->prepare($sql_insertar);
    $stmt_insertar->bind_param("ssssds", $nombre, $tipo, $descripcion, $ingredientes, $costo, $imagen_url);

    if ($stmt_insertar->execute()) {
        $response['success'] = true;
        $response['message'] = "Plato agregado correctamente.";
    } else {
        $response['message'] = "Error al agregar el plato: " . $stmt_insertar->error;
    }

    $stmt_insertar->close();
} else {
    http_response_code(405);
    $response['message'] = "Método no permitido.";
}

$conn->close();
echo json_encode($response);
?>