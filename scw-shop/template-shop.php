<?php
/**
 * Template Name: Page Boutique
 * Template for shop/category page
 *
 * @package SCW_Shop
 */

// Enqueue shop page scripts
wp_enqueue_script(
	'scw-shop-page',
	get_template_directory_uri() . '/assets/js/shop.js',
	array(),
	filemtime( get_template_directory() . '/assets/js/shop.js' ),
	true
);

// Localize script for AJAX
wp_localize_script( 'scw-shop-page', 'scwShopPage', array(
	'ajaxUrl' => admin_url( 'admin-ajax.php' ),
) );

get_header();

$user_role = scw_shop_get_user_role();
$user_mode = scw_shop_get_user_mode();

// Get query parameters
$selected_category = isset( $_GET['category'] ) ? sanitize_text_field( $_GET['category'] ) : 'all';
$selected_brand = isset( $_GET['brand'] ) ? sanitize_text_field( $_GET['brand'] ) : '';
$search_query = isset( $_GET['search'] ) ? sanitize_text_field( $_GET['search'] ) : '';
$sort_by = isset( $_GET['orderby'] ) ? sanitize_text_field( $_GET['orderby'] ) : 'default';

// Get all product categories (only parents first)
$parent_categories = get_terms( array(
	'taxonomy'   => 'product_cat',
	'hide_empty' => true,
	'parent'     => 0,
	'exclude'    => get_option( 'default_product_cat' ), // Exclude "Uncategorized"
) );

// Build hierarchical category structure
$categories_hierarchy = array();
foreach ( $parent_categories as $parent ) {
	$children = get_terms( array(
		'taxonomy'   => 'product_cat',
		'hide_empty' => true,
		'parent'     => $parent->term_id,
	) );
	$categories_hierarchy[] = array(
		'parent'   => $parent,
		'children' => $children,
	);
}

// Build WooCommerce query args
$paged = isset( $_GET['paged'] ) ? max( 1, intval( $_GET['paged'] ) ) : 1;
$args = array(
	'post_type'      => 'product',
	'posts_per_page' => 12,
	'paged'          => $paged,
	'post_status'    => 'publish',
);

// Category and Brand filters
$tax_queries = array();

if ( $selected_category !== 'all' ) {
	$tax_queries[] = array(
		'taxonomy' => 'product_cat',
		'field'    => 'slug',
		'terms'    => $selected_category,
	);
}

// Brand filter - try different brand taxonomies
if ( ! empty( $selected_brand ) ) {
	// Try to find which taxonomy the brand belongs to
	$brand_taxonomies = array( 'product_brand', 'pa_brand', 'pa_marque' );
	foreach ( $brand_taxonomies as $taxonomy ) {
		$term = get_term_by( 'slug', $selected_brand, $taxonomy );
		if ( $term && ! is_wp_error( $term ) ) {
			$tax_queries[] = array(
				'taxonomy' => $taxonomy,
				'field'    => 'slug',
				'terms'    => $selected_brand,
			);
			break;
		}
	}
}

// Apply tax queries if any
if ( ! empty( $tax_queries ) ) {
	if ( count( $tax_queries ) > 1 ) {
		$args['tax_query'] = array_merge( array( 'relation' => 'AND' ), $tax_queries );
	} else {
		$args['tax_query'] = $tax_queries;
	}
}

// Sorting
switch ( $sort_by ) {
	case 'price-asc':
		$args['meta_key'] = '_price';
		$args['orderby'] = 'meta_value_num';
		$args['order'] = 'ASC';
		break;
	case 'price-desc':
		$args['meta_key'] = '_price';
		$args['orderby'] = 'meta_value_num';
		$args['order'] = 'DESC';
		break;
	default:
		$args['orderby'] = 'date';
		$args['order'] = 'DESC';
}

$products_query = new WP_Query( $args );

// Filter products by search query in PHP (more reliable than SQL)
if ( ! empty( $search_query ) && $products_query->have_posts() ) {
	$filtered_posts = array();
	$search_lower = strtolower( $search_query );

	while ( $products_query->have_posts() ) {
		$products_query->the_post();
		$product = wc_get_product( get_the_ID() );

		// Get searchable fields
		$title = strtolower( get_the_title() );
		$content = strtolower( get_the_content() );
		$excerpt = strtolower( get_the_excerpt() );
		$sku = strtolower( $product->get_sku() );

		// Check if search term matches any field
		if (
			strpos( $title, $search_lower ) !== false ||
			strpos( $content, $search_lower ) !== false ||
			strpos( $excerpt, $search_lower ) !== false ||
			strpos( $sku, $search_lower ) !== false
		) {
			$filtered_posts[] = get_the_ID();
		}
	}

	wp_reset_postdata();

	// Re-run query with filtered IDs
	if ( ! empty( $filtered_posts ) ) {
		$args['post__in'] = $filtered_posts;
		$products_query = new WP_Query( $args );
	} else {
		// No results found
		$args['post__in'] = array( 0 ); // Force no results
		$products_query = new WP_Query( $args );
	}
}

