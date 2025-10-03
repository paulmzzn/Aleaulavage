<script>
jQuery(function($) {
  // Met à jour dynamiquement le prix unitaire affiché après chaque réponse AJAX du panier
  function updateUnitPricesFromAjax(response) {
    if (!response || !response.data || !response.data.items) return;
    $.each(response.data.items, function(cartKey, item) {
      if (item.unit_price_html) {
        // Desktop
        var $row = $('tr[data-cart-key="' + cartKey + '"]');
        $row.find('.product-price').html(item.unit_price_html);
        // Mobile
        $row.find('.product-price-mobile .price-value').html(item.unit_price_html);
      }
    });
  }

  // Patch la fonction AJAX d'origine pour injecter la MAJ du prix unitaire
  var oldAjax = $.ajax;
  $.ajax = function(settings) {
    if (settings && settings.data && settings.data.action === 'update_cart_ajax') {
      var oldSuccess = settings.success;
      settings.success = function(response) {
        if (typeof oldSuccess === 'function') oldSuccess(response);
        updateUnitPricesFromAjax(response);
      };
    }
    return oldAjax.apply(this, arguments);
  };
});
</script>
<?php
/**
 * Page Panier avec AJAX - Sans rechargement
 * 
 * Fonctionnalités AJAX :
 * - Mise à jour quantité en temps réel
 * - Suppression d'articles
 * - Mise à jour automatique des totaux
 * Color personnalisé: #2A3E6A
 */
defined('ABSPATH') || exit;

// Désactiver le zoom sur mobile pour cette page
add_action('wp_head', function() {
    if (is_cart()) {
        echo '<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">';
        echo '<style>body { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }</style>';
    }
}, 1);

do_action('woocommerce_before_cart'); ?>

