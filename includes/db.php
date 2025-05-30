<?php
$host = "localhost";
$user = "admin";      
$pass = "123";          
$db = "ader_db";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    if (strpos($_SERVER['PHP_SELF'], 'environments/') !== false) {
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode(["error" => "Conexión fallida: " . $conn->connect_error]);
        exit;
    } else {
        die("Conexión fallida: " . $conn->connect_error);
    }
}
?>