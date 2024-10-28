<?php
require_once __DIR__ . '/../src/conexion.php';

session_start();

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("location: login.php");
    exit;
}

// Verificar si se proporcionó un ID de webinario
if (!isset($_GET['id'])) {
    header("location: index.php");
    exit;
}

$webinar_id = $_GET['id'];

// Obtener los detalles del webinario
$sql = "SELECT * FROM webinarios WHERE id = :id";
$stmt = $conexion->prepare($sql);
$stmt->bindParam(':id', $webinar_id, PDO::PARAM_INT);
$stmt->execute();

if ($stmt->rowCount() == 0) {
    header("location: index.php");
    exit;
}

$webinar = $stmt->fetch(PDO::FETCH_ASSOC);

// Función para formatear la fecha/hora para ICS
function formatDateTime($date, $time) {
    return date('Ymd\THis', strtotime("$date $time"));
}

// Crear el contenido del archivo ICS
$ics_content = "BEGIN:VCALENDAR\r\n";
$ics_content .= "VERSION:2.0\r\n";
$ics_content .= "PRODID:-//ReservasTelsa//Webinarios//ES\r\n";
$ics_content .= "CALSCALE:GREGORIAN\r\n";
$ics_content .= "METHOD:PUBLISH\r\n";
$ics_content .= "BEGIN:VEVENT\r\n";
$ics_content .= "UID:" . uniqid() . "@reservastelsa.com\r\n";
$ics_content .= "DTSTAMP:" . formatDateTime(date('Y-m-d'), date('H:i:s')) . "\r\n";
$ics_content .= "DTSTART:" . formatDateTime($webinar['fecha'], $webinar['hora']) . "\r\n";
$ics_content .= "DTEND:" . formatDateTime($webinar['fecha'], date('H:i:s', strtotime($webinar['hora'] . ' + ' . $webinar['duracion'] . ' minutes'))) . "\r\n";
$ics_content .= "SUMMARY:" . str_replace(["\r", "\n"], " ", $webinar['nombre']) . "\r\n";
$ics_content .= "DESCRIPTION:" . str_replace(["\r", "\n"], "\\n", $webinar['descripcion']) . 
                "\\n\\nEnlace para unirse: " . $webinar['link_sesion'] . "\r\n";
$ics_content .= "URL:" . $webinar['link_sesion'] . "\r\n";
$ics_content .= "STATUS:CONFIRMED\r\n";
$ics_content .= "SEQUENCE:0\r\n";
$ics_content .= "END:VEVENT\r\n";
$ics_content .= "END:VCALENDAR\r\n";

// Configurar los headers para la descarga
header('Content-Type: text/calendar; charset=utf-8');
header('Content-Disposition: attachment; filename="webinar_' . $webinar_id . '.ics"');

// Enviar el contenido del archivo
echo $ics_content;
exit;
