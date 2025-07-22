<?php
/**
 * The Template for displaying Search Results pages.
 */

get_header();

if ( have_posts() ) :
?>
	<header class="page-header container left-search mt-5">
		<h1 class="page-title"><?php printf( esc_html__( 'Résultat de recherche pour : %s', 'daz' ), get_search_query() ); ?></h1>
	</header>
<?php
	get_template_part( 'archive', 'loop' );
else :
?>
	<article id="post-0" class="post no-results not-found left-search container mt-5 mb-5">
		<header class="entry-header">
			<h1 class="entry-title"><?php esc_html_e( 'Nous avons rien trouvé', 'daz' ); ?></h1>
		</header><!-- /.entry-header -->
		<p><?php esc_html_e( 'Essayer autres choses.', 'daz' ); ?></p>
		<?php
			get_search_form();
		?>
	</article><!-- /#post-0 -->
<?php
endif;
wp_reset_postdata();

get_footer();
