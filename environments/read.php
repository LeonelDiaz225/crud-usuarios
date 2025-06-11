<?php
include "../includes/db.php";

$tabla = preg_replace('/[^a-zA-Z0-9_]/', '', $_GET['tabla'] ?? '');
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 5;
$search = isset($_GET['search']) ? $_GET['search'] : '';

$offset = ($page - 1) * $limit;

// Construir la consulta base
$sql = "SELECT * FROM `$tabla` WHERE 1=1";
$countSql = "SELECT COUNT(*) as total FROM `$tabla` WHERE 1=1";

// Agregar condición de búsqueda si existe término
if (!empty($search)) {
    $searchTerm = $conn->real_escape_string($search);
    $searchCondition = [];
    
    // Obtener todas las columnas de la tabla
    $columnsResult = $conn->query("SHOW COLUMNS FROM `$tabla`");
    while($column = $columnsResult->fetch_assoc()) {
        $searchCondition[] = "`{$column['Field']}` LIKE '%$searchTerm%'";
    }
    
    if (!empty($searchCondition)) {
        $searchSql = " AND (" . implode(" OR ", $searchCondition) . ")";
        $sql .= $searchSql;
        $countSql .= $searchSql;
    }
}

// Agregar límite y offset
$sql .= " LIMIT ? OFFSET ?";

// Ejecutar consulta de conteo
$totalResult = $conn->query($countSql);
$totalRow = $totalResult->fetch_assoc();
$total = $totalRow['total'];

// Preparar y ejecutar la consulta principal
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $limit, $offset);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

// Devolver resultado como JSON
header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'data' => $data,
    'total' => $total,
    'page' => $page,
    'limit' => $limit
]);