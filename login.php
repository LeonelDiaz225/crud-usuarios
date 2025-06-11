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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-dark d-flex align-items-center justify-content-center" style="min-height:100vh;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-sm-8 col-md-6 col-lg-4">
                <div class="card shadow-sm" style="background:#2c3035; border:none;">
                    <div class="card-body">
                        <h2 class="card-title text-center mb-4 text-light">Iniciar Sesión</h2>
                        <?php if (!empty($error)) : ?>
                            <div class="alert alert-danger py-2" role="alert">
                                <?= htmlspecialchars($error) ?>
                            </div>
                        <?php endif; ?>
                        <form method="POST" autocomplete="off">
                            <div class="mb-3">
                                <input type="text" name="username" class="form-control" placeholder="Usuario" required autofocus>
                            </div>
                            <div class="mb-3">
                                <input type="password" name="password" class="form-control" placeholder="Contraseña" required>
                            </div>
                            <button type="submit" class="btn btn-success w-100">Ingresar</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>