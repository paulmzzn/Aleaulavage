<?php

/**
 * Offcanvas Category products
 */

if (!defined('ABSPATH')) {
  exit; // Exit if accessed directly.
}
?>

<form class="search-form" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<span class="icon-glass" data-bs-toggle="offcanvas" data-bs-target="#offcanvas-category"><i class="fa-solid fa-magnifying-glass"></i></span>
	<input type="text" name="s" class="form-control" placeholder="<?php esc_attr_e( 'Search', 'aleaulavage' ); ?>" title="<?php esc_attr_e( 'Search', 'aleaulavage' ); ?>"/>
        <input type="hidden" name="post_type" value="product" />
</form>

<?php
$cat_args = array(
	'orderby'    => 'name',
	'order'      => 'asc',
	'hide_empty' => false,
	'exclude'    => [16]
);

$product_categories = get_terms( 'product_cat', $cat_args );
if( !empty($product_categories) ){
?>
<hr class=my-4>
<ul>
	<?php
	foreach ($product_categories as $key => $category) {
		if ($category->parent !== 0) {
			continue;
		}
		$thumbnail_id = get_term_meta($category->term_id, 'thumbnail_id', true );
	?>
	<li>
		<img src="<?php echo wp_get_attachment_url( $thumbnail_id ) ?>" alt="category-img" class="caterory-thumbnails" data-bs-toggle="offcanvas" data-bs-target="#offcanvas-category"/>
		<a href="<?php echo get_term_link($category) ?>">
		<?php echo $category->name ?>
		</a>
	</li>
	<?php } ?>
</ul>

<?php
}
?>

<button type="button" class="close-offcanvas text-reset" data-bs-toggle="offcanvas" data-bs-target="#offcanvas-category" aria-controls="offcanvas-category"><i class="fa-solid fa-chevron-right"></i></button>