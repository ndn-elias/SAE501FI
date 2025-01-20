<?php 
// Inclure l'en-tête du thème WordPress
get_header();
?>

<!-- MAIN CONTENT -->
<div class="project-detail-container">
    <div class="project-detail-grid">
        <!-- Colonne de gauche : Carte avec projet -->
        <div class="left-column">
            <div class="project-card">
                <!-- Titre du projet -->
                <h1><?php the_title(); ?></h1> <!-- Affiche dynamiquement le titre du projet -->

                <!-- Description du projet -->
                <div class="project-description">
                    <?php the_content(); ?> <!-- Affiche dynamiquement le contenu du projet -->
                </div>

                <!-- Matières associées -->
                <div class="project-courses">
                    <h3>Matières associées :</h3>
                    <?php
                    // Récupérer les matières associées à ce projet
                    $courses = wp_get_post_terms(get_the_ID(), 'cours');
                    if (!empty($courses)) {
                        echo '<ul>';
                        foreach ($courses as $course) {
                            echo '<li>' . esc_html($course->name) . '</li>'; // Affiche les matières associées
                        }
                        echo '</ul>';
                    } else {
                        echo '<p>Aucune matière associée.</p>'; // Si aucune matière n'est associée
                    }
                    ?>
                </div>

                <!-- Étudiants associés -->
                <div class="project-students">
                    <h3>Étudiants associés :</h3>
                    <?php
                    // Récupérer les étudiants associés à ce projet (champ personnalisé)
                    $etudiants_associes = get_post_meta(get_the_ID(), '_etudiants_associes', true);
                    if (!empty($etudiants_associes)) {
                        echo '<ul>';
                        foreach ($etudiants_associes as $etudiant_id) {
                            $utilisateur = get_user_by('ID', $etudiant_id); // Récupérer l'utilisateur par ID
                            if ($utilisateur) {
                                echo '<li>' . esc_html($utilisateur->display_name) . '</li>'; // Affiche le nom de l'étudiant
                            }
                        }
                        echo '</ul>';
                    } else {
                        echo '<p>Aucun étudiant associé.</p>'; // Si aucun étudiant n'est associé
                    }
                    ?>
                </div>
            </div>
        </div>

        <!-- Colonne de droite : Commentaires -->
        <div class="right-column">
            <?php
            // Affiche les commentaires si activés
            if (comments_open()) {
                comments_template();
            } else {
                echo '<p>Les commentaires ne sont pas autorisés pour ce projet.</p>';
            }
            ?>
        </div>
    </div>
</div>

<!-- FOOTER -->
<?php
// Inclure le pied de page
get_footer();
?>