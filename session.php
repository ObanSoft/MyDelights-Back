<?php
session_start();
header('Content-Type: application/json');

if (isset($_SESSION['user_id'])) {
    $response = [
        'status' => 'success',
        'user_name' => $_SESSION['user_name'],
        'user_role' => $_SESSION['user_role']
    ];
} else {
    http_response_code(401);
    $response = [
        'status' => 'error',
        'message' => 'Usuario no autenticado'
    ];
}

echo json_encode($response);
?>