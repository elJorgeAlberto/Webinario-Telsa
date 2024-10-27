<?php
// Definir la ruta base del proyecto
define('BASE_PATH', __DIR__ . '/..');

// Iniciar sesión
session_start();

// Incluir el archivo de conexión
require_once BASE_PATH . '/src/conexion.php';

// Obtener la ruta solicitada
$request_uri = $_SERVER['REQUEST_URI'];

// Manejar las rutas
if ($request_uri == '/' || $request_uri == '/index.php') {
    // Consulta para obtener los próximos webinarios
    $sql = "SELECT * FROM webinarios WHERE fecha >= CURDATE() ORDER BY fecha ASC";
    $stmt = $conexion->prepare($sql);
    $stmt->execute();
    $webinarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Incluir el encabezado
    include BASE_PATH . '/views/header.php';
    ?>

    <div class="main-content">
        <?php if(isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
            <!-- Contenido para usuarios autenticados -->
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
                            <a href="mis_eventos.php" class="btn btn-secondary">Ver próximos webinars</a>
                            <a href="mis_eventos.php" class="btn btn-secondary">Mis eventos</a>
                        </div>
                    </section>

                    <section class="stats-section">
                        <h3>Estadísticas</h3>
                        <div class="stats-grid">
                            <div class="stat-card">
                                <h4>Webinars Disponibles</h4>
                                <p class="stat-number">3</p>
                            </div>
                            <div class="stat-card">
                                <h4>Mis Reservas</h4>
                                <p class="stat-number">5</p>
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
        <?php else: ?>
            <!-- Contenido para usuarios no autenticados -->
            <div class="container">
                <section class="welcome-section">
                    <h2>Bienvenido a ReservasTelsa</h2>
                    <p>Descubre nuestros próximos webinarios y únete a la comunidad.</p>
                    <div class="cta-buttons">
                        <a href="login.php" class="btn btn-primary">Iniciar Sesión</a>
                        <a href="registro.php" class="btn btn-secondary">Registrarse</a>
                    </div>
                </section>

                <section class="webinars-section">
                    <h3>Próximos Webinarios</h3>
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
                                                <i class="fas fa-users"></i>
                                                <span><?php echo htmlspecialchars($webinar['cupos']); ?> cupos</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="webinar-card-footer">
                                        <a href="login.php" class="btn btn-primary">Iniciar sesión para ver más</a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="no-webinars">No hay webinarios próximos disponibles.</p>
                    <?php endif; ?>
                </section>
            </div>
        <?php endif; ?>
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
