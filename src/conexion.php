<?php
require_once __DIR__ . '/../config/database.php';

try {
    $conexion = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conexion->exec("SET NAMES utf8");
} catch(PDOException $e) {
    echo "Error de conexión: " . $e->getMessage();
    exit;
}
?>
