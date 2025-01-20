<?php
// ENQUEUE SCRIPTS & STYLES
if ( !function_exists( 'mmi_enqueues' ) ) { // On vérifie que cela n'a pas déjà été chargé
    function mmi_enqueues() {
        // Récupère les données du thème
        $theme = wp_get_theme();
        $theme_version = $theme->get('Version');
        global $post;

        // Chargement du fichier style.css dans dist
        wp_enqueue_style('main-css', get_template_directory_uri() . '/dist/style.css', array(), $theme_version);
		
		// Chargement du fichier master.js dans dist
		wp_enqueue_script('main-js', get_template_directory_uri() . '/dist/master.js', array(), 1.0);
		wp_enqueue_script('ajax-projects', get_template_directory_uri() . '/dist/scriptajax.js', array('jquery'), '1.0', true);
		wp_localize_script( 'ajax-projects', 'adminAjax', admin_url( 'admin-ajax.php') );

    }
}

add_action( 'wp_enqueue_scripts', 'mmi_enqueues' );




// ENREGISTREMENT DES EMPLACEMENTS MENUS
function mmi_theme_register_menus() {
    register_nav_menus(array(
        'menu-principal' => __('Menu Principal', 'mmi-theme'),
        'menu-footer'    => __('Menu Pied de page', 'mmi-theme'),
    ));
}
add_action('init', 'mmi_theme_register_menus');



register_sidebar( array(
    'id'            => 'main-footer',
    'name'          => 'Footer principal',
    'before_widget' => '<div class="footer-widget">', /* Change li par div */
    'after_widget'  => '</div>',
    'before_title'  => '<h4 class="footer-title">',
    'after_title'   => '</h4>',
) );

function mmi_say_hello( $atts ) {
    // Récupération des infos de l'utilisateur connecté
    $current_user = wp_get_current_user();

    // Vérification si l'utilisateur est connecté
    if ( is_user_logged_in() && !empty( $current_user->user_firstname ) ) {
        $output = '<div class="hello-message">Bienvenue ' . esc_html( $current_user->user_firstname ) . ' sur mon site de projets !</div>';
    } else {
        // Message par défaut si l'utilisateur n'est pas connecté
        $output = '<div class="hello-message">Bienvenue Visiteur sur mon site de projets !</div>';
    }

    return $output; // Retourne la valeur
}
add_shortcode( 'say-hello', 'mmi_say_hello' );


function mmi_enqueues_login() {
	wp_enqueue_style('login-page', get_template_directory_uri() . '/dist/style-login.css', array('login'));
}
add_action( 'login_enqueue_scripts', 'mmi_enqueues_login' );

function mmi_custom_login_logo_url() {
    return home_url(); // Redirige vers la page d'accueil de votre site
}
add_filter('login_headerurl', 'mmi_custom_login_logo_url');



// Requête AJAX pour filtrer les projets par matière (taxonomie 'cours')
function mmi_filter_projects_by_course() {
    $cours_slug = isset($_POST['cours']) ? sanitize_text_field($_POST['cours']) : '';
    $paged = isset($_POST['paged']) ? intval($_POST['paged']) : 1;

    $args = array(
        'post_type'      => 'projet',
        'post_status'    => 'publish',
        'posts_per_page' => 3, // Limite de projets par page
        'paged'          => $paged, // Numéro de page actuel
    );

    if (!empty($cours_slug)) {
        $args['tax_query'] = array(
            array(
                'taxonomy' => 'cours',
                'field'    => 'slug',
                'terms'    => $cours_slug,
            ),
        );
    }

    $query = new WP_Query($args);

    if ($query->have_posts()) {
        ob_start();
        echo '<div class="projects-grid">';
        while ($query->have_posts()) {
            $query->the_post();
            ?>
            <div class="project-item">
                <h3 class="project-title"><?php the_title(); ?></h3>
				<p><?php echo get_the_term_list(get_the_ID(), 'cours', '', ', ', ''); ?></p>
                <a href="<?php the_permalink(); ?>" class="project-link button">Voir +</a>
            </div>
            <?php
        }
        echo '</div>'; // Fermeture du conteneur grid

        // Pagination
        $pagination = paginate_links(array(
            'total'   => $query->max_num_pages,
            'current' => $paged,
            'format'  => '?paged=%#%',
            'prev_text' => __('<'),
            'next_text' => __('>'),
        ));

        echo '<div class="pagination">' . $pagination . '</div>';

        wp_reset_postdata();
        $html = ob_get_clean();
        wp_send_json_success($html);
    } else {
        wp_send_json_error('Aucun projet trouvé.');
    }

    wp_die();
}

