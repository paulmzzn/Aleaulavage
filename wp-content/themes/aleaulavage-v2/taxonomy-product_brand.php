<?php
/**
 * The Template for displaying product brand archives
 * Modern & Minimalist Design - Full Width (No Sidebar)
 */

defined('ABSPATH') || exit;

get_header();

// Get current brand
$current_brand = get_queried_object();
?>

<main id="primary" class="site-main shop-page">

    <!-- Shop Hero Section -->
    <section class="shop-hero">
        <div class="container">
            <div class="shop-hero-content">

                <!-- Back Button -->
                <div class="mb-3">
                    <a href="<?php echo esc_url(home_url('boutique/')); ?>" class="btn-back-home">
                        <i class="fa-solid fa-store me-2"></i>Retour à la boutique
                    </a>
                </div>

                <h1 class="shop-title"><?php echo esc_html($current_brand->name); ?></h1>

                <?php if ($current_brand->description): ?>
                    <?php
                    $description = $current_brand->description;
                    $short_desc = wp_trim_words(strip_tags($description), 20, '...');
                    ?>
                    <div class="category-description-short">
                        <p class="shop-subtitle"><?php echo esc_html($short_desc); ?></p>
                        <?php if (str_word_count(strip_tags($description)) > 20): ?>
                            <button class="read-more-btn" onclick="scrollToFullDescription()">
                                <i class="fa-solid fa-circle-down me-2"></i>Lire la suite
                            </button>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Shop Content -->
    <section class="shop-content">
        <div class="container">

            <!-- Breadcrumb -->
            <div class="breadcrumb-simple mb-3">
                <?php woocommerce_breadcrumb(array(
                    'delimiter' => '<i class="fa-solid fa-chevron-right mx-2"></i>',
                    'wrap_before' => '<nav class="woocommerce-breadcrumb">',
                    'wrap_after' => '</nav>',
                )); ?>
            </div>

            <div class="row">

                <!-- Sidebar Filters (Desktop) -->
                <aside class="col-lg-3 shop-sidebar d-none d-lg-block">

                    <div class="sidebar-sticky">

                        <!-- Sorting Options -->
                        <div class="filter-widget sorting-widget">
                            <h3 class="filter-title">
                                <i class="fa-solid fa-arrow-down-wide-short me-2"></i>Trier par
                            </h3>
                            <div class="filter-content">
                                <form class="woocommerce-ordering" method="get">
                                    <select name="orderby" class="orderby" aria-label="Trier par">
                                        <?php
                                        $catalog_orderby_options = array(
                                            'menu_order' => 'Par défaut',
                                            'popularity' => 'Popularité',
                                            'rating' => 'Note moyenne',
                                            'date' => 'Les plus récents',
                                            'price' => 'Prix : croissant',
                                            'price-desc' => 'Prix : décroissant',
                                        );

                                        $orderby = isset($_GET['orderby']) ? wc_clean($_GET['orderby']) : wc_get_loop_prop('orderby');

                                        foreach ($catalog_orderby_options as $id => $name) {
                                            echo '<option value="' . esc_attr($id) . '" ' . selected($orderby, $id, false) . '>' . esc_html($name) . '</option>';
                                        }
                                        ?>
                                    </select>
                                    <input type="hidden" name="paged" value="1" />
                                    <?php wc_query_string_form_fields(null, array('orderby', 'submit', 'paged', 'product-page')); ?>
                                </form>
                            </div>
                        </div>

                        <!-- Brands Widget -->
                        <div class="filter-widget categories-widget">
                            <h3 class="filter-title">
                                <i class="fa-solid fa-tags me-2"></i>Marques
                            </h3>
                            <div class="filter-content categories-scrollable">
                                <?php
                                $brands = get_terms(array(
                                    'taxonomy' => 'product_brand',
                                    'hide_empty' => true,
                                    'orderby' => 'name',
                                ));

                                if (!empty($brands) && !is_wp_error($brands)) {
                                    echo '<ul class="product-categories">';
                                    foreach ($brands as $brand) {
                                        $is_current = ($brand->term_id === $current_brand->term_id);
                                        $classes = 'cat-item cat-item-' . $brand->term_id;
                                        if ($is_current) {
                                            $classes .= ' current-cat';
                                        }

                                        echo '<li class="' . esc_attr($classes) . '">';
                                        echo '<div class="cat-item-wrapper">';
                                        echo '<a href="' . esc_url(get_term_link($brand)) . '">';
                                        echo esc_html($brand->name);
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

                <!-- Products Grid -->
                <div class="col-lg-9 shop-products">

                    <!-- Mobile Filter Toggle -->
                    <div class="mobile-filter-toggle d-lg-none mb-4">
                        <button class="btn btn-outline-primary w-100" data-bs-toggle="offcanvas"
                            data-bs-target="#filterOffcanvas">
                            <i class="fa-solid fa-filter me-2"></i>Filtres & Marques
                        </button>
                    </div>

                <?php if (woocommerce_product_loop()): ?>

                    <?php woocommerce_product_loop_start(); ?>

                    <?php while (have_posts()): ?>
                        <?php the_post(); ?>
                        <?php wc_get_template_part('content', 'product'); ?>
                    <?php endwhile; ?>

                    <?php woocommerce_product_loop_end(); ?>

                    <!-- Pagination -->
                    <div class="shop-pagination">
                        <?php
                        woocommerce_pagination(array(
                            'prev_text' => '<i class="fa-solid fa-chevron-left"></i>',
                            'next_text' => '<i class="fa-solid fa-chevron-right"></i>',
                        ));
                        ?>
                    </div>

                <?php else: ?>

                    <!-- No Products Found -->
                    <div class="no-products-found">
                        <div class="no-products-icon">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </div>
                        <h3>Aucun produit trouvé</h3>
                        <p>Aucun produit disponible pour cette marque actuellement</p>
                        <a href="<?php echo esc_url(home_url('/boutique/')); ?>" class="btn btn-primary mt-3">
                            <i class="fa-solid fa-store me-2"></i>Voir tous les produits
                        </a>
                    </div>

                <?php endif; ?>

                </div>

            </div>
        </div>
    </section>

    <!-- Full Brand Description (SEO) -->
    <?php if ($current_brand->description && str_word_count(strip_tags($current_brand->description)) > 20): ?>
        <section class="category-description-full" id="full-description">
            <div class="container">
                <div class="description-content">
                    <h2 class="mb-4">À propos de <?php echo esc_html($current_brand->name); ?></h2>
                    <?php echo wp_kses_post(wpautop(wc_format_content($current_brand->description))); ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

</main>

<!-- Mobile Filter Offcanvas -->
<div class="offcanvas offcanvas-start" tabindex="-1" id="filterOffcanvas" aria-labelledby="filterOffcanvasLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="filterOffcanvasLabel">
            <i class="fa-solid fa-filter me-2"></i>Filtres
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">

        <!-- Sorting Options -->
        <div class="filter-widget sorting-widget">
            <h3 class="filter-title">
                <i class="fa-solid fa-arrow-down-wide-short me-2"></i>Trier par
            </h3>
            <div class="filter-content">
                <form class="woocommerce-ordering" method="get">
                    <select name="orderby" class="orderby" aria-label="Trier par">
                        <?php
                        $catalog_orderby_options = array(
                            'menu_order' => 'Par défaut',
                            'popularity' => 'Popularité',
                            'rating' => 'Note moyenne',
                            'date' => 'Les plus récents',
                            'price' => 'Prix : croissant',
                            'price-desc' => 'Prix : décroissant',
                        );

                        $orderby = isset($_GET['orderby']) ? wc_clean($_GET['orderby']) : wc_get_loop_prop('orderby');

                        foreach ($catalog_orderby_options as $id => $name) {
                            echo '<option value="' . esc_attr($id) . '" ' . selected($orderby, $id, false) . '>' . esc_html($name) . '</option>';
                        }
                        ?>
                    </select>
                    <input type="hidden" name="paged" value="1" />
                    <?php wc_query_string_form_fields(null, array('orderby', 'submit', 'paged', 'product-page')); ?>
                </form>
            </div>
        </div>

        <!-- Brands -->
        <div class="filter-widget categories-widget">
            <h3 class="filter-title">
                <i class="fa-solid fa-tags me-2"></i>Marques
            </h3>
            <div class="filter-content categories-scrollable">
                <?php
                $brands = get_terms(array(
                    'taxonomy' => 'product_brand',
                    'hide_empty' => true,
                    'orderby' => 'name',
                ));

                if (!empty($brands) && !is_wp_error($brands)) {
                    echo '<ul class="product-categories">';
                    foreach ($brands as $brand) {
                        $is_current = ($brand->term_id === $current_brand->term_id);
                        $classes = 'cat-item cat-item-' . $brand->term_id;
                        if ($is_current) {
                            $classes .= ' current-cat';
                        }

                        echo '<li class="' . esc_attr($classes) . '">';
                        echo '<div class="cat-item-wrapper">';
                        echo '<a href="' . esc_url(get_term_link($brand)) . '">';
                        echo esc_html($brand->name);
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

<script>
    // Scroll to full description (uses CSS scroll-margin-top for offset)
    function scrollToFullDescription() {
        const fullDescription = document.getElementById('full-description');
        if (fullDescription) {
            fullDescription.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    }

    // Auto-submit sorting form on change
    document.addEventListener('DOMContentLoaded', function () {
        const sortingSelects = document.querySelectorAll('.woocommerce-ordering select.orderby');
        sortingSelects.forEach(function(sortingSelect) {
            sortingSelect.addEventListener('change', function () {
                this.closest('form').submit();
            });
        });

        // Close offcanvas when clicking on brand links
        const offcanvasElement = document.getElementById('filterOffcanvas');
        if (offcanvasElement) {
            offcanvasElement.addEventListener('click', function (e) {
                const clickedLink = e.target.closest('.product-categories a');

                if (clickedLink) {
                    // Update the active state immediately for visual feedback
                    const allCatItems = offcanvasElement.querySelectorAll('.product-categories .cat-item');
                    allCatItems.forEach(item => {
                        item.classList.remove('current-cat');
                    });

                    // Add current-cat class to the clicked brand
                    const clickedCatItem = clickedLink.closest('.cat-item');
                    if (clickedCatItem) {
                        clickedCatItem.classList.add('current-cat');
                    }

                    // Close offcanvas
                    const offcanvasInstance = bootstrap.Offcanvas.getInstance(offcanvasElement);
                    if (offcanvasInstance) {
                        offcanvasInstance.hide();
                    }
                }
            });
        }

        // Update active state for sidebar brands (desktop)
        document.addEventListener('click', function (e) {
            const clickedLink = e.target.closest('.shop-sidebar .product-categories a');

            if (clickedLink) {
                const sidebar = document.querySelector('.shop-sidebar');
                if (sidebar) {
                    const allCatItems = sidebar.querySelectorAll('.product-categories .cat-item');
                    allCatItems.forEach(item => {
                        item.classList.remove('current-cat');
                    });

                    const clickedCatItem = clickedLink.closest('.cat-item');
                    if (clickedCatItem) {
                        clickedCatItem.classList.add('current-cat');
                    }
                }
            }
        });
    });
</script>

<?php
get_footer();
