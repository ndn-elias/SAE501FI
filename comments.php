<?php 
// Empêcher l'accès direct si le projet est protégé par mot de passe
if (post_password_required()) {
    return;
}
?>

<div id="comments" class="comments-area">

    <!-- Affichage des commentaires existants -->
    <?php if (have_comments()) : ?>
        <h2 class="comments-title">
            <?php
            $comment_count = get_comments_number();
            if ($comment_count === 1) {
                echo '1 Commentaire';
            } else {
                echo $comment_count . ' Commentaires';
            }
            ?>
        </h2>

        <ul class="comment-list">
            <?php
            wp_list_comments(array(
                'style' => 'ul',
                'short_ping' => true,
                'avatar_size' => 50,
                'reply_text' => 'Répondre', // Texte du bouton pour répondre
            ));
            ?>
        </ul>

        <?php the_comments_pagination(array(
            'prev_text' => '&laquo; Précédent',
            'next_text' => 'Suivant &raquo;',
        )); ?>
    <?php endif; ?>

    <!-- Formulaire de commentaire -->
    <h3>Laissez un commentaire</h3>
    <?php
    comment_form(array(
		'logged_in_as' => '',
        'title_reply' => '',
        'label_submit' => 'Envoyer',
        'comment_notes_before' => '',
        'comment_notes_after' => '',
        'comment_field' => '<p><label for="comment">Commentaire *</label><br><textarea id="comment" name="comment" required></textarea></p>',
    ));
    ?>
</div>