<div class="container-fluid px-3 my-5">
  <div class="cart-progress bg-light py-3 rounded-4 mb-4">
  <style>
  .cart-progress {
    border-radius: 1.25rem !important;
    overflow: hidden;
  }
  
  /* DESKTOP - Restaurer les paddings normaux avec priorité maximale */
  @media (min-width: 769px) {
    body.woocommerce-cart.woocommerce-cart .pt-5 {
        padding-top: 3rem !important;
    }
    
    body.woocommerce-cart.woocommerce-cart footer,
    body.woocommerce-cart.woocommerce-cart .footer,
    body.woocommerce-cart.woocommerce-cart #footer,
    body.woocommerce-cart.woocommerce-cart .site-footer {
        margin-top: 3rem !important;
        padding-top: 3rem !important;
    }
  }
  </style>
    <div class="progress mx-3" style="height: 8px;">
      <?php 
        $target = 550;
        $cart_subtotal = WC()->cart->get_subtotal(); // Prix HT
        // Utiliser seulement le prix HT pour le calcul de livraison gratuite
        $percent = $cart_subtotal > 0 ? min(100, ($cart_subtotal / $target) * 100) : 0;
      ?>
      <div class="progress-bar" role="progressbar" style="width: <?php echo esc_attr($percent); ?>%; background-color: #5899E2;"></div>
    </div>
    <div class="mt-2 small text-muted text-start text-md-start text-center mx-3">
      <span class="free-shipping-message">
        <?php 
        $remaining = max(0, 550 - $cart_subtotal);
        if ($cart_subtotal >= 550) {
          echo esc_html__('Livraison offerte !', 'woocommerce');
        } else {
          echo esc_html__('Plus que ', 'woocommerce') . wc_price($remaining) . ' HT ' . esc_html__('pour profiter de la livraison offerte', 'woocommerce');
        }
        ?>
      </span>
    </div>
  </div>
  <!-- Messages de notification -->
  <div id="cart-messages" class="alert-container mb-3" style="display: none;"></div>
  
  <!-- Loader -->
  <div id="cart-loader" class="text-center py-3" style="display: none;">
    <div class="spinner-border text-primary" role="status">
      <span class="visually-hidden"><?php esc_html_e('Chargement...', 'woocommerce'); ?></span>
    </div>
  </div>

  <div class="row" id="cart-content">
    <!-- Left Column: Cart Items -->
    <div class="col-lg-8 col-12 mb-4">
      <div class="card shadow-sm mb-4">
        <div class="card-body">
          <h2 class="h4 mb-4 text-uppercase"><?php esc_html_e('Votre panier', 'woocommerce'); ?></h2>

          <?php if (WC()->cart->is_empty()) : ?>
            <div class="empty-cart text-center py-5">
              <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
              <h3><?php esc_html_e('Votre panier est vide', 'woocommerce'); ?></h3>
              <a href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>" class="btn btn-primary mt-3">
                <?php esc_html_e('Continuer mes achats', 'woocommerce'); ?>
              </a>
            </div>
          <?php else : ?>
            <div class="table-responsive cart-table-wrapper">
              <table class="table cart-table mb-0">
                <thead class="table-light cart-table-head-rounded d-none d-md-table-header-group">
                <style>
                .cart-table-head-rounded th:first-child {
                  border-top-left-radius: 1rem;
                }
                .cart-table-head-rounded th:last-child {
                  border-top-right-radius: 1rem;
                }
                </style>
                  <tr>
                    <th><?php esc_html_e('Produit', 'woocommerce'); ?></th>
                    <th class="text-center"><?php esc_html_e('Prix', 'woocommerce'); ?></th>
                    <th class="text-center"><?php esc_html_e('Quantité', 'woocommerce'); ?></th>
                    <th class="text-end"><?php esc_html_e('Total', 'woocommerce'); ?></th>
                    <th class="text-center"></th>
                  </tr>
                </thead>
                <tbody id="cart-items">
                  <?php foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) :
                    $_product   = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key);
                    if (!$_product || !$_product->exists() || $cart_item['quantity'] <= 0) continue;
                    $product_permalink = $_product->is_visible() ? $_product->get_permalink($cart_item) : '';
                  ?>
                    <tr class="cart_item align-middle cart-item-mobile" data-cart-key="<?php echo esc_attr($cart_item_key); ?>">
                      <td class="product-info align-middle" colspan="5">
                        <!-- Mobile Layout -->
                        <div class="d-md-none mobile-cart-item">
                          <div class="mobile-item-header d-flex align-items-center justify-content-between mb-3">
                            <div class="d-flex align-items-center flex-grow-1">
                              <div class="thumb me-3 flex-shrink-0">
                                <div class="cart-thumb-img-wrapper">
                                  <?php echo apply_filters('woocommerce_cart_item_thumbnail', $_product->get_image('woocommerce_thumbnail'), $cart_item, $cart_item_key); ?>
                                </div>
                              </div>
                              <div class="product-info-mobile flex-grow-1">
                                <div class="product-name mb-1">
                                  <?php
                                    if ($product_permalink) echo '<a href="'.esc_url($product_permalink).'" class="product-link">';
                                    echo wp_kses_post($_product->get_name());
                                    if ($product_permalink) echo '</a>';
                                    echo wc_get_formatted_cart_item_data($cart_item);

                                    // Afficher "En réapprovisionnement" si le produit est en rupture ou en backorder
                                    $stock_status = $_product->get_stock_status();
                                    $stock_quantity = $_product->get_stock_quantity();

                                    // Afficher si hors stock OU si stock est 0/null et pas en stock
                                    if ($stock_status === 'outofstock' || $stock_status === 'onbackorder' || (!$_product->is_in_stock() && ($stock_quantity === 0 || $stock_quantity === null))) {
                                      echo '<div class="out-of-stock-notice" style="color: #e67e22; font-size: 0.85rem; margin: 4px 0; font-weight: 600;">En réapprovisionnement</div>';
                                    }
                                  ?>
                                  <div class="cart-stock-notice" data-cart-key="<?php echo esc_attr($cart_item_key); ?>" style="display: none;"></div>
                                </div>
                                <div class="product-price-mobile">
                                  <span class="price-label">Prix unitaire:</span>
                                  <span class="price-value"><?php echo WC()->cart->get_product_price($_product); ?></span>
                                </div>
                              </div>
                            </div>
                            <div class="remove-btn-mobile">
                              <button type="button" class="btn btn-outline-danger btn-sm remove-item" 
                                      data-cart-key="<?php echo esc_attr($cart_item_key); ?>"
                                      title="<?php esc_attr_e('Retirer cet article', 'woocommerce'); ?>">
                                <i class="fas fa-trash-alt"></i>
                              </button>
                            </div>
                          </div>
                          
                          <div class="mobile-item-controls">
                            <div class="row align-items-center">
                              <div class="col-6">
                                <div class="quantity-section">
                                  <label class="quantity-label mb-1">Quantité</label>
                                  <?php if ($_product->is_sold_individually()) : ?>
                                    <div class="qty-display-mobile">1</div>
                                  <?php else : ?>
                                    <div class="quantity-controls-mobile">
                                      <?php
                                        $backorders_mobile = $_product->get_backorders();
                                        $managing_stock_mobile = $_product->managing_stock();
                                        $stock_qty_mobile = $_product->get_stock_quantity();
                                        $current_qty_mobile = (int) $cart_item['quantity'];
                                      ?>
                                      <div class="custom-qty-box d-flex align-items-center justify-content-center"
                                           data-cart-key="<?php echo esc_attr($cart_item_key); ?>"
                                           data-stock-qty="<?php echo esc_attr($stock_qty_mobile !== null ? $stock_qty_mobile : 999999); ?>"
                                           data-backorders="<?php echo esc_attr($backorders_mobile); ?>">
                                        <button type="button" class="qty-btn qty-minus" aria-label="Diminuer la quantité">
                                          <span>−</span>
                                        </button>
                                        <input type="number" class="qty-input mx-2" value="<?php echo $current_qty_mobile; ?>" min="1" data-cart-key="<?php echo esc_attr($cart_item_key); ?>">
                                        <button type="button" class="qty-btn qty-plus" aria-label="Augmenter la quantité">
                                          <span>+</span>
                                        </button>
                                      </div>
                                    </div>
                                  <?php endif; ?>
                                </div>
                              </div>
                              <div class="col-6 text-end">
                                <div class="total-section">
                                  <label class="total-label mb-1">Total</label>
                                  <div class="item-total-mobile" data-cart-key="<?php echo esc_attr($cart_item_key); ?>">
                                    <?php echo WC()->cart->get_product_subtotal($_product, $cart_item['quantity']); ?>
                                  </div>
                                </div>
                              </div>
                            </div>
                          </div>
                        </div>
                        
                        <!-- Desktop Layout -->
                        <div class="d-none d-md-flex align-items-center">
                          <div class="thumb me-3">
                            <div class="cart-thumb-img-wrapper">
                              <?php echo apply_filters('woocommerce_cart_item_thumbnail', $_product->get_image('woocommerce_thumbnail'), $cart_item, $cart_item_key); ?>
                            </div>
