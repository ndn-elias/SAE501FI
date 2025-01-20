<?php 
// Inclure l'en-tête du thème WordPress
get_header();
?>

<!-- MAIN -->
<?php
    the_content();
    $args = array(
        'post_type'      => 'projet',
        'posts_per_page' => 3,
        'orderby'        => 'date',
        'order'          => 'DESC',
        'post_status'    => 'publish',
    );
    $query = new WP_Query($args); // Ajouter $args ici

    if ( $query->have_posts() ) :
?>
        <div class="projets-container accueil-projets"> <!-- Ajout de la classe spécifique à la page d'accueil -->
            <?php 
                while ( $query->have_posts() ) : 
                    $query->the_post();
                    get_template_part('card', 'project');
                endwhile; 
            ?>
        </div>
    <?php else : ?>
        <p>Aucun projet trouvé.</p>
    <?php endif;

    wp_reset_postdata(); // INDISPENSABLE !! Permet de récupérer les données de la page courante
?>

<!-- FOOTER -->
<?php
// Inclure l'en-tête du thème WordPress
get_footer();
?>
