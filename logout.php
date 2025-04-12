<?php
session_start();
session_unset();
session_destroy();

$response = [
    'status' => 'success',
    'message' => 'Sesión cerrada exitosamente'
];

header('Content-Type: application/json');
echo json_encode($response);
?>