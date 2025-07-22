<?php
/**
 * The Template for displaying Category Archive pages.
 */

get_header();

if ( have_posts() ) :
?>
<div class="container">

	<header class="page-header left-search">
		<h1 class="page-title"><?php printf( esc_html__( 'Category Archives: %s', 'daz' ), single_cat_title( '', false ) ); ?></h1>
	</header>

	<?php
		get_template_part( 'archive', 'loop' );
	?>

	<div class="category-description">
    <?php
        $category_description = category_description();
        	if ( ! empty( $category_description ) ) :
            	echo apply_filters( 'category_archive_meta', '<div class="category-archive-meta">' . $category_description . '</div>' );
				echo '<span class="show-more" onclick="toggleDescription()">Voir plus</span>';
        	endif;
    	?>
    	echo '<span class="show-more" onclick="toggleDescription()">Voir plus</span>';
	</div>
<?php
else :
	// 404.
	get_template_part( 'content', 'none' );
endif;
?>
