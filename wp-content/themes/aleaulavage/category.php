<?php
/**
 * The Template for displaying Category Archive pages.
 */

get_header();

if ( have_posts() ) :
?>
<div class="container">

	<header class="page-header ">
		<h1 class="page-title"><?php printf( esc_html__( 'Category Archives: %s', 'daz' ), single_cat_title( '', false ) ); ?></h1>
	</header>

	<div class="category-description-top">
    <?php
        $category_description = category_description();
        if ( ! empty( $category_description ) ) :
            $description_text = strip_tags($category_description);
            $description_short = substr($description_text, 0, 200);
            $description_full = $category_description;
        ?>
            <div class="category-description-short">
                <?php echo $description_short; ?>
                <?php if (strlen($description_text) > 200) : ?>
                    <span class="dots">...</span>
                    <span class="read-more" onclick="scrollToFullDescription()">Lire la suite</span>
                <?php endif; ?>
            </div>
        <?php endif; ?>
	</div>

	<?php
		get_template_part( 'archive', 'loop' );
	?>

<?php
else :
	// 404.
	get_template_part( 'content', 'none' );
endif;
?>
