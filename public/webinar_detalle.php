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
$mensaje = '';

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

// Procesar la actualización del webinario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action'])) {
        if ($_POST['action'] == 'update' && ($_SESSION['rol'] ?? '') === 'admin') {
            // Actualizar webinario
            $sql = "UPDATE webinarios SET 
                    nombre = :nombre,
                    fecha = :fecha,
                    hora = :hora,
                    link_sesion = :link_sesion,
                    cupos = :cupos,
                    descripcion = :descripcion,
                    ponentes = :ponentes,
                    duracion = :duracion,
                    categoria = :categoria
                    WHERE id = :id";
            
            $stmt = $conexion->prepare($sql);
            $stmt->bindParam(':nombre', $_POST['nombre']);
            $stmt->bindParam(':fecha', $_POST['fecha']);
            $stmt->bindParam(':hora', $_POST['hora']);
            $stmt->bindParam(':link_sesion', $_POST['link_sesion']);
            $stmt->bindParam(':cupos', $_POST['cupos']);
            $stmt->bindParam(':descripcion', $_POST['descripcion']);
            $stmt->bindParam(':ponentes', $_POST['ponentes']);
            $stmt->bindParam(':duracion', $_POST['duracion']);
            $stmt->bindParam(':categoria', $_POST['categoria']);
            $stmt->bindParam(':id', $webinar_id);

            if ($stmt->execute()) {
                $mensaje = "Webinario actualizado con éxito.";
                // Actualizar los datos mostrados
                $webinar = array_merge($webinar, $_POST);
            } else {
                $mensaje = "Error al actualizar el webinario.";
            }
        } elseif ($_POST['action'] == 'delete' && ($_SESSION['rol'] ?? '') === 'admin') {
            // Eliminar webinario
            $sql = "DELETE FROM webinarios WHERE id = :id";
            $stmt = $conexion->prepare($sql);
            $stmt->bindParam(':id', $webinar_id);
            
            if ($stmt->execute()) {
                header("location: index.php");
                exit;
            } else {
                $mensaje = "Error al eliminar el webinario.";
            }
        }
    }
}

include __DIR__ . '/../views/header.php';
?>

<div class="container">
    <div class="page-header">
        <!-- Breadcrumb -->
        <nav class="breadcrumb">
            <ul>
                <li><a href="/">Inicio</a></li>
                <li><a href="/index.php">Dashboard</a></li>
                <li class="current">Detalles del Webinar</li>
            </ul>
        </nav>

        <!-- Botón Regresar -->
        <a href="javascript:history.back()" class="back-button">
            <i class="fas fa-arrow-left"></i> Regresar
        </a>
    </div>

    <div class="webinar-detail">
        <?php if($mensaje): ?>
            <p class="<?php echo strpos($mensaje, 'éxito') !== false ? 'success-message' : 'error-message'; ?>"><?php echo $mensaje; ?></p>
        <?php endif; ?>

        <?php if(($_SESSION['rol'] ?? '') === 'admin'): ?>
            <!-- Formulario de edición para administradores -->
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . "?id=" . $webinar_id); ?>" method="post" class="form-container" enctype="multipart/form-data">
                <h2>Editar Webinario</h2>
                
                <!-- Mostrar imagen actual -->
                <?php if($webinar['imagen']): ?>
                    <div class="current-image">
                        <img src="<?php echo htmlspecialchars($webinar['imagen']); ?>" alt="Imagen del webinario">
                    </div>
                <?php endif; ?>

                <div class="form-group">
                    <label for="nombre">Nombre del Webinario:</label>
                    <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($webinar['nombre']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="categoria">Categoría:</label>
                    <input type="text" id="categoria" name="categoria" value="<?php echo htmlspecialchars($webinar['categoria']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="fecha">Fecha:</label>
                    <input type="date" id="fecha" name="fecha" value="<?php echo $webinar['fecha']; ?>" required>
                </div>
                <div class="form-group">
                    <label for="hora">Hora:</label>
                    <input type="time" id="hora" name="hora" value="<?php echo $webinar['hora']; ?>" required>
                </div>
                <div class="form-group">
                    <label for="link_sesion">Link de sesión:</label>
                    <input type="url" id="link_sesion" name="link_sesion" value="<?php echo htmlspecialchars($webinar['link_sesion']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="cupos">Cupos:</label>
                    <input type="number" id="cupos" name="cupos" value="<?php echo $webinar['cupos']; ?>" required>
                </div>
                <div class="form-group">
                    <label for="descripcion">Descripción:</label>
                    <textarea id="descripcion" name="descripcion" required><?php echo htmlspecialchars($webinar['descripcion']); ?></textarea>
                </div>
                <div class="form-group">
                    <label for="ponentes">Ponentes:</label>
                    <input type="text" id="ponentes" name="ponentes" value="<?php echo htmlspecialchars($webinar['ponentes']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="duracion">Duración (en minutos):</label>
                    <input type="number" id="duracion" name="duracion" value="<?php echo $webinar['duracion']; ?>" required>
                </div>
                <div class="form-group">
                    <label for="imagen">Nueva imagen (opcional):</label>
                    <input type="file" id="imagen" name="imagen" accept="image/*">
                </div>
                <div class="button-group">
                    <input type="hidden" name="action" value="update">
                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                    <button type="submit" name="action" value="delete" class="btn btn-danger" onclick="return confirm('¿Estás seguro de que deseas eliminar este webinario?')">Eliminar Webinario</button>
                    <a href="javascript:history.back()" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        <?php else: ?>
            <!-- Vista de solo lectura para usuarios normales -->
            <div class="webinar-info">
                <?php if($webinar['imagen']): ?>
                    <div class="webinar-image">
                        <img src="<?php echo htmlspecialchars($webinar['imagen']); ?>" alt="Imagen del webinario">
                    </div>
                <?php endif; ?>

                <h2><?php echo htmlspecialchars($webinar['nombre']); ?></h2>
                <p><strong>Categoría:</strong> <?php echo htmlspecialchars($webinar['categoria']); ?></p>
                <p><strong>Fecha:</strong> <?php echo $webinar['fecha']; ?></p>
                <p><strong>Hora:</strong> <?php echo $webinar['hora']; ?></p>
                <p><strong>Cupos disponibles:</strong> <?php echo $webinar['cupos']; ?></p>
                <p><strong>Ponentes:</strong> <?php echo htmlspecialchars($webinar['ponentes']); ?></p>
                <p><strong>Duración:</strong> <?php echo $webinar['duracion']; ?> minutos</p>
                <div class="descripcion">
                    <h3>Descripción:</h3>
                    <p><?php echo nl2br(htmlspecialchars($webinar['descripcion'])); ?></p>
                </div>
                <?php if($webinar['link_sesion']): ?>
                    <a href="<?php echo htmlspecialchars($webinar['link_sesion']); ?>" class="btn btn-primary" target="_blank">Unirse al Webinar</a>
                <?php endif; ?>
                <div class="button-group">
                    <a href="javascript:history.back()" class="btn btn-secondary">Volver al Dashboard</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../views/footer.php'; ?>
