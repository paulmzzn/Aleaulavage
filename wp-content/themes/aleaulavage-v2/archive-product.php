<?php
/**
 * The Template for displaying product archives (shop page)
 * Modern & Minimalist Design - Optimized for E-commerce
 */

defined('ABSPATH') || exit;

get_header();
?>

<main id="primary" class="site-main shop-page">

    <?php
    // Get current category if on category page
    $current_category = is_product_category() ? get_queried_object() : null;
    $is_search = is_search();
    ?>

    <!-- Shop Hero Section -->
    <section class="shop-hero">
        <div class="container">
            <div class="shop-hero-content">

                <!-- Back Button -->
                <div class="mb-3">
                    <a href="<?php echo esc_url(home_url('/')); ?>" class="btn-back-home">
                        <i class="fa-solid fa-house me-2"></i>Retour à l'accueil
                    </a>
                </div>

                <?php if ($is_search): ?>
                    <h1 class="shop-title">
                        Résultats de recherche
                        <?php if (have_posts()): ?>
                            <span class="text-primary">"<?php echo get_search_query(); ?>"</span>
                        <?php endif; ?>
                    </h1>
                    <?php if (have_posts()): ?>
                        <p class="shop-subtitle"><?php echo $wp_query->found_posts; ?> produit(s) trouvé(s)</p>
                    <?php endif; ?>
                <?php elseif ($current_category): ?>
                    <h1 class="shop-title"><?php echo esc_html($current_category->name); ?></h1>
                    <?php if ($current_category->description): ?>
                        <div class="category-description-short">
                            <?php
                            $description = strip_tags($current_category->description);
                            $short_desc = wp_trim_words($description, 20, '...');
                            echo '<p class="shop-subtitle">' . esc_html($short_desc) . '</p>';
                            if (str_word_count($description) > 20): ?>
                                <button class="read-more-btn" onclick="scrollToFullDescription()">
                                    <i class="fa-solid fa-circle-down me-2"></i>Lire la suite
                                </button>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <h1 class="shop-title">Notre <span class="text-primary">Boutique</span></h1>
                    <p class="shop-subtitle">Découvrez notre sélection de produits professionnels pour stations de lavage
                    </p>
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

                        <!-- Categories Widget -->
                        <div class="filter-widget categories-widget">
                            <h3 class="filter-title">
                                <i class="fa-solid fa-list me-2"></i>Catégories
                            </h3>
                            <div class="filter-content categories-scrollable">
                                <?php
                                // Get only parent categories
                                $parent_categories = get_terms(array(
                                    'taxonomy' => 'product_cat',
                                    'parent' => 0,
                                    'hide_empty' => true,
                                    'orderby' => 'name',
                                ));

                                if (!empty($parent_categories) && !is_wp_error($parent_categories)) {
                                    echo '<ul class="product-categories">';
                                    foreach ($parent_categories as $parent_cat) {
                                        $current_cat_id = is_product_category() ? get_queried_object_id() : 0;
                                        $is_current = ($parent_cat->term_id === $current_cat_id);

                                        // Get child categories
                                        $child_categories = get_terms(array(
                                            'taxonomy' => 'product_cat',
                                            'parent' => $parent_cat->term_id,
                                            'hide_empty' => true,
                                            'orderby' => 'name',
                                        ));

                                        $has_children = !empty($child_categories) && !is_wp_error($child_categories);

                                        // Check if any child is current
                                        $has_current_child = false;
                                        if ($has_children) {
                                            foreach ($child_categories as $child_cat) {
                                                if ($child_cat->term_id === $current_cat_id) {
                                                    $has_current_child = true;
                                                    break;
                                                }
                                            }
                                        }

                                        $classes = 'cat-item cat-item-' . $parent_cat->term_id;
                                        if ($is_current)
                                            $classes .= ' current-cat';
                                        if ($has_children)
                                            $classes .= ' has-children';
                                        if ($has_current_child)
                                            $classes .= ' has-current-child';
                                        if ($is_current || $has_current_child)
                                            $classes .= ' open';

                                        echo '<li class="' . esc_attr($classes) . '">';

                                        echo '<div class="cat-item-wrapper">';
                                        echo '<a href="' . esc_url(get_term_link($parent_cat)) . '">';
                                        echo esc_html($parent_cat->name);
                                        echo '</a>';

                                        if ($has_children) {
                                            echo '<button class="toggle-children" aria-label="Toggle sous-catégories"><i class="fa-solid fa-chevron-down"></i></button>';
                                        }
                                        echo '</div>';

                                        if ($has_children) {
                                            echo '<ul class="children">';
                                            foreach ($child_categories as $child_cat) {
                                                $is_current_child = ($child_cat->term_id === $current_cat_id);
                                                echo '<li class="cat-item cat-item-' . esc_attr($child_cat->term_id) . ($is_current_child ? ' current-cat' : '') . '">';
                                                echo '<a href="' . esc_url(get_term_link($child_cat)) . '">';
                                                echo esc_html($child_cat->name);
                                                echo '</a>';
                                                echo '</li>';
                                            }
                                            echo '</ul>';
                                        }

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
                            <i class="fa-solid fa-filter me-2"></i>Filtres & Catégories
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
                            <p>Essayez de modifier vos filtres ou explorez nos catégories</p>
                            <a href="<?php echo esc_url(home_url('/boutique/')); ?>" class="btn btn-primary mt-3">
                                <i class="fa-solid fa-store me-2"></i>Voir tous les produits
                            </a>
                        </div>

                    <?php endif; ?>

                </div>

            </div>
        </div>
    </section>

    <!-- Full Category Description (SEO) -->
    <?php if ($current_category && $current_category->description): ?>
        <section class="category-description-full" id="full-description">
            <div class="container">
                <div class="description-content">
                    <h2 class="mb-4">À propos de <?php echo esc_html($current_category->name); ?></h2>
                    <?php echo wp_kses_post(wpautop(wc_format_content($current_category->description))); ?>
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

        <!-- Categories -->
        <div class="filter-widget categories-widget">
            <h3 class="filter-title">
                <i class="fa-solid fa-list me-2"></i>Catégories
            </h3>
            <div class="filter-content categories-scrollable">
                <?php
                // Get only parent categories
                $parent_categories = get_terms(array(
                    'taxonomy' => 'product_cat',
                    'parent' => 0,
                    'hide_empty' => true,
                    'orderby' => 'name',
                ));

                if (!empty($parent_categories) && !is_wp_error($parent_categories)) {
                    echo '<ul class="product-categories">';
                    foreach ($parent_categories as $parent_cat) {
                        $current_cat_id = is_product_category() ? get_queried_object_id() : 0;
                        $is_current = ($parent_cat->term_id === $current_cat_id);

                        // Get child categories
                        $child_categories = get_terms(array(
                            'taxonomy' => 'product_cat',
                            'parent' => $parent_cat->term_id,
                            'hide_empty' => true,
                            'orderby' => 'name',
                        ));

                        $has_children = !empty($child_categories) && !is_wp_error($child_categories);

                        // Check if any child is current
                        $has_current_child = false;
                        if ($has_children) {
                            foreach ($child_categories as $child_cat) {
                                if ($child_cat->term_id === $current_cat_id) {
                                    $has_current_child = true;
                                    break;
                                }
                            }
                        }

                        $classes = 'cat-item cat-item-' . $parent_cat->term_id;
                        if ($is_current)
                            $classes .= ' current-cat';
                        if ($has_children)
                            $classes .= ' has-children';
                        if ($has_current_child)
                            $classes .= ' has-current-child';
                        if ($is_current || $has_current_child)
                            $classes .= ' open';

                        echo '<li class="' . esc_attr($classes) . '">';

                        echo '<div class="cat-item-wrapper">';
                        echo '<a href="' . esc_url(get_term_link($parent_cat)) . '">';
                        echo esc_html($parent_cat->name);
                        echo '</a>';

                        if ($has_children) {
                            echo '<button class="toggle-children" aria-label="Toggle sous-catégories"><i class="fa-solid fa-chevron-down"></i></button>';
                        }
                        echo '</div>';

                        if ($has_children) {
                            echo '<ul class="children">';
                            foreach ($child_categories as $child_cat) {
                                $is_current_child = ($child_cat->term_id === $current_cat_id);
                                echo '<li class="cat-item cat-item-' . esc_attr($child_cat->term_id) . ($is_current_child ? ' current-cat' : '') . '">';
                                echo '<a href="' . esc_url(get_term_link($child_cat)) . '">';
                                echo esc_html($child_cat->name);
                                echo '</a>';
                                echo '</li>';
                            }
                            echo '</ul>';
                        }

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

    // Category accordion toggles and interactions
    document.addEventListener('DOMContentLoaded', function () {
        console.log('Script loaded');

        // Category accordion toggles - use event delegation for better compatibility
        document.addEventListener('click', function (e) {
            // Check if clicked element is a toggle button
            if (e.target.closest('.toggle-children')) {
                e.preventDefault();
                e.stopPropagation();

                const button = e.target.closest('.toggle-children');
                const parentLi = button.closest('.cat-item');

                if (parentLi) {
                    const isOpen = parentLi.classList.contains('open');
                    console.log('Toggle clicked, isOpen:', isOpen);

                    // Toggle open class
                    if (isOpen) {
                        parentLi.classList.remove('open');
                    } else {
                        parentLi.classList.add('open');
                    }
                }
            }
        });

        // Close offcanvas when clicking on category links and update active state
        const offcanvasElement = document.getElementById('filterOffcanvas');
        if (offcanvasElement) {
            offcanvasElement.addEventListener('click', function (e) {
                const clickedLink = e.target.closest('.product-categories a');

                // Check if clicked on a category link (but not the toggle button)
                if (clickedLink && !e.target.closest('.toggle-children')) {
                    // Update the active state immediately for visual feedback
                    const allCatItems = offcanvasElement.querySelectorAll('.product-categories .cat-item');
                    allCatItems.forEach(item => {
                        item.classList.remove('current-cat');
                    });

                    // Add current-cat class to the clicked category
                    const clickedCatItem = clickedLink.closest('.cat-item');
                    if (clickedCatItem) {
                        clickedCatItem.classList.add('current-cat');
                    }

                    // Close offcanvas
                    const offcanvasInstance = bootstrap.Offcanvas.getInstance(offcanvasElement);
                    if (offcanvasInstance) {
                        offcanvasInstance.hide();
                    }

                    // Allow the default link behavior to navigate
                }
            });
        }

        // Also update active state for sidebar categories (desktop)
        document.addEventListener('click', function (e) {
            const clickedLink = e.target.closest('.shop-sidebar .product-categories a');

            if (clickedLink && !e.target.closest('.toggle-children')) {
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
