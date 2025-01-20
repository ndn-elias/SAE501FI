<div class="project-item">
                    <?php if (has_post_thumbnail()) {
                        the_post_thumbnail('medium'); // Afficher l'image du projet
                    } ?>
                    <h3><?php the_title(); ?></h3>
                    
                    <!-- Afficher les cours associés -->
					<div class="project-courses">
						<?php
						// Récupérer les termes de la taxonomie 'cours' associés à ce projet
						$courses = wp_get_post_terms(get_the_ID(), 'cours');
						if (!empty($courses)) {
							// Affichage des cours sous forme de texte séparé
							$course_names = array(); // Tableau pour stocker les noms des cours
							foreach ($courses as $course) {
								$course_names[] = esc_html($course->name); // Ajouter le nom du cours
							}
							// Joindre les noms des cours avec une virgule et un espace
							echo implode(', ', $course_names);
						}
						?>
					</div>
				<!-- Afficher le bouton "Voir +" -->
                <a href="<?php the_permalink(); ?>">
                    <button class="view-more-btn">Voir +</button>
                </a>

                </div>