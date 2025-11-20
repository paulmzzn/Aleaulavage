<?php
/**
 * Template Name: Page Favoris
 * The template for displaying the Wishlist page
 */

defined('ABSPATH') || exit;

get_header();
?>

<main id="primary" class="site-main shop-page">

    <!-- Wishlist Hero Section -->
    <section class="shop-hero">
        <div class="container">
            <div class="shop-hero-content">
                <!-- Back Button -->
                <div class="mb-3">
                    <a href="<?php echo esc_url(home_url('/')); ?>" class="btn-back-home">
                        <i class="fa-solid fa-house me-2"></i>Retour à l'accueil
                    </a>
                </div>

                <h1 class="shop-title">Mes <span class="text-primary">Favoris</span></h1>
                <p class="shop-subtitle">Retrouvez ici tous vos produits sauvegardés.</p>
            </div>
        </div>
    </section>

    <!-- Wishlist Content -->
    <section class="shop-content">
        <div class="container">

            <!-- Breadcrumb -->
            <div class="breadcrumb-simple mb-3">
                <nav class="woocommerce-breadcrumb">
                    <a href="<?php echo esc_url(home_url()); ?>">Accueil</a>
                    <i class="fa-solid fa-chevron-right mx-2"></i>
                    <span>Favoris</span>
                </nav>
            </div>

            <div class="row">

                <!-- Sidebar Filters (Desktop) - Visual Only for Wishlist -->
                <aside class="col-lg-3 shop-sidebar d-none d-lg-block">
                    <div class="sidebar-sticky">
                        <!-- Categories Widget -->
                        <div class="filter-widget categories-widget">
                            <h3 class="filter-title">
                                <i class="fa-solid fa-list me-2"></i>Catégories
                            </h3>
                            <div class="filter-content categories-scrollable">
                                <?php
                                // Get current wishlist category
                                $current_wishlist_cat = isset($_GET['wishlist_category']) ? absint($_GET['wishlist_category']) : 0;

                                // Get only parent categories
                                $parent_categories = get_terms(array(
                                    'taxonomy' => 'product_cat',
                                    'parent' => 0,
                                    'hide_empty' => true,
                                    'orderby' => 'name',
                                ));

                                if (!empty($parent_categories) && !is_wp_error($parent_categories)) {
                                    echo '<ul class="product-categories">';

                                    // Base URL for the wishlist page
                                    $base_url = get_permalink();

                                    // "All" link
                                    $all_classes = 'cat-item';
                                    if ($current_wishlist_cat === 0) $all_classes .= ' current-cat';
                                    echo '<li class="' . esc_attr($all_classes) . '">';
                                    echo '<div class="cat-item-wrapper">';
                                    echo '<a href="' . esc_url($base_url) . '">Tout voir</a>';
                                    echo '</div>';
                                    echo '</li>';

                                    foreach ($parent_categories as $parent_cat) {
                                        $classes = 'cat-item cat-item-' . $parent_cat->term_id;
                                        if ($parent_cat->term_id === $current_wishlist_cat) {
                                            $classes .= ' current-cat';
                                        }

                                        // Link to wishlist page with category param
                                        $link = add_query_arg('wishlist_category', $parent_cat->term_id, $base_url);

                                        echo '<li class="' . esc_attr($classes) . '">';
                                        echo '<div class="cat-item-wrapper">';
                                        echo '<a href="' . esc_url($link) . '">';
                                        echo esc_html($parent_cat->name);
                                        echo '</a>';
                                        echo '</div>';
                                        echo '</li>';
                                    }
                                    echo '</ul>';
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </aside>

                <!-- Wishlist Grid -->
                <div class="col-lg-9 shop-products">

                    <!-- Mobile Filter Toggle (Hidden for now as filters don't apply to wishlist directly, but keeping structure if needed) -->
                    <!-- 
                    <div class="mobile-filter-toggle d-lg-none mb-4">
                        <button class="btn btn-outline-primary w-100" data-bs-toggle="offcanvas" data-bs-target="#filterOffcanvas">
                            <i class="fa-solid fa-filter me-2"></i>Catégories
                        </button>
                    </div>
                    -->

                    <?php
                    // Output the wishlist shortcode
                    echo do_shortcode('[aleaulavage_wishlist]');
                    ?>

                </div>

            </div>
        </div>
    </section>

</main>

<!-- Mobile Categories Offcanvas -->
<div class="offcanvas offcanvas-start" tabindex="-1" id="filterOffcanvas" aria-labelledby="filterOffcanvasLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="filterOffcanvasLabel">
            <i class="fa-solid fa-list me-2"></i>Catégories
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <div class="filter-widget categories-widget">
            <div class="filter-content categories-scrollable">
                <?php
                if (!empty($parent_categories) && !is_wp_error($parent_categories)) {
                    echo '<ul class="product-categories">';
                    foreach ($parent_categories as $parent_cat) {
                        echo '<li class="cat-item cat-item-' . $parent_cat->term_id . '">';
                        echo '<div class="cat-item-wrapper">';
                        echo '<a href="' . esc_url(get_term_link($parent_cat)) . '">';
                        echo esc_html($parent_cat->name);
                        echo '</a>';
                        echo '</div>';
                        echo '</li>';
                    }
                    echo '</ul>';
                }
                ?>
            </div>
        </div>
    </div>
</div>

<?php
get_footer();
