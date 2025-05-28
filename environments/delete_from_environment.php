<?php
session_start();

include "../includes/db.php";
$tabla = preg_replace('/[^a-zA-Z0-9_]/', '', $_GET['tabla'] ?? '');
$id = intval($_GET['id'] ?? 0);

if (!isset($_SESSION['puede_eliminar_registros']) || !$_SESSION['puede_eliminar_registros']) {
  http_response_code(403); 
  echo "No tienes permiso para eliminar registros.";
  exit;
}

if (!$tabla || !$id) {
  http_response_code(400);
  echo "Datos inválidos.";
  exit;
}

// Verificar si la tabla existe
$tableCheck = $conn->query("SHOW TABLES LIKE '$tabla'");
if ($tableCheck->num_rows === 0) {
  http_response_code(404);
  echo "La tabla no existe.";
  exit;
}

// Usar prepared statement para borrar
$stmt = $conn->prepare("DELETE FROM `$tabla` WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
  echo "Registro eliminado correctamente.";
} else {
  http_response_code(500);
  echo "Error al eliminar: " . $conn->error;
}