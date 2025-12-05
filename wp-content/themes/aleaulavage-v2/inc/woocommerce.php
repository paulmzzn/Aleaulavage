<?php
/**
 * WooCommerce Functions
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Masquer complètement les notices WooCommerce sur les pages produit
 */
function aleaulavage_v2_hide_product_notices() {
    if (is_product()) {
        // Supprimer l'affichage des notices
        remove_action('woocommerce_before_single_product', 'woocommerce_output_all_notices', 10);
    }
}
add_action('wp', 'aleaulavage_v2_hide_product_notices');

/**
 * Activer l'ajout au panier AJAX sur les pages produit
 */
add_filter('woocommerce_add_to_cart_redirect', 'aleaulavage_v2_ajax_add_to_cart_redirect');
function aleaulavage_v2_ajax_add_to_cart_redirect($url) {
    // Retourner false pour empêcher la redirection
    if (isset($_REQUEST['add-to-cart']) && is_numeric($_REQUEST['add-to-cart'])) {
        return false;
    }
    return $url;
}

/**
 * Désactiver le message "Voir le panier" par défaut de WooCommerce
 * Car on utilise un système de notification personnalisé
 */
add_filter('wc_add_to_cart_message_html', '__return_empty_string');
add_filter('woocommerce_add_to_cart_message_html', '__return_empty_string');

/**
 * Désactiver l'ajout du bouton "Voir le panier" après l'ajout au panier
 */
add_filter('woocommerce_cart_redirect_after_error', '__return_false');
add_filter('wc_add_to_cart_message', '__return_empty_string', 10, 2);

/**
 * Support AJAX pour l'ajout au panier sur les pages produit simple
 */
add_action('wp_footer', 'aleaulavage_v2_ajax_add_to_cart_script');
function aleaulavage_v2_ajax_add_to_cart_script() {
    // Ne pas exécuter pendant les requêtes AJAX
    if (wp_doing_ajax()) {
        return;
    }

    if (!is_product()) {
        return;
    }
    ?>
    <script type="text/javascript">
    jQuery(document).ready(function($) {
        // Intercepter la soumission du formulaire d'ajout au panier
        $('form.cart').on('submit', function(e) {
            e.preventDefault();

            var form = $(this);
            var button = form.find('.single_add_to_cart_button');
            var quantity = form.find('input[name="quantity"]').val() || 1;

            // Get variation data if it's a variable product
            var variationId = form.find('input[name="variation_id"]').val() || 0;
            var variation = {};

            // Get product ID - for variable products, get from hidden field
            var productId = button.val();
            if (variationId > 0) {
                // For variable products, get the parent product ID from hidden field
                var hiddenProductId = form.find('input[name="product_id"]').val();
                if (hiddenProductId) {
                    productId = hiddenProductId;
                }
            }

            // Collect all variation attributes
            form.find('select[name^="attribute_"]').each(function() {
                var attrName = $(this).attr('name');
                var attrValue = $(this).val();
                if (attrValue) {
                    variation[attrName] = attrValue;
                }
            });

            console.log('Adding to cart - Product:', productId, 'Variation:', variationId, 'Quantity:', quantity, 'Attributes:', variation);

            // Désactiver le bouton pendant l'ajout
            button.prop('disabled', true).addClass('loading');

            var ajaxData = {
                action: 'woocommerce_ajax_add_to_cart',
                product_id: productId,
                quantity: quantity,
                nonce: '<?php echo wp_create_nonce('aleaulavage_cart_nonce'); ?>'
            };

            // Add variation data if it's a variable product
            if (variationId > 0) {
                ajaxData.variation_id = variationId;
                ajaxData.variation = variation;
            }

            $.ajax({
                type: 'POST',
                url: '<?php echo esc_url(admin_url('admin-ajax.php')); ?>',
                data: ajaxData,
                success: function(response) {
                    console.log('Response:', response);
                    console.log('Response success:', response.success);
                    console.log('Response data:', response.data);
                    console.log('Response error:', response.error);

                    if (response && response.success !== false && !response.error) {
                        // Déclencher l'événement WooCommerce
                        $(document.body).trigger('added_to_cart', [response.fragments, response.cart_hash, button]);

                        // Mettre à jour les fragments (panier)
                        if (response.fragments) {
                            $.each(response.fragments, function(key, value) {
                                $(key).replaceWith(value);
                            });
                        }
                    } else {
                        console.error('Error adding to cart:', response);
                        alert('Erreur lors de l\'ajout au panier: ' + (response.data || 'Erreur inconnue'));
                    }

                    // Réactiver le bouton
                    button.prop('disabled', false).removeClass('loading');
                },
                error: function(xhr, status, error) {
                    console.error('AJAX error:', status, error);
                    console.error('Response:', xhr.responseText);
                    button.prop('disabled', false).removeClass('loading');
                }
            });
        });
    });
    </script>
    <?php
}

/**
 * WooCommerce Cart Fragments
 * Update cart count via AJAX
 */
