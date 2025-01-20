<?php
get_header();

global $wp_query; // Récupération de la Query stockée dans la variable globale
?>

<main id="main-content">
    <header>
        <h1>
            <?php echo $wp_query->found_posts; ?> résultat(s) trouvé(s) pour : 
            <strong><?php echo get_search_query(); ?></strong>
        </h1>
    </header>

    <?php if ( have_posts() ) : ?>
        <div class="search-results-grid">
            <?php
            // Boucle WordPress pour afficher les résultats
            while ( have_posts() ) : the_post(); ?>
                <?php
                // Appel à la template part pour chaque résultat
                get_template_part('card', 'project');
                ?>
            <?php endwhile; ?>
        </div>

        <!-- Pagination -->
        <div class="pagination">
            <?php
            echo paginate_links( array(
                'total' => $wp_query->max_num_pages,
                'current' => max( 1, get_query_var( 'paged' ) ),
                'prev_text' => '&laquo; Précédent',
                'next_text' => 'Suivant &raquo;',
            ) );
            ?>
        </div>
    <?php else : ?>
        <!-- Message si aucun résultat -->
        <p>Aucun résultat trouvé pour <strong><?php echo get_search_query(); ?></strong>. Essayez un autre mot-clé.</p>
    <?php endif; ?>
</main>

<?php get_footer(); ?>
