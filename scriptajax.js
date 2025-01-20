document.addEventListener('DOMContentLoaded', function() {
    const formSelect = document.querySelector('#cours'); // Sélecteur du menu déroulant
    const resultsContainer = document.querySelector('#project-results'); // Conteneur des résultats
    const defaultProjectsContainer = document.querySelector('.projects-grid'); // Conteneur des projets initiaux
    const loader = document.createElement('div'); // Création dynamique du loader

    // Configuration du loader
    loader.className = 'loader'; // Classe CSS pour le loader
    loader.style.display = 'none';
    loader.style.margin = '20px auto';
    loader.innerHTML = '<div class="spinner"></div>'; // Style du loader
    resultsContainer.insertAdjacentElement('beforebegin', loader);

    // Fonction pour récupérer les projets via AJAX
    function fetchProjects(selectedCourse = '', paged = 1) {
        // Affiche le loader
        loader.style.display = 'block';
        resultsContainer.style.opacity = '0.5'; // Réduit l'opacité pendant le chargement

        jQuery.ajax({
            url: adminAjax,
            type: 'POST',
            data: {
                action: 'filter_projects',
                cours: selectedCourse, // Envoie la valeur sélectionnée
                paged: paged,          // Envoie la page actuelle
            },
            success: function(response) {
                console.log("Réponse AJAX :", response);

                // Supprime les projets initiaux
                if (defaultProjectsContainer) {
                    defaultProjectsContainer.innerHTML = '';
                }

                // Supprime toute pagination existante avant d'ajouter la nouvelle
                const existingPagination = document.querySelector('.pagination');
                if (existingPagination) {
                    existingPagination.remove();
                }

                // Affiche les résultats
                if (response.success) {
                    resultsContainer.innerHTML = response.data;

                    // Ajouter des événements sur les liens de pagination
                    const paginationLinks = resultsContainer.querySelectorAll('.pagination a');
                    paginationLinks.forEach(link => {
                        link.addEventListener('click', function(e) {
                            e.preventDefault();
                            const page = new URL(this.href).searchParams.get('paged') || 1;
                            fetchProjects(formSelect.value, page);
                        });
                    });
                } else {
                    resultsContainer.innerHTML = `<p>${response.data}</p>`;
                }
            },
            error: function() {
                resultsContainer.innerHTML = '<p>Une erreur est survenue.</p>';
            },
            complete: function() {
                // Cache le loader après la requête
                loader.style.display = 'none';
                resultsContainer.style.opacity = '1'; // Rétablit l'opacité
            }
        });
    }

    // Écouteur d'événements sur le changement de sélection
    formSelect.addEventListener('change', function () {
        fetchProjects(this.value);
    });
});
