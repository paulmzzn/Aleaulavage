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
 * Handler AJAX pour l'ajout au panier
 */
add_action('wp_ajax_woocommerce_ajax_add_to_cart', 'aleaulavage_v2_ajax_add_to_cart_handler');
add_action('wp_ajax_nopriv_woocommerce_ajax_add_to_cart', 'aleaulavage_v2_ajax_add_to_cart_handler');
function aleaulavage_v2_ajax_add_to_cart_handler() {
    $product_id = apply_filters('woocommerce_add_to_cart_product_id', absint($_POST['product_id']));
    $quantity = empty($_POST['quantity']) ? 1 : wc_stock_amount($_POST['quantity']);
    $passed_validation = apply_filters('woocommerce_add_to_cart_validation', true, $product_id, $quantity);

    if ($passed_validation && WC()->cart->add_to_cart($product_id, $quantity)) {
        do_action('woocommerce_ajax_added_to_cart', $product_id);

        WC_AJAX::get_refreshed_fragments();
    } else {
        $data = array(
            'error' => true,
            'product_url' => apply_filters('woocommerce_cart_redirect_after_error', get_permalink($product_id), $product_id)
        );

        wp_send_json($data);
    }
}

/**
 * Support AJAX pour l'ajout au panier sur les pages produit simple
 */
add_action('wp_footer', 'aleaulavage_v2_ajax_add_to_cart_script');
function aleaulavage_v2_ajax_add_to_cart_script() {
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
            var productId = button.val();
            var quantity = form.find('input[name="quantity"]').val() || 1;

            console.log('Adding to cart:', productId, 'Quantity:', quantity);

            // Désactiver le bouton pendant l'ajout
            button.prop('disabled', true).addClass('loading');

            $.ajax({
                type: 'POST',
                url: '<?php echo esc_url(admin_url('admin-ajax.php')); ?>',
                data: {
                    action: 'woocommerce_ajax_add_to_cart',
                    product_id: productId,
                    quantity: quantity
                },
                success: function(response) {
                    console.log('Response:', response);

                    if (response && !response.error) {
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
                    }

                    // Réactiver le bouton
                    button.prop('disabled', false).removeClass('loading');
                },
                error: function(xhr, status, error) {
                    console.error('AJAX error:', status, error);
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
                <span class="fw-bold">Total :</span>
                <span class="fw-bold"><?php echo WC()->cart->get_total(); ?></span>
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

    if ($product_id && WC()->cart) {
        $cart_item_key = WC()->cart->add_to_cart($product_id, $quantity);

        if ($cart_item_key) {
            // Trigger cart calculation
            WC()->cart->calculate_totals();

            // Get WooCommerce fragments
            WC_AJAX::get_refreshed_fragments();
        } else {
            wp_send_json_error('Could not add product to cart');
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
            'cart_total' => WC()->cart->get_total()
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
            'cart_total' => WC()->cart->get_total()
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

    // Get the actual cart contents
    $cart_contents = $cart->get_cart();
    $cart_count = count($cart_contents);

    // Alternative count method
    $cart_count_method2 = $cart->get_cart_contents_count();

    ob_start();

    if ($cart_count > 0) {
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
        'cart_count_method2' => $cart_count_method2,
        'cart_items' => count($cart_contents),
        'debug' => 'Cart items: ' . count($cart_contents) . ', Count method 1: ' . $cart_count . ', Count method 2: ' . $cart_count_method2
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
