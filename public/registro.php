<?php
require_once __DIR__ . '/../src/conexion.php';

session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $nombre = $_POST['nombre'];
    $email = $_POST['email'];

    // Validar que las contraseñas coincidan
    if ($password !== $confirm_password) {
        $error = "Las contraseñas no coinciden.";
    } else {
        // Verificar si el usuario ya existe
        $sql = "SELECT id FROM usuarios WHERE username = :username OR email = :email";
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $error = "El nombre de usuario o correo electrónico ya está en uso.";
        } else {
            // Insertar nuevo usuario
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO usuarios (username, password, nombre, email) VALUES (:username, :password, :nombre, :email)";
            $stmt = $conexion->prepare($sql);
            $stmt->bindParam(':username', $username, PDO::PARAM_STR);
            $stmt->bindParam(':password', $hashed_password, PDO::PARAM_STR);
            $stmt->bindParam(':nombre', $nombre, PDO::PARAM_STR);
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);

            if ($stmt->execute()) {
                $_SESSION['registro_exitoso'] = "Registro exitoso. Por favor, inicia sesión.";
                header("location: login.php");
                exit;
            } else {
                $error = "Ocurrió un error al registrar el usuario.";
            }
        }
    }
}

include __DIR__ . '/../views/header.php';
?>

<div class="login-container">
    <form class="login-form" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
        <h2>Registro de Usuario</h2>
        <?php if(isset($error)) { echo "<p class='error-message'>$error</p>"; } ?>
        <div class="form-group">
            <label for="username">Usuario:</label>
            <input type="text" id="username" name="username" required>
        </div>
        <div class="form-group">
            <label for="password">Contraseña:</label>
            <input type="password" id="password" name="password" required>
        </div>
        <div class="form-group">
            <label for="confirm_password">Confirmar Contraseña:</label>
            <input type="password" id="confirm_password" name="confirm_password" required>
        </div>
        <div class="form-group">
            <label for="nombre">Nombre:</label>
            <input type="text" id="nombre" name="nombre" required>
        </div>
        <div class="form-group">
            <label for="email">Correo Electrónico:</label>
            <input type="email" id="email" name="email" required>
        </div>
        <button type="submit" class="login-button">Registrarse</button>
    </form>
</div>

<?php include __DIR__ . '/../views/footer.php'; ?>
