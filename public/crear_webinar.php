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
        $imagen_nombre = $_FILES['imagen']['name'];
        $imagen_tmp = $_FILES['imagen']['tmp_name'];
        $imagen_destino = __DIR__ . '/uploads/' . $imagen_nombre;
        move_uploaded_file($imagen_tmp, $imagen_destino);
        $imagen = '/uploads/' . $imagen_nombre;
    }

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

include __DIR__ . '/../views/header.php';
?>

<div class="container">
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
            <label for="duracion">Duración:</label>
            <input type="text" id="duracion" name="duracion" required>
        </div>
        <div class="form-group">
            <label for="categoria">Categoría:</label>
            <input type="text" id="categoria" name="categoria" required>
        </div>
        <div class="form-group">
            <label for="imagen">Imagen:</label>
            <input type="file" id="imagen" name="imagen" accept="image/*" required>
        </div>
        <button type="submit" class="btn btn-primary">Crear Webinario</button>
    </form>
</div>

<?php include __DIR__ . '/../views/footer.php'; ?>
