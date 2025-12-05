<?php
/**
 * Template Name: Page Favoris
 * Template for favorites/wishlist page
 *
 * @package SCW_Shop
 */

get_header();

$user_role = scw_shop_get_user_role();
$user_mode = scw_shop_get_user_mode();

// Get user favorites
$favorites = array();
if ( is_user_logged_in() ) {
	$user_id = get_current_user_id();
	$favorites = get_user_meta( $user_id, 'scw_favorites', true );
	if ( ! is_array( $favorites ) ) {
		$favorites = array();
	}
}
// For guests, favorites will be managed via JavaScript/localStorage

// Get favorite products
$favorite_products = array();
if ( ! empty( $favorites ) ) {
	$args = array(
		'post_type'      => 'product',
		'post__in'       => $favorites,
		'posts_per_page' => -1,
		'post_status'    => 'publish',
	);
	$favorites_query = new WP_Query( $args );

	if ( $favorites_query->have_posts() ) {
		while ( $favorites_query->have_posts() ) {
			$favorites_query->the_post();
			$favorite_products[] = array(
				'id'       => get_the_ID(),
				'category' => wp_get_post_terms( get_the_ID(), 'product_cat' )[0]->name ?? 'Non classé',
			);
		}
		wp_reset_postdata();
	}
}

// Get categories present in favorites
$categories = array( 'all' => 'Tous les produits' );
foreach ( $favorite_products as $product ) {
	if ( ! isset( $categories[ $product['category'] ] ) ) {
		$categories[ $product['category'] ] = $product['category'];
	}
}

// Get suggested products (products not in favorites)
$suggested_args = array(
	'post_type'      => 'product',
	'posts_per_page' => 5,
	'post_status'    => 'publish',
	'post__not_in'   => $favorites,
	'orderby'        => 'rand',
);
$suggested_query = new WP_Query( $suggested_args );
?>

<main id="main" class="site-main">
	<div class="favorites-container" id="favorites-container" data-user-id="<?php echo esc_attr( get_current_user_id() ); ?>">

		<!-- EMPTY STATE (shown if no favorites via JS) -->
		<div class="favorites-empty" id="favorites-empty" style="display: none;">
			<div class="empty-icon">❤️</div>
			<h2><?php esc_html_e( 'Votre liste de souhaits est vide', 'scw-shop' ); ?></h2>
			<p><?php esc_html_e( 'Sauvegardez les produits qui vous intéressent pour préparer vos futurs projets.', 'scw-shop' ); ?></p>
			<a href="<?php echo esc_url( get_permalink( get_page_by_path( 'boutique' ) ) ); ?>" class="btn-primary">
				<?php esc_html_e( 'Explorer le catalogue', 'scw-shop' ); ?>
			</a>
		</div>

		<!-- CONTENT (shown if has favorites) -->
		<div id="favorites-content">
			<!-- HEADER & CONTROLS -->
			<div class="favorites-header-block">
				<div class="header-titles">
					<h1><?php esc_html_e( 'Mes Favoris', 'scw-shop' ); ?></h1>
					<p><?php esc_html_e( 'Retrouvez ici votre sélection de produits.', 'scw-shop' ); ?></p>
				</div>

				<div class="favorites-summary-card">
					<span class="summary-label"><?php esc_html_e( 'Valeur estimée de la sélection', 'scw-shop' ); ?></span>
					<span class="summary-total" id="favorites-total">0.00 € HT</span>
					<button class="btn-action-header" id="add-all-to-cart">
						<?php esc_html_e( 'Tout ajouter au panier', 'scw-shop' ); ?>
					</button>
				</div>
			</div>

			<!-- FILTRES (Onglets) -->
			<div class="favorites-tabs" id="favorites-tabs">
				<?php foreach ( $categories as $cat_slug => $cat_name ) : ?>
					<button class="fav-tab <?php echo $cat_slug === 'all' ? 'active' : ''; ?>" data-category="<?php echo esc_attr( $cat_slug ); ?>">
						<?php echo esc_html( $cat_name ); ?>
						<span class="tab-count" data-category-count="<?php echo esc_attr( $cat_slug ); ?>">0</span>
					</button>
				<?php endforeach; ?>
			</div>

			<!-- GRILLE FAVORIS -->
			<div class="favorites-grid" id="favorites-grid">
				<?php
				if ( ! empty( $favorites ) ) :
					$args = array(
						'post_type'      => 'product',
						'post__in'       => $favorites,
						'posts_per_page' => -1,
						'post_status'    => 'publish',
					);
					$favorites_query = new WP_Query( $args );

					if ( $favorites_query->have_posts() ) :
						while ( $favorites_query->have_posts() ) :
							$favorites_query->the_post();
							wc_get_template_part( 'content', 'product' );
						endwhile;
						wp_reset_postdata();
					endif;
				endif;
				?>
			</div>

			<!-- SECTION SUGGESTIONS -->
			<?php if ( $suggested_query->have_posts() ) : ?>
				<div class="favorites-suggestions">
					<div class="section-header">
						<h2 class="section-title"><?php esc_html_e( 'Vous pourriez aussi aimer', 'scw-shop' ); ?></h2>
						<p class="section-subtitle"><?php esc_html_e( 'Basé sur votre sélection actuelle', 'scw-shop' ); ?></p>
					</div>
					<div class="suggestions-grid">
						<?php
						while ( $suggested_query->have_posts() ) :
							$suggested_query->the_post();
							wc_get_template_part( 'content', 'product' );
						endwhile;
						wp_reset_postdata();
						?>
					</div>
				</div>
			<?php endif; ?>
		</div>

	</div>
</main>

<?php
get_footer();