<style>
.cart-thumb-img-wrapper {
  width: 80px;
  height: 80px;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
  border-radius: 12px;
  background: #f5f6fa;
}
.cart-thumb-img-wrapper img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  object-position: center;
  display: block;
}
</style>
                          </div>
                          <div>
                            <?php
                              if ($product_permalink) echo '<a href="'.esc_url($product_permalink).'">';
                              echo wp_kses_post($_product->get_name());
                              if ($product_permalink) echo '</a>';
                              echo wc_get_formatted_cart_item_data($cart_item);

                              // Afficher "En réapprovisionnement" si le produit est en rupture ou en backorder
                              $stock_status = $_product->get_stock_status();
                              $stock_quantity = $_product->get_stock_quantity();

                              // Afficher si hors stock OU si stock est 0/null et pas en stock
                              if ($stock_status === 'outofstock' || $stock_status === 'onbackorder' || (!$_product->is_in_stock() && ($stock_quantity === 0 || $stock_quantity === null))) {
                                echo '<div class="out-of-stock-notice" style="color: #e67e22; font-size: 0.85rem; margin: 4px 0; font-weight: 600;">En réapprovisionnement</div>';
                              }
                            ?>
                            <div class="cart-stock-notice" data-cart-key="<?php echo esc_attr($cart_item_key); ?>" style="display: none;"></div>
                          </div>
                        </div>
                      </td>
                      <td class="text-center align-middle product-price d-none d-md-table-cell">
                        <?php echo WC()->cart->get_product_price($_product); ?>
                      </td>
                      <td class="text-center align-middle d-none d-md-table-cell">
                        <?php if ($_product->is_sold_individually()) : ?>
                          <span class="qty-display">1</span>
                        <?php else : ?>
                          <div class="quantity-controls quantity-group d-flex align-items-center justify-content-center flex-column">
        <?php
          if ($_product->is_sold_individually()) {
            echo '<span class="qty-display">1</span>';
          } else {
            $backorders = $_product->get_backorders();
            $managing_stock = $_product->managing_stock();
            $stock_qty = $_product->get_stock_quantity();
            $current_qty = (int) $cart_item['quantity'];
        ?>
            <div class="custom-qty-box d-flex align-items-center justify-content-center"
                 data-cart-key="<?php echo esc_attr($cart_item_key); ?>"
                 data-stock-qty="<?php echo esc_attr($stock_qty !== null ? $stock_qty : 999999); ?>"
                 data-backorders="<?php echo esc_attr($backorders); ?>">
              <button type="button" class="qty-btn qty-minus" aria-label="Diminuer la quantité">
                <span>−</span>
              </button>
              <input type="number" class="qty-input mx-2" value="<?php echo $current_qty; ?>" min="1" data-cart-key="<?php echo esc_attr($cart_item_key); ?>">
              <button type="button" class="qty-btn qty-plus" aria-label="Augmenter la quantité">
                <span>+</span>
              </button>
            </div>
        <?php } ?>
                          </div>
                        <?php endif; ?>
                      </td>
                      <td class="text-end align-middle item-subtotal d-none d-md-table-cell">
                        <?php echo WC()->cart->get_product_subtotal($_product, $cart_item['quantity']); ?>
                      </td>
                      <td class="text-center align-middle d-none d-md-table-cell">
                        <button type="button" class="btn btn-link text-danger p-0 remove-item" 
                                data-cart-key="<?php echo esc_attr($cart_item_key); ?>"
                                title="<?php esc_attr_e('Retirer cet article', 'woocommerce'); ?>">
                          <i class="fas fa-trash-alt"></i>
                        </button>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php endif; ?>
        </div>
      </div>

      <!-- Section Code Promo -->
      <div class="card shadow-sm mb-4">
        <div class="card-body">
          <h3 class="h5 mb-3"><?php esc_html_e('Code promo', 'woocommerce'); ?></h3>
          <form class="promo-form d-flex flex-wrap gap-2 align-items-center" method="post" action="">
            <input type="text" name="coupon_code" class="form-control" placeholder="<?php esc_attr_e('Entrez votre code', 'woocommerce'); ?>" style="max-width:180px;">
            <button type="submit" class="btn btn-primary" name="apply_coupon" value="1"><?php esc_html_e('Appliquer', 'woocommerce'); ?></button>
            <?php wp_nonce_field('woocommerce-cart', 'woocommerce-cart-nonce'); ?>
            <input type="hidden" name="action" value="apply_coupon">
            <?php if ( WC()->cart->get_coupons() ) : ?>
              <div class="applied-coupons ms-2 d-flex gap-2 align-items-center flex-wrap">
                <?php foreach ( WC()->cart->get_coupons() as $code => $coupon ) : ?>
                  <span class="promo-active-code d-flex align-items-center" style="font-size:1.08em;font-weight:500;color:#222;background:#e2e4ea;border-radius:18px;padding:4px 18px 4px 14px;box-shadow:0 2px 8px 0 rgba(42,62,106,0.07);position:relative;">
                    <i class="fas fa-tag me-2" style="color:#2A3E6A;font-size:1em;"></i>
                    <?php echo esc_html( $coupon->get_code() ); ?>
                    <a href="<?php echo esc_url( add_query_arg( array( 'remove_coupon' => urlencode( $coupon->get_code() ) ), wc_get_cart_url() ) ); ?>" 
                       class="ms-2 remove-coupon-btn d-flex align-items-center justify-content-center" 
                       title="<?php esc_attr_e('Retirer ce code', 'woocommerce'); ?>"
                       style="width:22px;height:22px;border-radius:50%;background:#e9e9e9;color:#2A3E6A;font-size:1.1em;line-height:1;text-decoration:none;transition:background 0.15s;display:inline-flex;align-items:center;justify-content:center;margin-left:10px;">
                      <span style="font-size:1.2em;line-height:1;">&times;</span>
                    </a>
                  </span>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </form>
        </div>
      </div>
    </div>

    <!-- Right Column: Summary -->
    <div class="col-lg-4 col-12">
      <div class="card shadow-sm mb-4" id="cart-summary">
        <div class="card-body">
          <h2 class="h4 text-uppercase mb-4"><?php esc_html_e('Résumé de la commande', 'woocommerce'); ?></h2>
          <ul class="list-unstyled">
            <li class="d-flex justify-content-between">
              <span><?php esc_html_e('Total HT', 'woocommerce'); ?> :</span>
              <span class="cart-subtotal-amount"><?php echo wc_price(WC()->cart->get_subtotal()); ?></span>
            </li>
            <li class="d-flex justify-content-between">
              <span><?php esc_html_e('TVA', 'woocommerce'); ?> :</span>
              <span class="cart-tax-amount">
                <?php
                  $taxes = WC()->cart->get_taxes_total();
                  echo wc_price($taxes);
                ?>
              </span>
            </li>
            <li class="d-flex justify-content-between">
              <span><?php esc_html_e('Livraison', 'woocommerce'); ?> :</span>
              <span class="cart-shipping-amount">
                <?php
                  if ($cart_subtotal >= 550) {
                    echo esc_html__('Offerte', 'woocommerce');
                  } else {
                    echo wc_price(19); // Prix fixe de livraison
                  }
                ?>
              </span>
            </li>
            <?php if ( WC()->cart->get_coupons() ) : ?>
              <?php foreach ( WC()->cart->get_coupons() as $code => $coupon ) : ?>
                <li class="d-flex justify-content-between">
                  <span><?php echo esc_html( $coupon->get_code() ); ?> :</span>
                  <strong class="text-danger">- <?php echo wc_price( WC()->cart->get_coupon_discount_amount( $coupon->get_code() ) ); ?></strong>
                </li>
              <?php endforeach; ?>
            <?php endif; ?>
            <li class="d-flex justify-content-between border-top mt-2 pt-2">
              <span><?php esc_html_e('Total TTC', 'woocommerce'); ?> :</span>
              <strong class="cart-total"><?php echo wc_price(WC()->cart->total); ?></strong>
            </li>
          </ul>
          <?php if (!WC()->cart->is_empty()) : ?>
            <a href="<?php echo esc_url(wc_get_checkout_url()); ?>" 
               class="btn w-100 text-uppercase" 
               style="background-color: #2A3E6A; color: #fff; border: none;">
              <?php esc_html_e('Commander', 'woocommerce'); ?>
            </a>
          <?php endif; ?>
        </div>
      </div>

      <div class="card shadow-sm">
        <div class="card-header p-3" style="background-color: #2A3E6A; color: #fff;">
          <h5 class="mb-0"><?php esc_html_e('Livraison', 'woocommerce'); ?></h5>
        </div>
        <div class="card-body small text-muted">
          <?php
          // Vérifier s'il y a des produits en réapprovisionnement dans le panier
          $has_backorder_items = false;
          foreach (WC()->cart->get_cart() as $cart_item) {
            $_product = $cart_item['data'];
            $stock_status = $_product->get_stock_status();
            $stock_quantity = $_product->get_stock_quantity();
            $backorders = $_product->get_backorders();
            $current_qty = $cart_item['quantity'];

            // Vérifier si produit en réapprovisionnement
            if ($stock_status === 'outofstock' || $stock_status === 'onbackorder' ||
                (!$_product->is_in_stock() && ($stock_quantity === 0 || $stock_quantity === null))) {
              $has_backorder_items = true;
              break;
            }

            // Vérifier si quantité dépasse le stock avec backorders
            if (($backorders === 'yes' || $backorders === 'notify') &&
                $stock_quantity !== null && $current_qty > $stock_quantity) {
              $has_backorder_items = true;
              break;
            }
          }

          // Afficher le message de réapprovisionnement si nécessaire
          if ($has_backorder_items) {
            echo '<div class="delivery-backorder-notice" style="background: #FFF8E7; border-radius: 8px; padding: 10px 12px; margin-bottom: 12px; font-size: 0.8rem; color: #8B6914; display: flex; align-items: start; gap: 8px;">';
            echo '<i class="fa-solid fa-clock" style="color: #E9A825; font-size: 0.85rem; margin-top: 2px;"></i>';
            echo '<span style="line-height: 1.4;">Certains articles sont en réapprovisionnement. Délais de livraison susceptibles d\'être allongés.</span>';
            echo '</div>';
          }
          ?>
          <p class="mb-0">
            <strong><?php esc_html_e('Livraison', 'woocommerce'); ?></strong> <?php esc_html_e('à domicile', 'woocommerce'); ?> <br>
            <em><?php esc_html_e('1 à 4 jours ouvrables', 'woocommerce'); ?></em><br>
            <?php printf(esc_html__('Prix fixe : %s (offerte dès %s)', 'woocommerce'), wc_price(19), wc_price(550)); ?>
          </p>
        </div>
      </div>
    </div>
  </div>
