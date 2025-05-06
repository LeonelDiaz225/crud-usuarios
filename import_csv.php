<?php
include "db.php";
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_FILES["csv_file"])) {
    $file = $_FILES["csv_file"]["tmp_name"];

    if (($handle = fopen($file, "r")) !== FALSE) {
        $firstRow = true;
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            // Saltear fila vacía o con menos de 6 columnas
            if (count($data) < 6 || empty($data[0])) {
                continue;
            }

            // Omitir encabezado si es la primera fila
            if ($firstRow) {
                $firstRow = false;
                // Omitir si la primera fila tiene texto como encabezado
                if (strtolower($data[0]) === 'apellido y nombre' || strtolower($data[0]) === 'apellido_nombre') {
                    continue;
                }
            }

            // Limpiar los valores
            $apellido_nombre = trim($data[0]);
            $cuit_dni = trim($data[1]);
            $razon_social = trim($data[2]);
            $telefono = trim($data[3]);
            $correo = trim($data[4]);
            $rubro = trim($data[5]);

            // Insertar en la base de datos
            $stmt = $conn->prepare("INSERT INTO usuarios (apellido_nombre, cuit_dni, razon_social, telefono, correo, rubro) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssss", $apellido_nombre, $cuit_dni, $razon_social, $telefono, $correo, $rubro);
            $stmt->execute();
        }
        fclose($handle);
        header("Location: index.php?import=success");
    exit;
    } else {
        echo "No se pudo abrir el archivo CSV.";
    }
} else {
    echo "Archivo CSV no recibido.";
}
?>
