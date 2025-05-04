<?php

$servername = 'localhost';
$username = 'root';
$password = '1073676799';
$dbname = 'mydelights';


$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Conexion Fallida: " . $conn->connect_error);
}

?>
    