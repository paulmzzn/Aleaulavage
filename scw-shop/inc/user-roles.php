<?php
/**
 * User roles and modes management
 *
 * @package SCW_Shop
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Get current user role (guest, reseller, client)
 *
 * @return string User role
 */
function scw_shop_get_user_role() {
    if ( ! is_user_logged_in() ) {
        return 'guest';
    }

    $user = wp_get_current_user();

    // Check if user is a reseller
    if ( in_array( 'reseller', $user->roles ) ) {
        return 'reseller';
    }

    // Check if user is admin or shop manager - treat as client
    if ( in_array( 'administrator', $user->roles ) || in_array( 'shop_manager', $user->roles ) ) {
        return 'client';
    }

    // Check if user is a client
    if ( in_array( 'client', $user->roles ) || in_array( 'customer', $user->roles ) ) {
        return 'client';
    }

    // Default to guest for other roles
    return 'guest';
}

/**
 * Get current user mode for resellers (gestion, achat, vitrine)
 *
 * @return string|null User mode or null if not a reseller
 */
function scw_shop_get_user_mode() {
    if ( scw_shop_get_user_role() !== 'reseller' ) {
        return null;
    }

    // Get mode from session or user meta
    if ( isset( $_SESSION['scw_user_mode'] ) ) {
        return sanitize_text_field( $_SESSION['scw_user_mode'] );
    }

    $user_id = get_current_user_id();
    $mode = get_user_meta( $user_id, 'scw_user_mode', true );

    // Default to 'achat' if no mode is set
    return $mode ? $mode : 'achat';
}

/**
 * Set user mode
 *
 * @param string $mode Mode to set (gestion, achat, vitrine)
 * @return bool Success
 */
function scw_shop_set_user_mode( $mode ) {
    if ( ! in_array( $mode, array( 'gestion', 'achat', 'vitrine' ) ) ) {
        return false;
    }

    if ( scw_shop_get_user_role() !== 'reseller' ) {
        return false;
    }

    $user_id = get_current_user_id();
    update_user_meta( $user_id, 'scw_user_mode', $mode );

    if ( session_status() === PHP_SESSION_NONE ) {
        session_start();
    }
    $_SESSION['scw_user_mode'] = $mode;

    return true;
}

/**
 * Get user store color (for reseller clients)
 *
 * @return string|null Color hex or null
 */
function scw_shop_get_user_store_color() {
    $user_id = get_current_user_id();
    if ( ! $user_id ) {
        return null;
    }

    // For clients, get their reseller's store color
    if ( scw_shop_get_user_role() === 'client' ) {
        $reseller_id = get_user_meta( $user_id, 'scw_reseller_id', true );
        if ( $reseller_id ) {
            return get_user_meta( $reseller_id, 'scw_store_color', true );
        }
    }

    // For resellers, get their own store color
    if ( scw_shop_get_user_role() === 'reseller' ) {
        return get_user_meta( $user_id, 'scw_store_color', true );
    }

    return null;
}

/**
 * Check if user can see prices
 *
 * @return bool
 */
function scw_shop_can_see_prices() {
    $user_role = scw_shop_get_user_role();
    return in_array( $user_role, array( 'reseller', 'client' ) );
}

/**
 * AJAX handler for changing user mode
 */
function scw_shop_ajax_change_mode() {
    check_ajax_referer( 'scw-shop-nonce', 'nonce' );

    if ( ! isset( $_POST['mode'] ) ) {
        wp_send_json_error( array( 'message' => 'Mode non spécifié' ) );
    }

    $mode = sanitize_text_field( $_POST['mode'] );
    $success = scw_shop_set_user_mode( $mode );

    if ( $success ) {
        wp_send_json_success( array( 'mode' => $mode ) );
    } else {
        wp_send_json_error( array( 'message' => 'Erreur lors du changement de mode' ) );
    }
}
add_action( 'wp_ajax_scw_change_mode', 'scw_shop_ajax_change_mode' );

/**
 * Register custom user roles
 */
function scw_shop_register_roles() {
    // Add reseller role
    add_role(
        'reseller',
        __( 'Revendeur', 'scw-shop' ),
        array(
            'read'         => true,
            'edit_posts'   => false,
            'delete_posts' => false,
        )
    );

    // Add client role
    add_role(
        'client',
        __( 'Client', 'scw-shop' ),
        array(
            'read'         => true,
            'edit_posts'   => false,
            'delete_posts' => false,
        )
    );
}
add_action( 'after_switch_theme', 'scw_shop_register_roles' );

/**
 * Redirect to account page after login
 */
function scw_shop_login_redirect( $redirect_to, $request = '', $user = null ) {
    // WooCommerce passes only 2 args, WordPress passes 3
    if ( is_object( $request ) && isset( $request->roles ) ) {
        $user = $request;
    }

    // Check if user is logged in
    if ( $user && isset( $user->roles ) && is_array( $user->roles ) ) {
        // Get the account page URL
        $account_page = get_page_by_path( 'compte' );
        if ( $account_page ) {
            return get_permalink( $account_page->ID );
        }
    }
    return $redirect_to;
}
add_filter( 'login_redirect', 'scw_shop_login_redirect', 10, 3 );
add_filter( 'woocommerce_login_redirect', 'scw_shop_login_redirect', 10, 2 );
