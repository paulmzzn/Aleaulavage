<?php
/**
 * Cart Page
 *
 * @package WooCommerce\Templates
 * @version 7.9.0
 */

defined('ABSPATH') || exit;

do_action('woocommerce_before_cart');
?>

<div class="al-cart-wrapper">
    <form class="woocommerce-cart-form" action="<?php echo esc_url(wc_get_cart_url()); ?>" method="post">
        <?php do_action('woocommerce_before_cart_table'); ?>

        <?php if (!WC()->cart->is_empty()) : ?>
            <?php
            // Calcul pour la barre de progression
            $free_shipping_min = 550;
            if (class_exists('Aleaulavage_Customer_Types')) {
                $customer_type = Aleaulavage_Customer_Types::get_current_customer_type();
                $franco = Aleaulavage_Customer_Types::get_franco_de_port($customer_type);
                if ($franco !== null && $franco > 0) {
                    $free_shipping_min = $franco;
                }
            }

            // Calculer le montant total HT du panier (hors livraison)
            // Utilise get_cart_contents_total() pour prendre en compte les remises (prix après coupon).
            $current_total = WC()->cart->get_cart_contents_total();

            $percent = 0;
            $remaining = 0;

            if ($current_total < $free_shipping_min) {
                $percent = ($current_total / $free_shipping_min) * 100;
                $remaining = $free_shipping_min - $current_total;
            } else {
                $percent = 100;
            }
            ?>

            <style>
            .al-free-shipping-bar {
                background: #fff;
                padding: 20px;
                border-radius: 8px;
                margin-bottom: 20px;
                border: 1px solid #e0e0e0 !important;
            }
            .al-fs-text {
                text-align: center;
                margin-bottom: 10px;
                font-size: 1.1em;
                color: #333;
            }
            .al-fs-progress {
                height: 10px;
                background: #f0f0f0;
                border-radius: 5px;
                overflow: hidden;
            }
            .al-fs-progress-bar {
                height: 100%;
                background: #5899E2;
                border-radius: 5px;
                transition: width 0.5s ease;
            }
            .al-free-shipping-bar.success .al-fs-text {
                color: #5899E2;
                font-weight: bold;
            }
            .al-free-shipping-bar.success .al-fs-progress-bar {
                background: #5899E2;
            }
            .al-free-shipping-bar.success {
                border-color: #5899E2 !important;
            }
            </style>

            <?php if ($current_total < $free_shipping_min) : ?>
                <div class="al-free-shipping-bar">
                    <div class="al-fs-text">
                        <?php $remaining_html = wc_price($remaining);
                              $remaining_html = str_replace('<span class="woocommerce-Price-amount amount">', '<span class="woocommerce-Price-amount amount" style="color:#5899E2 !important;">', $remaining_html);
                        ?>
                        Plus que <strong><?php echo $remaining_html; ?></strong> HT pour la livraison gratuite
                    </div>
                    <div class="al-fs-progress">
                        <div class="al-fs-progress-bar" style="width: <?php echo esc_attr($percent); ?>%; background: #5899E2 !important; border-color: #5899E2 !important;"></div>
                    </div>
                </div>
            <?php else : ?>
                <div class="al-free-shipping-bar success">
                    <div class="al-fs-text" style="color:#5899E2 !important;">
                        Vous bénéficiez de la livraison gratuite !
                    </div>
                    <div class="al-fs-progress">
                        <div class="al-fs-progress-bar" style="width: 100%; background: #5899E2 !important; border-color: #5899E2 !important;"></div>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <div class="al-cart-main">
            <!-- Cart Items Section -->
            <div class="al-cart-items-section">
                <div class="al-cart-header">
                    <h2 class="al-cart-header-title">Mon Panier <span class="al-cart-items-count">(<?php echo WC()->cart->get_cart_contents_count(); ?> article<?php echo WC()->cart->get_cart_contents_count() > 1 ? 's' : ''; ?>)</span></h2>
                    <?php if (!WC()->cart->is_empty()) : ?>
                        <a href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>" class="al-cart-continue-btn">
                            <i class="fa-solid fa-arrow-left"></i>Continuer mes achats
                        </a>
                    <?php endif; ?>
                </div>

                <?php if (WC()->cart->is_empty()) : ?>
                    <div class="empty-cart-message text-center py-5">
                        <i class="fa-solid fa-basket-shopping text-muted mb-4" style="font-size: 4rem; opacity: 0.3;"></i>
                        <h3 class="mb-3">Votre panier est vide</h3>
                        <p class="text-muted mb-4">Découvrez nos produits et ajoutez-en à votre panier</p>
                        <a href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>" class="btn btn-primary btn-lg">
                            <i class="fa-solid fa-store me-2"></i>Découvrir la boutique
                        </a>
                    </div>
                <?php else : ?>
                    <div class="al-cart-items-list">
                        <?php
                        foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
                            $_product = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key);
                            $product_id = apply_filters('woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key);

                            if ($_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters('woocommerce_cart_item_visible', true, $cart_item, $cart_item_key)) {
                                $product_permalink = apply_filters('woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink($cart_item) : '', $cart_item, $cart_item_key);
                                ?>
                                <div class="al-cart-item" data-key="<?php echo esc_attr($cart_item_key); ?>">
                                    <!-- Product Image -->
                                    <div class="al-cart-item-image">
                                        <?php
                                        $thumbnail = apply_filters('woocommerce_cart_item_thumbnail', $_product->get_image(), $cart_item, $cart_item_key);
                                        if (!$product_permalink) {
                                            echo $thumbnail;
                                        } else {
                                            printf('<a href="%s">%s</a>', esc_url($product_permalink), $thumbnail);
                                        }
                                        ?>
                                    </div>

                                    <!-- Product Details -->
                                    <div class="al-cart-item-details">
                                        <h4 class="al-cart-item-name">
                                            <?php
                                            if (!$product_permalink) {
                                                echo wp_kses_post(apply_filters('woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key) . '&nbsp;');
                                            } else {
                                                echo wp_kses_post(apply_filters('woocommerce_cart_item_name', sprintf('<a href="%s">%s</a>', esc_url($product_permalink), $_product->get_name()), $cart_item, $cart_item_key));
                                            }

                                            // Badge orange pour backorder à droite du titre
                                            if ($_product->backorders_require_notification() && $_product->is_on_backorder($cart_item['quantity'])) {
                                                echo '<span class="al-backorder-badge"><i class="fa-solid fa-clock"></i></span>';
                                            }
                                            ?>
                                        </h4>
                                        <?php
                                        // Meta data
                                        echo wc_get_formatted_cart_item_data($cart_item);
                                        ?>

                                        <!-- Quantity and Price (under the name) -->
                                        <div class="al-cart-item-meta">
                                            <!-- Quantity Selector (Pill Style like offcanvas) -->
                                            <div class="al-cart-qty-wrapper">
                                                <span class="al-cart-qty-label">Quantité</span>
                                                <div class="al-cart-qty-selector">
                                                    <button type="button" class="al-qty-btn al-qty-decrease"
                                                            data-cart-item-key="<?php echo esc_attr($cart_item_key); ?>"
                                                            <?php echo ($cart_item['quantity'] <= 1) ? 'disabled' : ''; ?>>
                                                        <i class="fa-solid fa-minus"></i>
                                                    </button>
                                                    <input type="number"
                                                           class="al-qty-input"
                                                           name="cart[<?php echo esc_attr($cart_item_key); ?>][qty]"
                                                           value="<?php echo esc_attr($cart_item['quantity']); ?>"
                                                           min="1"
                                                           <?php
                                                           $max_qty = $_product->get_max_purchase_quantity();
                                                           if ($max_qty > 0) {
                                                               echo 'max="' . esc_attr($max_qty) . '"';
                                                           }
                                                           ?>
                                                           data-cart-item-key="<?php echo esc_attr($cart_item_key); ?>"
                                                           step="1">
                                                    <button type="button" class="al-qty-btn al-qty-increase"
                                                            data-cart-item-key="<?php echo esc_attr($cart_item_key); ?>">
                                                        <i class="fa-solid fa-plus"></i>
                                                    </button>
                                                </div>
                                            </div>

                                            <!-- × Prix unitaire (mobile) -->
                                            <div class="al-cart-unit-price-mobile">
                                                <span class="al-cart-times">×</span>
                                                <span class="al-cart-unit-price-value">
                                                    <?php echo apply_filters('woocommerce_cart_item_price', WC()->cart->get_product_price($_product), $cart_item, $cart_item_key); ?>
                                                </span>
                                            </div>

                                            <!-- Prix total (mobile) -->
                                            <div class="al-cart-total-mobile">
                                                <?php echo apply_filters('woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal($_product, $cart_item['quantity']), $cart_item, $cart_item_key); ?>
                                            </div>

                                            <!-- Price (desktop) -->
                                            <div class="al-cart-price-wrapper">
                                                <span class="al-cart-price-label">Prix unitaire</span>
                                                <span class="al-cart-price-value">
                                                    <?php echo apply_filters('woocommerce_cart_item_price', WC()->cart->get_product_price($_product), $cart_item, $cart_item_key); ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Subtotal (desktop only) -->
                                    <div class="al-cart-subtotal-wrapper">
                                        <span class="al-cart-subtotal-label">Sous-total</span>
                                        <span class="al-cart-subtotal-value">
                                            <?php echo apply_filters('woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal($_product, $cart_item['quantity']), $cart_item, $cart_item_key); ?>
                                        </span>
                                    </div>

                                    <!-- Remove Button -->
                                    <button type="button" class="al-cart-remove-btn" data-cart_item_key="<?php echo esc_attr($cart_item_key); ?>" title="Supprimer">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </div>
                                <?php
                            }
                        }
                        ?>
                    </div>

                    <!-- Cart Actions -->
                    <div class="al-cart-actions">
                        <!-- Coupon -->
                        <?php if (wc_coupons_enabled()) : ?>
                            <div class="al-cart-coupon">
                                <input type="text" name="coupon_code" class="al-cart-coupon-input" id="coupon_code" value="" placeholder="Code promo" />
                                <button type="submit" class="al-cart-coupon-btn" name="apply_coupon" value="<?php esc_attr_e('Apply coupon', 'woocommerce'); ?>">
                                    Appliquer
                                </button>
                            </div>
                        <?php endif; ?>

                        <!-- Update Cart Button removed: cart updates handled via AJAX -->
                    </div>
                <?php endif; ?>

                <?php do_action('woocommerce_after_cart_table'); ?>
            </div>

            <?php if (!WC()->cart->is_empty()) : ?>
                <!-- Cart Totals Section -->
                <div class="al-cart-sidebar">
                    <?php do_action('woocommerce_before_cart_collaterals'); ?>

                    <div class="al-cart-totals-card">
                        <!-- Titre -->
                        <h3 class="al-cart-totals-title">Résumé de la commande</h3>

                        <!-- Contenu -->
                        <div class="al-cart-totals-content">
                            <?php
                            // Récupérer les données
                            $cart_subtotal = WC()->cart->get_subtotal();
                            $cart_tax = WC()->cart->get_total_tax();
                            $shipping_total = WC()->cart->get_shipping_total();
                            $cart_total = WC()->cart->get_total('');

                            // Franco de port
                            $free_shipping_min = 550;
                            if (class_exists('Aleaulavage_Customer_Types')) {
                                $customer_type = Aleaulavage_Customer_Types::get_current_customer_type();
                                $franco = Aleaulavage_Customer_Types::get_franco_de_port($customer_type);
                                if ($franco !== null && $franco > 0) {
                                    $free_shipping_min = $franco;
                                }
                            }
                            ?>

                            <!-- Sous-total -->
                            <div class="al-cart-line">
                                <span class="al-cart-label">Sous-total</span>
                                <span class="al-cart-value">
                                    <?php $subtotal_html = wc_price($cart_subtotal);
                                    $subtotal_html = str_replace('<span class="woocommerce-Price-amount amount">', '<span class="woocommerce-Price-amount amount" style="color:#1a1a1a !important;">', $subtotal_html . ' HT');
                                    echo $subtotal_html; ?>
                                </span>
                            </div>

                            <!-- Livraison -->
                            <div class="al-cart-line">
                                <span class="al-cart-label">Livraison</span>
                                <span class="al-cart-value">
                                    <?php if ($shipping_total > 0) : ?>
                                        <?php $ship_html = wc_price($shipping_total);
                                        $ship_html = str_replace('<span class="woocommerce-Price-amount amount">', '<span class="woocommerce-Price-amount amount" style="color:#1a1a1a !important;">', $ship_html);
                                        echo $ship_html; ?>
                                    <?php else : ?>
                                        Offerte
                                    <?php endif; ?>
                                </span>
                            </div>

                            <!-- TVA -->
                            <?php if ($cart_tax > 0) : ?>
                                <div class="al-cart-line">
                                    <span class="al-cart-label">TVA (20%)</span>
                                    <span class="al-cart-value"><?php $tax_html = wc_price($cart_tax);
                                    $tax_html = str_replace('<span class="woocommerce-Price-amount amount">', '<span class="woocommerce-Price-amount amount" style="color:#1a1a1a !important;">', $tax_html);
                                    echo $tax_html; ?></span>
                                </div>
                            <?php endif; ?>

                            <!-- Total -->
                            <div class="al-cart-line-total">
                                <span class="al-cart-label-total">Total TTC</span>
                                <span class="al-cart-value-total"><?php $total_html = wc_price($cart_total);
                                $total_html = str_replace('<span class="woocommerce-Price-amount amount">', '<span class="woocommerce-Price-amount amount" style="color:#1a1a1a !important; font-weight:700;">', $total_html);
                                echo $total_html; ?></span>
                            </div>
                        </div>

                        <!-- Bouton Commander -->
                        <div class="al-cart-checkout-zone">
                            <a href="<?php echo esc_url(wc_get_checkout_url()); ?>" class="al-cart-checkout-btn">
                                Valider la commande
                            </a>
                        </div>

                        <!-- Shipping info -->
                        <div class="al-cart-shipping-zone">
                            <div class="al-shipping-header">Informations de livraison</div>
                            <div class="al-shipping-content">
                                <div class="al-shipping-row">
                                    <span class="al-shipping-label">Mode</span>
                                    <span class="al-shipping-value">Livraison à domicile</span>
                                </div>
                                <div class="al-shipping-row">
                                    <span class="al-shipping-label">Délai</span>
                                    <span class="al-shipping-value">1 à 4 jours ouvrables</span>
                                </div>
                                <div class="al-shipping-row al-shipping-price-row">
                                    <span class="al-shipping-label">Tarif</span>
                                    <div class="al-shipping-price-block">
                                        <span class="al-shipping-price"><?php echo wc_price(19); ?></span>
                                        <?php if (isset($free_shipping_min) && $free_shipping_min > 0) : ?>
                                            <span class="al-shipping-free-info">Offerte dès <?php echo wc_price($free_shipping_min); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <?php
                                // Vérifier si un produit est en backorder
                                $has_backorder = false;
                                foreach (WC()->cart->get_cart() as $cart_item) {
                                    $_product = $cart_item['data'];
                                    if ($_product->backorders_require_notification() && $_product->is_on_backorder($cart_item['quantity'])) {
                                        $has_backorder = true;
                                        break;
                                    }
                                }

                                if ($has_backorder) :
                                ?>
                                <div class="al-shipping-row al-backorder-warning">
                                    <div class="al-backorder-warning-content">
                                        <i class="fa-solid fa-clock"></i>
                                        <div>
                                            <strong>Produit en réapprovisionnement</strong>
                                            <p>Un ou plusieurs produits sont actuellement en cours de réapprovisionnement. Le délai de livraison peut être légèrement prolongé.</p>
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Cacher le contenu WooCommerce par défaut -->
                    <div class="cart-totals-card" style="display: none;">
                        <h3 class="cart-totals-title">Résumé de la commande</h3>
                        <?php woocommerce_cart_totals(); ?>
                        <div class="proceed-to-checkout mt-4">
                            <?php do_action('woocommerce_proceed_to_checkout'); ?>
                        </div>
                        <div class="trust-badges mt-4 pt-4 border-top">
                            <div class="trust-badge-item mb-3">
                                
                                <span class="small">Paiement 100% sécurisé</span>
                            </div>
                            <div class="trust-badge-item mb-3">
                                
                                <span class="small">Livraison rapide</span>
                            </div>
                            <div class="trust-badge-item">
                                
                                <span class="small">Service client disponible</span>
                            </div>
                        </div>
                    </div>

                    <?php do_action('woocommerce_after_cart'); ?>
                </div>
            <?php endif; ?>
        </div>

        <?php wp_nonce_field('woocommerce-cart', 'woocommerce-cart-nonce'); ?>
    </form>
</div>

<script>
jQuery(function($) {
    // Remove item button - Delegated
    $(document).on('click', '.al-cart-remove-btn', function(e) {
        e.preventDefault();
        var $button = $(this);
        var cartItemKey = $button.data('cart_item_key');

        if (confirm('Êtes-vous sûr de vouloir supprimer cet article ?')) {
            $button.closest('.al-cart-item').css('opacity', '0.5');

            // Build remove URL
            var removeUrl = wc_cart_params.wc_ajax_url.toString().replace('%%endpoint%%', 'remove_from_cart');

            $.post(removeUrl, {
                cart_item_key: cartItemKey
            }, function() {
                location.reload();
            });
        }
    });

    // Quantity selector buttons
    $(document).on('click', '.al-qty-decrease, .al-qty-increase', function(e) {
        e.preventDefault();
        var $button = $(this);
        var $selector = $button.closest('.al-cart-qty-selector');
        var $input = $selector.find('.al-qty-input');
        var currentQty = parseInt($input.val()) || 1;
        var maxQty = parseInt($input.attr('max')) || 999;
        var newQty = currentQty;

        if ($button.hasClass('al-qty-decrease')) {
            newQty = Math.max(1, currentQty - 1);
        } else {
            newQty = Math.min(maxQty, currentQty + 1);
        }

        if (newQty !== currentQty) {
            $input.val(newQty).trigger('change');
        }

        // Update button states
        $selector.find('.al-qty-decrease').prop('disabled', newQty <= 1);
    });

    // Auto-update cart when quantity changes
    var updateCartTimer;
    $(document).on('change', '.al-qty-input', function() {
        clearTimeout(updateCartTimer);
        var $input = $(this);
        var $selector = $input.closest('.al-cart-qty-selector');
        // Keep the minus button state in sync when editing manually
        var currentQty = parseInt($input.val()) || 1;
        $selector.find('.al-qty-decrease').prop('disabled', currentQty <= 1);

        updateCartTimer = setTimeout(function() {
            // Mettre à jour visuellement
            $('.al-cart-items-section, .al-cart-sidebar').css('opacity', '0.6');

            var $form = $('form.woocommerce-cart-form');
            var formData = $form.serialize();

            // Ensure update_cart is in data
            if (formData.indexOf('update_cart') === -1) {
                formData += '&update_cart=true';
            }

            $.ajax({
                type: 'POST',
                url: $form.attr('action'),
                data: formData,
                success: function(response) {
                    var $html = $(response);
                    var $newContent = $html.find('.al-cart-wrapper');

                    // Remove "Cart updated" message
                    $newContent.find('.woocommerce-message').each(function() {
                        if ($(this).text().indexOf('Panier mis à jour') !== -1 || $(this).text().indexOf('Cart updated') !== -1) {
                            $(this).remove();
                        }
                    });

                    if ($newContent.length) {
                        $('.al-cart-wrapper').replaceWith($newContent);

                        // Re-run sticky init
                        initSmartSticky();

                        // Trigger WooCommerce events
                        $(document.body).trigger('updated_cart_totals');
                        $(document.body).trigger('wc_fragments_refreshed');
                    } else {
                        location.reload();
                    }
                },
                error: function() {
                    location.reload();
                }
            });
        }, 500);
    });

    // Smart sticky - la section la plus courte suit la plus longue
    function initSmartSticky() {
        var $itemsSection = $('.al-cart-items-section');
        var $sidebar = $('.al-cart-sidebar');

        if ($itemsSection.length === 0 || $sidebar.length === 0) {
            return;
        }

        // Reset styles first to recalculate correctly
        $itemsSection.css({'position': '', 'top': '', 'align-self': ''});
        $sidebar.css({'position': '', 'top': '', 'align-self': ''});

        // Vérifier si on est sur desktop (> 992px)
        if ($(window).width() <= 992) {
            return;
        }

        var itemsHeight = $itemsSection.outerHeight();
        var sidebarHeight = $sidebar.outerHeight();

        // Offset sticky au sommet. Mettre à 0 pour éviter le décalage initial.
        // Si un header fixe recouvre le contenu lors du scroll, on pourra affiner ici.
        var topOffset = 0;

        // Déterminer quelle section doit être sticky
        if (itemsHeight > sidebarHeight) {
            // Items plus long - sidebar sticky
            $sidebar.css({
                'position': 'sticky',
                'top': topOffset + 'px',
                'align-self': 'flex-start'
            });
        } else {
            // Sidebar plus long - items sticky
            $itemsSection.css({
                'position': 'sticky',
                'top': topOffset + 'px',
                'align-self': 'flex-start'
            });
        }
    }

    // Init au chargement
    initSmartSticky();

    // Réinit au resize
    var resizeTimer;
    $(window).on('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function() {
            initSmartSticky();
        }, 250);
    });
});
</script>
