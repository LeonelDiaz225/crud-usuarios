<?php
include "db.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $stmt = $conn->prepare("UPDATE usuarios SET apellido_nombre=?, cuit_dni=?, razon_social=?, telefono=?, correo=?, rubro=? WHERE id=?");
    $stmt->bind_param("ssssssi", $_POST["apellido_nombre"], $_POST["cuit_dni"], $_POST["razon_social"], $_POST["telefono"], $_POST["correo"], $_POST["rubro"], $_POST["id"]);
    $stmt->execute();
    $stmt->close();
}
?>
