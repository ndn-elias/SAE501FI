document.addEventListener('DOMContentLoaded', function () {
    const mobileMenu = document.getElementById('mobile-menu');
    const navLinks = document.querySelector('.menu-container');
    const overlay = document.getElementById('menu-overlay');

    // Ajouter un événement pour activer/désactiver le menu et l'overlay
    mobileMenu.addEventListener('click', function () {
        navLinks.classList.toggle('active'); // Affiche ou cache le menu
        overlay.classList.toggle('active');  // Affiche ou cache l'overlay
    });

    // Fermer le menu si l'overlay est cliqué
    overlay.addEventListener('click', function () {
        navLinks.classList.remove('active'); // Cache le menu
        overlay.classList.remove('active');  // Cache l'overlay
    });
});

