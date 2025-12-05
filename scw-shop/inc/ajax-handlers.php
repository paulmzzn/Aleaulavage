<?php
/**
 * AJAX handlers for cart and other operations
 *
 * @package SCW_Shop
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Check if product is in user's favorites
 */
function scw_shop_is_in_favorites( $product_id ) {
	if ( ! is_user_logged_in() ) {
		return false;
	}
	
	$user_id = get_current_user_id();
	$favorites = get_user_meta( $user_id, 'scw_favorites', true );
	
	if ( ! is_array( $favorites ) ) {
		return false;
	}
	
	return in_array( $product_id, $favorites );
}

/**
 * Add product to cart via AJAX
 */
function scw_add_to_cart() {
	// Verify nonce
	if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'scw-shop-nonce' ) ) {
		wp_send_json_error( array( 'message' => 'Invalid nonce' ) );
		return;
	}

	$product_id = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
	$quantity = isset( $_POST['quantity'] ) ? absint( $_POST['quantity'] ) : 1;

	if ( ! $product_id ) {
		wp_send_json_error( array( 'message' => 'Invalid product ID' ) );
		return;
	}

	// Check if product exists and is purchasable
	$product = wc_get_product( $product_id );
	if ( ! $product || ! $product->is_purchasable() ) {
		wp_send_json_error( array( 'message' => 'Product not available' ) );
		return;
	}

	// Check stock
	if ( ! $product->is_in_stock() ) {
		wp_send_json_error( array( 'message' => 'Product out of stock' ) );
		return;
	}

	// Add to cart
	$cart_item_key = WC()->cart->add_to_cart( $product_id, $quantity );

	if ( $cart_item_key ) {
		wp_send_json_success( array(
			'message' => 'Product added to cart',
			'cart_item_key' => $cart_item_key,
			'cart_count' => WC()->cart->get_cart_contents_count(),
			'cart_total' => WC()->cart->get_cart_total(),
		) );
	} else {
		wp_send_json_error( array( 'message' => 'Failed to add product to cart' ) );
	}
}
add_action( 'wp_ajax_scw_add_to_cart', 'scw_add_to_cart' );
add_action( 'wp_ajax_nopriv_scw_add_to_cart', 'scw_add_to_cart' );

/**
 * Get cart count via AJAX
 */
function scw_get_cart_count() {
	wp_send_json_success( array(
		'count' => WC()->cart ? WC()->cart->get_cart_contents_count() : 0,
	) );
}
add_action( 'wp_ajax_scw_get_cart_count', 'scw_get_cart_count' );
add_action( 'wp_ajax_nopriv_scw_get_cart_count', 'scw_get_cart_count' );

/**
 * Toggle favorite via AJAX
 */
function scw_toggle_favorite() {
	$product_id = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
	$add = isset( $_POST['add'] ) && $_POST['add'] === '1';

	if ( ! $product_id ) {
		wp_send_json_error( array( 'message' => 'Invalid product ID' ) );
		return;
	}

	if ( is_user_logged_in() ) {
		$user_id = get_current_user_id();
		$favorites = get_user_meta( $user_id, 'scw_favorites', true );
		if ( ! is_array( $favorites ) ) {
			$favorites = array();
		}

		if ( $add ) {
			if ( ! in_array( $product_id, $favorites ) ) {
				$favorites[] = $product_id;
			}
		} else {
			$favorites = array_diff( $favorites, array( $product_id ) );
		}

		update_user_meta( $user_id, 'scw_favorites', array_values( $favorites ) );
		wp_send_json_success( array( 'favorites' => $favorites ) );
	} else {
		// For guests, just confirm - JS handles localStorage
		wp_send_json_success( array( 'message' => 'Favorite toggled' ) );
	}
}
add_action( 'wp_ajax_scw_toggle_favorite', 'scw_toggle_favorite' );
add_action( 'wp_ajax_nopriv_scw_toggle_favorite', 'scw_toggle_favorite' );

/**
 * Add product to favorites via AJAX
 */
function scw_add_to_favorites() {
	$product_id = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;

	if ( ! $product_id ) {
		wp_send_json_error( array( 'message' => 'Invalid product ID' ) );
		return;
	}

	if ( is_user_logged_in() ) {
		$user_id = get_current_user_id();
		$favorites = get_user_meta( $user_id, 'scw_favorites', true );
		if ( ! is_array( $favorites ) ) {
			$favorites = array();
		}

		if ( ! in_array( $product_id, $favorites ) ) {
			$favorites[] = $product_id;
		}

		update_user_meta( $user_id, 'scw_favorites', array_values( $favorites ) );
		wp_send_json_success( array( 'favorites' => $favorites ) );
	} else {
		wp_send_json_success( array( 'message' => 'Favorite added (guest)' ) );
	}
}
add_action( 'wp_ajax_scw_add_to_favorites', 'scw_add_to_favorites' );
add_action( 'wp_ajax_nopriv_scw_add_to_favorites', 'scw_add_to_favorites' );

