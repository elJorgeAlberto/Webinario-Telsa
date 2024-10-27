<?php
// ... (mismo contenido que mis_reservas.php, solo cambiando el título y referencias)
require_once __DIR__ . '/../src/conexion.php';

session_start();

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("location: login.php");
    exit;
}

// Obtener todos los webinarios
$sql = "SELECT * FROM webinarios ORDER BY fecha ASC";
$stmt = $conexion->prepare($sql);
$stmt->execute();
$webinarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/../views/header.php';
?>

<div class="container">
    <div class="page-header">
        <!-- Breadcrumb -->
        <nav class="breadcrumb">
            <ul>
                <li><a href="/">Inicio</a></li>
                <li class="current">Mis Eventos</li>
            </ul>
        </nav>

        <!-- Botón Regresar -->
        <a href="javascript:history.back()" class="back-button">
            <i class="fas fa-arrow-left"></i> Regresar
        </a>
    </div>

    <div class="reservas-container">
        <h2>Todos los Eventos</h2>
        
        <?php if (!empty($webinarios)): ?>
            <div class="webinar-grid">
                <?php foreach ($webinarios as $webinar): ?>
                    <div class="webinar-card">
                        <?php if($webinar['imagen']): ?>
                            <div class="webinar-card-image">
                                <img src="<?php echo htmlspecialchars($webinar['imagen']); ?>" alt="Imagen del webinario">
                            </div>
                        <?php endif; ?>
                        <div class="webinar-card-content">
                            <h4><?php echo htmlspecialchars($webinar['nombre']); ?></h4>
                            <p><strong>Fecha:</strong> <?php echo htmlspecialchars($webinar['fecha']); ?></p>
                            <p><strong>Hora:</strong> <?php echo htmlspecialchars($webinar['hora']); ?></p>
                            <p><strong>Categoría:</strong> <?php echo htmlspecialchars($webinar['categoria']); ?></p>
                            <p><strong>Cupos disponibles:</strong> <?php echo htmlspecialchars($webinar['cupos']); ?></p>
                        </div>
                        <div class="webinar-card-footer">
                            <a href="webinar_detalle.php?id=<?php echo $webinar['id']; ?>" class="btn btn-primary">Ver detalles</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="no-webinars">No hay eventos disponibles en este momento.</p>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../views/footer.php'; ?>
