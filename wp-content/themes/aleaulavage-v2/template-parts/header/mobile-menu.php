<?php
/**
 * Template part for displaying the mobile menu offcanvas
 */
?>
<div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvas-navbar">
    <div class="offcanvas-header bg-light">
        <span class="h5 mb-0">Menu</span>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body p-0">
        <div class="modern-mobile-menu">

            <!-- Categories Section -->
            <div class="menu-section">
                <p class="menu-section-title">Nos Catégories</p>
                <div class="menu-grid">
                    <?php
                    $cat_args = array(
                        'orderby' => 'menu_order',
                        'order' => 'ASC',
                        'hide_empty' => true,
                        'parent' => 0,
                        'exclude' => [16]
                    );

                    $product_categories = get_terms('product_cat', $cat_args);

                    // "All Categories" Link
                    echo '<a href="' . esc_url(home_url('boutique/')) . '" class="menu-card">';
                    echo '<div class="menu-card-icon"><i class="fa-solid fa-grid-2"></i></div>';
                    echo '<div class="menu-card-title">Toutes les catégories</div>';
                    echo '<div class="menu-card-subtitle">Voir tout</div>';
                    echo '</a>';

                    if (!empty($product_categories) && !is_wp_error($product_categories)) {
                        foreach ($product_categories as $index => $category) {
                            $thumbnail_id = get_term_meta($category->term_id, 'thumbnail_id', true);
                            $category_link = get_term_link($category);

                            echo '<a href="' . esc_url($category_link) . '" class="menu-card">';

                            if ($thumbnail_id) {
                                echo '<img src="' . esc_url(wp_get_attachment_url($thumbnail_id)) . '" alt="' . esc_attr($category->name) . '">';
                            } else {
                                // Fallback icons
                                $icons = [
                                    'fa-solid fa-microchip',
                                    'fa-solid fa-wrench',
                                    'fa-solid fa-cog',
                                    'fa-solid fa-bolt',
                                    'fa-solid fa-tools',
                                    'fa-solid fa-plug',
                                    'fa-solid fa-gear',
                                    'fa-solid fa-hammer'
                                ];
                                $icon = $icons[$index % count($icons)];
                                echo '<div class="menu-card-icon"><i class="' . $icon . '"></i></div>';
                            }

                            echo '<div class="menu-card-title">' . esc_html($category->name) . '</div>';
                            echo '</a>';
                        }
                    }
                    ?>
                </div>
            </div>

            <!-- Navigation Section -->
            <div class="menu-section">
                <p class="menu-section-title">Navigation</p>
                <div class="menu-grid">
                    <a href="<?php echo esc_url(home_url('mon-compte/')); ?>" class="menu-card">
                        <div class="menu-card-icon"><i class="fa-solid fa-user"></i></div>
                        <div class="menu-card-title">Mon Compte</div>
                        <div class="menu-card-subtitle">Profil & commandes</div>
                    </a>

                    <a href="<?php echo esc_url(home_url('favoris/')); ?>" class="menu-card">
                        <div class="menu-card-icon"><i class="fa-solid fa-heart" style="color: #f1bb69;"></i></div>
                        <div class="menu-card-title">Mes Favoris</div>
                        <div class="menu-card-subtitle">Produits préférés</div>
                    </a>

                    <a href="<?php echo esc_url(home_url('boutique/')); ?>" class="menu-card">
                        <div class="menu-card-icon"><i class="fa-solid fa-store"></i></div>
                        <div class="menu-card-title">Boutique</div>
                        <div class="menu-card-subtitle">Tous nos produits</div>
                    </a>

                    <a href="<?php echo esc_url(home_url('catalogue-2025')); ?>" class="menu-card">
                        <div class="menu-card-icon"><i class="fa-solid fa-book"></i></div>
                        <div class="menu-card-title">Catalogue 2025</div>
                        <div class="menu-card-subtitle">Voir le catalogue</div>
                    </a>

                    <a href="<?php echo esc_url(home_url('contact/')); ?>" class="menu-card">
                        <div class="menu-card-icon"><i class="fa-solid fa-envelope"></i></div>
                        <div class="menu-card-title">Contact</div>
                        <div class="menu-card-subtitle">Nous contacter</div>
                    </a>

                    <a href="<?php echo esc_url(home_url('service/')); ?>" class="menu-card">
                        <div class="menu-card-icon"><i class="fa-solid fa-handshake"></i></div>
                        <div class="menu-card-title">Services</div>
                        <div class="menu-card-subtitle">Nos services</div>
                    </a>
                </div>
            </div>

        </div>
    </div>
</div>