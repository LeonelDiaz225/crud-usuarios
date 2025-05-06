<?php
include "db.php";

$result = $conn->query("SELECT * FROM usuarios ORDER BY id DESC");
$rows = [];
while ($row = $result->fetch_assoc()) {
    $rows[] = $row;
}
echo json_encode($rows);
?>
