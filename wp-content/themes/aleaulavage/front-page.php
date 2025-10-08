<?php
/**
 * Template Name: Home
 * Description: Home.
 *
 */

get_header();

the_post();
?>
<div class="home">
	<div class="home-thumbnail mt-5"><!-- Ajout d'un margin-top -->
        <?php the_post_thumbnail() ?>
		<div class="home-baseline">
			<h1 class="text-light">Votre Station de Lavage <br/> Notre Priorité !</h1>
			<div class="d-flex gap-2 mt-4">
				<a class="btn bg-secondary" href="<?php echo esc_url(home_url('boutique')); ?>">Boutique</a>
			</div>
		</div>
    </div>

	<?php
	// Section jeux désormais gérée par le plugin aleaulavage-tpe-salon
	do_action('aleaulavage_home_games');
	?>

	<div>
		<div id="post-<?php the_ID(); ?>" <?php post_class( 'content' ); ?>>
			<?php
			if (!is_page('mon-compte') && !is_front_page()) {
				?>
				<h1 class="entry-title"><?php the_title(); ?></h1>
				<?php
			}

				the_content();

				wp_link_pages(
					array(
						'before' => '<div class="page-links">' . __( 'Pages:', 'daz' ),
						'after'  => '</div>',
					)
				);
				edit_post_link( esc_html__( 'Edit', 'aleaulavage' ), '<span class="edit-link">', '</span>' );
			?>
		</div><!-- /#post-<?php the_ID(); ?> -->
		<?php
			// If comments are open or we have at least one comment, load up the comment template.
			if ( comments_open() || get_comments_number() ) :
				comments_template();
			endif;
		?>
	</div>

	<!-- Modal pour les jeux -->
	<div id="game-modal" class="game-modal" style="display: none;">
		<div class="game-modal-content">
			<span class="game-modal-close">&times;</span>
			<iframe id="game-iframe" src="" frameborder="0" allowfullscreen></iframe>
		</div>
	</div>
</div>
<?php
get_footer();