</div>

<?php do_action('woocommerce_after_cart'); ?>

<script>
// Empêcher le zoom par pinch sur mobile
document.addEventListener('gesturestart', function (e) {
    e.preventDefault();
});

// Empêcher le zoom par double-tap
let lastTouchEnd = 0;
document.addEventListener('touchend', function (event) {
    const now = (new Date()).getTime();
    if (now - lastTouchEnd <= 300) {
        event.preventDefault();
    }
    lastTouchEnd = now;
}, false);

// Empêcher le zoom par wheel + ctrl
document.addEventListener('wheel', function(e) {
    if (e.ctrlKey) {
        e.preventDefault();
    }
}, { passive: false });

// Empêcher le zoom par clavier
document.addEventListener('keydown', function(e) {
    if ((e.ctrlKey || e.metaKey) && (e.keyCode === 61 || e.keyCode === 107 || e.keyCode === 173 || e.keyCode === 109 || e.keyCode === 187 || e.keyCode === 189)) {
        e.preventDefault();
    }
});

jQuery(function($) {
    let updateTimeout;
    let isUpdating = false;

    // Fonction pour afficher les messages
    function showMessage(message, type = 'success') {
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        $('#cart-messages').html(`
            <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `).show();

        // Auto-hide après 3 secondes
        setTimeout(() => {
            $('#cart-messages .alert').fadeOut();
        }, 3000);
    }

    // Fonction pour mettre à jour le panier
    function updateCart(cartKey, quantity, action = 'update') {
        if (isUpdating) return;
        isUpdating = true;

        $('#cart-loader').show();
        
        $.ajax({
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            type: 'POST',
            data: {
                action: 'update_cart_ajax',
                cart_key: cartKey,
                quantity: quantity,
                security: '<?php echo wp_create_nonce('ajax_cart_nonce'); ?>'
            },
            success: function(response) {
                if (response.success) {
                    // Mettre à jour tous les totaux du résumé
                    if (response.data.cart_totals) {
                        console.log('Full AJAX response:', response.data);
                        console.log('Cart totals:', response.data.cart_totals);
                        console.log('Debug info:', response.data.debug_info);
                        
                        // Total HT (subtotal) - utiliser classe spécifique
                        $('.cart-subtotal-amount').html(response.data.cart_totals.subtotal_display || response.data.cart_totals.subtotal);
                        
                        // TVA - utiliser classe spécifique
                        $('.cart-tax-amount').html(response.data.cart_totals.tax_display || response.data.cart_totals.tax);
                        
                        // Livraison - utiliser classe spécifique
                        var shippingValue = response.data.cart_totals.shipping_display || response.data.cart_totals.shipping;
                        console.log('Shipping value to update:', shippingValue);
                        $('.cart-shipping-amount').html(shippingValue);
                        console.log('Updated shipping amount');
                        
                        // Total TTC
                        $('.cart-total').html(response.data.cart_totals.total);
                    }

                    // Mettre à jour la barre de progression
                    if (response.data.progress !== undefined) {
                        $('.progress-bar').css('width', response.data.progress + '%');
                        $('.free-shipping-message').html(response.data.shipping_message);
                    }

                    // Si quantité = 0, supprimer la ligne
                    if (quantity === 0) {
                        $(`tr[data-cart-key="${cartKey}"]`).fadeOut(300, function() {
                            $(this).remove();
                            
                            // Vérifier si le panier est vide
                            if ($('#cart-items tr').length === 0) {
                                location.reload(); // Recharger pour afficher le panier vide
                            }
                        });
                    } else {
                        // Mettre à jour le sous-total de la ligne (desktop)
                        const $row = $(`tr[data-cart-key="${cartKey}"]`);
                        if (response.data.item_subtotal) {
                            $row.find('.item-subtotal').html(response.data.item_subtotal);
                            // Mettre à jour aussi le total mobile
                            $(`.item-total-mobile[data-cart-key="${cartKey}"]`).html(response.data.item_subtotal);
                        }
                    }
                    
                    // showMessage(response.data.message || 'Panier mis à jour', 'success');
                } else {
                    // Afficher le message d'erreur du serveur
                    showMessage(response.data || 'Erreur lors de la mise à jour', 'error');
                    console.error('Erreur serveur:', response);
                }
            },
            error: function(xhr, status, error) {
                console.error('Erreur AJAX:', {
                    status: status,
                    error: error,
                    response: xhr.responseText
                });
                showMessage('Erreur de connexion. Veuillez réessayer.', 'error');
            },
            complete: function() {
                isUpdating = false;
                $('#cart-loader').hide();
            }
        });
    }


  // Fonction pour mettre à jour l'affichage du stock
  function updateCartStockNotice(cartKey, currentQty) {
    const $qtyBox = $('.custom-qty-box[data-cart-key="' + cartKey + '"]');
    const stockQty = parseInt($qtyBox.data('stock-qty')) || 999999;
    const backorders = $qtyBox.data('backorders');
    const $plusBtn = $qtyBox.find('.qty-plus');
    const $notice = $('.cart-stock-notice[data-cart-key="' + cartKey + '"]');

    // Reset
    $plusBtn.removeClass('at-limit');
    $notice.hide().html('');

    // Si on dépasse le stock
    if (currentQty >= stockQty && stockQty < 999999) {
      if (backorders === 'no') {
        // Stock max atteint, mettre le bouton + en rouge
        $plusBtn.addClass('at-limit');
      } else if (backorders === 'yes' || backorders === 'notify') {
        // Afficher message de réapprovisionnement
        const inStock = stockQty;
        const toOrder = currentQty - stockQty;
        if (toOrder > 0) {
          $notice.html(inStock + ' en stock, ' + toOrder + ' en cours de réapprovisionnement').show();
        }
      }
    }
  }

  // Gestion des boutons + et - pour la quantité
  $(document).on('click', '.custom-qty-box .qty-btn', function() {
    const $box = $(this).closest('.custom-qty-box');
    const $input = $box.find('.qty-input');
    const cartKey = $box.data('cart-key');
    let qty = parseInt($input.val()) || 1;
    const isPlus = $(this).hasClass('qty-plus');
    const isMinus = $(this).hasClass('qty-minus');
    const stockQty = parseInt($box.data('stock-qty')) || 999999;
    const backorders = $box.data('backorders');
    let min = parseInt($input.attr('min')) || 1;

    if (isPlus) {
      // Bloquer si stock max atteint et backorders non autorisés
      if (qty >= stockQty && backorders === 'no') {
        return;
      }
      qty++;
    }

    if (isMinus && qty > min) {
      qty--;
    } else if (isMinus && qty <= min) {
      showMessage('Quantité minimale: ' + min, 'error');
      return;
    }

    $input.val(qty);
    updateCart(cartKey, qty);

    // Met à jour la couleur du bouton moins
    const $minusBtn = $box.find('.qty-minus');
    if (qty === 1) {
      $minusBtn.addClass('danger');
    } else {
      $minusBtn.removeClass('danger');
    }

    // Mettre à jour l'affichage du stock
    updateCartStockNotice(cartKey, qty);
  });

  // Gestion de la saisie directe dans le champ quantité
  $(document).on('change blur', '.qty-input', function() {
    const $input = $(this);
    const $box = $input.closest('.custom-qty-box');
    const cartKey = $input.data('cart-key');
    let qty = parseInt($input.val()) || 1;
    const min = parseInt($input.attr('min')) || 1;
    const stockQty = parseInt($box.data('stock-qty')) || 999999;
    const backorders = $box.data('backorders');

    // Validation des limites
    if (qty < min) {
      qty = min;
      $input.val(qty);
    }

    // Bloquer si dépasse le stock et backorders non autorisés
    if (qty > stockQty && backorders === 'no') {
      qty = stockQty;
      $input.val(qty);
    }

    // Mettre à jour l'affichage du stock
    updateCartStockNotice(cartKey, qty);

    updateCart(cartKey, qty);

    // Met à jour la couleur du bouton moins
    const $minusBtn = $box.find('.qty-minus');
    if (qty === 1) {
      $minusBtn.addClass('danger');
    } else {
      $minusBtn.removeClass('danger');
    }
  });

  // Sélectionner tout le texte quand on clique sur l'input
  $(document).on('click focus', '.qty-input', function() {
    $(this).select();
  });

  // Permettre la saisie de chiffres dans le champ quantité
  $(document).on('keydown', '.qty-input', function(e) {
    // Permettre les touches de contrôle
    if (e.keyCode === 8 || e.keyCode === 9 || e.keyCode === 13 || e.keyCode === 27 || 
        e.keyCode === 37 || e.keyCode === 38 || e.keyCode === 39 || e.keyCode === 40 ||
        e.keyCode === 46 || e.keyCode === 35 || e.keyCode === 36) {
      return true;
    }
    // Permettre Ctrl+A, Ctrl+C, Ctrl+V, Ctrl+X
    if ((e.ctrlKey || e.metaKey) && (e.keyCode === 65 || e.keyCode === 67 || e.keyCode === 86 || e.keyCode === 88)) {
      return true;
    }
    // Permettre seulement les chiffres (0-9)
    if (e.keyCode >= 48 && e.keyCode <= 57) {
      return true;
    }
    // Permettre les chiffres du pavé numérique
    if (e.keyCode >= 96 && e.keyCode <= 105) {
      return true;
    }
    // Bloquer tout le reste
    e.preventDefault();
    return false;
  });

  // Fonction pour mettre à jour le message de réapprovisionnement dans la section livraison
  function updateBackorderNotice() {
    try {
      let hasBackorder = false;

      // Parcourir tous les produits du panier
      $('.custom-qty-box').each(function() {
        const $box = $(this);
        const stockQty = parseInt($box.data('stock-qty')) || 999999;
        const backorders = $box.data('backorders');
        const $input = $box.find('.qty-input');
        const currentQty = parseInt($input.val()) || 1;

        // Vérifier si le produit est en réapprovisionnement
        // 1. Si le stock est à 0 ou très bas
        if (stockQty === 0 || stockQty === null) {
          hasBackorder = true;
          return false; // break
        }

        // 2. Si la quantité commandée dépasse le stock et backorders autorisés
        if ((backorders === 'yes' || backorders === 'notify') && currentQty > stockQty && stockQty < 999999) {
          hasBackorder = true;
          return false; // break
        }
      });

      // Afficher ou masquer le message
      const $notice = $('.delivery-backorder-notice');
      if (hasBackorder && $notice.length === 0) {
        // Créer le message s'il n'existe pas
        const noticeHtml = '<div class="delivery-backorder-notice" style="background: #FFF8E7; border-radius: 8px; padding: 10px 12px; margin-bottom: 12px; font-size: 0.8rem; color: #8B6914; display: flex; align-items: start; gap: 8px;">' +
          '<i class="fa-solid fa-clock" style="color: #E9A825; font-size: 0.85rem; margin-top: 2px;"></i>' +
          '<span style="line-height: 1.4;">Certains articles sont en réapprovisionnement. Délais de livraison susceptibles d\'être allongés.</span>' +
          '</div>';
        $('.card-body.small.text-muted p').first().before(noticeHtml);
      } else if (!hasBackorder && $notice.length > 0) {
        // Supprimer le message s'il existe
        $notice.remove();
      }
    } catch (error) {
      console.error('Erreur dans updateBackorderNotice:', error);
    }
  }

    // Gestion de la suppression
    $(document).on('click', '.remove-item', function(e) {
        e.preventDefault();
        const cartKey = $(this).data('cart-key');

        if (confirm('Êtes-vous sûr de vouloir supprimer cet article ?')) {
            updateCart(cartKey, 0);
            // Mettre à jour le message de réapprovisionnement après suppression
            setTimeout(updateBackorderNotice, 800);
        }
    });

    // Initialiser l'état des boutons au chargement
    function initializeButtonStates() {
        $('.custom-qty-box').each(function() {
            const $box = $(this);
            const $input = $box.find('.qty-input');
            const cartKey = $box.data('cart-key');
            const qty = parseInt($input.val()) || 1;
            const min = parseInt($input.attr('min')) || 1;

            // État du bouton moins
            const $minusBtn = $box.find('.qty-minus');
            if (qty <= min) {
                $minusBtn.addClass('danger');
            } else {
                $minusBtn.removeClass('danger');
            }

            // Initialiser l'affichage du stock
            updateCartStockNotice(cartKey, qty);
        });
    }

    // Initialiser au chargement
    initializeButtonStates();

    // Mettre à jour le message après chaque modification du panier
    $(document.body).on('updated_cart_totals', function() {
      setTimeout(function() {
        updateBackorderNotice();
        initializeButtonStates();
      }, 100);
    });

    // Mettre à jour aussi après les modifications de quantité
    $(document).on('change', '.qty-input', function() {
      setTimeout(updateBackorderNotice, 700);
    });

    // Empêcher la soumission du formulaire par défaut
    $(document).on('submit', '.woocommerce-cart-form', function(e) {
        e.preventDefault();
    });
});
</script>

