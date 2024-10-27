<?php
require_once __DIR__ . '/../src/conexion.php';

session_start();

// Verificar si el usuario ha iniciado sesión y es administrador
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['rol'] !== 'admin') {
    header("location: login.php");
    exit;
}

$mensaje = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST['nombre'];
    $fecha = $_POST['fecha'];
    $hora = $_POST['hora'];
    $link_sesion = $_POST['link_sesion'];
    $cupos = $_POST['cupos'];
    $descripcion = $_POST['descripcion'];
    $ponentes = $_POST['ponentes'];
    $duracion = $_POST['duracion'];
    $categoria = $_POST['categoria'];
    
    // Manejo de la imagen
    $imagen = '';
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
            $imagen = '/uploads/' . $imagen_nombre;
        } else {
            $error = "Error al subir la imagen. Código de error: " . $_FILES['imagen']['error'];
            // Opcional: Mostrar más información sobre el error
            error_log("Error al subir imagen: " . error_get_last()['message']);
        }
    }

    // Solo continuar con la inserción si no hay errores
    if (!isset($error)) {
        $sql = "INSERT INTO webinarios (nombre, fecha, hora, link_sesion, cupos, descripcion, ponentes, duracion, categoria, imagen, creado_por) 
                VALUES (:nombre, :fecha, :hora, :link_sesion, :cupos, :descripcion, :ponentes, :duracion, :categoria, :imagen, :creado_por)";
        
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(':nombre', $nombre, PDO::PARAM_STR);
        $stmt->bindParam(':fecha', $fecha, PDO::PARAM_STR);
        $stmt->bindParam(':hora', $hora, PDO::PARAM_STR);
        $stmt->bindParam(':link_sesion', $link_sesion, PDO::PARAM_STR);
        $stmt->bindParam(':cupos', $cupos, PDO::PARAM_INT);
        $stmt->bindParam(':descripcion', $descripcion, PDO::PARAM_STR);
        $stmt->bindParam(':ponentes', $ponentes, PDO::PARAM_STR);
        $stmt->bindParam(':duracion', $duracion, PDO::PARAM_STR);
        $stmt->bindParam(':categoria', $categoria, PDO::PARAM_STR);
        $stmt->bindParam(':imagen', $imagen, PDO::PARAM_STR);
        $stmt->bindParam(':creado_por', $_SESSION['id'], PDO::PARAM_INT);

        if ($stmt->execute()) {
            $mensaje = "Webinario creado con éxito.";
        } else {
            $mensaje = "Error al crear el webinario.";
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
                <li class="current">Crear Webinar</li>
            </ul>
        </nav>

        <!-- Botón Regresar -->
        <a href="javascript:history.back()" class="back-button">
            <i class="fas fa-arrow-left"></i> Regresar
        </a>
    </div>

    <div class="form-container">
        <h2>Crear Nuevo Webinario</h2>
        <?php if($mensaje): ?>
            <p class="<?php echo strpos($mensaje, 'éxito') !== false ? 'success-message' : 'error-message'; ?>"><?php echo $mensaje; ?></p>
        <?php endif; ?>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="nombre">Nombre del Webinario:</label>
                <input type="text" id="nombre" name="nombre" required>
            </div>
            <div class="form-group">
                <label for="categoria">Categoría:</label>
                <input type="text" id="categoria" name="categoria" required>
            </div>
            <div class="form-group">
                <label for="fecha">Fecha:</label>
                <input type="date" id="fecha" name="fecha" required>
            </div>
            <div class="form-group">
                <label for="hora">Hora:</label>
                <input type="time" id="hora" name="hora" required>
            </div>
            <div class="form-group">
                <label for="link_sesion">Link de sesión:</label>
                <input type="url" id="link_sesion" name="link_sesion" required>
            </div>
            <div class="form-group">
                <label for="cupos">Cupos:</label>
                <input type="number" id="cupos" name="cupos" required>
            </div>
            <div class="form-group">
                <label for="descripcion">Descripción:</label>
                <textarea id="descripcion" name="descripcion" required></textarea>
            </div>
            <div class="form-group">
                <label for="ponentes">Ponentes:</label>
                <input type="text" id="ponentes" name="ponentes" required>
            </div>
            <div class="form-group">
                <label for="duracion">Duración (en minutos):</label>
                <input type="number" id="duracion" name="duracion" min="1" required>
            </div>
            <div class="form-group">
                <label for="imagen">Imagen:</label>
                <input type="file" id="imagen" name="imagen" accept="image/*" required>
            </div>
            <div class="button-group">
                <button type="submit" class="btn btn-primary">Crear Webinario</button>
                <a href="javascript:history.back()" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../views/footer.php'; ?>
