<?php
/**
 * Template part for product search form
 *
 * @param string $classes Additional CSS classes for the container
 * @param string $input_classes CSS classes for the input group
 * @param string $placeholder Placeholder text for the search input
 */

$classes = isset($args['classes']) ? $args['classes'] : '';
$input_classes = isset($args['input_classes']) ? $args['input_classes'] : 'search-input-group';
$placeholder = isset($args['placeholder']) ? $args['placeholder'] : 'Que recherchez-vous ?';
$aria_label = isset($args['aria_label']) ? $args['aria_label'] : 'Recherche de produits';
?>

<div class="search-container <?php echo esc_attr($classes); ?>">
    <form role="search" method="get" class="woocommerce-product-search"
        action="<?php echo esc_url(home_url('/')); ?>" aria-label="<?php echo esc_attr($aria_label); ?>">
        <div class="input-group <?php echo esc_attr($input_classes); ?>">
            <span class="input-group-text bg-white border-0 pe-0" aria-hidden="true">
                <i class="fa-solid fa-magnifying-glass"></i>
            </span>
            <input type="search" class="form-control border-0 ps-2" placeholder="<?php echo esc_attr($placeholder); ?>"
                value="<?php echo get_search_query(); ?>" name="s" aria-label="Rechercher un produit">
            <input type="hidden" name="post_type" value="product" />
        </div>
    </form>
</div>
