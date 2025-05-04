<?php

include '../config/db.php'; 
header('Content-Type: application/json');

$response = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $cedula = $input['cedula']; 
    $contraseña = $input['password'];

    $sql = "SELECT * FROM usuarios WHERE cedula = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $cedula);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        if (password_verify($contraseña, $row['contraseña'])) {
            $token = bin2hex(random_bytes(16));

            $sql_token = "UPDATE usuarios SET session_token = ? WHERE id = ?";
            $stmt_token = $conn->prepare($sql_token);
            $stmt_token->bind_param("si", $token, $row['id']);

            if ($stmt_token->execute()) {
                $response = [
                    'success' => true,
                    'message' => 'Inicio de sesión exitoso',
                    'user_role' => $row['tipo_usuario'],
                    'user_name' => $row['nombre'],
                    'token' => $token 
                ];
            } else {
                http_response_code(500); 
                $response = [
                    'success' => false,
                    'message' => 'No se pudo generar el token. Intente nuevamente.'
                ];
            }

            $stmt_token->close();
        } else {
            http_response_code(401); 
            $response = [
                'success' => false,
                'message' => 'Contraseña incorrecta.'
            ];
        }
    } else {
        http_response_code(404); 
        $response = [
            'success' => false,
            'message' => 'Usuario no encontrado.'
        ];
    }

    $stmt->close();
} else {
    http_response_code(405); 
    $response = [
        'success' => false,
        'message' => 'Método no permitido.'
    ];
}

echo json_encode($response);
$conn->close();

?>