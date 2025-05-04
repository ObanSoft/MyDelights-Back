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

    function validar_cedula($cedula) {
        if (!preg_match('/^\d{8,}$/', $cedula)) {
            return "La cédula debe contener solo números y tener al menos 8 dígitos.";
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

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);

        $nombre = $input['username'];
        $email = $input['email'];
        $contraseña = $input['password'];
        $cedula = $input['cedula'];
        $sexo = $input['sexo'];
        $fecha_nacimiento = $input['fecha_nacimiento'];
        $direccion = $input['direccion'];
        $telefono = $input['telefono'];

        $resultado_email = validar_email($email);
        if ($resultado_email !== true) {
            $response['message'] = $resultado_email;
            echo json_encode($response);
            exit;
        }

        $resultado_contraseña = validar_contraseña($contraseña);
        if ($resultado_contraseña !== true) {
            $response['message'] = $resultado_contraseña;
            echo json_encode($response);
            exit;
        }

        $resultado_cedula = validar_cedula($cedula);
        if ($resultado_cedula !== true) {
            $response['message'] = $resultado_cedula;
            echo json_encode($response);
            exit;
        }

        $resultado_sexo = validar_sexo($sexo);
        if ($resultado_sexo !== true) {
            $response['message'] = $resultado_sexo;
            echo json_encode($response);
            exit;
        }

        $resultado_fecha_nacimiento = validar_fecha_nacimiento($fecha_nacimiento);
        if ($resultado_fecha_nacimiento !== true) {
            $response['message'] = $resultado_fecha_nacimiento;
            echo json_encode($response);
            exit;
        }

        $resultado_telefono = validar_telefono($telefono);
        if ($resultado_telefono !== true) {
            $response['message'] = $resultado_telefono;
            echo json_encode($response);
            exit;
        }

        $sql = "SELECT * FROM usuarios WHERE email = '$email' OR nombre = '$nombre' OR cedula = '$cedula'";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            $response['message'] = "El correo electrónico, la cédula o el nombre de usuario ya están en uso.";
            echo json_encode($response);
            exit;
        }

        $contraseña_hashed = password_hash($contraseña, PASSWORD_DEFAULT);

        $sql = "INSERT INTO usuarios (nombre, email, contraseña, cedula, sexo, fecha_nacimiento, direccion, telefono, tipo_usuario) 
                VALUES ('$nombre', '$email', '$contraseña_hashed', '$cedula', '$sexo', '$fecha_nacimiento', '$direccion', '$telefono', 'cliente')";
        if ($conn->query($sql) === TRUE) {
            $response['success'] = true;
            $response['message'] = "Cliente registrado correctamente.";
        } else {
            $response['message'] = "Error: " . $sql . "<br>" . $conn->error;
        }
    }

    $conn->close();
    echo json_encode($response);
?>