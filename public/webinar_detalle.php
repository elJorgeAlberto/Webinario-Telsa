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

// Verificar si el usuario ya está inscrito
$sql = "SELECT * FROM inscripciones WHERE webinar_id = :webinar_id AND usuario_id = :usuario_id";
$stmt = $conexion->prepare($sql);
$stmt->bindParam(':webinar_id', $webinar_id);
$stmt->bindParam(':usuario_id', $_SESSION['id']);
$stmt->execute();
$inscrito = $stmt->rowCount() > 0;

// Procesar la actualización del webinario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action'])) {
        if ($_POST['action'] == 'update' && ($_SESSION['rol'] ?? '') === 'admin') {
            // Manejar la nueva imagen si se subió una
            $imagen = $webinar['imagen']; // Mantener la imagen actual por defecto
            if(isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0){
                // Crear el directorio si no existe
                $upload_dir = __DIR__ . '/uploads';
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }

                // Generar un nombre único para el archivo
                $extension = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
                $imagen_nombre = uniqid() . '.' . $extension;
                $imagen_tmp = $_FILES['imagen']['tmp_name'];
                $imagen_destino = $upload_dir . '/' . $imagen_nombre;

                // Intentar mover el archivo
                if (move_uploaded_file($imagen_tmp, $imagen_destino)) {
                    // Si hay una imagen anterior, la eliminamos
                    if($webinar['imagen'] && file_exists(__DIR__ . $webinar['imagen'])) {
                        unlink(__DIR__ . $webinar['imagen']);
                    }
                    $imagen = '/uploads/' . $imagen_nombre;
                } else {
                    $error = "Error al subir la nueva imagen.";
                }
            }

            // Actualizar webinario incluyendo la imagen
            $sql = "UPDATE webinarios SET 
                    nombre = :nombre,
                    fecha = :fecha,
                    hora = :hora,
                    link_sesion = :link_sesion,
                    cupos = :cupos,
                    descripcion = :descripcion,
                    ponentes = :ponentes,
                    duracion = :duracion,
                    categoria = :categoria,
                    imagen = :imagen
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
            $stmt->bindParam(':imagen', $imagen);
            $stmt->bindParam(':id', $webinar_id);

            if ($stmt->execute()) {
                $mensaje = "Webinario actualizado con éxito.";
                // Actualizar los datos mostrados
                $webinar = array_merge($webinar, $_POST, ['imagen' => $imagen]);
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

// Procesar la inscripción
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'inscribirse') {
    // Verificar si hay cupos disponibles
    if ($webinar['cupos'] > 0) {
        try {
            $conexion->beginTransaction();

            // Insertar inscripción
            $sql = "INSERT INTO inscripciones (webinar_id, usuario_id) VALUES (:webinar_id, :usuario_id)";
            $stmt = $conexion->prepare($sql);
            $stmt->bindParam(':webinar_id', $webinar_id);
            $stmt->bindParam(':usuario_id', $_SESSION['id']);
            $stmt->execute();

            // Actualizar cupos disponibles
            $sql = "UPDATE webinarios SET cupos = cupos - 1 WHERE id = :id AND cupos > 0";
            $stmt = $conexion->prepare($sql);
            $stmt->bindParam(':id', $webinar_id);
            $stmt->execute();

            $conexion->commit();
            $mensaje = "Te has inscrito exitosamente al webinar.";
            $inscrito = true;
            
            // Actualizar los cupos mostrados
            $webinar['cupos']--;
            
        } catch (Exception $e) {
            $conexion->rollBack();
            $mensaje = "Error al procesar la inscripción.";
        }
    } else {
        $mensaje = "Lo sentimos, no hay cupos disponibles.";
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
                <p><strong>Fecha:</strong> <?php echo date('d/m/Y', strtotime($webinar['fecha'])); ?></p>
                <p><strong>Hora:</strong> <?php echo date('H:i', strtotime($webinar['hora'])); ?> hrs</p>
                <p><strong>Cupos disponibles:</strong> <?php echo $webinar['cupos']; ?></p>
                <p><strong>Ponentes:</strong> <?php echo htmlspecialchars($webinar['ponentes']); ?></p>
                <p><strong>Duración:</strong> <?php echo $webinar['duracion']; ?> minutos</p>
                <div class="descripcion">
                    <h3>Descripción:</h3>
                    <p><?php echo nl2br(htmlspecialchars($webinar['descripcion'])); ?></p>
                </div>
                
                <div class="button-group">
                    <?php if(!$inscrito && $webinar['cupos'] > 0): ?>
                        <form action="" method="post" style="display: inline;">
                            <input type="hidden" name="action" value="inscribirse">
                            <button type="submit" class="btn btn-primary">Inscribirse al Webinar</button>
                        </form>
                    <?php elseif($inscrito): ?>
                        <?php if($webinar['link_sesion']): ?>
                            <a href="<?php echo htmlspecialchars($webinar['link_sesion']); ?>" class="btn btn-primary" target="_blank">
                                <i class="fas fa-video"></i> Unirse al Webinar
                            </a>
                        <?php endif; ?>
                        <p class="success-message">Ya estás inscrito en este webinar</p>
                    <?php else: ?>
                        <p class="error-message">No hay cupos disponibles</p>
                    <?php endif; ?>
                    <a href="javascript:history.back()" class="btn btn-secondary">Volver</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../views/footer.php'; ?>