// Get selected category name or brand name
$page_title = 'Tous nos produits';
if ( ! empty( $selected_brand ) ) {
	// Try to find the brand term from different taxonomies
	$brand_taxonomies = array( 'product_brand', 'pa_brand', 'pa_marque' );
	foreach ( $brand_taxonomies as $taxonomy ) {
		$brand_term = get_term_by( 'slug', $selected_brand, $taxonomy );
		if ( $brand_term && ! is_wp_error( $brand_term ) ) {
			$page_title = strtoupper( $brand_term->name );
			break;
		}
	}
} elseif ( $selected_category !== 'all' ) {
	$cat_term = get_term_by( 'slug', $selected_category, 'product_cat' );
	if ( $cat_term ) {
		$page_title = $cat_term->name;
	}
}

// Get all brands for sidebar filter
$brands_for_filter = array();
$brand_taxonomies = array( 'product_brand', 'pa_brand', 'pa_marque' );
foreach ( $brand_taxonomies as $taxonomy ) {
	$brands_terms = get_terms( array(
		'taxonomy'   => $taxonomy,
		'hide_empty' => true,
		'orderby'    => 'name',
		'order'      => 'ASC',
	) );
	if ( ! empty( $brands_terms ) && ! is_wp_error( $brands_terms ) ) {
		$brands_for_filter = $brands_terms;
		break;
	}
}
?>

