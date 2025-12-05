<?php
/**
 * Template functions
 *
 * @package SCW_Shop
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Get product price based on user role
 *
 * @param int    $product_id Product ID
 * @param string $user_role  User role (guest, client, reseller)
 * @return float Product price
 */
function scw_shop_get_product_price( $product_id, $user_role = null ) {
    $product = wc_get_product( $product_id );
    if ( ! $product ) {
        return 0;
    }

    if ( $user_role === null ) {
        $user_role = scw_shop_get_user_role();
    }

    // For resellers in achat mode, return the buy price
    if ( $user_role === 'reseller' ) {
        $user_mode = scw_shop_get_user_mode();
        if ( $user_mode === 'achat' ) {
            $buy_price = get_post_meta( $product_id, '_scw_buy_price', true );
            if ( $buy_price ) {
                return (float) $buy_price;
            }
        }
        
        // For gestion mode, check if user has custom price
        if ( $user_mode === 'gestion' && is_user_logged_in() ) {
            $user_id = get_current_user_id();
            $reseller_prices = get_user_meta( $user_id, 'scw_reseller_prices', true );
            if ( is_array( $reseller_prices ) && isset( $reseller_prices[ $product_id ] ) ) {
                return (float) $reseller_prices[ $product_id ];
            }
        }
    }

    // Default: return regular/sale price
    $price = $product->get_price();
    return $price ? (float) $price : 0;
}

/**
 * Get template part with fallback
 *
 * @param string $slug Template slug
 * @param string $name Template name
 * @param array  $args Arguments to pass to template
 */
function scw_shop_get_template_part( $slug, $name = null, $args = array() ) {
    if ( ! empty( $args ) ) {
        extract( $args );
    }

    $templates = array();
    if ( $name ) {
        $templates[] = "{$slug}-{$name}.php";
    }
    $templates[] = "{$slug}.php";

    locate_template( $templates, true, false );
}

/**
 * Add custom body classes
 *
 * @param array $classes Body classes
 * @return array
 */
function scw_shop_body_classes( $classes ) {
    // User role class
    $user_role = scw_shop_get_user_role();
    if ( $user_role ) {
        $classes[] = 'user-role-' . $user_role;
    }

    // User mode class
    $user_mode = scw_shop_get_user_mode();
    if ( $user_mode ) {
        $classes[] = 'user-mode-' . $user_mode;
    }

    // Add class for pages without sidebar
    if ( ! is_active_sidebar( 'sidebar-1' ) ) {
        $classes[] = 'no-sidebar';
    }

    return $classes;
}
add_filter( 'body_class', 'scw_shop_body_classes' );

/**
 * Get dynamic CSS variables based on user role and settings
 *
 * @return string CSS variables
 */
function scw_shop_get_css_variables() {
    $user_role = scw_shop_get_user_role();
    $store_color = scw_shop_get_user_store_color();

    $css = ':root {';
    $css .= '--color-primary-dark: #0f172a;';
    $css .= '--color-primary-light: #1e293b;';
    $css .= '--color-accent-default: #0ea5e9;';
    $css .= '--color-reseller-accent: #4338ca;';

    // Dynamic accent color based on user role
    if ( $user_role === 'reseller' ) {
        $css .= '--site-accent-color: var(--color-reseller-accent);';
    } else {
        $css .= '--site-accent-color: var(--color-accent-default);';
    }

    // User store color (for reseller clients)
    if ( $store_color ) {
        $css .= '--user-store-color: ' . esc_attr( $store_color ) . ';';
    }

    $css .= '}';

    return $css;
}

/**
 * Output inline CSS variables
 */
function scw_shop_output_css_variables() {
    echo '<style id="scw-shop-variables">' . scw_shop_get_css_variables() . '</style>';
}
add_action( 'wp_head', 'scw_shop_output_css_variables' );

/**
 * Get SVG icon
 *
 * @param string $icon Icon name
 * @param string $class Additional classes
 * @return string
 */
function scw_shop_get_icon( $icon, $class = '' ) {
    $icons = array(
        'menu' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>',
        'close' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>',
        'chevron-down' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>',
        'user' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>',
        'cart' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" /></svg>',
        'heart' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" /></svg>',
    );

    if ( ! isset( $icons[ $icon ] ) ) {
        return '';
    }

    return sprintf(
        '<span class="icon icon-%s %s">%s</span>',
        esc_attr( $icon ),
        esc_attr( $class ),
        $icons[ $icon ]
    );
}

/**
 * Pagination
 */
function scw_shop_pagination() {
    the_posts_pagination( array(
        'mid_size'  => 2,
        'prev_text' => __( '&larr; Précédent', 'scw-shop' ),
        'next_text' => __( 'Suivant &rarr;', 'scw-shop' ),
    ) );
}

/**
 * Get category icon SVG or thumbnail image
 *
 * @param mixed $category Category object or index
 * @return string SVG icon or image HTML
 */
function scw_shop_get_category_icon( $category ) {
    // Si c'est un objet catégorie, essayer de récupérer l'image thumbnail
    if ( is_object( $category ) && isset( $category->term_id ) ) {
        $thumbnail_id = get_term_meta( $category->term_id, 'thumbnail_id', true );
        
        if ( $thumbnail_id ) {
            $image_url = wp_get_attachment_image_url( $thumbnail_id, 'medium' );
            if ( $image_url ) {
                return '<img src="' . esc_url( $image_url ) . '" alt="' . esc_attr( $category->name ) . '" class="category-thumbnail" />';
            }
        }
        
        // Pas d'image, utiliser l'index 0 pour l'icône par défaut
        $index = 0;
    } else {
        // C'est un index
        $index = (int) $category;
    }
    
    $icons = array(
        '<svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4 4h6v6H4zM14 4h6v6h-6zM4 14h6v6H4zM14 14h6v6h-6z" /></svg>',
        '<svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83" /></svg>',
        '<svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M14 16H9m10 0h3v-3.15a1 1 0 00-.84-.99L16 11l-2.7-3.6a1 1 0 00-.8-.4H5.24a2 2 0 00-1.8 1.1l-.8 1.63A6 6 0 002 12.42V16h2" /></svg>',
        '<svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M9.59 4.59A2 2 0 1 1 11 8H2m10.59 11.41A2 2 0 1 0 14 16H2m15.73-8.27A2.5 2.5 0 1 1 19.5 12H2" /></svg>',
        '<svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" /></svg>',
        '<svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z" /></svg>',
        '<svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z" /></svg>',
        '<svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 22a10 10 0 1 0 0-20 10 10 0 0 0 0 20zM12 6v6l4 2" /></svg>',
        '<svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 2.69l5.74 5.88a6 6 0 0 1-8.48 8.48A6 6 0 0 1 5.53 9.43L12 2.69z" /></svg>',
    );

    $icon_index = $index % count( $icons );
    return $icons[ $icon_index ];
}
