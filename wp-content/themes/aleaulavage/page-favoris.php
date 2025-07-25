<?php
/**
 * Template Name: Page Favoris
 * Description: Page pour afficher les produits favoris de l'utilisateur
 */

get_header(); ?>
<link rel="stylesheet" href="<?php echo get_stylesheet_directory_uri(); ?>/css/custom-favoris.css?v=20250725">
<div class="favoris-page">
    <div class="container">
        <div class="favoris-header">
            <nav class="breadcrumb mb-4 mt-2 py-2 small opacity-50" itemprop="breadcrumb">
                <a href="<?php echo esc_url(home_url('/')); ?>">Accueil</a> &nbsp;&gt;&nbsp; Favoris
            </nav>
            <h1 class="page-title">Favoris</h1>
        </div>

        <?php if (!is_user_logged_in()) : ?>
            <div class="empty-state">
                <i class="fa-solid fa-heart-crack empty-icon"></i>
                <h2 class="empty-title">Connexion requise</h2>
                <p class="empty-text">
                    Connectez-vous à votre compte pour accéder à votre liste de favoris<br>
                    et retrouver tous vos produits préférés.
                </p>
                <button type="button" class="btn-primary-custom" onclick="showLoginModal()">
                    <i class="fa-solid fa-sign-in-alt"></i>
                    Se connecter
                </button>
            </div>
        
        <?php else : ?>
            <?php
            $user_id = get_current_user_id();
            $wishlist = get_user_meta($user_id, 'user_wishlist', true);
            
            if (!is_array($wishlist) || empty($wishlist)) : ?>
                <div class="empty-state">
                    <i class="fa-regular fa-heart empty-icon"></i>
                    <h2 class="empty-title">Votre liste est vide</h2>
                    <p class="empty-text">
                        Vous n'avez pas encore ajouté de produits à vos favoris.<br>
                        Découvrez notre sélection et créez votre liste de souhaits !
                    </p>
                    <a href="<?php echo esc_url(home_url('boutique/')); ?>" class="btn-primary-custom">
                        <i class="fa-solid fa-shopping-bag"></i>
                        Découvrir nos produits
                    </a>
                </div>
            
            <?php else : ?>
                <?php
                // Pagination logic
                $per_page = 16;
                $total_products = is_array($wishlist) ? count($wishlist) : 0;
                $current_page = isset($_GET['wishlist_page']) ? max(1, intval($_GET['wishlist_page'])) : 1;
                $total_pages = $total_products > 0 ? ceil($total_products / $per_page) : 1;
                $start = ($current_page - 1) * $per_page;
                $end = min($start + $per_page, $total_products);
                ?>
                <p class="woocommerce-result-count" aria-hidden="false">
                    <?php if ($total_pages > 1) {
                        echo 'Affichage de ' . ($start + 1) . '–' . $end . ' sur ' . $total_products . ' résultats';
                    } else {
                        echo 'Affichage des ' . $total_products . ' résultats';
                    } ?>
                </p>
                <?php if ($total_pages > 1): ?>
                <?php endif; ?>
                <div class="row">
                    <?php
                    if (is_array($wishlist)) {
                        $paged_wishlist = array_slice($wishlist, $start, $per_page);
                        foreach ($paged_wishlist as $product_id) :
                            $product = wc_get_product($product_id);
                            if ($product) : ?>
                            <div class="col-md-6 col-lg-4 col-xxl-3 mb-4 card_container">
                                <div <?php wc_product_class('card h-100 d-flex', $product); ?>>
                                    <?php if (!$product->is_in_stock()) : ?>
                                        <div class="out-of-stock-indicator"></div>
                                    <?php else: ?>
                                        <!-- Bouton d'ajout au panier flottant -->
                                        <a href="<?php echo esc_url($product->add_to_cart_url()); ?>" 
                                           data-quantity="1" 
                                           class="add-to-cart-button add_to_cart_button ajax_add_to_cart" 
                                           data-product_id="<?php echo esc_attr($product->get_id()); ?>" 
                                           data-product_sku="<?php echo esc_attr($product->get_sku()); ?>" 
                                           aria-label="Ajouter au panier">
                                            <i class="fa-solid fa-cart-plus"></i>
                                        </a>
                                    <?php endif; ?>
                                    <a href="<?php echo get_permalink($product_id); ?>" class="woocommerce-LoopProduct-link woocommerce-loop-product__link">
                                        <?php echo $product->get_image('shop_catalog', array('class' => 'card-img-top')); ?>
                                    </a>
                                    <div class="card-body d-flex flex-column">
                                        <a href="<?php echo get_permalink($product_id); ?>" class="woocommerce-LoopProduct-link woocommerce-loop-product__link">
                                            <h2 class="woocommerce-loop-product__title">
                                                <?php echo esc_html($product->get_name()); ?>
                                            </h2>
                                        </a>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-between px-3 mb-4 cta_container">
                                        <div class="price order-1">
                                            <?php if (!$product->is_in_stock()) {
                                                echo '<span class="out-of-stock-notice">Rupture de stock</span>';
                                            } else {
                                                echo $product->get_price_html();
                                            }
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endif;
                        endforeach;
                    }
                    ?>
                </div>
                <?php if ($total_pages > 1): ?>
                <ul class="pagination justify-content-center ft-wpbs mb-4">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <?php if ($i == $current_page): ?>
                            <li class="page-item active"><span class="page-link"><span class="sr-only">Current Page </span><?php echo $i; ?></span></li>
                        <?php else: ?>
                            <li class="page-item"><a class="page-link" href="<?php echo esc_url(add_query_arg('wishlist_page', $i)); ?>"><span class="sr-only">Page </span><?php echo $i; ?></a></li>
                        <?php endif; ?>
                    <?php endfor; ?>
                </ul>
                <?php endif; ?>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<script src="<?php echo get_stylesheet_directory_uri(); ?>/js/custom-favoris.js?v=20250725" defer></script>

<?php get_footer(); ?>