/**
 * Remove product from favorites via AJAX
 */
function scw_remove_from_favorites() {
	$product_id = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;

	if ( ! $product_id ) {
		wp_send_json_error( array( 'message' => 'Invalid product ID' ) );
		return;
	}

	if ( is_user_logged_in() ) {
		$user_id = get_current_user_id();
		$favorites = get_user_meta( $user_id, 'scw_favorites', true );
		if ( ! is_array( $favorites ) ) {
			$favorites = array();
		}

		$favorites = array_diff( $favorites, array( $product_id ) );

		update_user_meta( $user_id, 'scw_favorites', array_values( $favorites ) );
		wp_send_json_success( array( 'favorites' => $favorites ) );
	} else {
		wp_send_json_success( array( 'message' => 'Favorite removed (guest)' ) );
	}
}
add_action( 'wp_ajax_scw_remove_from_favorites', 'scw_remove_from_favorites' );
add_action( 'wp_ajax_nopriv_scw_remove_from_favorites', 'scw_remove_from_favorites' );

/**
 * Update product price (for reseller gestion mode)
 */
function scw_update_product_price() {
	// Verify nonce
	if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'scw-shop-nonce' ) ) {
		wp_send_json_error( array( 'message' => 'Invalid nonce' ) );
		return;
	}

	// Check if user is logged in and is a reseller
	if ( ! is_user_logged_in() ) {
		wp_send_json_error( array( 'message' => 'User not logged in' ) );
		return;
	}

	$user_role = scw_shop_get_user_role();
	if ( $user_role !== 'reseller' ) {
		wp_send_json_error( array( 'message' => 'Not authorized' ) );
		return;
	}

	$product_id = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
	$price = isset( $_POST['price'] ) ? floatval( $_POST['price'] ) : 0;

	if ( ! $product_id || $price < 0 ) {
		wp_send_json_error( array( 'message' => 'Invalid parameters' ) );
		return;
	}

	// Save custom price for this reseller
	$user_id = get_current_user_id();
	$reseller_prices = get_user_meta( $user_id, 'scw_reseller_prices', true );
	if ( ! is_array( $reseller_prices ) ) {
		$reseller_prices = array();
	}

	$reseller_prices[ $product_id ] = $price;
	update_user_meta( $user_id, 'scw_reseller_prices', $reseller_prices );

	wp_send_json_success( array( 
		'message' => 'Price updated',
		'product_id' => $product_id,
		'price' => $price,
	) );
}
add_action( 'wp_ajax_scw_update_product_price', 'scw_update_product_price' );

/**
 * Add to wholesale cart (for reseller achat mode)
 */
function scw_add_to_wholesale_cart() {
	// Verify nonce
	if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'scw-shop-nonce' ) ) {
		wp_send_json_error( array( 'message' => 'Invalid nonce' ) );
		return;
	}

	// Check if user is logged in and is a reseller
	if ( ! is_user_logged_in() ) {
		wp_send_json_error( array( 'message' => 'User not logged in' ) );
		return;
	}

	$product_id = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
	$quantity = isset( $_POST['quantity'] ) ? absint( $_POST['quantity'] ) : 1;

	if ( ! $product_id ) {
		wp_send_json_error( array( 'message' => 'Invalid product ID' ) );
		return;
	}

	// For now, use the regular cart but we could use a separate wholesale cart
	$cart_item_key = WC()->cart->add_to_cart( $product_id, $quantity );

	if ( $cart_item_key ) {
		wp_send_json_success( array(
			'message' => 'Product added to wholesale cart',
			'cart_count' => WC()->cart->get_cart_contents_count(),
		) );
	} else {
		wp_send_json_error( array( 'message' => 'Failed to add product' ) );
	}
}
add_action( 'wp_ajax_scw_add_to_wholesale_cart', 'scw_add_to_wholesale_cart' );

/**
 * Get wholesale cart count
 */
function scw_get_wholesale_cart_count() {
	wp_send_json_success( array(
		'count' => WC()->cart ? WC()->cart->get_cart_contents_count() : 0,
	) );
}
add_action( 'wp_ajax_scw_get_wholesale_cart_count', 'scw_get_wholesale_cart_count' );

