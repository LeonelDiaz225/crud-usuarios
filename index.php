<?php
session_start();
function esadmin() {
  return isset($_SESSION['rol'], $_SESSION['puede_crear_entorno'], $_SESSION['puede_eliminar_entorno'], $_SESSION['puede_editar_entorno'])
      && $_SESSION['rol'] === 'admin'
      && $_SESSION['puede_crear_entorno']
      && $_SESSION['puede_eliminar_entorno']
      && $_SESSION['puede_editar_entorno'];
}


if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}
include "includes/db.php";
 
if (esadmin() && isset($_POST['crear_usuario'])) {
  $username = trim($_POST['username'] ?? '');
  $password = $_POST['password'] ?? '';
  $rol = $_POST['rol'] ?? 'user';
  $puede_crear_entorno = isset($_POST['puede_crear_entorno']) ? 1 : 0;
  $puede_eliminar_entorno = isset($_POST['puede_eliminar_entorno']) ? 1 : 0;
  $puede_editar_entorno = isset($_POST['puede_editar_entorno']) ? 1 : 0;
  $puede_editar_registros = isset($_POST['puede_editar_registros']) ? 1 : 0;
  $puede_eliminar_registros = isset($_POST['puede_eliminar_registros']) ? 1 : 0;
  $entornos_asignados = trim($_POST['entornos_asignados'] ?? '');
  if ($username && $password) {
      $hash = password_hash($password, PASSWORD_DEFAULT);
      $stmt = $conn->prepare("INSERT INTO usuarios (username, password, rol, puede_crear_entorno, puede_eliminar_entorno, puede_editar_entorno, puede_editar_registros, puede_eliminar_registros, entornos_asignados) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
      $stmt->bind_param("sssiiiiis", $username, $hash, $rol, $puede_crear_entorno, $puede_eliminar_entorno, $puede_editar_entorno, $puede_editar_registros, $puede_eliminar_registros, $entornos_asignados);
      if ($stmt->execute()) {
          echo "<div style='color:green;'>Usuario creado correctamente.</div>";
      } else {
          echo "<div style='color:red;'>Error al crear usuario: ".$conn->error."</div>";
      }
  } else {
      echo "<div style='color:red;'>Usuario y contraseña obligatorios.</div>";
  }
}
$result = $conn->query("SELECT * FROM entornos ORDER BY fecha_creacion DESC");
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Entornos de Trabajo</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>

<a href="logout.php" style="float:right;">Cerrar sesión</a>
<?php if (esadmin()) { ?>
  <form method="post" style="display:inline;">
    <button type="submit" name="mostrar_formulario_usuario" style="margin-top:10px;">Crear usuario</button>
  </form>
<?php } ?>

<?php if (esadmin() && isset($_POST['mostrar_formulario_usuario'])) { ?>
  <h2>Crear usuario</h2>
  <form method="post">
    <input type="text" name="username" placeholder="Usuario" required><br>
    <input type="password" name="password" placeholder="Contraseña" required><br>
    <select name="rol">
      <option value="user">Usuario</option>
      <option value="admin">Admin</option>
    </select><br>
    <label><input type="checkbox" name="puede_crear_entorno"> Crear entornos</label>
    <label><input type="checkbox" name="puede_eliminar_entorno"> Eliminar entornos</label>
    <label><input type="checkbox" name="puede_editar_entorno"> Editar entornos</label><br>
    <label><input type="checkbox" name="puede_editar_registros"> Editar registros</label>
    <label><input type="checkbox" name="puede_eliminar_registros"> Eliminar registros</label>
    <input type="text" name="entornos_asignados" placeholder="entorno1,entorno2"><br>
    <button type="submit" name="crear_usuario">Crear usuario</button>
  </form>
<?php } ?>

<h1>Gestión de Entornos</h1>

  <!-- Crear nuevo entorno -->
  <?php if ($_SESSION['puede_crear_entorno']) { ?>
  <form action="environments/create_environment.php" method="POST">
    <input type="text" name="nombre" placeholder="Nombre del entorno" required>
    <button type="submit">Crear Entorno</button>
  </form>
<?php } ?>

  <!-- Lista de entornos existentes -->
  <table>
    <thead>
      <tr>
        <th>Nombre del Entorno</th>
        <th>Fecha de Creación</th>
        <th>Acción</th>
      </tr>
    </thead>
    <tbody>
      <?php 
      // Obtener los entornos asignados al usuario actual y convertirlos en un array
      $entornos_permitidos_usuario = isset($_SESSION['entornos_asignados']) ? explode(',', $_SESSION['entornos_asignados']) : [];
      
      while ($row = $result->fetch_assoc()): 
        // Verificar si el usuario tiene acceso al entorno actual
        $nombre_entorno_actual = $row['nombre'];
        $tiene_acceso = false;
        if ($_SESSION['rol'] === 'admin' || in_array($nombre_entorno_actual, $entornos_permitidos_usuario)) {
          $tiene_acceso = true;
        }
      ?>
        <tr>
          <td><?= htmlspecialchars($nombre_entorno_actual) ?></td>
          <td><?= $row['fecha_creacion'] ?></td>
          <td>
            <?php if ($tiene_acceso): ?>
              <form action="entorno.php" method="get" style="display:inline-block; margin:0; padding:0; vertical-align:middle;">
                <input type="hidden" name="tabla" value="<?= htmlspecialchars($nombre_entorno_actual) ?>">
                <button type="submit" class="action-btn ingresar" style="background:#42a5f5; color:white;">Ingresar</button>
              </form>
              <?php if ($_SESSION['puede_eliminar_entorno']): ?>
                <form action="environments/delete_environment.php" method="POST" style="display:inline-block; margin:0; padding:0; vertical-align:middle;">
                  <input type="hidden" name="nombre" value="<?= htmlspecialchars($nombre_entorno_actual) ?>">
                  <button type="submit" class="action-btn eliminar" style="background:#f44336; color:white;">Eliminar</button>
                </form>
              <?php endif; ?>
            <?php else: ?>
              <span style="color: red;">No tienes acceso a este entorno</span>
            <?php endif; ?>
          </td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</body>
</html>