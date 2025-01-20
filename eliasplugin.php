<?php

/*
 * Plugin Name:       eliasplugin
 * Plugin URI:        https://nde2704a.mmiweb.iut-tlse3.fr/plugins/eliasplugin/
 * Description:       Plugin pour l'ajout de projets, avec une option pour activer ou non Gutenberg, et avec différents rôles d'utilisateurs.
 * Version:           1.10.3
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Elias NODON
 * Author URI:        https://author.example.com
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Update URI:        https://example.com/my-plugin/
 * Text Domain:       eliasplugin
 * Domain Path:       /languages
 */

function activation_plugin() {
    add_role('etudiant', 'Étudiant', array(
        'read' => true,
        'edit_projects' => true,
        'read_projets' => true,
        'delete_projects' => true,
    ));

    add_role('enseignant', 'Enseignant', array(
        'read' => true,
        'edit_projects' => true,
        'publish_projects' => true,
        'delete_projects' => true,
        'edit_others_projects' => true,
        'delete_others_projects' => true,
        'read_projets' => true,
    ));

    $role_admin = get_role('administrator');
    $role_admin->add_cap('read_projets');
    $role_admin->add_cap('edit_projects');
    $role_admin->add_cap('publish_projects');
    $role_admin->add_cap('delete_projects');
    $role_admin->add_cap('edit_others_projects');
    $role_admin->add_cap('delete_others_projects');

    add_option('plugintest_option', 'Valeur par défaut');
}

function desactivation_plugin() {
    remove_role('etudiant');
    remove_role('enseignant');
    delete_option('plugintest_option');
}

function desinstallation_plugin() {
    delete_option('plugintest_option');
    global $wpdb;
    $wpdb->query("DELETE FROM $wpdb->postmeta WHERE meta_key = '_github_link'");
}

register_activation_hook(__FILE__, 'activation_plugin');
register_deactivation_hook(__FILE__, 'desactivation_plugin');
register_uninstall_hook(__FILE__, 'desinstallation_plugin');

function enregistrer_type_poste_projet() {
    $labels = array(
        'name'               => _x('Projets', 'nom général du type de post'),
        'singular_name'      => _x('Projet', 'nom singulier du type de post'),
        'menu_name'          => __('Projets'),
        'add_new'            => __('Ajouter Projet'),
        'add_new_item'       => __('Ajouter Nouveau Projet'),
        'edit_item'          => __('Modifier Projet'),
        'view_item'          => __('Voir Projet'),
        'all_items'          => __('Tous les Projets'),
        'search_items'       => __('Rechercher des Projets'),
        'not_found'          => __('Aucun Projet trouvé.'),
        'not_found_in_trash' => __('Aucun Projet trouvé dans la corbeille.'),
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => array('slug' => 'projet'),
        'capability_type'    => 'post',
        'capabilities'       => array(
            'edit_post'          => 'edit_projects',
            'read_post'          => 'read_projets',
            'delete_post'        => 'delete_projects',
            'edit_posts'         => 'edit_projects',
            'edit_others_posts'  => 'edit_others_projects',
            'publish_posts'      => 'publish_projects',
            'delete_posts'       => 'delete_projects',
            'delete_others_posts'=> 'delete_others_projects',
            'read_private_posts' => 'read_projets',
            'edit_private_posts' => 'edit_projects',
            'delete_private_posts'=> 'delete_projects',
        ),
        'has_archive'        => true,
        'hierarchical'       => false,
        'menu_position'      => null,
        'show_in_rest'       => true,
        'supports'           => array('title', 'editor', 'thumbnail', 'custom-fields', 'excerpt', 'comments'),
    );

    register_post_type('projet', $args);
}
add_action('init', 'enregistrer_type_poste_projet');

function desactiver_gutenberg_pour_projets($use_block_editor, $post_type) {
    if ($post_type === 'projet') {
        $activer_gutenberg = get_option('enable_gutenberg', 'yes');
        return $activer_gutenberg === 'yes';
    }
    return $use_block_editor;
}
add_filter('use_block_editor_for_post_type', 'desactiver_gutenberg_pour_projets', 10, 2);

function enregistrer_taxonomie_cours() {
    $labels = array(
        'name'              => _x('Cours', 'taxonomie nom général'),
        'singular_name'     => _x('Cours', 'taxonomie nom singulier'),
        'menu_name'         => __('Cours'),
    );

    $args = array(
        'hierarchical'      => true,
        'labels'            => $labels,
        'rewrite'           => array('slug' => 'cours'),
        'show_in_rest'      => true,
    );

    register_taxonomy('cours', array('projet'), $args);
}
add_action('init', 'enregistrer_taxonomie_cours');