<main id="main" class="site-main">
	<div class="shop-container">

		<!-- SIDEBAR FILTRES -->
		<aside class="shop-sidebar">
			<div class="filter-group search-group">
				<h3>Recherche</h3>
				<form method="get" action="<?php echo esc_url( get_permalink() ); ?>" class="shop-search-form">
					<?php if ( $selected_category !== 'all' ) : ?>
						<input type="hidden" name="category" value="<?php echo esc_attr( $selected_category ); ?>" />
					<?php endif; ?>
					<?php if ( ! empty( $selected_brand ) ) : ?>
						<input type="hidden" name="brand" value="<?php echo esc_attr( $selected_brand ); ?>" />
					<?php endif; ?>
					<?php if ( $sort_by !== 'default' ) : ?>
						<input type="hidden" name="orderby" value="<?php echo esc_attr( $sort_by ); ?>" />
					<?php endif; ?>
					<input
						type="text"
						name="search"
						placeholder="Mot-clé, référence..."
						value="<?php echo esc_attr( $search_query ); ?>"
						class="shop-search-input"
					/>
				</form>
			</div>

			<div class="filter-group">
				<h3>Catégories</h3>
				<div class="category-list">
					<a href="<?php echo esc_url( remove_query_arg( array( 'category', 'search', 'brand' ) ) ); ?>"
					   class="category-item <?php echo $selected_category === 'all' ? 'active' : ''; ?>">
						<span>Tout voir</span>
					</a>
					<?php foreach ( $categories_hierarchy as $cat_group ) : 
						$parent = $cat_group['parent'];
						$children = $cat_group['children'];
						$parent_active = $selected_category === $parent->slug;
						$has_active_child = false;
						
						// Check if any child is active
						foreach ( $children as $child ) {
							if ( $selected_category === $child->slug ) {
								$has_active_child = true;
								break;
							}
						}
						
						$is_expanded = $parent_active || $has_active_child;
					?>
						<div class="category-parent-group <?php echo $is_expanded ? 'expanded' : ''; ?>">
							<a href="<?php echo esc_url( add_query_arg( 'category', $parent->slug, remove_query_arg( array( 'search', 'brand' ) ) ) ); ?>"
							   class="category-item parent <?php echo $parent_active ? 'active' : ''; ?>">
								<span><?php echo esc_html( $parent->name ); ?></span>
								<?php if ( ! empty( $children ) ) : ?>
									<svg class="toggle-icon" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
										<polyline points="6 9 12 15 18 9"></polyline>
									</svg>
								<?php endif; ?>
							</a>
							<?php if ( ! empty( $children ) ) : ?>
								<div class="category-children">
									<?php foreach ( $children as $child ) : ?>
										<a href="<?php echo esc_url( add_query_arg( 'category', $child->slug, remove_query_arg( array( 'search', 'brand' ) ) ) ); ?>"
										   class="category-item child <?php echo $selected_category === $child->slug ? 'active' : ''; ?>">
											<span><?php echo esc_html( $child->name ); ?></span>
										</a>
									<?php endforeach; ?>
								</div>
							<?php endif; ?>
						</div>
					<?php endforeach; ?>
				</div>
			</div>

			<?php if ( ! empty( $brands_for_filter ) ) : ?>
			<div class="filter-group">
				<h3>Marques</h3>
				<div class="category-list">
					<?php
					// Build URL for clearing brand filter
					$clear_brand_url = remove_query_arg( array( 'brand', 'category' ) );
					?>
					<a href="<?php echo esc_url( $clear_brand_url ); ?>"
					   class="category-item <?php echo empty( $selected_brand ) ? 'active' : ''; ?>">
						<span>Toutes les marques</span>
					</a>
					<?php foreach ( $brands_for_filter as $brand ) :
						$brand_active = $selected_brand === $brand->slug;
						// Build URL removing category filter
						$brand_url = add_query_arg( 'brand', $brand->slug, remove_query_arg( array( 'brand', 'search', 'category' ) ) );
					?>
						<a href="<?php echo esc_url( $brand_url ); ?>"
						   class="category-item <?php echo $brand_active ? 'active' : ''; ?>">
							<span><?php echo esc_html( strtoupper( $brand->name ) ); ?></span>
						</a>
					<?php endforeach; ?>
				</div>
			</div>
			<?php endif; ?>
		</aside>

		<!-- CONTENU PRINCIPAL -->
		<div class="shop-content">
			<div class="shop-header">
				<h1>
					<?php echo esc_html( $page_title ); ?>
					<span class="product-count">(<?php echo $products_query->found_posts; ?> résultat<?php echo $products_query->found_posts > 1 ? 's' : ''; ?>)</span>
				</h1>

				<div class="sort-selector">
					<form method="get" id="sort-form">
						<?php if ( $selected_category !== 'all' ) : ?>
							<input type="hidden" name="category" value="<?php echo esc_attr( $selected_category ); ?>" />
						<?php endif; ?>
						<?php if ( ! empty( $selected_brand ) ) : ?>
							<input type="hidden" name="brand" value="<?php echo esc_attr( $selected_brand ); ?>" />
						<?php endif; ?>
						<?php if ( ! empty( $search_query ) ) : ?>
							<input type="hidden" name="search" value="<?php echo esc_attr( $search_query ); ?>" />
						<?php endif; ?>
						<select name="orderby" onchange="this.form.submit()">
							<option value="default" <?php selected( $sort_by, 'default' ); ?>>Pertinence</option>
							<option value="price-asc" <?php selected( $sort_by, 'price-asc' ); ?>>Prix croissant</option>
							<option value="price-desc" <?php selected( $sort_by, 'price-desc' ); ?>>Prix décroissant</option>
						</select>
					</form>
				</div>
			</div>

			<?php if ( $products_query->have_posts() ) : ?>
				<div class="shop-grid" data-max-pages="<?php echo $products_query->max_num_pages; ?>" data-current-page="1">
					<?php
					while ( $products_query->have_posts() ) :
						$products_query->the_post();
						wc_get_template_part( 'content', 'product' );
					endwhile;
					wp_reset_postdata();
					?>
				</div>
				<?php if ( $products_query->max_num_pages > 1 ) : ?>
					<div class="load-more-container" style="text-align: center; margin-top: 2rem;">
						<div class="load-more-spinner" style="display: none; margin: 2rem 0;">
							<svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="animation: spin 1s linear infinite;">
								<circle cx="12" cy="12" r="10" opacity="0.25"></circle>
								<path d="M12 2a10 10 0 0 1 10 10" opacity="0.75"></path>
							</svg>
						</div>
						<style>
							@keyframes spin {
								to { transform: rotate(360deg); }
							}
						</style>
					</div>
				<?php endif; ?>
			<?php else : ?>
				<div class="no-results">
					<p>Aucun produit ne correspond à vos critères.</p>
					<a href="<?php echo esc_url( remove_query_arg( array( 'category', 'search', 'orderby' ) ) ); ?>" class="btn-reset">
						Réinitialiser les filtres
					</a>
				</div>
			<?php endif; ?>
		</div>

	</div>
</main>

<?php
get_footer();
