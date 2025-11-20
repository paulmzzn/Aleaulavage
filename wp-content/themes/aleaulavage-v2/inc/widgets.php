<?php
/**
 * Register Widget Areas
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

function aleaulavage_v2_widgets_init()
{
    // Sidebar
    register_sidebar(array(
        'name' => esc_html__('Sidebar', 'aleaulavage-v2'),
        'id' => 'sidebar-1',
        'description' => esc_html__('Add widgets here.', 'aleaulavage-v2'),
        'before_widget' => '<section id="%1$s" class="widget %2$s">',
        'after_widget' => '</section>',
        'before_title' => '<h2 class="widget-title">',
        'after_title' => '</h2>',
    ));

    // Footer Contact Area
    register_sidebar(array(
        'name' => esc_html__('Footer Contact Area', 'aleaulavage-v2'),
        'id' => 'footer_contact_area',
        'description' => esc_html__('Add contact information widgets here.', 'aleaulavage-v2'),
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget' => '</div>',
        'before_title' => '<h4 class="widget-title">',
        'after_title' => '</h4>',
    ));

    // Footer Social Area
    register_sidebar(array(
        'name' => esc_html__('Footer Social Area', 'aleaulavage-v2'),
        'id' => 'footer_social_area',
        'description' => esc_html__('Add social media widgets here.', 'aleaulavage-v2'),
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget' => '</div>',
        'before_title' => '<h4 class="widget-title">',
        'after_title' => '</h4>',
    ));

    // Footer Schedule Area
    register_sidebar(array(
        'name' => esc_html__('Footer Schedule Area', 'aleaulavage-v2'),
        'id' => 'footer_schedule_area',
        'description' => esc_html__('Add schedule/hours widgets here.', 'aleaulavage-v2'),
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget' => '</div>',
        'before_title' => '<h4 class="widget-title">',
        'after_title' => '</h4>',
    ));

    // Shop Sidebar (for filters & widgets)
    register_sidebar(array(
        'name' => esc_html__('Shop Sidebar', 'aleaulavage-v2'),
        'id' => 'shop-sidebar',
        'description' => esc_html__('Add shop filters and widgets here.', 'aleaulavage-v2'),
        'before_widget' => '<div id="%1$s" class="filter-widget %2$s">',
        'after_widget' => '</div>',
        'before_title' => '<h3 class="filter-title">',
        'after_title' => '</h3>',
    ));
}
add_action('widgets_init', 'aleaulavage_v2_widgets_init');
