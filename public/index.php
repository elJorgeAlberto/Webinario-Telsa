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

    // Incluir el encabezado
    include BASE_PATH . '/views/header.php';
    ?>

    <main class="dashboard">
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

        <section class="recent-webinars">
            <h3>Webinars Recientes</h3>
            <ul class="webinar-list">
                <li>Introducción a PHP - 15/05/2023</li>
                <li>Diseño Responsivo con CSS - 20/05/2023</li>
                <li>JavaScript Avanzado - 25/05/2023</li>
            </ul>
        </section>
    </main>

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
