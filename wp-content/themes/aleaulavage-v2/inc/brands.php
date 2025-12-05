<?php
/**
 * Brand taxonomy setup
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register custom taxonomy for product brands
 */
function aleaulavage_register_brand_taxonomy() {
    $labels = array(
        'name'              => 'Marques',
        'singular_name'     => 'Marque',
        'search_items'      => 'Rechercher des marques',
        'all_items'         => 'Toutes les marques',
        'parent_item'       => 'Marque parente',
        'parent_item_colon' => 'Marque parente :',
        'edit_item'         => 'Modifier la marque',
        'update_item'       => 'Mettre à jour la marque',
        'add_new_item'      => 'Ajouter une nouvelle marque',
        'new_item_name'     => 'Nom de la nouvelle marque',
        'menu_name'         => 'Marques',
    );

    $args = array(
        'hierarchical'      => false,
        'labels'            => $labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'show_in_rest'      => true,
        'query_var'         => true,
        'rewrite'           => array(
            'slug'         => 'marque',
            'with_front'   => false,
            'hierarchical' => false,
        ),
        'show_in_nav_menus' => true,
        'public'            => true,
    );

    register_taxonomy('product_brand', array('product'), $args);
}
add_action('init', 'aleaulavage_register_brand_taxonomy');

/**
 * Add brand to WooCommerce product admin columns
 */
function aleaulavage_add_brand_column($columns) {
    $new_columns = array();
    foreach ($columns as $key => $column) {
        $new_columns[$key] = $column;
        if ($key === 'product_cat') {
            $new_columns['product_brand'] = 'Marque';
        }
    }
    return $new_columns;
}
add_filter('manage_edit-product_columns', 'aleaulavage_add_brand_column');

/**
 * Display brand in WooCommerce product admin column
 */
function aleaulavage_display_brand_column($column, $post_id) {
    if ($column === 'product_brand') {
        $terms = get_the_terms($post_id, 'product_brand');
        if (!empty($terms) && !is_wp_error($terms)) {
            $brand_names = array();
            foreach ($terms as $term) {
                $brand_names[] = $term->name;
            }
            echo implode(', ', $brand_names);
        } else {
            echo '—';
        }
    }
}
add_action('manage_product_posts_custom_column', 'aleaulavage_display_brand_column', 10, 2);
