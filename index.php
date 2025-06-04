<?php
session_start();
function esadmin() {
  return isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin'
      && isset($_SESSION['puede_crear_entorno']) && $_SESSION['puede_crear_entorno']
      && isset($_SESSION['puede_eliminar_entorno']) && $_SESSION['puede_eliminar_entorno']
      && isset($_SESSION['puede_editar_entorno']) && $_SESSION['puede_editar_entorno'];
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
  $entornos_asignados = isset($_POST['entornos_asignados']) && is_array($_POST['entornos_asignados']) ? implode(',', $_POST['entornos_asignados']) : '';
  if ($username && $password) {
      $hash = password_hash($password, PASSWORD_DEFAULT);
      $stmt = $conn->prepare("INSERT INTO usuarios (username, password, rol, puede_crear_entorno, puede_eliminar_entorno, puede_editar_entorno, puede_editar_registros, puede_eliminar_registros, entornos_asignados) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
      $stmt->bind_param("sssiiiiis", $username, $hash, $rol, $puede_crear_entorno, $puede_eliminar_entorno, $puede_editar_entorno, $puede_editar_registros, $puede_eliminar_registros, $entornos_asignados);
if ($stmt->execute()) {
    $_SESSION['mensaje'] = "Usuario creado correctamente";
    $_SESSION['tipo_mensaje'] = "success";
    header("Location: index.php");
    exit;
} else {
    $_SESSION['mensaje'] = "Error al crear usuario: ".$conn->error;
    $_SESSION['tipo_mensaje'] = "danger";
    header("Location: index.php");
    exit;
}
  } else {
      echo "<div class='alert alert-danger text-center'>Usuario y contraseña obligatorios.</div>";
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
<body class="bg-dark text-light">

<?php if (isset($_SESSION['mensaje'])): ?>
    <div id="mensaje-alerta" data-mensaje="<?= htmlspecialchars($_SESSION['mensaje']) ?>" style="display:none"></div>
    <?php unset($_SESSION['mensaje']); ?>
<?php endif; ?>

<!-- Sidebar derecha -->
<div id="sidebar" class="sidebar bg-dark text-light shadow position-fixed end-0 top-0 vh-100 p-4">
  <div class="d-flex flex-column h-100">
    <div class="mb-4">
      <div class="fw-bold mb-2">👤 <?= htmlspecialchars($_SESSION['username'] ?? 'Usuario', ENT_QUOTES, 'UTF-8') ?></div>
    </div>
    <div class="flex-grow-1 d-flex flex-column">
      <div class="d-grid gap-3">
        <?php if (esadmin()) { ?>
          <button type="button" class="sidebar-btn sidebar-btn-green" data-bs-toggle="modal" data-bs-target="#crearUsuarioModal">
            <i class="bi bi-person-plus me-2"></i> Crear usuario
          </button>
        <?php } ?>
        <form action="logout.php" method="POST" class="m-0">
          <button type="submit" class="sidebar-btn sidebar-btn-red">
            <i class="bi bi-box-arrow-right me-2"></i> Cerrar sesión
          </button>
        </form>
      </div>
    </div>
  </div>
</div>
<div id="sidebarOverlay"></div>

<button id="sidebarToggle" class="btn position-fixed" style="top:20px; right:20px; z-index:1100;">
  <i class="bi bi-list" style="font-size:1.5rem;"></i>
</button>

<!-- Modal de creación de usuario -->
<div class="modal fade" id="crearUsuarioModal" tabindex="-1" aria-labelledby="crearUsuarioModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content bg-dark text-light">
      <form method="post">
        <div class="modal-header border-0">
          <h5 class="modal-title" id="crearUsuarioModalLabel">Crear usuario</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-12 col-md-6 mb-3">
              <input type="text" name="username" class="form-control" placeholder="Nombre usuario" required>
            </div>
            <div class="col-12 col-md-6 mb-3">
              <input type="password" name="password" class="form-control" placeholder="Contraseña" required>
            </div>
            <div class="col-12 mb-3">
              <select name="rol" class="form-select bg-dark text-light border-secondary">
                <option value="user">Usuario</option>
                <option value="admin">Admin</option>
              </select>
            </div>
          </div>
          <div class="mb-2 fw-bold">Otorgar permisos</div>
          <div class="row mb-3">
            <div class="col-12 col-md-6">
              <div class="form-check mb-2">
                <input type="checkbox" class="form-check-input" name="puede_crear_entorno" id="crearEntorno">
                <label class="form-check-label" for="crearEntorno">Crear entornos</label>
              </div>
              <div class="form-check mb-2">
                <input type="checkbox" class="form-check-input" name="puede_eliminar_entorno" id="eliminarEntorno">
                <label class="form-check-label" for="eliminarEntorno">Eliminar entornos</label>
              </div>
              <!-- Descomentar si se desea permitir editar entornos (o quitar de la BDD)
              <div class="form-check mb-2">
                <input type="checkbox" class="form-check-input" name="puede_editar_entorno" id="editarEntorno">
                <label class="form-check-label" for="editarEntorno">Editar entornos</label>
              </div>
              -->
            </div>
            <div class="col-12 col-md-6">
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
              <div class="fw-bold mb-1">Entornos asignados:</div>
              <ul id="entornosSeleccionados" class="list-group"></ul>
            </div>
          </div>
          <div id="entornosHiddenInputs"></div>
        </div>
        <div class="modal-footer border-0">
          <button type="submit" name="crear_usuario" class="btn btn-success w-100">
            <i class="bi bi-person-plus"></i> Crear usuario
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<h1 class="text-center my-4">Gestión de Entornos</h1>

<?php if ($_SESSION['puede_crear_entorno']) { ?>
  <div class="row justify-content-center mb-4">
    <div class="col-12 col-md-8 col-lg-6">
      <form action="environments/create_environment.php" method="POST" class="card card-body shadow-sm bg-dark text-light border-0">
        <div class="input-group">
          <input type="text" name="nombre" class="form-control" placeholder="Nombre del entorno" required>
          <button type="submit" class="btn btn-success">Crear Entorno</button>
        </div>
      </form>
    </div>
  </div>
<?php } ?>

<div class="row justify-content-center">
  <div class="col-12 col-md-10 col-lg-8">
    <div class="table-responsive">
      <table class="table table-striped table-bordered align-middle shadow-sm bg-dark text-light">
        <thead class="table-success">
          <tr>
            <th>Nombre del Entorno</th>
            <th>Fecha de Creación</th>
            <th>Acción</th>
          </tr>
        </thead>
        <tbody>
          <?php 
          $entornos_permitidos_usuario = isset($_SESSION['entornos_asignados']) ? explode(',', $_SESSION['entornos_asignados']) : [];
          while ($row = $result->fetch_assoc()): 
            $nombre_entorno_actual = $row['nombre'];
            $tiene_acceso = ($_SESSION['rol'] === 'admin' || in_array($nombre_entorno_actual, $entornos_permitidos_usuario));
          ?>
            <tr>
              <td><?= htmlspecialchars($nombre_entorno_actual) ?></td>
              <td><?= $row['fecha_creacion'] ?></td>
              <td>
                <?php if ($tiene_acceso): ?>
                  <form action="entorno.php" method="get" class="d-inline">
                    <input type="hidden" name="tabla" value="<?= htmlspecialchars($nombre_entorno_actual) ?>">
                    <button type="submit" class="btn btn-primary btn-sm me-1">
                      <i class="bi bi-box-arrow-in-right"></i> Ingresar
                    </button>
                  </form>
                  <?php if ($_SESSION['puede_eliminar_entorno']): ?>
                    <form action="environments/delete_environment.php" method="POST" class="d-inline">
                      <input type="hidden" name="nombre" value="<?= htmlspecialchars($nombre_entorno_actual) ?>">
                      <button type="submit" class="btn btn-danger btn-sm">
                        <i class="bi bi-trash"></i> Eliminar
                      </button>
                    </form>
                  <?php endif; ?>
                <?php else: ?>
                  <span class="text-danger">No tienes acceso a este entorno</span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script src="js/script.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>