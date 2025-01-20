<?php
// Inclure l'en-tête du thème WordPress
get_header();
?>

<div class="student-page-content" style="min-height: calc(100vh - 80px); display: flex; flex-direction: column;">
    <?php
    // Assurez-vous que c'est une page auteur
    if (!is_author()) {
        wp_redirect(home_url());
        exit;
    }

    // Obtenez les informations de l'étudiant
    $curauth = get_queried_object();

    if ($curauth && in_array('etudiant', $curauth->roles)) {
        ?>
        <div class="student-details" style="margin: 20px;">
            <h1>Détails de l'étudiant</h1>
            <div class="student-info" style="border: 1px solid #ddd; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                <h2><?php echo esc_html($curauth->display_name); ?></h2>
                <p><strong>Email :</strong> <?php echo esc_html($curauth->user_email); ?></p>
                <p><strong>Description :</strong> <?php echo esc_html(get_user_meta($curauth->ID, 'description', true)); ?></p>
            </div>

            <div class="student-projects" style="margin-top: 20px;">
                <h2>Projets associés :</h2>

                <div class="projects-grid">
                    <?php
                    // Déterminer la page actuelle pour la pagination
                    $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;

                    // Requête pour récupérer les projets associés avec pagination
                    $args = array(
                        'post_type'  => 'projet',
                        'post_status' => 'publish',
                        'posts_per_page' => 3, // Nombre de projets par page
                        'paged' => $paged,
                        'meta_query' => array(
                            array(
                                'key'     => '_etudiants_associes',
                                'value'   => serialize(intval($curauth->ID)),
                                'compare' => 'LIKE',
                            ),
                        ),
                    );

                    $query = new WP_Query($args);

                    if ($query->have_posts()) {
                        while ($query->have_posts()) {
                            $query->the_post();

                            // Inclure la template part pour chaque projet
                            get_template_part('card', 'project');
                        }
                    } else {
                        echo '<p>Aucun projet associé trouvé.</p>';
                    }
                    ?>
                </div>
                <?php wp_reset_postdata(); ?>
            </div>
        </div>
        <?php
    } else {
        ?>
        <div class="error" style="margin: 20px; text-align: center;">
            <h1>Utilisateur introuvable ou non autorisé</h1>
            <p>Veuillez vérifier l'URL ou contacter l'administrateur du site.</p>
        </div>
        <?php
    }
    ?>
</div>

<?php
// Inclure le footer du thème WordPress
get_footer();
?>
