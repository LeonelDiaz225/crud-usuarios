<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>

<?php include "db.php"; ?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>CRUD Usuarios</title>
  <link rel="stylesheet" href="style.css" />
</head>
<body>

<?php if (isset($_GET['import']) && $_GET['import'] === 'success'): ?>
  <div id="importMessage" style="background-color: #d4edda; color: #155724; padding: 10px; margin-bottom: 15px; border: 1px solid #c3e6cb; border-radius: 5px;">
    Archivo CSV importado correctamente.
  </div>
  <script>
    setTimeout(() => {
      const msg = document.getElementById("importMessage");
      if (msg) msg.remove();
    }, 4000); // Elimina el mensaje después de 4 segundos
  </script>
<?php endif; ?>

<h1>Gestión de Usuarios</h1>

<!-- Formulario de creación y edición de usuarios -->
<form id="userForm" autocomplete="off">
  <input type="hidden" id="id" name="id">
  <input type="text" id="apellido_nombre" name="apellido_nombre" placeholder="Apellido y Nombre" required>
  <input type="text" id="cuit_dni" name="cuit_dni" placeholder="CUIT o DNI" required>
  <input type="text" id="razon_social" name="razon_social" placeholder="Razón Social" required>
  <input type="text" id="telefono" name="telefono" placeholder="Teléfono" required>
  <input type="email" id="correo" name="correo" placeholder="Correo Electrónico" required>
  <input type="text" id="rubro" name="rubro" placeholder="Rubro" required>
  <button type="submit">Guardar</button>
</form>

<!-- Formulario para importar CSV -->
<h2 style="text-align: center;">Importar desde CSV</h2>
<form id="csvForm" action="import_csv.php" method="POST" enctype="multipart/form-data">
<input type="file" id="csvFile" name="csvFile" accept=".csv" required>
  <button type="submit">Importar CSV</button>
</form>

<!-- Tabla de usuarios -->
<table id="userTable">
  <thead >
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
    <!-- Los datos de los usuarios se cargarán aquí por JavaScript -->
  </tbody>
</table>

<script src="script.js"></script>

</body>
</html>
