<?php
include "db.php";
$result = $conn->query("SELECT * FROM entornos ORDER BY fecha_creacion DESC");
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Entornos de Trabajo</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <h1>Gestión de Entornos</h1>

  <!-- Crear nuevo entorno -->
  <form action="create_environment.php" method="POST">
    <input type="text" name="nombre" placeholder="Nombre del entorno" required>
    <button type="submit">Crear Entorno</button>
  </form>

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
      <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
          <td><?= htmlspecialchars($row['nombre']) ?></td>
          <td><?= $row['fecha_creacion'] ?></td>
          <td>
            <a href="entorno.php?tabla=<?= urlencode($row['nombre']) ?>">
              Ingresar
            </a>
          </td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</body>
</html>
