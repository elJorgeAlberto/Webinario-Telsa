<?php
// Definir la ruta base del proyecto
define('BASE_PATH', __DIR__ . '/..');

// Iniciar sesión
session_start();

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("location: login.php");
    exit;
}

// Incluir el archivo de conexión
require_once BASE_PATH . '/src/conexion.php';

// Obtener la ruta solicitada
$request_uri = $_SERVER['REQUEST_URI'];

// Manejar las rutas
if ($request_uri == '/' || $request_uri == '/index.php') {
    // Consulta para obtener el número de webinarios disponibles
    $sql = "SELECT COUNT(*) as total_webinarios FROM webinarios WHERE fecha >= CURDATE()";
    $stmt = $conexion->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $total_webinarios = $result['total_webinarios'];

    // Consulta para obtener los próximos webinarios
    $sql = "SELECT * FROM webinarios WHERE fecha >= CURDATE() ORDER BY fecha ASC LIMIT 5";
    $stmt = $conexion->prepare($sql);
    $stmt->execute();
    $webinarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Incluir el encabezado
    include BASE_PATH . '/views/header.php';
    ?>

    <div class="main-content">
        <main class="dashboard">
            <div class="container">
                <section class="welcome-section">
                    <h2>Bienvenido, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>
                    <p>Estás en el panel de control de ReservasTelsa.</p>
                </section>

                <section class="quick-actions">
                    <h3>Acciones Rápidas</h3>
                    <div class="action-buttons">
                        <?php if(($_SESSION['rol'] ?? 'usuario') === 'admin'): ?>
                            <a href="crear_webinar.php" class="btn btn-primary">Crear nuevo webinar</a>
                        <?php endif; ?>
                        <a href="#" class="btn btn-secondary">Ver próximos webinars</a>
                        <a href="#" class="btn btn-secondary">Mis reservas</a>
                    </div>
                </section>

                <section class="stats-section">
                    <h3>Estadísticas</h3>
                    <div class="stats-grid">
                        <div class="stat-card">
                            <h4>Webinars Disponibles</h4>
                            <p class="stat-number"><?php echo $total_webinarios; ?></p>
                        </div>
                        <div class="stat-card">
                            <h4>Mis Reservas</h4>
                            <p class="stat-number">3</p>
                        </div>
                        <div class="stat-card">
                            <h4>Webinars Completados</h4>
                            <p class="stat-number">5</p>
                        </div>
                    </div>
                </section>

                <section class="webinars-section">
                    <h3>Próximos Webinars</h3>
                    <?php if (!empty($webinarios)): ?>
                        <div class="webinar-grid">
                            <?php foreach ($webinarios as $webinar): ?>
                                <div class="webinar-card">
                                    <div class="webinar-card-content">
                                        <h4><?php echo htmlspecialchars($webinar['nombre']); ?></h4>
                                        <p>Fecha: <?php echo htmlspecialchars($webinar['fecha']); ?></p>
                                        <p>Hora: <?php echo htmlspecialchars($webinar['hora']); ?></p>
                                    </div>
                                    <div class="webinar-card-footer">
                                        <a href="webinar_detalle.php?id=<?php echo $webinar['id']; ?>" class="btn btn-secondary">Ver detalles</a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p>No hay webinars próximos disponibles.</p>
                    <?php endif; ?>
                </section>
            </div>
        </main>
    </div>

    <?php
    // Incluir el pie de página
    include BASE_PATH . '/views/footer.php';
} elseif (strpos($request_uri, '/css/') === 0) {
    // Servir archivos CSS
    $css_file = BASE_PATH . '/public' . $request_uri;
    if (file_exists($css_file)) {
        header('Content-Type: text/css');
        readfile($css_file);
    } else {
        http_response_code(404);
        echo "Archivo CSS no encontrado";
    }
} else {
    // Manejar otras rutas o mostrar un error 404
    http_response_code(404);
    echo "Página no encontrada";
}
?>
