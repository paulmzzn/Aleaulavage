<?php
/**
 * Template part for displaying the category bar (desktop)
 */
?>
<div class="category-bar bg-light d-none d-md-block">
    <div class="category-scroll-container position-relative">
        <button class="category-scroll-btn category-scroll-left" id="scrollLeft">
            <i class="fa-solid fa-chevron-left"></i>
        </button>
        <div class="category-list">
            <?php
            $cat_args = array(
                'orderby' => 'menu_order',
                'order' => 'ASC',
                'hide_empty' => true,
                'parent' => 0,
                'exclude' => [16], // Uncategorized or specific ID
                'number' => 20
            );

            $product_categories = get_terms('product_cat', $cat_args);

            // "All Categories" Link
            echo '<a href="' . esc_url(home_url('boutique/')) . '" class="category-item">';
            echo '<i class="fa-solid fa-grid-2 me-2"></i>Toutes les catégories';
            echo '</a>';

            if (!empty($product_categories) && !is_wp_error($product_categories)) {
                foreach ($product_categories as $category) {
                    $thumbnail_id = get_term_meta($category->term_id, 'thumbnail_id', true);
                    $category_link = get_term_link($category);

                    echo '<a href="' . esc_url($category_link) . '" class="category-item">';

                    if ($thumbnail_id) {
                        echo '<img src="' . esc_url(wp_get_attachment_url($thumbnail_id)) . '" alt="' . esc_attr($category->name) . '">';
                    }

                    echo esc_html($category->name);
                    echo '</a>';
                }
            }
            ?>
        </div>
        <button class="category-scroll-btn category-scroll-right" id="scrollRight">
            <i class="fa-solid fa-chevron-right"></i>
        </button>
    </div>
</div>