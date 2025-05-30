<?php

header("Content-Type: application/json");
include "../includes/db.php";
$tabla = preg_replace('/[^a-zA-Z0-9_]/', '', $_GET['tabla'] ?? '');
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$offset = ($page - 1) * $limit;
$search = trim($_GET['search'] ?? '');

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

// Campos a buscar
$campos = [
  "apellido_nombre",
  "cuit_dni",
  "razon_social",
  "telefono",
  "correo",
  "rubro"
];

$where = "";
$params = [];
$types = "";

if ($search !== "") {
  $like = "%" . $search . "%";
  $whereParts = [];
  foreach ($campos as $campo) {
    $whereParts[] = "`$campo` LIKE ?";
    $params[] = $like;
    $types .= "s";
  }
  $where = "WHERE (" . implode(" OR ", $whereParts) . ")";
}

// Contar total de registros (con filtro si hay búsqueda)
$countSql = "SELECT COUNT(*) as total FROM `$tabla` $where";
$countStmt = $conn->prepare($countSql);
if ($where !== "") {
  $countStmt->bind_param($types, ...$params);
}
$countStmt->execute();
$countResult = $countStmt->get_result();
$totalRows = 0;
if ($countResult) {
  $totalRowsData = $countResult->fetch_assoc();
  $totalRows = $totalRowsData['total'];
}

// Consulta de datos paginados (con filtro si hay búsqueda)
$dataSql = "SELECT * FROM `$tabla` $where ORDER BY id DESC LIMIT ? OFFSET ?";
$dataStmt = $conn->prepare($dataSql);
if ($where !== "") {
  $bindParams = array_merge($params, [$limit, $offset]);
  $dataStmt->bind_param($types . "ii", ...$bindParams);
} else {
  $dataStmt->bind_param("ii", $limit, $offset);
}
$dataStmt->execute();
$result = $dataStmt->get_result();
$datos = [];

if ($result && $result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
    $datos[] = $row;
  }
}

echo json_encode([
  "data" => $datos,
  "total" => (int)$totalRows,
  "limit" => $limit,
  "page" => $page
]);