function aleaulavage_v2_cart_fragments($fragments)
{
    // Safety check for WooCommerce
    if (!class_exists('WooCommerce') || !WC()->cart) {
        return $fragments;
    }

    // Update Cart Count Badge
    ob_start();
    $count = WC()->cart->get_cart_contents_count();
    ?>
    <span class="cart-content">
        <?php if ($count > 0) { ?>
            <span
                class="badge bg-primary rounded-pill position-absolute top-0 start-100 translate-middle"><?php echo esc_html($count); ?></span>
        <?php } ?>
    </span>
    <?php
    $fragments['.cart-content'] = ob_get_clean();

    // Update Cart Footer (Total + Buttons)
    ob_start();
    ?>
    <div class="cart-footer bg-light p-3 border-top mt-auto">
        <?php if (WC()->cart && !WC()->cart->is_empty()): ?>
            <div class="mini-cart-backorder-notice">
                <?php
                $has_backorder_items = false;
                foreach (WC()->cart->get_cart() as $cart_item) {
                    $_product = $cart_item['data'];
                    if ($_product && $_product->is_on_backorder($cart_item['quantity'])) {
                        $has_backorder_items = true;
                        break;
                    }
                }
                if ($has_backorder_items) {
                    echo '<div style="background: #FFF8E7; border-radius: 8px; padding: 10px 12px; margin-bottom: 12px; font-size: 0.8rem; color: #8B6914; display: flex; align-items: start; gap: 8px;">';
                    echo '<i class="fa-solid fa-clock" style="color: #E9A825; font-size: 0.85rem; margin-top: 2px;" aria-hidden="true"></i>';
                    echo '<span style="line-height: 1.4;">Certains articles sont en réapprovisionnement. Délais de livraison susceptibles d\'être allongés.</span>';
                    echo '</div>';
                }
                ?>
            </div>
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="fw-bold cart-quick-label text-dark">Total HT :</span>
                <?php $quick_html = wc_price( WC()->cart->get_cart_contents_total() );
                    $quick_html = str_replace('<span class="woocommerce-Price-amount amount">', '<span class="woocommerce-Price-amount amount" style="color:#1a1a1a !important; font-weight:700;">', $quick_html);
                ?>
                <span class="fw-bold cart-quick-amount text-dark"><?php echo $quick_html; ?> <small class="text-dark">HT</small></span>
            </div>
            <button type="button" class="btn btn-outline-primary w-100 mb-2" data-bs-dismiss="offcanvas">Continuer mes
                achats</button>
            <a href="<?php echo esc_url(wc_get_cart_url()); ?>" class="btn btn-primary w-100">Commander</a>
        <?php endif; ?>
    </div>
    <?php
    $fragments['.cart-footer'] = ob_get_clean();

    return $fragments;
}
add_filter('woocommerce_add_to_cart_fragments', 'aleaulavage_v2_cart_fragments');

// Custom AJAX Add to Cart Handler
function aleaulavage_v2_ajax_add_to_cart()
{
    // Security Check
    check_ajax_referer('aleaulavage_cart_nonce', 'nonce');

    if (!isset($_POST['product_id'])) {
        wp_send_json_error('Product ID is required');
        return;
    }

    $product_id = absint($_POST['product_id']);
    $quantity = isset($_POST['quantity']) ? absint($_POST['quantity']) : 1;
    $variation_id = isset($_POST['variation_id']) ? absint($_POST['variation_id']) : 0;
    $variation = isset($_POST['variation']) ? wc_clean($_POST['variation']) : array();

    // Debug logging
    error_log('Add to cart attempt - Product: ' . $product_id . ', Variation: ' . $variation_id . ', Quantity: ' . $quantity);
    error_log('Variation attributes: ' . print_r($variation, true));

    if ($product_id && WC()->cart) {
        // Add to cart with variation support
        $cart_item_key = WC()->cart->add_to_cart($product_id, $quantity, $variation_id, $variation);

        if ($cart_item_key) {
            error_log('Successfully added to cart: ' . $cart_item_key);

            // Trigger cart calculation
            WC()->cart->calculate_totals();

            // Get WooCommerce fragments
            WC_AJAX::get_refreshed_fragments();
        } else {
            $error_msg = 'Could not add product to cart';
            error_log('Failed to add to cart - Product: ' . $product_id . ', Variation: ' . $variation_id);

            // Get WooCommerce notices if any
            $notices = wc_get_notices('error');
            if (!empty($notices)) {
                error_log('WooCommerce errors: ' . print_r($notices, true));
                $error_msg = implode(', ', array_map(function($notice) {
                    return is_array($notice) ? $notice['notice'] : $notice;
                }, $notices));
                wc_clear_notices();
            }

            wp_send_json_error($error_msg);
        }
    } else {
        wp_send_json_error('Invalid product ID');
    }
}
add_action('wp_ajax_woocommerce_ajax_add_to_cart', 'aleaulavage_v2_ajax_add_to_cart');
add_action('wp_ajax_nopriv_woocommerce_ajax_add_to_cart', 'aleaulavage_v2_ajax_add_to_cart');

// Get Cart Quantities for all products
function aleaulavage_v2_get_cart_quantities()
{
    // No nonce needed for reading public cart data, but good practice to have if user specific
    // For now, keeping it open as it's just reading quantities

    $quantities = array();

    if (WC()->cart) {
        foreach (WC()->cart->get_cart() as $cart_item) {
            $product_id = $cart_item['product_id'];
            if (isset($quantities[$product_id])) {
                $quantities[$product_id] += $cart_item['quantity'];
            } else {
                $quantities[$product_id] = $cart_item['quantity'];
            }
        }
    }

    wp_send_json_success($quantities);
}
add_action('wp_ajax_get_cart_quantities', 'aleaulavage_v2_get_cart_quantities');
add_action('wp_ajax_nopriv_get_cart_quantities', 'aleaulavage_v2_get_cart_quantities');

