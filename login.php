<?php
include 'db.php';
header('Content-Type: application/json');

session_start();

$response = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $cedula = $input['cedula']; 
    $contraseña = $input['password'];

    $sql = "SELECT * FROM usuarios WHERE cedula='$cedula'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (password_verify($contraseña, $row['contraseña'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['user_role'] = $row['tipo_usuario'];
            $_SESSION['user_name'] = $row['nombre']; 

            $response = [
                'status' => 'success',
                'message' => 'Inicio de sesión exitoso',
                'user_role' => $row['tipo_usuario'],
                'user_name' => $row['nombre'] 
            ];
        } else {
            http_response_code(401); 
            $response = [
                'status' => 'error',
                'message' => 'Credenciales Incorrectas'
            ];
        }
    } else {
        http_response_code(404); 
        $response = [
            'status' => 'error',
            'message' => 'Credenciales Incorrectas'
        ];
    }
} else {
    http_response_code(405); 
    $response = [
        'status' => 'error',
        'message' => 'Método no permitido'
    ];
}

echo json_encode($response);
$conn->close();
?>