function ajouter_meta_box_github_projet() {
    add_meta_box(
        'github_link_metabox',
        'Lien GitHub',
        'afficher_meta_box_github',
        'projet'
    );
}
add_action('add_meta_boxes', 'ajouter_meta_box_github_projet');

function afficher_meta_box_github($post) {
    $github_link = get_post_meta($post->ID, '_github_link', true);
    ?>
    <label for="github_link">Lien GitHub :</label>
    <input type="url" name="github_link" id="github_link" value="<?php echo esc_attr($github_link); ?>" style="width: 100%;" placeholder="https://github.com/votre-projet">

    <p>
        <label>
            <input type="checkbox" name="supprimer_github_link" value="1">
            Supprimer ce champ
        </label>
    </p>
    <?php
}

function sauvegarder_meta_box_github($post_id) {
    if (isset($_POST['supprimer_github_link']) && $_POST['supprimer_github_link'] === '1') {
        delete_post_meta($post_id, '_github_link');
    } elseif (isset($_POST['github_link'])) {
        update_post_meta($post_id, '_github_link', esc_url_raw($_POST['github_link']));
    }
}
add_action('save_post', 'sauvegarder_meta_box_github');

function ajouter_meta_box_etudiants() {
    add_meta_box(
        'meta_box_etudiants',
        __('Étudiants associés'),
        'afficher_meta_box_etudiants',
        'projet',
        'side',
        'default'
    );
}
add_action('add_meta_boxes', 'ajouter_meta_box_etudiants');

function recuperer_liste_etudiants() {
    $args = array(
        'role'    => 'étudiant',
        'orderby' => 'display_name',
        'order'   => 'ASC',
    );

    $utilisateurs = get_users($args);
    return $utilisateurs;
}

function afficher_meta_box_etudiants($post) {
    $etudiants = recuperer_liste_etudiants();

    $etudiants_selectionnes = get_post_meta($post->ID, '_etudiants_associes', true);
    $etudiants_selectionnes = is_array($etudiants_selectionnes) ? $etudiants_selectionnes : array();

    foreach ($etudiants as $etudiant) {
        $selectionne = in_array($etudiant->ID, $etudiants_selectionnes) ? 'checked' : '';
        echo '<p><input type="checkbox" name="etudiants_associes[]" value="' . esc_attr($etudiant->ID) . '" ' . $selectionne . '> ' . esc_html($etudiant->display_name) . '</p>';
    }

    echo '<p>' . __('Sélectionnez les étudiants associés à ce projet.') . '</p>';
}

function sauvegarder_etudiants_associes($post_id) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    if (isset($_POST['etudiants_associes'])) {
        $etudiants = array_map('intval', $_POST['etudiants_associes']);
        update_post_meta($post_id, '_etudiants_associes', $etudiants);
    } else {
        delete_post_meta($post_id, '_etudiants_associes');
    }
}
add_action('save_post', 'sauvegarder_etudiants_associes');

function ajouter_etudiants_a_rest($response, $post, $context) {
    $etudiants_associes = get_post_meta($post->ID, '_etudiants_associes', true);
    if ($etudiants_associes) {
        $donnees_etudiants = array();
        foreach ($etudiants_associes as $etudiant_id) {
            $utilisateur = get_user_by('ID', $etudiant_id);
            if ($utilisateur) {
                $donnees_etudiants[] = array(
                    'ID'   => $utilisateur->ID,
                    'name' => $utilisateur->display_name,
                );
            }
        }
        $response->data['etudiants_associes'] = $donnees_etudiants;
    }
    return $response;
}
add_filter('rest_prepare_projet', 'ajouter_etudiants_a_rest', 10, 3);

function enregistrer_page_parametres_plugin() {
    add_menu_page(
        'Réglages du Plugin',
        'Réglages Plugin',
        'administrator',
        'plugin_options',
        'render_page_parametres_plugin'
    );
}
add_action('admin_menu', 'enregistrer_page_parametres_plugin');

function render_page_parametres_plugin() {
    ?>
    <div class="wrap">
        <h1>Réglages du Plugin</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('sae_options_group');
            do_settings_sections('plugin_options');
            ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Activer Gutenberg</th>
                    <td>
                        <input type="checkbox" name="enable_gutenberg" value="yes" <?php checked(get_option('enable_gutenberg'), 'yes'); ?>>
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

function enregistrer_parametres_plugin() {
    register_setting('sae_options_group', 'enable_gutenberg');
}
add_action('admin_init', 'enregistrer_parametres_plugin');
