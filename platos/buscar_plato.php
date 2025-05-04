<?php
include '../config/db.php';
header('Content-Type: application/json');

$response = array('success' => false, 'message' => '');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (!isset($_GET['nombre']) || empty($_GET['nombre'])) {
        $response['message'] = "El nombre del plato es requerido.";
        echo json_encode($response);
        exit;
    }

    $nombre = $_GET['nombre'];

    $sql = "SELECT * FROM platos WHERE nombre = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $nombre);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $plato = $result->fetch_assoc();
        $response['success'] = true;
        $response['message'] = "Plato encontrado.";
        $response['data'] = $plato;
    } else {
        $response['message'] = "No se encontró ningún plato con el nombre proporcionado.";
    }
    $stmt->close();
} else {
    http_response_code(405); 
    $response['message'] = "Método no permitido.";
}

$conn->close();
echo json_encode($response);
?>