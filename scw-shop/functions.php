<?php
/**
 * SCW Shop functions and definitions
 *
 * @package SCW_Shop
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Theme version - use timestamp for cache busting during development
define( 'SCW_SHOP_VERSION', '1.1.0-' . time() );

// Theme directory path
define( 'SCW_SHOP_DIR', get_template_directory() );

// Theme directory URI
define( 'SCW_SHOP_URI', get_template_directory_uri() );

/**
 * Load theme includes
 */
require_once SCW_SHOP_DIR . '/inc/theme-setup.php';
require_once SCW_SHOP_DIR . '/inc/enqueue-scripts.php';
require_once SCW_SHOP_DIR . '/inc/template-functions.php';
require_once SCW_SHOP_DIR . '/inc/user-roles.php';
require_once SCW_SHOP_DIR . '/inc/home-page-setup.php';
require_once SCW_SHOP_DIR . '/inc/footer-page-setup.php';
require_once SCW_SHOP_DIR . '/inc/ajax-handlers.php';

/**
 * Load WooCommerce compatibility if WooCommerce is active
 */
if ( class_exists( 'WooCommerce' ) ) {
    require_once SCW_SHOP_DIR . '/inc/woocommerce.php';
}

/**
 * Set up clean permalinks on theme activation
 */
function scw_shop_setup_permalinks() {
    // Set permalink structure to post name
    update_option( 'permalink_structure', '/%postname%/' );

    // Flush rewrite rules
    flush_rewrite_rules();

    // Update page slugs
    $pages = array(
        array( 'title' => 'Compte', 'slug' => 'compte' ),
        array( 'title' => 'Boutique', 'slug' => 'boutique' ),
    );

    foreach ( $pages as $page_data ) {
        $query = new WP_Query( array(
            'post_type'      => 'page',
            'title'          => $page_data['title'],
            'posts_per_page' => 1,
        ) );

        if ( $query->have_posts() ) {
            $page = $query->posts[0];
            if ( $page->post_name !== $page_data['slug'] ) {
                wp_update_post( array(
                    'ID'        => $page->ID,
                    'post_name' => $page_data['slug'],
                ) );
            }
        }
        wp_reset_postdata();
    }
}
add_action( 'after_switch_theme', 'scw_shop_setup_permalinks' );
