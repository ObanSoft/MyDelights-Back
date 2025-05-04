<?php
include '../config/db.php';
header('Content-Type: application/json');

function validar_contraseña($contraseña) {
    if (strlen($contraseña) < 8) {
        return "La contraseña debe tener al menos 8 caracteres.";
    }
    if (!preg_match('/[A-Z]/', $contraseña)) {
        return "La contraseña debe tener al menos una letra mayúscula.";
    }
    if (!preg_match('/[a-z]/', $contraseña)) {
        return "La contraseña debe tener al menos una letra minúscula.";
    }
    return true;
}

function validar_email($email) {
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return "El formato del correo electrónico no es válido.";
    }
    return true;
}

function validar_sexo($sexo) {
    $sexos_validos = ['Masculino', 'Femenino'];
    if (!in_array($sexo, $sexos_validos)) {
        return "El sexo debe ser 'Masculino' o 'Femenino'.";
    }
    return true;
}

function validar_fecha_nacimiento($fecha) {
    $formato = 'Y-m-d';
    $d = DateTime::createFromFormat($formato, $fecha);
    if (!$d || $d->format($formato) !== $fecha) {
        return "La fecha de nacimiento debe estar en el formato 'YYYY-MM-DD'.";
    }
    return true;
}

function validar_telefono($telefono) {
    if (!preg_match('/^\d{10,15}$/', $telefono)) {
        return "El teléfono celular debe contener entre 10 y 15 dígitos.";
    }
    return true;
}

$response = array('success' => false, 'message' => '');

if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['cedula'])) {
        $response['message'] = "El número de cédula es requerido para identificar al cliente.";
        echo json_encode($response);
        exit;
    }

    $cedula = $input['cedula']; 

    $nombre = isset($input['nombre']) ? $input['nombre'] : null;
    $email = isset($input['email']) ? $input['email'] : null;
    $sexo = isset($input['sexo']) ? $input['sexo'] : null;
    $fecha_nacimiento = isset($input['fecha_nacimiento']) ? $input['fecha_nacimiento'] : null;
    $direccion = isset($input['direccion']) ? $input['direccion'] : null;
    $telefono = isset($input['telefono']) ? $input['telefono'] : null;
    $contraseña = isset($input['contraseña']) ? $input['contraseña'] : null;

    if ($email) {
        $resultado_email = validar_email($email);
        if ($resultado_email !== true) {
            $response['message'] = $resultado_email;
            echo json_encode($response);
            exit;
        }
    }

    if ($sexo) {
        $resultado_sexo = validar_sexo($sexo);
        if ($resultado_sexo !== true) {
            $response['message'] = $resultado_sexo;
            echo json_encode($response);
            exit;
        }
    }

    if ($fecha_nacimiento) {
        $resultado_fecha_nacimiento = validar_fecha_nacimiento($fecha_nacimiento);
        if ($resultado_fecha_nacimiento !== true) {
            $response['message'] = $resultado_fecha_nacimiento;
            echo json_encode($response);
            exit;
        }
    }

    if ($telefono) {
        $resultado_telefono = validar_telefono($telefono);
        if ($resultado_telefono !== true) {
            $response['message'] = $resultado_telefono;
            echo json_encode($response);
            exit;
        }
    }

    if ($contraseña) {
        $resultado_contraseña = validar_contraseña($contraseña);
        if ($resultado_contraseña !== true) {
            $response['message'] = $resultado_contraseña;
            echo json_encode($response);
            exit;
        }
        $contraseña_hashed = password_hash($contraseña, PASSWORD_DEFAULT); 
    }

    $updates = [];
    if ($nombre) $updates[] = "nombre = '$nombre'";
    if ($email) $updates[] = "email = '$email'";
    if ($sexo) $updates[] = "sexo = '$sexo'";
    if ($fecha_nacimiento) $updates[] = "fecha_nacimiento = '$fecha_nacimiento'";
    if ($direccion) $updates[] = "direccion = '$direccion'";
    if ($telefono) $updates[] = "telefono = '$telefono'";
    if ($contraseña) $updates[] = "contraseña = '$contraseña_hashed'";

    if (empty($updates)) {
        $response['message'] = "No se enviaron campos para actualizar.";
        echo json_encode($response);
        exit;
    }

    $updates_sql = implode(", ", $updates);
    $sql = "UPDATE usuarios SET $updates_sql WHERE cedula = '$cedula'";

    if ($conn->query($sql) === TRUE) {
        if ($conn->affected_rows > 0) {
            $response['success'] = true;
            $response['message'] = "Cliente actualizado correctamente.";
        } else {
            $response['message'] = "No se encontró ningún cliente con la cédula proporcionada.";
        }
    } else {
        $response['message'] = "Error al actualizar el cliente: " . $conn->error;
    }
} else {
    http_response_code(405); 
    $response['message'] = "Método no permitido.";
}

echo json_encode($response);
$conn->close();
?>