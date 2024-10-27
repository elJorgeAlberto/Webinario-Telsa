<?php
require_once __DIR__ . '/../src/conexion.php';

session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username_or_email = $_POST['username_or_email'];
    $password = $_POST['password'];

    $sql = "SELECT id, username, password, email, rol FROM usuarios WHERE username = :username_or_email OR email = :username_or_email";
    $stmt = $conexion->prepare($sql);
    $stmt->bindParam(':username_or_email', $username_or_email, PDO::PARAM_STR);
    $stmt->execute();

    if ($stmt->rowCount() == 1) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (password_verify($password, $row['password'])) {
            $_SESSION['loggedin'] = true;
            $_SESSION['id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['rol'] = $row['rol'];  // Guardamos el rol en la sesión
            header("location: index.php");
            exit;
        } else {
            $error = "La contraseña no es válida.";
        }
    } else {
        $error = "No existe cuenta con ese nombre de usuario o correo electrónico.";
    }
}

include __DIR__ . '/../views/header.php';
?>

<div class="login-container">
    <form class="login-form" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
        <h2>Iniciar Sesión</h2>
        <?php 
        if(isset($error)) { 
            echo "<p class='error-message'>$error</p>"; 
        }
        if(isset($_SESSION['logout_message'])) {
            echo "<p class='success-message'>" . $_SESSION['logout_message'] . "</p>";
            unset($_SESSION['logout_message']);
        }
        if(isset($_SESSION['registro_exitoso'])) {
            echo "<p class='success-message'>" . $_SESSION['registro_exitoso'] . "</p>";
            unset($_SESSION['registro_exitoso']);
        }
        ?>
        <div class="form-group">
            <label for="username_or_email">Usuario o Correo Electrónico:</label>
            <input type="text" id="username_or_email" name="username_or_email" required>
        </div>
        <div class="form-group">
            <label for="password">Contraseña:</label>
            <input type="password" id="password" name="password" required>
        </div>
        <button type="submit" class="login-button">Iniciar Sesión</button>
        <p class="register-link">¿No tienes una cuenta? <a href="registro.php">Regístrate aquí</a></p>
    </form>
</div>

<?php include __DIR__ . '/../views/footer.php'; ?>