<style>
.quantity-group {
  display: flex;
  align-items: center;
  background: none;
  border: none;
  box-shadow: none;
  padding: 0;
  gap: 0;
}
.quantity-group .btn {
  border: none;
  background: none;
  color: #888;
  width: 28px;
  height: 28px;
  min-width: 28px;
  min-height: 28px;
  padding: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.1rem;
  transition: color 0.15s;
  box-shadow: none;
}
.quantity-group .btn:hover, .quantity-group .btn:focus {
  color: #2A3E6A;
  background: none;
}
.quantity-group .qty-input {
  border: none;
  border-radius: 0;
  width: 32px;
  height: 28px;
  font-size: 1.1rem;
  text-align: center;
  outline: none;
  box-shadow: none;
  padding: 0;
  margin: 0 2px;
  background: none;
  color: #222;
}

.cart_item {
    transition: opacity 0.3s ease;
}

.cart_item.updating {
    opacity: 0.6;
}

#cart-loader {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    z-index: 1000;
}

.alert-container {
    position: sticky;
    top: 20px;
    z-index: 1050;
}

@media (max-width: 768px) {
    .container-fluid {
        padding-left: 8px !important;
        padding-right: 8px !important;
    }
    
    /* Corriger le problème de .pt-5 UNIQUEMENT sur mobile pour la page panier */
    body.woocommerce-cart .pt-5 {
        padding-top: 0 !important;
    }
    
    /* Rétablir le padding du footer UNIQUEMENT sur mobile */
    body.woocommerce-cart footer,
    body.woocommerce-cart .footer,
    body.woocommerce-cart #footer {
        margin-top: 3rem !important;
        padding-top: 3rem !important;
    }
    
    /* Assurer un espacement correct en bas de page sur mobile */
    body.woocommerce-cart .container-fluid:last-of-type {
        margin-bottom: 3rem !important;
    }
    
    /* Supprimer le padding excessif du titre de page sur mobile */
    .page-title,
    .entry-title,
    h1.page-title,
    .woocommerce-cart h1,
    .page-header {
        margin-top: 0 !important;
        padding-top: 0 !important;
    }
    
    body.woocommerce-cart .page-header,
    body.woocommerce-cart .entry-header {
        margin-top: 0 !important;
        padding-top: 0 !important;
    }
    
    .cart-progress {
        margin-left: 0;
        margin-right: 0;
        border-radius: 1.25rem !important;
    }
    
    .cart-progress .text-start {
        text-align: center !important;
    }
    
    .cart-progress .d-flex {
        flex-direction: column;
        gap: 10px;
    }
    
    .cart-progress .d-flex span:last-child {
        font-size: 0.85rem;
        text-align: center;
    }
    
    .table-responsive {
        border: none;
        box-shadow: none;
        overflow: visible;
    }
    
    .mobile-cart-item {
        border: 1px solid #e9ecef;
        border-radius: 12px;
        padding: 12px;
        margin-bottom: 12px;
        background: #fff;
        box-shadow: 0 1px 8px rgba(0,0,0,0.06);
        width: 100%;
    }
    
    .mobile-item-header {
        border-bottom: 1px solid #f0f0f0;
        padding-bottom: 10px;
        margin-bottom: 10px !important;
    }
    
    .cart-thumb-img-wrapper {
        width: 60px !important;
        height: 60px !important;
        border-radius: 8px;
    }
    
    .product-info-mobile {
        min-width: 0;
    }
    
    .product-name {
        font-size: 1rem;
        font-weight: 600;
        line-height: 1.3;
        margin-bottom: 6px !important;
        color: #2A3E6A;
    }
    
    .product-link {
        color: #2A3E6A;
        text-decoration: none;
    }
    
    .product-link:hover {
        color: #1a2d4a;
        text-decoration: underline;
    }
    
    .product-price-mobile {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 8px;
        font-size: 0.9rem;
    }
    
    .price-label {
        color: #666;
        font-size: 0.85rem;
    }
    
    .price-value {
        color: #2A3E6A;
        font-weight: 600;
    }
    
    .remove-btn-mobile .btn {
        padding: 6px 10px;
        font-size: 0.8rem;
        border-radius: 8px;
    }
    
    .mobile-item-controls {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 10px;
    }
    
    .quantity-label, .total-label {
        font-size: 0.85rem;
        font-weight: 600;
        color: #666;
        display: block;
        margin-bottom: 8px !important;
    }
    
    .mobile-cart-item .custom-qty-box {
        min-width: 90px !important;
        min-height: 32px !important;
        border: 1.2px solid #222;
        border-radius: 8px;
        padding: 1px 4px;
        background: #fff;
    }
    
    .mobile-cart-item .custom-qty-box .qty-btn {
        border: none;
        background: none;
        width: 20px !important;
        height: 20px !important;
        min-width: 20px !important;
        min-height: 20px !important;
        padding: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        color: #222;
        transition: color 0.15s;
        outline: none;
    }
    
    .mobile-cart-item .custom-qty-box .qty-btn span {
        font-size: 1rem;
        line-height: 1;
    }
    
    .mobile-cart-item .custom-qty-box .qty-btn.danger {
        color: #d32d2f !important;
    }
    
    .mobile-cart-item .custom-qty-box .qty-btn.disabled {
        color: #ccc !important;
        cursor: not-allowed;
        opacity: 0.5;
    }
    
    .mobile-cart-item .custom-qty-box .qty-input {
        font-size: 1rem;
        font-weight: 600;
        color: #222;
        min-width: 30px;
        width: 30px;
        text-align: center;
        border: none;
        background: transparent;
        outline: none;
        -moz-appearance: textfield;
        padding: 0;
        margin: 0 2px !important;
        cursor: text;
        pointer-events: auto;
    }
    
    .mobile-cart-item .custom-qty-box .qty-input:focus {
        background: rgba(42, 62, 106, 0.05);
        color: #222;
    }
    
    .mobile-cart-item .custom-qty-box .qty-input::-webkit-outer-spin-button,
    .mobile-cart-item .custom-qty-box .qty-input::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }
    
    .qty-display-mobile {
        background: #f8f9fa;
        padding: 8px 12px;
        border-radius: 10px;
        font-weight: 600;
        color: #2A3E6A;
        text-align: center;
    }
    
    .item-total-mobile {
        font-size: 1.2rem;
        font-weight: 700;
        color: #2A3E6A;
        background: #f0f8ff;
        padding: 8px 12px;
        border-radius: 10px;
        text-align: center;
    }
    
    .total-section {
        text-align: right;
    }
    
    .card {
        border-radius: 16px !important;
        margin-bottom: 20px !important;
        border: 1px solid #e9ecef;
    }
    
    .card-body {
        padding: 15px !important;
    }
    
    .card .h4 {
        font-size: 1.1rem;
        margin-bottom: 15px !important;
        margin-top: 0 !important;
        padding-top: 0 !important;
    }
    
    .container-fluid.my-5 {
        margin-top: 1rem !important;
        margin-bottom: 2rem !important;
    }
    
    .promo-form {
        align-items: stretch !important;
    }
    
    .promo-form .form-control {
        max-width: none !important;
        margin-bottom: 10px;
        border-radius: 10px;
        padding: 12px;
    }
    
    .promo-form .btn {
        width: 100%;
        border-radius: 10px;
        padding: 12px;
    }
    
    .applied-coupons {
        margin-left: 0 !important;
        margin-top: 10px;
        width: 100%;
    }
}

