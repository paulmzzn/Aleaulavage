<?php
/**
 * Theme setup functions
 *
 * @package SCW_Shop
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Sets up theme defaults and registers support for various WordPress features
 */
function scw_shop_setup() {
    // Make theme available for translation
    load_theme_textdomain( 'scw-shop', SCW_SHOP_DIR . '/languages' );

    // Add default posts and comments RSS feed links to head
    add_theme_support( 'automatic-feed-links' );

    // Let WordPress manage the document title
    add_theme_support( 'title-tag' );

    // Enable support for Post Thumbnails on posts and pages
    add_theme_support( 'post-thumbnails' );

    // Custom image sizes
    add_image_size( 'scw-product-thumbnail', 300, 300, true );
    add_image_size( 'scw-product-large', 800, 800, true );
    add_image_size( 'scw-product-slider', 600, 400, true );

    // Register navigation menus
    register_nav_menus( array(
        'primary'    => __( 'Menu principal', 'scw-shop' ),
        'categories' => __( 'Menu catégories', 'scw-shop' ),
        'footer'     => __( 'Menu pied de page', 'scw-shop' ),
    ) );

    // Switch default core markup to output valid HTML5
    add_theme_support( 'html5', array(
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
        'style',
        'script',
    ) );

    // Add theme support for selective refresh for widgets
    add_theme_support( 'customize-selective-refresh-widgets' );

    // Add support for custom logo
    add_theme_support( 'custom-logo', array(
        'height'      => 80,
        'width'       => 200,
        'flex-height' => true,
        'flex-width'  => true,
    ) );

    // Add support for WooCommerce
    add_theme_support( 'woocommerce' );
    add_theme_support( 'wc-product-gallery-zoom' );
    add_theme_support( 'wc-product-gallery-lightbox' );
    add_theme_support( 'wc-product-gallery-slider' );

    // Add support for editor styles
    add_theme_support( 'editor-styles' );
}
add_action( 'after_setup_theme', 'scw_shop_setup' );

/**
 * Register widget areas
 */
function scw_shop_widgets_init() {
    register_sidebar( array(
        'name'          => __( 'Sidebar', 'scw-shop' ),
        'id'            => 'sidebar-1',
        'description'   => __( 'Zone de widgets principale', 'scw-shop' ),
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h2 class="widget-title">',
        'after_title'   => '</h2>',
    ) );

    register_sidebar( array(
        'name'          => __( 'Pied de page - Colonne 1', 'scw-shop' ),
        'id'            => 'footer-1',
        'description'   => __( 'Première colonne du pied de page', 'scw-shop' ),
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ) );

    register_sidebar( array(
        'name'          => __( 'Pied de page - Colonne 2', 'scw-shop' ),
        'id'            => 'footer-2',
        'description'   => __( 'Deuxième colonne du pied de page', 'scw-shop' ),
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ) );

    register_sidebar( array(
        'name'          => __( 'Pied de page - Colonne 3', 'scw-shop' ),
        'id'            => 'footer-3',
        'description'   => __( 'Troisième colonne du pied de page', 'scw-shop' ),
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ) );
}
add_action( 'widgets_init', 'scw_shop_widgets_init' );

/**
 * Set the content width in pixels
 */
function scw_shop_content_width() {
    $GLOBALS['content_width'] = apply_filters( 'scw_shop_content_width', 1200 );
}
add_action( 'after_setup_theme', 'scw_shop_content_width', 0 );
