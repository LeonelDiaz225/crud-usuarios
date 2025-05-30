<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

// Incluir conexión a la base de datos
include "includes/db.php";

// Validar el nombre de la tabla
$tabla = preg_replace('/[^a-zA-Z0-9_]/', '', $_GET['tabla'] ?? '');
if (!$tabla) {
  die("<p>Error: Entorno no válido.</p><a href='index.php'>Volver a entornos</a>");
}

$entornos_asignados = isset($_SESSION['entornos_asignados']) ? explode(',', $_SESSION['entornos_asignados']) : [];
if ($_SESSION['rol'] !== 'admin' && !in_array($tabla, $entornos_asignados)) {
    echo "<p>No tienes acceso a este entorno.</p>";
    exit;
}

// Verificar si la tabla existe
$tableCheck = $conn->query("SHOW TABLES LIKE '$tabla'");
if ($tableCheck->num_rows === 0) {
  die("<p>Error: La tabla '$tabla' no existe.</p><a href='index.php'>Volver a entornos</a>");
}

// Guardar manualmente si es un formulario de carga manual (no CSV)
if (
  $_SERVER["REQUEST_METHOD"] === "POST" && 
  isset($_POST["apellido_nombre"]) && 
  !isset($_FILES['csvFile'])
) {
  $stmt = $conn->prepare("INSERT INTO `$tabla` (apellido_nombre, cuit_dni, razon_social, telefono, correo, rubro) VALUES (?, ?, ?, ?, ?, ?)");
  $stmt->bind_param(
    "ssssss",
    $_POST["apellido_nombre"],
    $_POST["cuit_dni"],
    $_POST["razon_social"],
    $_POST["telefono"],
    $_POST["correo"],
    $_POST["rubro"]
  );
  
  if ($stmt->execute()) {
    $mensaje = "Registro guardado correctamente.";
  } else {
    $mensaje = "Error al guardar: " . $conn->error;
  }
  
  // No redireccionamos para poder ver el mensaje
  // header("Location: entorno.php?tabla=$tabla");
  // exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($tabla) ?> - Entorno</title>
  <link rel="stylesheet" href="css/style.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body>

<div class="d-flex justify-content-end mb-3">
    <a href="logout.php" class="btn btn-outline-danger">Cerrar sesión</a>
  </div>

  <h1>Entorno: <?= htmlspecialchars($tabla) ?></h1>
  <a href="index.php" class="back-index">← Volver a entornos</a>
  
  <?php if (isset($mensaje)): ?>
    <div style="margin: 10px 0; padding: 10px; background-color: #f0f0f0; border-left: 4px solid #4CAF50;">
      <?= $mensaje ?>
    </div>
  <?php endif; ?>

  <h2 style="text-align: center">Agregar registro manualmente</h2>
  <form method="POST" autocomplete="off">
    <input type="text" name="apellido_nombre" placeholder="Apellido y Nombre" required>
    <input type="text" name="cuit_dni" placeholder="CUIT o DNI" required>
    <input type="text" name="razon_social" placeholder="Razón Social" required>
    <input type="text" name="telefono" placeholder="Teléfono" required>
    <input type="email" name="correo" placeholder="Correo Electrónico" required>
    <input type="text" name="rubro" placeholder="Rubro" required>
    <button type="submit">Guardar</button>
  </form>

  <!-- Formulario para CSV -->
  <h2 style="text-align: center">Importar CSV</h2>
  <form id="csvForm" action="environments/import_csv_to_environment.php?tabla=<?= htmlspecialchars($tabla) ?>" method="POST" enctype="multipart/form-data">
    <input type="file" id="csvFile" name="csvFile" accept=".csv" required>
    <input type="hidden" name="tabla" value="<?= htmlspecialchars($tabla) ?>">
    <button type="submit">Importar CSV</button>
  </form>
  
  <p style="text-align: center; font-size: 0.9em; color: #666;">
    El CSV debe tener los campos: Apellido y Nombre, CUIT/DNI, Razón Social, Teléfono, Correo, Rubro.
  </p>

  <h2 style="text-align: center">Registros en <?= htmlspecialchars($tabla) ?></h2>

  
<div class="search-container">
  <input type="text" id="buscadorGeneral" placeholder="Buscar registros...">
</div>

<!-- Exportar a Excel -->
<div class="exportar-container">
  <button id="exportExcelBtn" class="btn btn-success btn-sm exportar-btn">
    <i class="bi bi-file-earmark-excel"></i> Exportar
  </button>
</div>

  <!-- Tabla de registros -->
  <table>
    <thead>
      <tr>
        <th>Apellido y Nombre</th>
        <th>CUIT o DNI</th>
        <th>Razón Social</th>
        <th>Teléfono</th>
        <th>Correo</th>
        <th>Rubro</th>
        <th>Acciones</th>
      </tr>
    </thead>
    <tbody id="userTableBody">
      <!-- Los datos se cargarán dinámicamente con JavaScript -->
      <tr>
        <td colspan="7" style="text-align: center">Cargando datos...</td>
      </tr>
    </tbody>
  </table>
  <div class="pagination-container" style="display: flex; justify-content: flex-end; width: 100%;">
    <div id="pagination-controls" style="text-align: center; margin-top: 20px;">
      <!-- Los controles de paginación se cargarán aquí -->
    </div>
  </div>

  
  <!-- Modal de edición -->
  <div id="editModal" class="modal" style="display:none;">
    <div class="modal-content">
      <span class="close" id="closeEditModal">&times;</span>
      <h2>Editar registro</h2>
      <form id="editForm" autocomplete="off">
        <input type="hidden" name="id" id="edit_id">
        <input type="hidden" name="tabla" id="edit_tabla" value="<?= htmlspecialchars($tabla) ?>">
        <input type="text" name="apellido_nombre" id="edit_apellido_nombre" placeholder="Apellido y Nombre" required>
        <input type="text" name="cuit_dni" id="edit_cuit_dni" placeholder="CUIT o DNI" required>
        <input type="text" name="razon_social" id="edit_razon_social" placeholder="Razón Social" required>
        <input type="text" name="telefono" id="edit_telefono" placeholder="Teléfono" required>
        <input type="email" name="correo" id="edit_correo" placeholder="Correo Electrónico" required>
        <input type="text" name="rubro" id="edit_rubro" placeholder="Rubro" required>
        <div style="display:flex; gap:10px; margin-top:10px;">
          <button type="submit" id="editSaveBtn">Aceptar cambios</button>
          <button type="button" id="editCancelBtn" style="background:#f44336;">Cancelar</button>
        </div>
      </form>
    </div>
  </div>
  <script>
    const puedeEditarRegistros = <?= isset($_SESSION['puede_editar_registros']) && $_SESSION['puede_editar_registros'] ? 'true' : 'false' ?>;
    const puedeEliminarRegistros = <?= isset($_SESSION['puede_eliminar_registros']) && $_SESSION['puede_eliminar_registros'] ? 'true' : 'false' ?>;
    const tabla = "<?= htmlspecialchars($tabla) ?>"; // Aseguramos que la variable tabla también esté disponible globalmente
  </script>

  <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
  <script src="js/script.js"></script>
 
</body>
</html>