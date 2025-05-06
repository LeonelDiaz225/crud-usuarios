<?php
include 'db.php';

$apellido_nombre = $_POST['apellido_nombre'] ?? null;
$cuit_dni = $_POST['cuit_dni'] ?? null;
$razon_social = $_POST['razon_social'] ?? null;
$telefono = $_POST['telefono'] ?? null;
$correo = $_POST['correo'] ?? null;
$rubro = $_POST['rubro'] ?? null;

$sql = "INSERT INTO usuarios (apellido_nombre, cuit_dni, razon_social, telefono, correo, rubro)
        VALUES ('$apellido_nombre', '$cuit_dni', '$razon_social', '$telefono', '$correo', '$rubro')";

if ($conn->query($sql) === TRUE) {
  echo "Usuario agregado correctamente";
} else {
  echo "Error: " . $conn->error;
}
?>