/* Stock notice styles */
.cart-stock-notice {
  color: #e67e22;
  font-size: 0.85rem;
  margin: 4px 0;
  font-weight: 600;
}

.qty-btn.at-limit {
  color: #d32d2f !important;
  cursor: not-allowed;
}

/* Custom quantity box styles */
/* Réduction du sélecteur de quantité */
.custom-qty-box {
  border: 1.2px solid #222;
  border-radius: 8px;
  padding: 1px 4px;
  background: #fff;
  min-width: 70px;
  min-height: 28px;
  box-sizing: border-box;
}
.custom-qty-box .qty-btn {
  border: none;
  background: none;
  width: 20px;
  height: 20px;
  min-width: 20px;
  min-height: 20px;
  padding: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1rem;
  color: #222;
  transition: color 0.15s;
  outline: none;
}
.custom-qty-box .qty-btn span {
  font-size: 1rem;
  line-height: 1;
}
.custom-qty-box .qty-btn.danger {
  color: #d32d2f !important;
}
.custom-qty-box .qty-btn.disabled {
  color: #ccc !important;
  cursor: not-allowed;
  opacity: 0.5;
}
.custom-qty-box .qty-input {
  font-size: 1rem;
  font-weight: 600;
  color: #222;
  min-width: 30px;
  width: 30px;
  text-align: center;
  border: none;
  background: transparent;
  outline: none;
  -moz-appearance: textfield;
  padding: 0;
  margin: 0;
  cursor: text;
  pointer-events: auto;
}
.custom-qty-box .qty-input:focus {
  background: rgba(42, 62, 106, 0.05);
  color: #222;
}
.custom-qty-box .qty-input::-webkit-outer-spin-button,
.custom-qty-box .qty-input::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}
</style>