/**
 * Update cart item quantity
 */
function scw_update_cart_quantity() {
	// Verify nonce
	if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'scw-cart-nonce' ) ) {
		wp_send_json_error( array( 'message' => 'Invalid nonce' ) );
		return;
	}

	// Check if user is logged in
	if ( ! is_user_logged_in() ) {
		wp_send_json_error( array( 'message' => 'User not logged in' ) );
		return;
	}

	// Get parameters
	$cart_item_key = isset( $_POST['cart_item_key'] ) ? sanitize_text_field( $_POST['cart_item_key'] ) : '';
	$quantity = isset( $_POST['quantity'] ) ? absint( $_POST['quantity'] ) : 1;

	if ( empty( $cart_item_key ) || $quantity < 1 ) {
		wp_send_json_error( array( 'message' => 'Invalid parameters' ) );
		return;
	}

	// Update cart quantity
	$cart = WC()->cart;
	$cart->set_quantity( $cart_item_key, $quantity, true );

	wp_send_json_success( array( 'message' => 'Cart updated' ) );
}
add_action( 'wp_ajax_scw_update_cart_quantity', 'scw_update_cart_quantity' );

/**
 * Remove cart item
 */
function scw_remove_cart_item() {
	// Verify nonce
	if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'scw-cart-nonce' ) ) {
		wp_send_json_error( array( 'message' => 'Invalid nonce' ) );
		return;
	}

	// Check if user is logged in
	if ( ! is_user_logged_in() ) {
		wp_send_json_error( array( 'message' => 'User not logged in' ) );
		return;
	}

	// Get parameters
	$cart_item_key = isset( $_POST['cart_item_key'] ) ? sanitize_text_field( $_POST['cart_item_key'] ) : '';

	if ( empty( $cart_item_key ) ) {
		wp_send_json_error( array( 'message' => 'Invalid cart item key' ) );
		return;
	}

	// Remove item from cart
	$cart = WC()->cart;
	$result = $cart->remove_cart_item( $cart_item_key );

	if ( $result ) {
		wp_send_json_success( array( 'message' => 'Item removed' ) );
	} else {
		wp_send_json_error( array( 'message' => 'Failed to remove item' ) );
	}
}
add_action( 'wp_ajax_scw_remove_cart_item', 'scw_remove_cart_item' );

/**
 * Update customer account details
 */
function scw_update_account_details() {
	// Check if user is logged in
	if ( ! is_user_logged_in() ) {
		return;
	}

	// Verify nonce
	if ( ! isset( $_POST['scw_account_nonce'] ) || ! wp_verify_nonce( $_POST['scw_account_nonce'], 'scw_update_account' ) ) {
		return;
	}

	// Check if form was submitted
	if ( ! isset( $_POST['save_account_details'] ) ) {
		return;
	}

	$user_id = get_current_user_id();
	$errors = array();

	// Update user data
	$user_data = array(
		'ID' => $user_id,
	);

	// First name
	if ( isset( $_POST['account_first_name'] ) ) {
		$user_data['first_name'] = sanitize_text_field( $_POST['account_first_name'] );
		update_user_meta( $user_id, 'first_name', $user_data['first_name'] );
	}

	// Last name
	if ( isset( $_POST['account_last_name'] ) ) {
		$user_data['last_name'] = sanitize_text_field( $_POST['account_last_name'] );
		update_user_meta( $user_id, 'last_name', $user_data['last_name'] );
	}

	// Display name
	if ( isset( $_POST['account_display_name'] ) ) {
		$user_data['display_name'] = sanitize_text_field( $_POST['account_display_name'] );
	}

	// Email
	if ( isset( $_POST['account_email'] ) ) {
		$email = sanitize_email( $_POST['account_email'] );
		if ( ! is_email( $email ) ) {
			$errors[] = __( 'Adresse e-mail invalide.', 'scw-shop' );
		} elseif ( email_exists( $email ) && $email !== wp_get_current_user()->user_email ) {
			$errors[] = __( 'Cette adresse e-mail est déjà utilisée.', 'scw-shop' );
		} else {
			$user_data['user_email'] = $email;
		}
	}

	// Password change
	if ( ! empty( $_POST['password_new'] ) ) {
		$current_password = isset( $_POST['password_current'] ) ? $_POST['password_current'] : '';
		$new_password = $_POST['password_new'];
		$confirm_password = isset( $_POST['password_confirm'] ) ? $_POST['password_confirm'] : '';

		if ( empty( $current_password ) ) {
			$errors[] = __( 'Veuillez entrer votre mot de passe actuel.', 'scw-shop' );
		} elseif ( ! wp_check_password( $current_password, wp_get_current_user()->user_pass, $user_id ) ) {
			$errors[] = __( 'Le mot de passe actuel est incorrect.', 'scw-shop' );
		} elseif ( empty( $confirm_password ) ) {
			$errors[] = __( 'Veuillez confirmer votre nouveau mot de passe.', 'scw-shop' );
		} elseif ( $new_password !== $confirm_password ) {
			$errors[] = __( 'Les mots de passe ne correspondent pas.', 'scw-shop' );
		} else {
			$user_data['user_pass'] = $new_password;
		}
	}

	// If no errors, update user
	if ( empty( $errors ) ) {
		wp_update_user( $user_data );
		wc_add_notice( __( 'Vos informations ont été mises à jour avec succès.', 'scw-shop' ), 'success' );
	} else {
		foreach ( $errors as $error ) {
			wc_add_notice( $error, 'error' );
		}
	}

	// Redirect back to account page
	wp_safe_redirect( wc_get_page_permalink( 'myaccount' ) );
	exit;
}
add_action( 'template_redirect', 'scw_update_account_details' );