// AJAX: Update cart item quantity
function aleaulavage_v2_update_cart_item_quantity()
{
    // Security Check
    check_ajax_referer('aleaulavage_cart_nonce', 'nonce');

    if (!isset($_POST['cart_item_key']) || !isset($_POST['quantity'])) {
        wp_send_json_error('Missing parameters');
        return;
    }

    $cart_item_key = sanitize_text_field($_POST['cart_item_key']);
    $quantity = absint($_POST['quantity']);

    if ($quantity < 1) {
        wp_send_json_error('Invalid quantity');
        return;
    }

    if (WC()->cart) {
        WC()->cart->set_quantity($cart_item_key, $quantity, true);
        WC()->cart->calculate_totals();
        wp_send_json_success(array(
            'cart_count' => WC()->cart->get_cart_contents_count(),
            'cart_total' => wc_price( WC()->cart->get_cart_contents_total() )
        ));
    } else {
        wp_send_json_error('Cart not available');
    }
}
add_action('wp_ajax_update_cart_item_quantity', 'aleaulavage_v2_update_cart_item_quantity');
add_action('wp_ajax_nopriv_update_cart_item_quantity', 'aleaulavage_v2_update_cart_item_quantity');

// AJAX: Remove cart item
function aleaulavage_v2_remove_cart_item()
{
    // Security Check
    check_ajax_referer('aleaulavage_cart_nonce', 'nonce');

    if (!isset($_POST['cart_item_key'])) {
        wp_send_json_error('Missing cart item key');
        return;
    }

    $cart_item_key = sanitize_text_field($_POST['cart_item_key']);

    if (WC()->cart) {
        WC()->cart->remove_cart_item($cart_item_key);
        WC()->cart->calculate_totals();
        wp_send_json_success(array(
            'cart_count' => WC()->cart->get_cart_contents_count(),
            'cart_total' => wc_price( WC()->cart->get_cart_contents_total() )
        ));
    } else {
        wp_send_json_error('Cart not available');
    }
}
add_action('wp_ajax_remove_cart_item', 'aleaulavage_v2_remove_cart_item');
add_action('wp_ajax_nopriv_remove_cart_item', 'aleaulavage_v2_remove_cart_item');

// AJAX: Get cart offcanvas content
function aleaulavage_v2_get_cart_offcanvas_content()
{
    // Ensure WooCommerce is loaded
    if (!function_exists('WC')) {
        wp_send_json_error(array('message' => 'WooCommerce not available'));
        return;
    }

    // Initialize WooCommerce session and cart if needed
    if (is_null(WC()->cart)) {
        wc_load_cart();
    }

    // Get cart
    $cart = WC()->cart;
    if (!$cart) {
        wp_send_json_error(array('message' => 'Cart not available'));
        return;
    }

    // Force recalculate totals to ensure fresh data
    $cart->calculate_totals();

    // Get the actual cart contents
    $cart_contents = $cart->get_cart();
    $cart_items_count = count($cart_contents); // Number of different products (lines)
    $cart_count = $cart->get_cart_contents_count(); // Total quantity of all items

    ob_start();

    if ($cart_items_count > 0) {
        // Include the offcanvas body content directly
        $template_path = get_template_directory() . '/template-parts/header/cart-offcanvas-body.php';
        if (file_exists($template_path)) {
            include $template_path;
        } else {
            echo '<p>Erreur: Template non trouvé à ' . esc_html($template_path) . '</p>';
        }
    } else {
        // Empty cart state
        ?>
        <div class="empty-cart d-flex flex-column align-items-center justify-content-center p-5 text-center h-100">
            <i class="fa-solid fa-basket-shopping text-muted mb-4" style="font-size: 4rem; opacity: 0.3;"></i>
            <h5 class="mb-3">Votre panier est vide</h5>
            <p class="text-muted mb-4">Ajoutez des produits pour commencer vos achats</p>
            <a href="<?php echo esc_url(home_url('boutique/')); ?>" class="btn btn-primary" data-bs-dismiss="offcanvas">
                <i class="fa-solid fa-store me-2"></i>
                Découvrir la boutique
            </a>
        </div>
        <?php
    }

    $html = ob_get_clean();
    wp_send_json_success(array(
        'html' => $html,
        'cart_count' => $cart_count,
        'cart_items' => $cart_items_count,
        'debug' => 'Cart items (lines): ' . $cart_items_count . ', Total quantity: ' . $cart_count
    ));
}
add_action('wp_ajax_get_cart_offcanvas_content', 'aleaulavage_v2_get_cart_offcanvas_content');
add_action('wp_ajax_nopriv_get_cart_offcanvas_content', 'aleaulavage_v2_get_cart_offcanvas_content');

/**
 * Wrap category count in a span and move it inside the link for styling
 */
function aleaulavage_v2_cat_count_span($links)
{
    // Move count inside the link and wrap in span
    $links = preg_replace('/<\/a>\s*\(([\d]+)\)/', ' <span class="count">$1</span></a>', $links);
    return $links;
}
add_filter('wp_list_categories', 'aleaulavage_v2_cat_count_span');

/**
 * AJAX Handler for Wishlist Page
 */
function aleaulavage_v2_get_wishlist()
{
    // Check nonce if needed, but this is public read-only based on IDs sent by client
    // check_ajax_referer('aleaulavage_wishlist_nonce', 'nonce');

    $product_ids = isset($_POST['product_ids']) ? array_map('absint', $_POST['product_ids']) : array();

    if (empty($product_ids)) {
        wp_send_json_error('No product IDs provided');
    }

    $args = array(
        'post_type' => 'product',
        'post_status' => 'publish',
        'post__in' => $product_ids,
        'posts_per_page' => -1,
        'orderby' => 'post__in' // Keep order of addition if possible, or just ID order
    );

    $query = new WP_Query($args);

    ob_start();

    if ($query->have_posts()) {
        echo '<ul class="products columns-4">'; // Use standard WooCommerce grid classes
        while ($query->have_posts()) {
            $query->the_post();
            wc_get_template_part('content', 'product');
        }
        echo '</ul>';
    } else {
        // Should not happen if IDs are valid, but handle empty result
        echo '<p class="text-center">Aucun produit trouvé.</p>';
    }

    wp_reset_postdata();

    $html = ob_get_clean();

    wp_send_json_success(array('html' => $html));
}

