<?php
session_start();
include "includes/db.php";

$error = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"] ?? "");
    $password = $_POST["password"] ?? "";
    
    if ($username && $password) {
        $stmt = $conn->prepare("SELECT * FROM usuarios WHERE username = ? LIMIT 1");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && $result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user["password"])) {
                $_SESSION["user_id"] = $user["id"];
                $_SESSION["username"] = $user["username"];
                $_SESSION["rol"] = $user["rol"];
                $_SESSION["puede_crear_entorno"] = $user["puede_crear_entorno"];
                $_SESSION["puede_eliminar_entorno"] = $user["puede_eliminar_entorno"];
                $_SESSION["puede_editar_entorno"] = $user["puede_editar_entorno"];
                $_SESSION["puede_editar_registros"] = $user["puede_editar_registros"];
                $_SESSION["puede_eliminar_registros"] = $user["puede_eliminar_registros"];
                $_SESSION["entornos_asignados"] = $user["entornos_asignados"];
                $_SESSION['loggedin'] = true; 
                header("Location: index.php");
                exit;
            } else {
                $error = "Contraseña incorrecta.";
            }
        } else {
            $error = "Usuario no encontrado.";
        }
    } else {
        $error = "Completa todos los campos.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <h1>Iniciar Sesión</h1>
    <?php if (!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>
    <form method="POST">
        <input type="text" name="username" placeholder="Usuario" required><br>
        <input type="password" name="password" placeholder="Contraseña" required><br>
        <button type="submit">Ingresar</button>
    </form>
</body>
</html>