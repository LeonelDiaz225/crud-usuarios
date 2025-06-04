<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include "../includes/db.php";

$tabla = preg_replace('/[^a-zA-Z0-9_]/', '', $_GET['tabla'] ?? '');
if (!$tabla) {
  header("Location: ../entorno.php?tabla=&mensaje=" . urlencode("Error: Entorno no válido."));
  exit;
}

if (!isset($_FILES['csvFile']) || $_FILES['csvFile']['error'] !== UPLOAD_ERR_OK) {
  $error = isset($_FILES['csvFile']) ? $_FILES['csvFile']['error'] : 'No se envió el archivo';
  header("Location: ../entorno.php?tabla=$tabla&mensaje=" . urlencode("Error: No se pudo procesar el archivo CSV. Código: $error"));
  exit;
}

$tableCheck = $conn->query("SHOW TABLES LIKE '$tabla'");
if ($tableCheck->num_rows === 0) {
  header("Location: ../entorno.php?tabla=$tabla&mensaje=" . urlencode("Error: La tabla '$tabla' no existe."));
  exit;
}

$file = $_FILES["csvFile"]["tmp_name"];
if (!file_exists($file)) {
  header("Location: ../entorno.php?tabla=$tabla&mensaje=" . urlencode("Error: El archivo temporal no existe."));
  exit;
}

$handle = fopen($file, "r");
if (!$handle) {
  header("Location: ../entorno.php?tabla=$tabla&mensaje=" . urlencode("Error: No se pudo abrir el archivo CSV."));
  exit;
}

$count = 0;
$errors = 0;

$header = fgetcsv($handle);
if (!$header) {
  fclose($handle);
  header("Location: ../entorno.php?tabla=$tabla&mensaje=" . urlencode("El archivo CSV está vacío o tiene un formato incorrecto."));
  exit;
}

while (($data = fgetcsv($handle, 1000, ",")) !== false) {
  if (count($data) < 6) {
    $errors++;
    continue;
  }
  try {
    $stmt = $conn->prepare("INSERT INTO `$tabla` (apellido_nombre, cuit_dni, razon_social, telefono, correo, rubro) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $data[0], $data[1], $data[2], $data[3], $data[4], $data[5]);
    if ($stmt->execute()) {
      $count++;
    } else {
      $errors++;
    }
  } catch (Exception $e) {
    $errors++;
  }
}
fclose($handle);

if ($count > 0 && $errors === 0) {
  header("Location: ../entorno.php?tabla=$tabla&mensaje=" . urlencode("Archivo importado correctamente"));
} elseif ($count > 0 && $errors > 0) {
  header("Location: ../entorno.php?tabla=$tabla&mensaje=" . urlencode("Importado con advertencias: $count registros correctos, $errors con error."));
} else {
  header("Location: ../entorno.php?tabla=$tabla&mensaje=" . urlencode("No se pudo importar ningún registro. Verifique el archivo."));
}
exit;