/**
 * ==============================================================================
 * USER WISHLIST SYSTEM
 * ==============================================================================
 */

/**
 * Add product to user wishlist
 */
function aleaulavage_v2_add_to_wishlist()
{
    check_ajax_referer('aleaulavage_wishlist_nonce', 'nonce');

    if (!is_user_logged_in()) {
        wp_send_json_error(['message' => 'Vous devez être connecté pour ajouter aux favoris']);
    }

    $product_id = isset($_POST['product_id']) ? absint($_POST['product_id']) : 0;
    $user_id = get_current_user_id();

    if (!$product_id) {
        wp_send_json_error(['message' => 'ID produit invalide']);
    }

    $wishlist = get_user_meta($user_id, 'user_wishlist', true);
    if (!is_array($wishlist)) {
        $wishlist = array();
    }

    if (!in_array($product_id, $wishlist)) {
        $wishlist[] = $product_id;
        update_user_meta($user_id, 'user_wishlist', $wishlist);
        $count = count($wishlist);
        wp_send_json_success(['action' => 'added', 'message' => 'Produit ajouté aux favoris', 'count' => $count]);
    } else {
        wp_send_json_error(['message' => 'Produit déjà dans les favoris']);
    }
}
add_action('wp_ajax_aleaulavage_add_to_wishlist', 'aleaulavage_v2_add_to_wishlist');

/**
 * Remove product from user wishlist
 */
function aleaulavage_v2_remove_from_wishlist()
{
    check_ajax_referer('aleaulavage_wishlist_nonce', 'nonce');

    if (!is_user_logged_in()) {
        wp_send_json_error(['message' => 'Vous devez être connecté']);
    }

    $product_id = isset($_POST['product_id']) ? absint($_POST['product_id']) : 0;
    $user_id = get_current_user_id();

    if (!$product_id) {
        wp_send_json_error(['message' => 'ID produit invalide']);
    }

    $wishlist = get_user_meta($user_id, 'user_wishlist', true);
    if (!is_array($wishlist)) {
        $wishlist = array();
    }

    $key = array_search($product_id, $wishlist);
    if ($key !== false) {
        unset($wishlist[$key]);
        $wishlist = array_values($wishlist); // Re-index array
        update_user_meta($user_id, 'user_wishlist', $wishlist);
        $count = count($wishlist);
        wp_send_json_success(['action' => 'removed', 'message' => 'Produit retiré des favoris', 'count' => $count]);
    } else {
        wp_send_json_error(['message' => 'Produit non trouvé dans les favoris']);
    }
}
add_action('wp_ajax_aleaulavage_remove_from_wishlist', 'aleaulavage_v2_remove_from_wishlist');

/**
 * Check if product is in wishlist
 */
function aleaulavage_v2_check_wishlist_status()
{
    // Allow non-logged in checks (returns false)
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'aleaulavage_wishlist_nonce')) {
        wp_send_json_error(['message' => 'Erreur de sécurité']);
    }

    if (!is_user_logged_in()) {
        wp_send_json_success(['in_wishlist' => false]);
    }

    $product_id = isset($_POST['product_id']) ? absint($_POST['product_id']) : 0;
    $user_id = get_current_user_id();

    $wishlist = get_user_meta($user_id, 'user_wishlist', true);
    if (!is_array($wishlist)) {
        $wishlist = array();
    }

    $in_wishlist = in_array($product_id, $wishlist);

    wp_send_json_success(['in_wishlist' => $in_wishlist]);
}
add_action('wp_ajax_aleaulavage_check_wishlist_status', 'aleaulavage_v2_check_wishlist_status');
add_action('wp_ajax_nopriv_aleaulavage_check_wishlist_status', 'aleaulavage_v2_check_wishlist_status');

/**
 * AJAX Login for Wishlist Modal
 */
