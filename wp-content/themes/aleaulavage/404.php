<?php
/**
 * Template Name: Not found
 * Description: Page template 404 Not found.
 *
 */

get_header();

$search_enabled = get_theme_mod( 'search_enabled', '1' ); // Get custom meta-value.
?>
<div id="post-0" class="container content error404 not-found pb-5 pt-5">
	<h1 class="entry-title"><?php esc_html_e( 'Not found', 'daz' ); ?></h1>
	<div class="entry-content">
		<p><?php esc_html_e( 'It looks like nothing was found at this location.', 'daz' ); ?></p>
		<div>
			<?php
				if ( '1' === $search_enabled ) :
					get_search_form();
				endif;
			?>
		</div>
	</div><!-- /.entry-content -->
</div><!-- /#post-0 -->
<?php
get_footer();
