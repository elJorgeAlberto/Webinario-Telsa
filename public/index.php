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
    // Consulta para obtener el número de webinarios disponibles
    $sql = "SELECT COUNT(*) as total_webinarios FROM webinarios WHERE fecha >= CURDATE()";
    $stmt = $conexion->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $total_webinarios = $result['total_webinarios'];

    // Modificar la consulta para incluir el conteo de inscripciones para admins
    if (($_SESSION['rol'] ?? '') === 'admin') {
        $sql = "SELECT w.*, 
                (SELECT COUNT(*) FROM inscripciones WHERE webinar_id = w.id) as total_inscritos
                FROM webinarios w 
                WHERE w.fecha >= CURDATE() 
                ORDER BY w.fecha ASC";
        $stmt = $conexion->prepare($sql);
    } else {
        // Mantener la consulta original para usuarios normales
        $sql = "SELECT w.*, 
                CASE 
                    WHEN i.id IS NOT NULL THEN i.estado
                    ELSE NULL
                END as estado_inscripcion
                FROM webinarios w 
                LEFT JOIN inscripciones i ON w.id = i.webinar_id AND i.usuario_id = :usuario_id 
                WHERE w.fecha >= CURDATE() 
                ORDER BY w.fecha ASC";
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(':usuario_id', $_SESSION['id'], PDO::PARAM_INT);
    }
    $stmt->execute();
    $webinarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Consultas para estadísticas de inscripciones del usuario
    if(isset($_SESSION['id'])) {
        // Total de inscripciones confirmadas
        $sql = "SELECT COUNT(*) as total_confirmados FROM inscripciones 
                WHERE usuario_id = :usuario_id AND estado = 'confirmado'";
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(':usuario_id', $_SESSION['id']);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $total_confirmados = $result['total_confirmados'];

        // Total de inscripciones pendientes
        $sql = "SELECT COUNT(*) as total_pendientes FROM inscripciones 
                WHERE usuario_id = :usuario_id AND estado = 'pendiente'";
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(':usuario_id', $_SESSION['id']);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $total_pendientes = $result['total_pendientes'];

        // Total de inscripciones canceladas
        $sql = "SELECT COUNT(*) as total_cancelados FROM inscripciones 
                WHERE usuario_id = :usuario_id AND estado = 'cancelado'";
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(':usuario_id', $_SESSION['id']);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $total_cancelados = $result['total_cancelados'];

        // Total de webinars completados (asistencia)
        if(($_SESSION['rol'] ?? '') === 'admin') {
            // Total de webinarios realizados
            $sql = "SELECT COUNT(*) as total_completados FROM webinarios WHERE estado = 2";
            $stmt = $conexion->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $total_completados = $result['total_completados'] ?? 0; // Añadido valor por defecto
        } else {
            // Para usuarios normales, contar asistencias
            $sql = "SELECT COUNT(*) as total_completados FROM inscripciones 
                    WHERE usuario_id = :usuario_id AND asistencia = TRUE";
            $stmt = $conexion->prepare($sql);
            $stmt->bindParam(':usuario_id', $_SESSION['id']);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $total_completados = $result['total_completados'] ?? 0; // Añadido valor por defecto
        }
    }

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
                        <p>Regístrate a nuestros webinarios</p>
                    </section>

                    <?php if(($_SESSION['rol'] ?? 'usuario') === 'admin'): ?>
                        <section class="quick-actions">
                            <h3>Acciones Rápidas</h3>
                            <div class="action-buttons">
                                <a href="crear_webinar.php" class="btn btn-primary">Crear nuevo webinar</a>
                                <a href="admin_dashboard.php" class="btn btn-primary">
                                    <i class="fas fa-chart-line"></i> Dashboard Admin
                                </a>
                                <a href="mis_eventos.php" class="btn btn-secondary">Mis eventos</a>
                            </div>
                        </section>
                    <?php endif; ?>

                    <section class="stats-section">
                        <div class="stats-grid">
                            <div class="stat-card">
                                <h4>Webinars Disponibles</h4>
                                <p class="stat-number"><?php echo $total_webinarios; ?></p>
                            </div>

                            <?php if(($_SESSION['rol'] ?? 'usuario') === 'usuario'): ?>
                                <div class="stat-card">
                                    <h4>Estado de Inscripciones</h4>
                                    <div class="stat-details">
                                        <div class="stat-item confirmados"> 
                                            <span><i class="fas fa-check-circle"></i> Confirmados: <?php echo $total_confirmados; ?></span>
                                        </div>
                                        <div class="stat-item pendientes">                                
                                           <span> <i class="fas fa-clock"></i>Pendientes: <?php echo $total_pendientes; ?></span>
                                        </div>
                                        <div class="stat-item cancelados">
                                            <span><i class="fas fa-times-circle"></i>Cancelados: <?php echo $total_cancelados; ?></span>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <div class="stat-card">
                                <h4>Webinars Completados</h4>
                                <p class="stat-number"><?php echo isset($total_completados) ? $total_completados : '0'; ?></p>
                            </div>
                        </div>
                    </section>

                    <section class="webinars-section">
                        <h3>Próximos Webinars</h3>
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
                                                <?php if(($_SESSION['rol'] ?? '') === 'admin'): ?>
                                                    <div class="info-item">
                                                        <i class="fas fa-user-check"></i>
                                                        <span class="inscritos-count">
                                                            <?php echo $webinar['total_inscritos']; ?> inscritos
                                                        </span>
                                                    </div>
                                                <?php endif; ?>
                                                
                                                <?php if(($_SESSION['rol'] ?? '') === 'usuario'): ?>
                                                    <div class="info-item">
                                                        <i class="fas fa-tag"></i>
                                                        <span><?php echo htmlspecialchars($webinar['categoria']); ?></span>
                                                    </div>
                                                    <div class="info-item">
                                                        <i class="fas fa-user-tie"></i>
                                                        <span><?php echo htmlspecialchars($webinar['ponentes']); ?></span>
                                                    </div>
                                                    <div class="info-item">
                                                        <i class="fas fa-hourglass-half"></i>
                                                        <span><?php echo htmlspecialchars($webinar['duracion']); ?> minutos</span>
                                                    </div>
                                                    <div class="info-item webinar-description">
                                                        <i class="fas fa-info-circle"></i>
                                                        <span><?php echo substr(htmlspecialchars($webinar['descripcion']), 0, 100); ?>...</span>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="webinar-card-footer">
                                            <?php if(isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
                                                <a href="webinar_detalle.php?id=<?php echo $webinar['id']; ?>" class="btn btn-secondary">Ver detalles</a>
                                                <?php if(isset($webinar['estado_inscripcion']) && $webinar['estado_inscripcion'] === 'confirmado' && $webinar['link_sesion']): ?>
                                                    <a href="<?php echo htmlspecialchars($webinar['link_sesion']); ?>" class="btn btn-primary" target="_blank">
                                                        <i class="fas fa-video"></i> Unirse
                                                    </a>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <a href="login.php" class="btn btn-primary">Iniciar sesión para ver más</a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="no-webinars">No hay webinarios próximos disponibles.</p>
                        <?php endif; ?>
                    </section>
                </div>
            </main>
        <?php else: ?>
            <!-- Contenido para usuarios no autenticados -->
            <div class="container">
                <section class="welcome-section">
                    <h2>Bienvenido a </h2>
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
                                            <?php if(isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
                                                <?php if(isset($webinar['estado_inscripcion'])): ?>
                                                    <div class="info-item">
                                                        <?php if($webinar['estado_inscripcion'] === 'pendiente'): ?>
                                                            <i class="fas fa-clock"></i>
                                                        <?php elseif($webinar['estado_inscripcion'] === 'confirmado'): ?>
                                                            <i class="fas fa-check-circle"></i>
                                                        <?php elseif($webinar['estado_inscripcion'] === 'cancelado'): ?>
                                                            <i class="fas fa-times-circle"></i>
                                                        <?php endif; ?>
                                                        <span class="status-<?php echo $webinar['estado_inscripcion']; ?>">
                                                            <?php echo ucfirst($webinar['estado_inscripcion']); ?>
                                                        </span>
                                                    </div>
                                                <?php else: ?>
                                                    <div class="info-item">
                                                        <i class="fas fa-info-circle"></i>
                                                        <span class="status-no-inscrito">No inscrito</span>
                                                    </div>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="webinar-card-footer">
                                        <?php if(isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
                                            <a href="webinar_detalle.php?id=<?php echo $webinar['id']; ?>" class="btn btn-secondary">Ver detalles</a>
                                            <?php if(isset($webinar['estado_inscripcion']) && $webinar['estado_inscripcion'] === 'confirmado' && $webinar['link_sesion']): ?>
                                                <a href="<?php echo htmlspecialchars($webinar['link_sesion']); ?>" class="btn btn-primary" target="_blank">
                                                    <i class="fas fa-video"></i> Unirse
                                                </a>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <a href="login.php" class="btn btn-primary">Iniciar sesión para ver más</a>
                                        <?php endif; ?>
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
