document.addEventListener('DOMContentLoaded', function() {
    // Toggle para el menú móvil
    const navbarToggle = document.querySelector('.navbar-toggle');
    const navbarMenu = document.querySelector('.navbar-menu');

    if (navbarToggle && navbarMenu) {
        navbarToggle.addEventListener('click', function() {
            navbarMenu.classList.toggle('active');
            // Opcional: Animar el icono de hamburguesa
            this.querySelector('i').classList.toggle('fa-bars');
            this.querySelector('i').classList.toggle('fa-times');
        });

        // Cerrar el menú al hacer clic fuera de él
        document.addEventListener('click', function(event) {
            if (!navbarToggle.contains(event.target) && !navbarMenu.contains(event.target)) {
                navbarMenu.classList.remove('active');
                navbarToggle.querySelector('i').classList.add('fa-bars');
                navbarToggle.querySelector('i').classList.remove('fa-times');
            }
        });
    }
});