add_action('wp_ajax_filter_projects', 'mmi_filter_projects_by_course');
add_action('wp_ajax_nopriv_filter_projects', 'mmi_filter_projects_by_course');


function afficher_etudiants_en_cartes() {
    $args = array(
        'role'    => 'etudiant',
        'orderby' => 'display_name',
        'order'   => 'ASC',
    );

    $etudiants = get_users($args);
    $output = '<div class="full-page-center">';  /* Conteneur pour centrer la grille */
    $output .= '<div class="students-grid" style="display: flex; flex-wrap: wrap; gap: 20px;">';

    if (!empty($etudiants)) {
        foreach ($etudiants as $etudiant) {
            $details_url = site_url('/student-details/?id=' . $etudiant->ID);
            $output .= '<div class="student-card" style="border: 1px solid #ddd; padding: 15px; border-radius: 8px; width: 250px; text-align: center;">';
            $output .= '<h3>' . esc_html($etudiant->display_name) . '</h3>';
            $output .= '<p>Email : ' . esc_html($etudiant->user_email) . '</p>';
            $output .= '<a href="/author/' . $etudiant->user_nicename . '" style="text-decoration: none; color: #0073aa;">Voir les détails</a>';
            $output .= '</div>';
        }
    } else {
        $output .= '<p>Aucun étudiant trouvé.</p>';
    }

    $output .= '</div>';
    $output .= '</div>';  /* Fermeture du conteneur pour centrer */
    return $output;
}
add_shortcode('list-students', 'afficher_etudiants_en_cartes');





// Action AJAX pour la pagination des projets associés
add_action('wp_ajax_pagination_author_projects', 'mmi_pagination_author_projects');
add_action('wp_ajax_nopriv_pagination_author_projects', 'mmi_pagination_author_projects');

function mmi_pagination_author_projects() {
    $paged = isset($_POST['paged']) ? intval($_POST['paged']) : 1;
    $author_id = isset($_POST['author_id']) ? intval($_POST['author_id']) : 0;

    if (!$author_id) {
        wp_send_json_error("ID d'auteur manquant.");
    }

    $args = array(
        'post_type'      => 'projet',
        'post_status'    => 'publish',
        'posts_per_page' => 3, // Limite de projets par page
        'paged'          => $paged,
        'author'         => $author_id,
    );

    $query = new WP_Query($args);

    if ($query->have_posts()) {
        ob_start();
        echo '<div class="projects-grid">';
        while ($query->have_posts()) {
            $query->the_post();
            ?>
            <div class="project-item">
                <h3 class="project-title"><?php the_title(); ?></h3>
                <a href="<?php the_permalink(); ?>" class="project-link button">Voir +</a>
            </div>
            <?php
        }
        echo '</div>';

        // Pagination
        $pagination = paginate_links(array(
            'total'   => $query->max_num_pages,
            'current' => $paged,
            'format'  => '?paged=%#%',
            'prev_text' => __('<'),
            'next_text' => __('>'),
        ));

        echo '<div class="pagination">' . $pagination . '</div>';

        wp_reset_postdata();
        $html = ob_get_clean();
        wp_send_json_success($html);
    } else {
        wp_send_json_error('Aucun projet trouvé.');
    }

    wp_die();
}
?>