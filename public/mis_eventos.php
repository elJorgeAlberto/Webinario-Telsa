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
                            <div class="webinar-info-grid">
                                <div class="info-item">
                                    <i class="fas fa-calendar"></i>
                                    <span><?php echo date('d/m/Y', strtotime($webinar['fecha'])); ?></span>
                                </div>
                                <div class="info-item">
                                    <i class="fas fa-clock"></i>
                                    <span><?php echo date('H:i', strtotime($webinar['hora'])); ?> hrs</span>
                                </div>
                                <div class="info-item">
                                    <i class="fas fa-tag"></i>
                                    <span><?php echo htmlspecialchars($webinar['categoria']); ?></span>
                                </div>
                                <div class="info-item">
                                    <i class="fas fa-users"></i>
                                    <span><?php echo htmlspecialchars($webinar['cupos']); ?> cupos</span>
                                </div>
                                <div class="info-item">
                                    <i class="fas fa-user-tie"></i>
                                    <span><?php echo htmlspecialchars($webinar['ponentes']); ?></span>
                                </div>
                                <div class="info-item">
                                    <i class="fas fa-hourglass-half"></i>
                                    <span><?php echo htmlspecialchars($webinar['duracion']); ?> minutos</span>
                                </div>
                            </div>
                            <div class="webinar-description">
                                <p><?php echo substr(htmlspecialchars($webinar['descripcion']), 0, 100); ?>...</p>
                            </div>
                        </div>
                        <div class="webinar-card-footer">
                            <a href="webinar_detalle.php?id=<?php echo $webinar['id']; ?>" class="btn btn-primary">Ver detalles</a>
                            <?php if($webinar['link_sesion']): ?>
                                <a href="<?php echo htmlspecialchars($webinar['link_sesion']); ?>" class="btn btn-secondary" target="_blank">
                                    <i class="fas fa-video"></i> Unirse
                                </a>
                            <?php endif; ?>
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
