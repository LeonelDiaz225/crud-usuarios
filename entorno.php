<?php
// Activar la visualización de errores para depuración
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Incluir conexión a la base de datos
include "db.php";

// Validar el nombre de la tabla
$tabla = preg_replace('/[^a-zA-Z0-9_]/', '', $_GET['tabla'] ?? '');
if (!$tabla) {
  die("<p>Error: Entorno no válido.</p><a href='index.php'>Volver a entornos</a>");
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
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <h1>Entorno: <?= htmlspecialchars($tabla) ?></h1>
  <a href="index.php">← Volver a entornos</a>
  
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
  <form id="csvForm" action="import_csv_to_environment.php?tabla=<?= htmlspecialchars($tabla) ?>" method="POST" enctype="multipart/form-data">
    <input type="file" id="csvFile" name="csvFile" accept=".csv" required>
    <input type="hidden" name="tabla" value="<?= htmlspecialchars($tabla) ?>">
    <button type="submit">Importar CSV</button>
  </form>
  
  <p style="text-align: center; font-size: 0.9em; color: #666;">
    El CSV debe tener los campos: Apellido y Nombre, CUIT/DNI, Razón Social, Teléfono, Correo, Rubro.
  </p>

  <!-- Tabla de registros -->
  <h2 style="text-align: center">Registros en <?= htmlspecialchars($tabla) ?></h2>
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

  <!-- Información de depuración opcional -->
  <div id="debug-info" style="margin-top: 20px; padding: 10px; background-color: #f8f9fa; border: 1px solid #ddd;">
    <h3>Información de depuración</h3>
    <div><strong>Tabla actual:</strong> <?= htmlspecialchars($tabla) ?></div>
    <div><strong>URL:</strong> <?= htmlspecialchars($_SERVER['REQUEST_URI']) ?></div>
    <div id="debug-output"></div>
  </div>

  <!-- Scripts -->
  <script src="script.js"></script>
</body>
</html>