/**
 * Save customer address
 */
function scw_save_address() {
	// Check if user is logged in
	if ( ! is_user_logged_in() ) {
		wp_send_json_error( array( 'message' => 'User not logged in' ) );
		return;
	}

	// Verify nonce
	if ( ! isset( $_POST['address_nonce'] ) || ! wp_verify_nonce( $_POST['address_nonce'], 'scw_save_address' ) ) {
		wp_send_json_error( array( 'message' => 'Invalid nonce' ) );
		return;
	}

	$user_id = get_current_user_id();
	$address_type = isset( $_POST['address_type'] ) ? sanitize_text_field( $_POST['address_type'] ) : '';

	if ( ! in_array( $address_type, array( 'billing', 'shipping' ) ) ) {
		wp_send_json_error( array( 'message' => 'Invalid address type' ) );
		return;
	}

	$customer = new WC_Customer( $user_id );

	// Update address fields
	$fields = array( 'first_name', 'last_name', 'company', 'address_1', 'address_2', 'city', 'postcode', 'country' );

	if ( $address_type === 'billing' ) {
		$fields[] = 'phone';
	}

	foreach ( $fields as $field ) {
		$field_name = $address_type . '_' . $field;
		if ( isset( $_POST[ $field_name ] ) ) {
			$value = sanitize_text_field( $_POST[ $field_name ] );
			$method = 'set_' . $field_name;
			if ( method_exists( $customer, $method ) ) {
				$customer->$method( $value );
			}
		}
	}

	// Save customer
	$customer->save();

	wp_send_json_success( array( 'message' => 'Address saved successfully' ) );
}
add_action( 'wp_ajax_scw_save_address', 'scw_save_address' );

/**
 * Load more products via AJAX (for infinite scroll)
 */
function scw_load_more_products() {
	// Get parameters
	$paged = isset( $_POST['paged'] ) ? max( 1, intval( $_POST['paged'] ) ) : 1;
	$selected_category = isset( $_POST['category'] ) ? sanitize_text_field( $_POST['category'] ) : 'all';
	$selected_brand = isset( $_POST['brand'] ) ? sanitize_text_field( $_POST['brand'] ) : '';
	$search_query = isset( $_POST['search'] ) ? sanitize_text_field( $_POST['search'] ) : '';
	$sort_by = isset( $_POST['orderby'] ) ? sanitize_text_field( $_POST['orderby'] ) : 'default';

	// Build WooCommerce query args
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
			wp_send_json_success( array(
				'html' => '',
				'has_more' => false,
			) );
			return;
		}
	}

	// Generate HTML for products
	ob_start();
	if ( $products_query->have_posts() ) {
		while ( $products_query->have_posts() ) {
			$products_query->the_post();
			wc_get_template_part( 'content', 'product' );
		}
	}
	$html = ob_get_clean();
	wp_reset_postdata();

	wp_send_json_success( array(
		'html' => $html,
		'has_more' => $paged < $products_query->max_num_pages,
	) );
}
add_action( 'wp_ajax_scw_load_more_products', 'scw_load_more_products' );
add_action( 'wp_ajax_nopriv_scw_load_more_products', 'scw_load_more_products' );
