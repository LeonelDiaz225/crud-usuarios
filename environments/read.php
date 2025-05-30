<?php
header("Content-Type: application/json");
include "../includes/db.php";
$tabla = preg_replace('/[^a-zA-Z0-9_]/', '', $_GET['tabla'] ?? '');
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10; // Default items per page
$offset = ($page - 1) * $limit;

if (!$tabla) {
  http_response_code(400);
  echo json_encode(["error" => "Nombre de tabla no proporcionado."]);
  exit;
}

// Verificar si la tabla existe
$tableCheck = $conn->query("SHOW TABLES LIKE '$tabla'");
if ($tableCheck->num_rows === 0) {
  http_response_code(404);
  echo json_encode(["error" => "La tabla no existe."]);
  exit;
}

// Contar el total de registros para la paginación
$totalResult = $conn->query("SELECT COUNT(*) as total FROM `$tabla`");
$totalRows = 0;
if ($totalResult) {
    $totalRowsData = $totalResult->fetch_assoc();
    $totalRows = $totalRowsData['total'];
}

// Consulta segura con prepared statements para los datos paginados
$stmt = $conn->prepare("SELECT * FROM `$tabla` ORDER BY id DESC LIMIT ? OFFSET ?");
if (!$stmt) {
  http_response_code(500);
  echo json_encode(["error" => "Error preparando la consulta: " . $conn->error]);
  exit;
}

$stmt->bind_param("ii", $limit, $offset);
$stmt->execute();
$result = $stmt->get_result();
$datos = [];

if ($result && $result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
    $datos[] = $row;
  }
}

echo json_encode(["data" => $datos, "total" => (int)$totalRows, "limit" => $limit, "page" => $page]);