<?php
require_once __DIR__ . '/../src/conexion.php';

session_start();

// Verificar si el usuario es administrador
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['rol'] !== 'admin') {
    header("location: login.php");
    exit;
}

// Obtener estadísticas generales
$sql = "SELECT 
            w.id,
            w.nombre,
            w.fecha,
            w.hora,
            w.cupos,
            COUNT(i.id) as total_inscritos,
            SUM(CASE WHEN i.estado = 'confirmado' THEN 1 ELSE 0 END) as confirmados,
            SUM(CASE WHEN i.estado = 'pendiente' THEN 1 ELSE 0 END) as pendientes,
            SUM(CASE WHEN i.estado = 'cancelado' THEN 1 ELSE 0 END) as cancelados,
            SUM(CASE WHEN i.asistencia = TRUE THEN 1 ELSE 0 END) as asistieron
        FROM webinarios w
        LEFT JOIN inscripciones i ON w.id = i.webinar_id
        GROUP BY w.id
        ORDER BY w.fecha DESC";

$stmt = $conexion->prepare($sql);
$stmt->execute();
$webinarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener datos para las gráficas
$sql_graficas = "SELECT 
    w.nombre,
    w.cupos,
    COUNT(i.id) as total_inscritos,
    SUM(CASE WHEN i.estado = 'confirmado' THEN 1 ELSE 0 END) as confirmados,
    SUM(CASE WHEN i.estado = 'pendiente' THEN 1 ELSE 0 END) as pendientes,
    SUM(CASE WHEN i.estado = 'cancelado' THEN 1 ELSE 0 END) as cancelados,
    SUM(CASE WHEN i.asistencia = TRUE THEN 1 ELSE 0 END) as asistieron
    FROM webinarios w
    LEFT JOIN inscripciones i ON w.id = i.webinar_id
    GROUP BY w.id";

$stmt = $conexion->prepare($sql_graficas);
$stmt->execute();
$datos_graficas = $stmt->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/../views/header.php';
?>

<!-- Agregar Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="container">
    <div class="page-header">
        <nav class="breadcrumb">
            <ul>
                <li><a href="/">Inicio</a></li>
                <li class="current">Dashboard Administrativo</li>
            </ul>
        </nav>
    </div>

    <div class="admin-dashboard">
        <h2>Dashboard Administrativo</h2>
        
        <div class="dashboard-cards">
            <?php foreach ($webinarios as $webinar): ?>
                <div class="dashboard-card">
                    <div class="card-header">
                        <h3><?php echo htmlspecialchars($webinar['nombre']); ?></h3>
                        <span class="date">
                            <?php echo date('d/m/Y', strtotime($webinar['fecha'])); ?> 
                            <?php echo date('H:i', strtotime($webinar['hora'])); ?> hrs
                        </span>
                    </div>
                    
                    <div class="stats-grid">
                        <div class="stat-item">
                            <span class="label">Total Inscritos</span>
                            <span class="value"><?php echo $webinar['total_inscritos']; ?>/<?php echo $webinar['cupos']; ?></span>
                        </div>
                        <div class="stat-item">
                            <span class="label">Confirmados</span>
                            <span class="value confirmados"><?php echo $webinar['confirmados']; ?></span>
                        </div>
                        <div class="stat-item">
                            <span class="label">Pendientes</span>
                            <span class="value pendientes"><?php echo $webinar['pendientes']; ?></span>
                        </div>
                        <div class="stat-item">
                            <span class="label">Cancelados</span>
                            <span class="value cancelados"><?php echo $webinar['cancelados']; ?></span>
                        </div>
                        <div class="stat-item">
                            <span class="label">Asistencia</span>
                            <span class="value"><?php echo $webinar['asistieron']; ?></span>
                        </div>
                    </div>

                    <div class="card-actions">
                        <a href="admin_webinar_detalle.php?id=<?php echo $webinar['id']; ?>" class="btn btn-primary">
                            <i class="fas fa-users"></i> Ver Participantes
                        </a>
                        <a href="webinar_detalle.php?id=<?php echo $webinar['id']; ?>" class="btn btn-secondary">
                            <i class="fas fa-edit"></i> Editar Webinar
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="graficas-section">
            <button class="btn-toggle-graficas" onclick="toggleGraficas()">
                <i class="fas fa-chart-bar"></i> Mostrar Gráficas
            </button>

            <div id="graficas-container" style="display: none;">
                <div class="graficas-grid">
                    <div class="grafica-card">
                        <h3>Inscripciones por Webinar</h3>
                        <canvas id="inscripcionesChart"></canvas>
                    </div>
                    <div class="grafica-card">
                        <h3>Estado de Inscripciones</h3>
                        <canvas id="estadosChart"></canvas>
                    </div>
                    <div class="grafica-card">
                        <h3>Asistencia vs Inscritos</h3>
                        <canvas id="asistenciaChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function toggleGraficas() {
    const container = document.getElementById('graficas-container');
    const button = document.querySelector('.btn-toggle-graficas');
    
    if (container.style.display === 'none') {
        container.style.display = 'block';
        button.innerHTML = '<i class="fas fa-chart-bar"></i> Ocultar Gráficas';
        initCharts();
    } else {
        container.style.display = 'none';
        button.innerHTML = '<i class="fas fa-chart-bar"></i> Mostrar Gráficas';
    }
}

function initCharts() {
    const datos = <?php echo json_encode($datos_graficas); ?>;
    
    // Gráfica de Inscripciones por Webinar
    new Chart(document.getElementById('inscripcionesChart'), {
        type: 'bar',
        data: {
            labels: datos.map(d => d.nombre),
            datasets: [{
                label: 'Total Inscritos',
                data: datos.map(d => d.total_inscritos),
                backgroundColor: '#1403FF',
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Gráfica de Estados
    new Chart(document.getElementById('estadosChart'), {
        type: 'pie',
        data: {
            labels: ['Confirmados', 'Pendientes', 'Cancelados'],
            datasets: [{
                data: [
                    datos.reduce((sum, d) => sum + parseInt(d.confirmados), 0),
                    datos.reduce((sum, d) => sum + parseInt(d.pendientes), 0),
                    datos.reduce((sum, d) => sum + parseInt(d.cancelados), 0)
                ],
                backgroundColor: ['#28a745', '#ffc107', '#dc3545']
            }]
        },
        options: {
            responsive: true
        }
    });

    // Gráfica de Asistencia
    new Chart(document.getElementById('asistenciaChart'), {
        type: 'bar',
        data: {
            labels: datos.map(d => d.nombre),
            datasets: [{
                label: 'Inscritos',
                data: datos.map(d => d.total_inscritos),
                backgroundColor: '#423AAA'
            }, {
                label: 'Asistieron',
                data: datos.map(d => d.asistieron),
                backgroundColor: '#1F3650'
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}
</script>

<?php include __DIR__ . '/../views/footer.php'; ?>