function aleaulavage_v2_ajax_login()
{
    check_ajax_referer('aleaulavage_wishlist_nonce', 'security');

    $username = isset($_POST['username']) ? sanitize_text_field($_POST['username']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    if (empty($username) || empty($password)) {
        wp_send_json_error(['message' => 'Veuillez remplir tous les champs.']);
    }

    $credentials = array(
        'user_login' => $username,
        'user_password' => $password,
        'remember' => true
    );

    $user = wp_signon($credentials, false);

    if (is_wp_error($user)) {
        $error_message = 'Identifiants incorrects.';

        if ($user->get_error_code() === 'invalid_username') {
            $error_message = 'Nom d\'utilisateur ou email invalide.';
        } elseif ($user->get_error_code() === 'incorrect_password') {
            $error_message = 'Mot de passe incorrect.';
        }

        wp_send_json_error(['message' => $error_message]);
    } else {
        wp_set_current_user($user->ID);
        wp_set_auth_cookie($user->ID, true);

        wp_send_json_success([
            'message' => 'Connexion réussie !',
            'user_id' => $user->ID,
            'user_name' => $user->display_name
        ]);
    }
}
add_action('wp_ajax_aleaulavage_ajax_login', 'aleaulavage_v2_ajax_login');
add_action('wp_ajax_nopriv_aleaulavage_ajax_login', 'aleaulavage_v2_ajax_login');


/**
 * Check if product is in user's wishlist
 */
function is_product_in_wishlist($product_id)
{
    if (!is_user_logged_in()) {
        return false;
    }

    $user_id = get_current_user_id();
    $wishlist = get_user_meta($user_id, 'user_wishlist', true);

    if (!is_array($wishlist)) {
        return false;
    }

    return in_array($product_id, $wishlist);
}

/**
 * ==============================================================================
 * CUSTOM REGISTRATION FIELDS
 * ==============================================================================
 */

/**
 * Add custom fields to registration form
 */
function aleaulavage_v2_add_registration_fields()
{
    ?>
    <p class="woocommerce-form-row woocommerce-form-row--first form-row form-row-first">
        <label for="reg_billing_first_name">Prénom&nbsp;<span class="required" aria-hidden="true">*</span><span class="screen-reader-text">Obligatoire</span></label>
        <input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="billing_first_name" id="reg_billing_first_name" autocomplete="given-name" value="<?php echo (!empty($_POST['billing_first_name'])) ? esc_attr(wp_unslash($_POST['billing_first_name'])) : ''; ?>" required aria-required="true" />
    </p>

    <p class="woocommerce-form-row woocommerce-form-row--last form-row form-row-last">
        <label for="reg_billing_last_name">Nom&nbsp;<span class="required" aria-hidden="true">*</span><span class="screen-reader-text">Obligatoire</span></label>
        <input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="billing_last_name" id="reg_billing_last_name" autocomplete="family-name" value="<?php echo (!empty($_POST['billing_last_name'])) ? esc_attr(wp_unslash($_POST['billing_last_name'])) : ''; ?>" required aria-required="true" />
    </p>

    <div class="clear"></div>
    <?php
}
add_action('woocommerce_register_form_start', 'aleaulavage_v2_add_registration_fields');

/**
 * Validate custom registration fields
 */
function aleaulavage_v2_validate_registration_fields($errors, $username, $email)
{
    if (empty($_POST['billing_first_name'])) {
        $errors->add('billing_first_name_error', __('Le prénom est requis.', 'aleaulavage-v2'));
    }

    if (empty($_POST['billing_last_name'])) {
        $errors->add('billing_last_name_error', __('Le nom est requis.', 'aleaulavage-v2'));
    }

    return $errors;
}
add_filter('woocommerce_registration_errors', 'aleaulavage_v2_validate_registration_fields', 10, 3);

/**
 * Save custom registration fields
 */
function aleaulavage_v2_save_registration_fields($customer_id)
{
    if (isset($_POST['billing_first_name'])) {
        update_user_meta($customer_id, 'first_name', sanitize_text_field($_POST['billing_first_name']));
        update_user_meta($customer_id, 'billing_first_name', sanitize_text_field($_POST['billing_first_name']));
    }

    if (isset($_POST['billing_last_name'])) {
        update_user_meta($customer_id, 'last_name', sanitize_text_field($_POST['billing_last_name']));
        update_user_meta($customer_id, 'billing_last_name', sanitize_text_field($_POST['billing_last_name']));
    }
}
add_action('woocommerce_created_customer', 'aleaulavage_v2_save_registration_fields');

/**
 * Customize password strength requirements text
 */
function aleaulavage_v2_password_strength_meter_text($translated_text, $text, $domain)
{
    if ($text === 'Hint: The password should be at least twelve characters long. To make it stronger, use upper and lower case letters, numbers, and symbols like ! " ? $ % ^ &amp; ).') {
        return 'Conseil : Le mot de passe devrait contenir au moins huit caractères. Pour le rendre plus sûr, utilisez des lettres en majuscules et minuscules et des nombres.';
    }
    return $translated_text;
}
add_filter('gettext', 'aleaulavage_v2_password_strength_meter_text', 20, 3);

/**
 * Change password minimum length to 8
 */
function aleaulavage_v2_password_strength_settings()
{
    ?>
    <script type="text/javascript">
        if (typeof wc_password_strength_meter_params !== 'undefined') {
            wc_password_strength_meter_params.min_password_strength = 3;
            wc_password_strength_meter_params.min_password_length = 8;
        }
    </script>
    <?php
}
add_action('woocommerce_register_form', 'aleaulavage_v2_password_strength_settings');

/**
 * Change privacy policy link to /mentions-legales/
 */
function aleaulavage_v2_privacy_policy_text($text)
{
    $text = str_replace(
        'politique de confidentialité',
        '<a href="/mentions-legales/" class="woocommerce-privacy-policy-link" target="_blank">politique de confidentialité</a>',
        $text
    );
    return $text;
}
add_filter('woocommerce_registration_privacy_policy_text', 'aleaulavage_v2_privacy_policy_text', 10, 1);

/**
 * Remove username field from registration (use email as username)
 */
function aleaulavage_v2_remove_username_registration()
{
    ?>
    <script type="text/javascript">
        jQuery(function($) {
            // Hide username field
            $('.woocommerce-form-register p:has(#reg_username)').hide();

            // Auto-fill username from email
            $('#reg_email').on('blur', function() {
                var email = $(this).val();
                if (email && $('#reg_username').length) {
                    $('#reg_username').val(email);
                }
            });
        });
    </script>
    <?php
}
add_action('woocommerce_register_form', 'aleaulavage_v2_remove_username_registration');

/**
 * Add tabs functionality to login/register page
 */
function aleaulavage_v2_login_register_tabs()
{
    // Ne pas exécuter pendant les requêtes AJAX
    if (wp_doing_ajax()) {
        return;
    }

    if (!is_account_page() || is_user_logged_in()) {
        return;
    }
    ?>
    <script type="text/javascript">
        jQuery(function($) {
            // Wrap forms in tab structure
            var $columns = $('#customer_login');

            if ($columns.length) {
                // Create tabs navigation
                var $tabsNav = $('<div class="auth-tabs-nav"></div>');
                $tabsNav.append('<button class="auth-tab-btn active" data-tab="login">Se connecter</button>');
                $tabsNav.append('<button class="auth-tab-btn" data-tab="register">S\'inscrire</button>');

                // Add tabs nav before columns
                $columns.before($tabsNav);

                // Wrap in container
                $columns.wrap('<div class="auth-tabs-container"></div>');

                // Add data attributes to columns
                $('.u-column1').attr('data-tab-content', 'login').addClass('active');
                $('.u-column2').attr('data-tab-content', 'register');

                // Tab switching
                $('.auth-tab-btn').on('click', function() {
                    var tab = $(this).data('tab');

                    // Update buttons
                    $('.auth-tab-btn').removeClass('active');
                    $(this).addClass('active');

                    // Update content
                    $('[data-tab-content]').removeClass('active');
                    $('[data-tab-content="' + tab + '"]').addClass('active');
                });
            }
        });
    </script>
    <?php
}
add_action('wp_footer', 'aleaulavage_v2_login_register_tabs');

/**
 * Customize Checkout Fields
 * Match SCW Shop Layout exactly
 */
function aleaulavage_v2_checkout_fields($fields) {
    // --- Billing Fields ---
    
    // Hide unnecessary fields instead of unset, to avoid validation errors
    // We set them to hidden and provide default values in HTML
    $fields['billing']['billing_country']['class'] = array('d-none', 'hidden');
    $fields['billing']['billing_country']['label_class'] = array('d-none', 'hidden');
    $fields['billing']['billing_country']['required'] = false; // Handled by hidden input
    $fields['billing']['billing_country']['default'] = 'FR'; // Force default value
    
    $fields['billing']['billing_state']['class'] = array('d-none', 'hidden');
    $fields['billing']['billing_state']['label_class'] = array('d-none', 'hidden');
    $fields['billing']['billing_state']['required'] = false;

    $fields['billing']['billing_address_2']['class'] = array('d-none', 'hidden');
    $fields['billing']['billing_address_2']['label_class'] = array('d-none', 'hidden');
    $fields['billing']['billing_address_2']['required'] = false;

    // ... (Billing fields setup) ...

    // --- Shipping Fields (Mirror Billing) ---
    
    $fields['shipping']['shipping_country']['class'] = array('d-none', 'hidden');
    $fields['shipping']['shipping_country']['label_class'] = array('d-none', 'hidden');
    $fields['shipping']['shipping_country']['required'] = false;
    $fields['shipping']['shipping_country']['default'] = 'FR'; // Force default value
    
    $fields['shipping']['shipping_state']['class'] = array('d-none', 'hidden');
    $fields['shipping']['shipping_state']['label_class'] = array('d-none', 'hidden');
    $fields['shipping']['shipping_state']['required'] = false;

    $fields['shipping']['shipping_address_2']['class'] = array('d-none', 'hidden');
    $fields['shipping']['shipping_address_2']['label_class'] = array('d-none', 'hidden');
    $fields['shipping']['shipping_address_2']['required'] = false;

    $fields['shipping']['shipping_first_name']['priority'] = 10;
    $fields['shipping']['shipping_first_name']['class'] = array('form-row-first');
    $fields['shipping']['shipping_first_name']['label'] = 'Prénom';

    $fields['shipping']['shipping_last_name']['priority'] = 20;
    $fields['shipping']['shipping_last_name']['class'] = array('form-row-last');
    $fields['shipping']['shipping_last_name']['label'] = 'Nom';

    $fields['shipping']['shipping_company']['priority'] = 30;
    $fields['shipping']['shipping_company']['class'] = array('form-row-first');
    $fields['shipping']['shipping_company']['label'] = 'Société';
    $fields['shipping']['shipping_company']['required'] = true;

    $fields['shipping']['shipping_address_1']['priority'] = 40;
    $fields['shipping']['shipping_address_1']['class'] = array('form-row-wide');
    $fields['shipping']['shipping_address_1']['placeholder'] = 'N° et nom de rue';
    $fields['shipping']['shipping_address_1']['label'] = 'Adresse';

    $fields['shipping']['shipping_postcode']['priority'] = 50;
    $fields['shipping']['shipping_postcode']['class'] = array('form-row-first');
    $fields['shipping']['shipping_postcode']['label'] = 'Code Postal';

    $fields['shipping']['shipping_city']['priority'] = 60;
    $fields['shipping']['shipping_city']['class'] = array('form-row-last');
    $fields['shipping']['shipping_city']['label'] = 'Ville';

    return $fields;
}
add_filter('woocommerce_checkout_fields', 'aleaulavage_v2_checkout_fields');


/**
 * Prevent WooCommerce from copying billing to shipping address
 * This ensures shipping address fields from the form are respected
 */
add_filter( 'woocommerce_ship_to_different_address_checked', '__return_true' );

/**
 * Save SIRET field to Order Meta and ensure all shipping fields are saved
 */
function aleaulavage_v2_checkout_field_update_order_meta( $order_id ) {
    // Get the order object
    $order = wc_get_order( $order_id );
    if ( ! $order ) {
        return;
    }

    // Save SIRET fields using WooCommerce methods
    if ( ! empty( $_POST['billing_siret'] ) ) {
        $order->update_meta_data( '_billing_siret', sanitize_text_field( $_POST['billing_siret'] ) );
    }
    if ( ! empty( $_POST['shipping_siret'] ) ) {
        $order->update_meta_data( '_shipping_siret', sanitize_text_field( $_POST['shipping_siret'] ) );
    }

    // Ensure all shipping address fields are saved using WooCommerce setter methods
    // Only set if the value exists in POST to avoid overwriting with empty values
    if ( ! empty( $_POST['shipping_first_name'] ) ) {
        $order->set_shipping_first_name( sanitize_text_field( $_POST['shipping_first_name'] ) );
    }
    if ( ! empty( $_POST['shipping_last_name'] ) ) {
        $order->set_shipping_last_name( sanitize_text_field( $_POST['shipping_last_name'] ) );
    }
    if ( ! empty( $_POST['shipping_company'] ) ) {
        $order->set_shipping_company( sanitize_text_field( $_POST['shipping_company'] ) );
    }
    if ( ! empty( $_POST['shipping_address_1'] ) ) {
        $order->set_shipping_address_1( sanitize_text_field( $_POST['shipping_address_1'] ) );
    }
    if ( ! empty( $_POST['shipping_postcode'] ) ) {
        $order->set_shipping_postcode( sanitize_text_field( $_POST['shipping_postcode'] ) );
    }
    if ( ! empty( $_POST['shipping_city'] ) ) {
        $order->set_shipping_city( sanitize_text_field( $_POST['shipping_city'] ) );
    }
    if ( ! empty( $_POST['shipping_country'] ) ) {
        $order->set_shipping_country( sanitize_text_field( $_POST['shipping_country'] ) );
    }
    if ( isset( $_POST['shipping_state'] ) ) {
        $order->set_shipping_state( sanitize_text_field( $_POST['shipping_state'] ) );
    }

    // Save all changes to the order
    $order->save();
}
add_action( 'woocommerce_checkout_update_order_meta', 'aleaulavage_v2_checkout_field_update_order_meta', 10, 1 );

/**
 * Display SIRET in Admin Order Details
 */
function aleaulavage_v2_admin_order_data_after_shipping_address( $order ) {
    $siret = get_post_meta( $order->get_id(), '_shipping_siret', true );
    if ( ! empty( $siret ) ) {
        echo '<p><strong>' . __( 'SIRET', 'aleaulavage-v2' ) . ':</strong> ' . esc_html( $siret ) . '</p>';
    }
}
add_action( 'woocommerce_admin_order_data_after_shipping_address', 'aleaulavage_v2_admin_order_data_after_shipping_address' );

/**
 * Save SIRET to User Meta (optional, for auto-fill next time)
 */
function aleaulavage_v2_checkout_update_user_meta( $customer_id, $posted ) {
    if ( ! empty( $_POST['billing_siret'] ) ) {
        update_user_meta( $customer_id, 'billing_siret', sanitize_text_field( $_POST['billing_siret'] ) );
    }
    if ( ! empty( $_POST['shipping_siret'] ) ) {
        update_user_meta( $customer_id, 'shipping_siret', sanitize_text_field( $_POST['shipping_siret'] ) );
    }

    // Ensure shipping address fields are saved correctly
    // This fixes the issue where billing and shipping addresses were being saved as the same
    $shipping_fields = array(
        'shipping_first_name',
        'shipping_last_name',
        'shipping_company',
        'shipping_siret',
        'shipping_address_1',
        'shipping_postcode',
        'shipping_city',
        'shipping_country',
        'shipping_state'
    );

    foreach ( $shipping_fields as $field ) {
        if ( isset( $_POST[$field] ) ) {
            update_user_meta( $customer_id, $field, sanitize_text_field( $_POST[$field] ) );
        }
    }

    // Ensure billing address fields are saved correctly
    $billing_fields = array(
        'billing_first_name',
        'billing_last_name',
        'billing_company',
        'billing_siret',
        'billing_address_1',
        'billing_postcode',
        'billing_city',
        'billing_country',
        'billing_state',
        'billing_email',
        'billing_phone'
    );

    foreach ( $billing_fields as $field ) {
        if ( isset( $_POST[$field] ) ) {
            update_user_meta( $customer_id, $field, sanitize_text_field( $_POST[$field] ) );
        }
    }
}
add_action( 'woocommerce_checkout_update_user_meta', 'aleaulavage_v2_checkout_update_user_meta', 10, 2 );
/**
 * === GUEST CHECKOUT & CART MANAGEMENT ===
 */

/**
 * Gérer le bouton "Continuer en tant qu'invité"
 */
function aleaulavage_v2_handle_guest_checkout() {
    if ( isset( $_POST['enable_guest_checkout_temp'] ) && 
         wp_verify_nonce( $_POST['guest_checkout_nonce'], 'enable_guest_checkout' ) ) {
        
        // Activer temporairement le guest checkout pour cette session
        WC()->session->set( 'guest_checkout_enabled', true );
        
        // Rediriger vers le checkout
        wp_safe_redirect( wc_get_checkout_url() );
        exit;
    }
}
add_action( 'template_redirect', 'aleaulavage_v2_handle_guest_checkout' );

/**
 * Permettre le guest checkout si activé en session
 * Forcer à 'no' par défaut pour toujours afficher la page de login/invité
 */
function aleaulavage_v2_allow_guest_checkout( $value ) {
    if ( WC()->session && WC()->session->get( 'guest_checkout_enabled' ) ) {
        return 'yes';
    }
    return 'no'; // Forcer à 'no' pour toujours montrer la page de login/invité
}
add_filter( 'option_woocommerce_enable_guest_checkout', 'aleaulavage_v2_allow_guest_checkout' );
add_filter( 'pre_option_woocommerce_enable_guest_checkout', 'aleaulavage_v2_allow_guest_checkout' );

/**
 * Désactiver le panier persistant de WooCommerce si keep_cart=1
 * Cela empêche WooCommerce de fusionner l'ancien panier de l'utilisateur
 */
function aleaulavage_v2_disable_persistent_cart( $enabled ) {
    if ( isset( $_GET['keep_cart'] ) && $_GET['keep_cart'] == '1' ) {
        return false;
    }
    return $enabled;
}
add_filter( 'woocommerce_persistent_cart_enabled', 'aleaulavage_v2_disable_persistent_cart', 10, 1 );

/**
 * Sauvegarder le panier de session dans un cookie avant la connexion
 */
function aleaulavage_v2_save_cart_to_cookie() {
    if ( isset( $_GET['keep_cart'] ) && $_GET['keep_cart'] == '1' && ! is_user_logged_in() ) {
        $cart = WC()->session->get( 'cart' );
        if ( $cart && ! empty( $cart ) ) {
            // Sauvegarder dans un cookie temporaire (expire dans 1 heure)
            setcookie( 'temp_checkout_cart', json_encode( $cart ), time() + HOUR_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN );
        }
    }
}
add_action( 'template_redirect', 'aleaulavage_v2_save_cart_to_cookie', 5 );

/**
 * Restaurer le panier depuis le cookie après connexion
 */
function aleaulavage_v2_restore_cart_from_cookie() {
    // Vérifier si l'utilisateur vient de se connecter avec keep_cart=1
    if ( is_user_logged_in() && isset( $_GET['keep_cart'] ) && $_GET['keep_cart'] == '1' ) {
        // Vérifier si le cookie existe
        if ( isset( $_COOKIE['temp_checkout_cart'] ) ) {
            $saved_cart = json_decode( stripslashes( $_COOKIE['temp_checkout_cart'] ), true );

            if ( $saved_cart && ! empty( $saved_cart ) ) {
                // Vider complètement le panier actuel
                WC()->cart->empty_cart( false );

                // Restaurer le panier depuis le cookie
                WC()->session->set( 'cart', $saved_cart );

                // Supprimer le cookie
                setcookie( 'temp_checkout_cart', '', time() - 3600, COOKIEPATH, COOKIE_DOMAIN );

                // Recalculer les totaux
                WC()->cart->calculate_totals();
            }
        }
    }
}
add_action( 'woocommerce_load_cart_from_session', 'aleaulavage_v2_restore_cart_from_cookie', 999 );

/**
 * Redirection après connexion vers le panier avec le panier de session préservé
 */
function aleaulavage_v2_login_redirect( $redirect_to, $request, $user ) {
    // Vérifier si un paramètre redirect a été passé dans l'URL de connexion
    if ( isset( $_REQUEST['redirect_to'] ) && ! empty( $_REQUEST['redirect_to'] ) ) {
        $redirect_to = wp_unslash( $_REQUEST['redirect_to'] );
    }

    return $redirect_to;
}
add_filter( 'login_redirect', 'aleaulavage_v2_login_redirect', 100, 3 );

/**
 * Redirection WooCommerce après connexion
 */
function aleaulavage_v2_woocommerce_login_redirect( $redirect, $user ) {
    // Vérifier si un paramètre redirect a été passé dans l'URL de connexion
    if ( isset( $_REQUEST['redirect_to'] ) && ! empty( $_REQUEST['redirect_to'] ) ) {
        $redirect = wp_unslash( $_REQUEST['redirect_to'] );
    }

    return $redirect;
}
add_filter( 'woocommerce_login_redirect', 'aleaulavage_v2_woocommerce_login_redirect', 100, 2 );

/**
 * Effacer la session guest checkout après une commande
 * Pour que l'utilisateur doive choisir à nouveau à sa prochaine commande
 */
function aleaulavage_v2_clear_guest_checkout_after_order( $order_id ) {
    if ( WC()->session ) {
        WC()->session->set( 'guest_checkout_enabled', null );
    }
}
add_action( 'woocommerce_thankyou', 'aleaulavage_v2_clear_guest_checkout_after_order', 10, 1 );

/**
 * Inject cart quantity data for JS
 */
function aleaulavage_v2_inject_cart_data() {
    if ( ! function_exists('is_product') || ! is_product() ) {
        return;
    }
    
    global $product;
    if ( ! $product || ! is_object($product) ) {
        return;
    }
    
    $cart_qty = 0;
    if ( function_exists('WC') && WC()->cart ) {
        foreach ( WC()->cart->get_cart() as $cart_item ) {
            // Check product ID match (parent or variation)
            if ( $cart_item['product_id'] == $product->get_id() || $cart_item['variation_id'] == $product->get_id() ) {
                $cart_qty += $cart_item['quantity'];
            }
        }
    }
    
    ?>
    <script type="text/javascript">
    var aleaulavageProductData = {
        cartQty: <?php echo intval($cart_qty); ?>,
        regularPriceHtml: '<?php echo wc_price(wc_get_price_to_display($product, array('price' => $product->get_regular_price()))); ?>',
        regularPrice: <?php echo floatval(wc_get_price_to_display($product, array('price' => $product->get_regular_price()))); ?>,
        productId: <?php echo intval($product->get_id()); ?>,
        ajaxUrl: '<?php echo esc_url(admin_url('admin-ajax.php')); ?>'
    };
    </script>
    <?php
}
add_action( 'wp_footer', 'aleaulavage_v2_inject_cart_data' );

/**
 * AJAX: Récupérer la quantité panier actuelle pour un produit donné
 * Utilisé pour synchroniser la page produit quand le panier change ailleurs (offcanvas)
 */
function aleaulavage_v2_get_product_cart_qty_ajax() {
    $product_id = isset($_POST['product_id']) ? absint($_POST['product_id']) : 0;
    
    if ( ! $product_id ) {
        wp_send_json_error('No product ID');
    }
    
    $cart_qty = 0;
    if ( function_exists('WC') && WC()->cart ) {
        foreach ( WC()->cart->get_cart() as $cart_item ) {
            if ( $cart_item['product_id'] == $product_id || $cart_item['variation_id'] == $product_id ) {
                $cart_qty += $cart_item['quantity'];
            }
        }
    }
    
    wp_send_json_success(['qty' => $cart_qty]);
}
add_action('wp_ajax_aleaulavage_get_product_cart_qty', 'aleaulavage_v2_get_product_cart_qty_ajax');
add_action('wp_ajax_nopriv_aleaulavage_get_product_cart_qty', 'aleaulavage_v2_get_product_cart_qty_ajax');
