<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eventos y Webinarios Telsa</title>
    <link rel="stylesheet" href="/css/estilos.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <nav class="navbar">
        <div class="navbar-container">
            <div class="navbar-logo">
                <a href="/">
                    <img src="https://www.telsamayorista.com/Documents-FTP/Design/Images/Imagotipos/Imagotipo-Telsa-Medium-Pr-V2-251024 1.svg" alt="Logo" class="logo-img">
                </a>
            </div>
            <ul class="navbar-menu">
                <li><a href="/">Inicio</a></li>
                <li><a href="/nosotros">Nosotros</a></li>
                <li><a href="/contacto">Contacto</a></li>
                <?php if(isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
                    <li><a href="/logout.php" class="login-btn">Logout</a></li>
                <?php else: ?>
                    <li><a href="/login.php" class="login-btn">Login</a></li>
                <?php endif; ?>
            </ul>
            <div class="navbar-toggle">
                <i class="fas fa-bars"></i>
            </div>
        </div>
    </nav>
    <!-- Agregar el overlay -->
    <div class="menu-overlay"></div>
    <div class="container">
        <header class="main-header">
            <h1>Eventos y Webinarios Telsa</h1>
        </header>
    </div>
    <script src="/js/main.js"></script>
</body>
</html>
