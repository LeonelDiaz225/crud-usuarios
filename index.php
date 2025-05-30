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
  $entornos_asignados = isset($_POST['entornos_asignados']) ? implode(',', $_POST['entornos_asignados']) : '';
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
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Entornos de Trabajo</title>
  <link rel="stylesheet" href="css/style.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body class="bg-light">

<div class="container py-4">
  <div class="d-flex justify-content-end mb-3">
    <a href="logout.php" class="btn btn-outline-danger">Cerrar sesión</a>
  </div>
  <?php if (esadmin()) { ?>
    <form method="post" class="mb-3">
      <button type="submit" name="mostrar_formulario_usuario" class="btn btn-success w-100">
        <i class="bi bi-person-plus"></i> Crear usuario
      </button>
    </form>
  <?php } ?>

 <?php if (esadmin() && isset($_POST['mostrar_formulario_usuario'])) { ?>
  <div class="row justify-content-center">
    <div class="col-12 col-md-6">
      <div class="card shadow-sm mb-4">
        <div class="card-body">
          <h2 class="card-title mb-4 text-center">Crear usuario</h2>
<form method="post">
  <div class="row">
    <div class="col-12 col-md-6 mb-3">
      <input type="text" name="username" class="form-control" placeholder="Nombre usuario" required>
    </div>
    <div class="col-12 col-md-6 mb-3">
      <input type="password" name="password" class="form-control" placeholder="Contraseña" required>
    </div>
    <div class="col-12 mb-3">
      <select name="rol" class="form-select">
        <option value="user">Usuario</option>
        <option value="admin">Admin</option>
      </select>
    </div>
  </div>

  <!-- Otorgar permisos -->
<div class="mb-2 fw-bold">Otorgar permisos</div>
<div class="row mb-3">
  <div class="col-12">
    <div class="form-check mb-2">
      <input type="checkbox" class="form-check-input" name="puede_crear_entorno" id="crearEntorno">
      <label class="form-check-label" for="crearEntorno">Crear entornos</label>
    </div>
    <div class="form-check mb-2">
      <input type="checkbox" class="form-check-input" name="puede_eliminar_entorno" id="eliminarEntorno">
      <label class="form-check-label" for="eliminarEntorno">Eliminar entornos</label>
    </div>
    <div class="form-check mb-2">
      <input type="checkbox" class="form-check-input" name="puede_editar_entorno" id="editarEntorno">
      <label class="form-check-label" for="editarEntorno">Editar entornos</label>
    </div>
    <div class="form-check mb-2">
      <input type="checkbox" class="form-check-input" name="puede_editar_registros" id="editarRegistros">
      <label class="form-check-label" for="editarRegistros">Editar registros</label>
    </div>
    <div class="form-check mb-2">
      <input type="checkbox" class="form-check-input" name="puede_eliminar_registros" id="eliminarRegistros">
      <label class="form-check-label" for="eliminarRegistros">Eliminar registros</label>
    </div>
  </div>
</div>

  <!-- Entornos asignados -->
  <div class="mb-3">
    <div class="dropdown">
      <button class="btn btn-outline-primary dropdown-toggle w-100" type="button" id="dropdownEntornos" data-bs-toggle="dropdown" aria-expanded="false">
        Seleccionar entornos
      </button>
      <ul class="dropdown-menu w-100" aria-labelledby="dropdownEntornos" id="entornosDropdownList">
        <?php
        $entornos_result = $conn->query("SELECT nombre FROM entornos ORDER BY nombre ASC");
        while ($ent = $entornos_result->fetch_assoc()):
        ?>
          <li>
            <a class="dropdown-item entorno-item" href="#" data-nombre="<?= htmlspecialchars($ent['nombre']) ?>">
              <?= htmlspecialchars($ent['nombre']) ?>
            </a>
          </li>
        <?php endwhile; ?>
      </ul>
    </div>
    <div class="mt-3">
      <div class="fw-bold mb-1">Seleccionados:</div>
      <ul id="entornosSeleccionados" class="list-group"></ul>
    </div>
  </div>
  <div id="entornosHiddenInputs"></div>
  <button type="submit" name="crear_usuario" class="btn btn-success w-100 mt-2">
    <i class="bi bi-person-plus"></i> Crear usuario
  </button>
</form>
      </div>
      </div>
    </div>
  </div>
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
  <script src="js/script.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>