<?php
/**
 * Template Name: Page (Default)
 * Description: Page template with Sidebar on the left side.
 *
 */

get_header();

the_post();
?>
	<div class="d-flex justify-content-center container pt-5">
		<div id="post-<?php the_ID(); ?>" <?php post_class( 'content' ); ?> style="max-width: 1320px;">
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
				edit_post_link( esc_html__( 'Edit', 'daz' ), '<span class="edit-link">', '</span>' );
			?>
		</div><!-- /#post-<?php the_ID(); ?> -->
		<?php
			// If comments are open or we have at least one comment, load up the comment template.
			if ( comments_open() || get_comments_number() ) :
				comments_template();
			endif;
		?>
	</div>
<?php
get_footer();
