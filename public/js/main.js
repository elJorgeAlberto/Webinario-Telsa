document.addEventListener('DOMContentLoaded', function() {
    // Toggle para el menú móvil
    const navbarToggle = document.querySelector('.navbar-toggle');
    const navbarMenu = document.querySelector('.navbar-menu');
    const menuOverlay = document.querySelector('.menu-overlay');

    if (navbarToggle && navbarMenu && menuOverlay) {
        navbarToggle.addEventListener('click', function() {
            navbarMenu.classList.toggle('active');
            menuOverlay.classList.toggle('active');
            this.querySelector('i').classList.toggle('fa-bars');
            this.querySelector('i').classList.toggle('fa-times');
        });

        // Cerrar el menú al hacer clic en el overlay
        menuOverlay.addEventListener('click', function() {
            navbarMenu.classList.remove('active');
            menuOverlay.classList.remove('active');
            navbarToggle.querySelector('i').classList.add('fa-bars');
            navbarToggle.querySelector('i').classList.remove('fa-times');
        });

        // Cerrar el menú al hacer clic en un enlace
        navbarMenu.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', function() {
                navbarMenu.classList.remove('active');
                menuOverlay.classList.remove('active');
                navbarToggle.querySelector('i').classList.add('fa-bars');
                navbarToggle.querySelector('i').classList.remove('fa-times');
            });
        });
    }
});
