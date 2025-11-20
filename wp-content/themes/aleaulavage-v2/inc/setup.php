<?php
/**
 * Theme Setup
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

function aleaulavage_v2_setup()
{
    // Add default posts and comments RSS feed links to head.
    add_theme_support('automatic-feed-links');

    // Let WordPress manage the document title.
    add_theme_support('title-tag');

    // Enable support for Post Thumbnails on posts and pages.
    add_theme_support('post-thumbnails');

    // Register Navigation Menus
    register_nav_menus(array(
        'primary' => esc_html__('Primary Menu', 'aleaulavage-v2'),
        'mobile' => esc_html__('Mobile Menu', 'aleaulavage-v2'),
        'footer-menu' => esc_html__('Footer Menu', 'aleaulavage-v2'),
        'footer-legal' => esc_html__('Footer Legal', 'aleaulavage-v2'),
    ));

    // HTML5 support
    add_theme_support('html5', array(
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
        'style',
        'script',
    ));

    // WooCommerce Support
    add_theme_support('woocommerce');
}
add_action('after_setup_theme', 'aleaulavage_v2_setup');
