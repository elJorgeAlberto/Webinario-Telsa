<?php
require_once __DIR__ . '/../src/conexion.php';

session_start();

// Verificar si el usuario es administrador
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['rol'] !== 'admin') {
    header("location: login.php");
    exit;
}

if (!isset($_GET['id'])) {
    header("location: admin_dashboard.php");
    exit;
}

$webinar_id = $_GET['id'];
$mensaje = ''; // Inicializar variable mensaje
$tipo_mensaje = ''; // Inicializar variable tipo_mensaje

// Agregar manejo de acciones POST para actualizar estado y asistencia
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    if ($_POST['action'] === 'update_status') {
        $inscripcion_id = $_POST['inscripcion_id'];
        $nuevo_estado = $_POST['nuevo_estado'];
        
        $sql = "UPDATE inscripciones SET estado = :estado WHERE id = :id";
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(':estado', $nuevo_estado);
        $stmt->bindParam(':id', $inscripcion_id);
        
        if ($stmt->execute()) {
            $mensaje = "Estado actualizado correctamente.";
            $tipo_mensaje = "success";
        } else {
            $mensaje = "Error al actualizar el estado.";
            $tipo_mensaje = "error";
        }
    } elseif ($_POST['action'] === 'update_attendance') {
        $inscripcion_id = $_POST['inscripcion_id'];
        $asistencia = $_POST['asistencia'];
        
        // Primero verificamos el valor actual de asistencia
        $sql = "SELECT asistencia FROM inscripciones WHERE id = :id";
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(':id', $inscripcion_id);
        $stmt->execute();
        $actual = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Actualizamos al valor opuesto del actual
        $nuevo_valor = $actual['asistencia'] ? '0' : '1';
        
        $sql = "UPDATE inscripciones SET asistencia = :asistencia WHERE id = :id";
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(':asistencia', $nuevo_valor, PDO::PARAM_STR);
        $stmt->bindParam(':id', $inscripcion_id);
        
        if ($stmt->execute()) {
            $mensaje = "Asistencia actualizada correctamente.";
            $tipo_mensaje = "success";
        } else {
            $mensaje = "Error al actualizar la asistencia.";
            $tipo_mensaje = "error";
        }
    }
}

// Modificar la consulta para incluir el ID de la inscripción
$sql = "SELECT 
            w.*,
            u.nombre as nombre_usuario,
            u.email,
            u.username,
            i.id as inscripcion_id,
            i.estado,
            i.fecha_inscripcion,
            i.asistencia
        FROM webinarios w
        LEFT JOIN inscripciones i ON w.id = i.webinar_id
        LEFT JOIN usuarios u ON i.usuario_id = u.id
        WHERE w.id = :webinar_id
        ORDER BY i.fecha_inscripcion DESC";

$stmt = $conexion->prepare($sql);
$stmt->bindParam(':webinar_id', $webinar_id);
$stmt->execute();
$participantes = $stmt->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/../views/header.php';
?>

<div class="container">
    <div class="page-header">
        <nav class="breadcrumb">
            <ul>
                <li><a href="/">Inicio</a></li>
                <li><a href="admin_dashboard.php">Dashboard</a></li>
                <li class="current">Participantes del Webinar</li>
            </ul>
        </nav>
        <a href="admin_dashboard.php" class="back-button">
            <i class="fas fa-arrow-left"></i> Volver al Dashboard
        </a>
    </div>

    <div class="participantes-list">
        <h2>Participantes - <?php echo htmlspecialchars($participantes[0]['nombre']); ?></h2>

        <?php if($mensaje): ?>
            <p class="<?php echo $tipo_mensaje === 'success' ? 'success-message' : 'error-message'; ?>">
                <?php echo $mensaje; ?>
            </p>
        <?php endif; ?>

        <div class="table-container">
            <table class="participantes-table">
                <thead>
                    <tr>
                        <th>Usuario</th>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Estado</th>
                        <th>Fecha Inscripción</th>
                        <th>Asistencia</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($participantes as $participante): ?>
                        <?php if ($participante['nombre_usuario']): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($participante['username']); ?></td>
                                <td><?php echo htmlspecialchars($participante['nombre_usuario']); ?></td>
                                <td><?php echo htmlspecialchars($participante['email']); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo $participante['estado']; ?>">
                                        <?php echo ucfirst($participante['estado']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('d/m/Y H:i', strtotime($participante['fecha_inscripcion'])); ?></td>
                                <td>
                                    <span class="asistencia-badge <?php echo $participante['asistencia'] ? 'asistio' : 'no-asistio'; ?>">
                                        <?php echo $participante['asistencia'] ? 'Sí' : 'No'; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn-icon" onclick="mostrarModalEstado(<?php echo $participante['inscripcion_id']; ?>, '<?php echo $participante['estado']; ?>')">
                                            <i class="fas fa-edit" title="Cambiar Estado"></i>
                                        </button>
                                        <button class="btn-icon" onclick="toggleAsistencia(<?php echo $participante['inscripcion_id']; ?>, <?php echo $participante['asistencia'] ? 'false' : 'true'; ?>)">
                                            <i class="fas fa-user-check" title="<?php echo $participante['asistencia'] ? 'Marcar No Asistió' : 'Marcar Asistió'; ?>"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal para cambiar estado -->
<div id="modalEstado" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h3>Cambiar Estado</h3>
        <form id="formEstado" method="POST">
            <input type="hidden" name="action" value="update_status">
            <input type="hidden" name="inscripcion_id" id="inscripcion_id">
            <div class="form-group">
                <label for="nuevo_estado">Estado:</label>
                <select name="nuevo_estado" id="nuevo_estado" required>
                    <option value="pendiente">Pendiente</option>
                    <option value="confirmado">Confirmado</option>
                    <option value="cancelado">Cancelado</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Guardar</button>
        </form>
    </div>
</div>

<script>
// Función para mostrar el modal de cambio de estado
function mostrarModalEstado(inscripcionId, estadoActual) {
    const modal = document.getElementById('modalEstado');
    document.getElementById('inscripcion_id').value = inscripcionId;
    document.getElementById('nuevo_estado').value = estadoActual;
    modal.style.display = 'block';
}

// Función para cambiar la asistencia
function toggleAsistencia(inscripcionId, nuevoValor) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.style.display = 'none';

    const actionInput = document.createElement('input');
    actionInput.type = 'hidden';
    actionInput.name = 'action';
    actionInput.value = 'update_attendance';

    const idInput = document.createElement('input');
    idInput.type = 'hidden';
    idInput.name = 'inscripcion_id';
    idInput.value = inscripcionId;

    const asistenciaInput = document.createElement('input');
    asistenciaInput.type = 'hidden';
    asistenciaInput.name = 'asistencia';
    asistenciaInput.value = nuevoValor;

    form.appendChild(actionInput);
    form.appendChild(idInput);
    form.appendChild(asistenciaInput);
    document.body.appendChild(form);
    form.submit();
}

// Cerrar el modal
document.querySelector('.close').onclick = function() {
    document.getElementById('modalEstado').style.display = 'none';
}

window.onclick = function(event) {
    const modal = document.getElementById('modalEstado');
    if (event.target == modal) {
        modal.style.display = 'none';
    }
}
</script>

<?php include __DIR__ . '/../views/footer.php'; ?>
