<?php
// Inclure l'en-tête du thème WordPress
get_header();
?>
<h1 class="titre_page"> Tous Les Projets : </h1>

<!-- Formulaire de tri -->
<form class="form-tri-cours" method="POST" action="javascript:void(0);">
    <label for="cours" class="form-tri-label">Trier par cours :</label>
    <select name="cours" id="cours" class="form-tri-select">
        <option value="">Tous les cours</option>
        <?php
        $terms = get_terms([
            'taxonomy' => 'cours',
            'hide_empty' => false,
        ]);

        foreach ($terms as $term) {
            echo "<option value='{$term->slug}'>{$term->name}</option>";
        }
        ?>
    </select>
</form>

<div id="loader" class="loader"></div>
<div id="project-results" class="project-results">
	<!-- Conteneur des résultats -->
    <?php
    // Afficher les projets par défaut (tous)
    $query = new WP_Query([
        'post_type'      => 'project',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
    ]);

    if ($query->have_posts()) :
        while ($query->have_posts()) : $query->the_post();
            ?>
<div class="project-item">
    <h3><?php the_title(); ?></h3>
    <p><?php the_terms(get_the_ID(), 'cours', 'Cours : '); ?></p>
    <a href="<?php the_permalink(); ?>">
        <button class="view-more-btn">Voir +</button>
    </a>
</div>

            <?php
        endwhile;
        wp_reset_postdata();
    endif;
    ?>

</div>

<div class="projects-grid">
    <?php
    // Déterminer la page actuelle pour la pagination
    $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;


    // Vérifier si un cours est sélectionné
    if (!empty($_GET['cours'])) {
        $args['tax_query'] = [
            [
                'taxonomy' => 'cours', // Remplacez par le slug de votre taxonomie
                'field' => 'slug',
                'terms' => sanitize_text_field($_GET['cours']), // Term slug sélectionné
            ]
        ];
    }

    // Requête WordPress pour afficher les projets

    if (have_posts()) {
        while (have_posts()) {
            the_post();
            get_template_part('card', 'project');
        }
    } else {
        echo '<p>Aucun projet trouvé pour ce cours.</p>';
    }
    ?>
</div> <!-- End of .projects-grid -->

<!-- Pagination -->
<div class="pagination">
    <?php
    echo paginate_links([
        //'total' => max_num_pages, // Nombre total de pages
        'current' => $paged, // Page actuelle
        'format' => '?paged=%#%', // Structure des liens
        'prev_text' => __('<'), // Texte pour le lien précédent
        'next_text' => __('>'), // Texte pour le lien suivant
    ]);
    ?>
</div>



<!-- FOOTER -->
<?php
// Inclure le footer du thème WordPress
get_footer();
?>
