<?php

/**
 * Mini-cart
 *
 * Contains the markup for the mini-cart, used by the cart widget.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/cart/mini-cart.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 5.2.0
 */

defined('ABSPATH') || exit;

do_action('woocommerce_before_mini_cart'); ?>

<?php if (!WC()->cart->is_empty()) : ?>

  <div class="woocommerce-mini-cart cart_list product_list_widget list-group list-group-flush <?php echo esc_attr($args['list_class']); ?>">
    <?php
    do_action('woocommerce_before_mini_cart_contents');

    foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
      $_product   = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key);
      $product_id = apply_filters('woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key);

      if ($_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters('woocommerce_widget_cart_item_visible', true, $cart_item, $cart_item_key)) {
        $product_name      = apply_filters('woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key);
        $thumbnail         = apply_filters('woocommerce_cart_item_thumbnail', $_product->get_image(), $cart_item, $cart_item_key);
        $product_price     = apply_filters('woocommerce_cart_item_price', WC()->cart->get_product_price($_product), $cart_item, $cart_item_key);
        $product_permalink = apply_filters('woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink($cart_item) : '', $cart_item, $cart_item_key);
    ?>
        <div class="woocommerce-mini-cart-item list-group-item <?php echo esc_attr(apply_filters('woocommerce_mini_cart_item_class', 'mini_cart_item', $cart_item, $cart_item_key)); ?>">

          <div class="row">

            <div class="item-image col-3">
              <?php if (empty($product_permalink)) : ?>
                <?php echo $thumbnail; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
                ?>
              <?php else : ?>
                <a href="<?php echo esc_url($product_permalink); ?>">
                  <?php echo $thumbnail; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
                  ?>
                </a>
              <?php endif; ?>
            </div>

            <div class="item-name col-7">
              <?php if (empty($product_permalink)) : ?>
                <?php echo $product_name; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
                ?>
              <?php else : ?>
                <strong><a href="<?php echo esc_url($product_permalink); ?>">
                  <?php echo $product_name; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
                  ?>
                </a></strong>
              <?php endif; ?>
              <div class="item-quantity">
                <?php echo wc_get_formatted_cart_item_data($cart_item); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                ?>
                <?php
                // Afficher "En réapprovisionnement" si le produit est en rupture ou en backorder
                $stock_status = $_product->get_stock_status();
                $stock_quantity = $_product->get_stock_quantity();

                // Afficher si hors stock OU si stock est 0/null et pas en stock
                if ($stock_status === 'outofstock' || $stock_status === 'onbackorder' || (!$_product->is_in_stock() && ($stock_quantity === 0 || $stock_quantity === null))) {
                  echo '<div class="out-of-stock-notice" style="color: #e67e22; font-size: 0.85rem; margin: 4px 0; font-weight: 600;">En réapprovisionnement</div>';
                }
                ?>
                <?php
                // Déterminer le stock disponible pour la notice dynamique
                $backorders = $_product->get_backorders();
                $managing_stock = $_product->managing_stock();
                $stock_qty = $_product->get_stock_quantity();
                ?>
                <div class="mini-cart-stock-notice" data-cart-key="<?php echo esc_attr($cart_item_key); ?>" style="display: none;"></div>

                <div class="d-flex align-items-center justify-content-between mt-2">
                  <div class="mini-cart-quantity-box d-flex align-items-center"
                       data-cart-key="<?php echo esc_attr($cart_item_key); ?>"
                       data-stock-qty="<?php echo esc_attr($stock_qty !== null ? $stock_qty : 999999); ?>"
                       data-backorders="<?php echo esc_attr($backorders); ?>">
                    <button type="button" class="mini-qty-btn mini-qty-minus" data-cart-key="<?php echo esc_attr($cart_item_key); ?>">
                      <span>−</span>
                    </button>
                    <input type="number" class="mini-qty-input mx-2"
                           value="<?php echo esc_attr($cart_item['quantity']); ?>"
                           min="1"
                           data-cart-key="<?php echo esc_attr($cart_item_key); ?>">
                    <button type="button" class="mini-qty-btn mini-qty-plus" data-cart-key="<?php echo esc_attr($cart_item_key); ?>">
                      <span>+</span>
                    </button>
                  </div>
                  <span class="quantity-times">×</span>
                  <span class="quantity-price"><?php echo $product_price; ?></span>
                </div>
              </div>
            </div>



            <div class="remove col-2 text-end">
              <?php echo apply_filters( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                'woocommerce_cart_item_remove_link',
                sprintf(
                  '<a href="%s" class="remove_from_cart_button text-danger" aria-label="%s" data-product_id="%s" data-cart_item_key="%s" data-product_sku="%s"><i class="fa-regular fa-trash-can"></i></a>',
                  esc_url(wc_get_cart_remove_url($cart_item_key)),
                  esc_attr__('Remove this item', 'woocommerce'),
                  esc_attr($product_id),
                  esc_attr($cart_item_key),
                  esc_attr($_product->get_sku())
                ),
                $cart_item_key
              );
              ?>
            </div>

          </div>
          <!--row-->

        </div>
    <?php
      }
    }

    do_action('woocommerce_mini_cart_contents');
    ?>
  </div>

<?php else : ?>

  <p class="woocommerce-mini-cart__empty-message alert alert-info m-3"><?php esc_html_e('No products in the cart.', 'woocommerce'); ?></p>

<?php endif; ?>

<?php do_action('woocommerce_after_mini_cart'); ?>

<style>
.mini-cart-quantity-box {
  background: #fff;
  border: 1.2px solid #222;
  border-radius: 6px;
  padding: 2px 6px;
}

.mini-qty-btn {
  background: transparent;
  border: none;
  color: #222;
  cursor: pointer;
  padding: 0;
  width: 18px;
  height: 18px;
  font-size: 0.9rem;
  transition: color 0.2s;
  display: flex;
  align-items: center;
  justify-content: center;
  line-height: 1;
  font-weight: 400;
}

.mini-qty-btn span {
  font-size: 0.9rem;
  font-weight: 400;
}

.mini-qty-btn:hover {
  color: #5899E2;
}

.mini-qty-btn:disabled {
  color: #ccc;
  cursor: not-allowed;
}

.mini-qty-input {
  font-weight: 600;
  font-size: 0.85rem;
  width: 28px;
  text-align: center;
  border: none;
  background: transparent;
  outline: none;
  -moz-appearance: textfield;
  padding: 0;
  margin: 0 4px !important;
}

.mini-qty-input::-webkit-outer-spin-button,
.mini-qty-input::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}

.mini-qty-input:focus {
  background: rgba(88, 153, 226, 0.1);
  border-radius: 3px;
}

.quantity-times {
  color: #666;
  font-size: 0.85rem;
  margin: 0 6px;
}

.quantity-price {
  font-weight: 600;
  color: #2A3E6A;
  font-size: 0.9rem;
}

.mini-cart-loading {
  opacity: 0.5;
  pointer-events: none;
}

.mini-qty-btn.at-limit {
  color: #d32d2f !important;
  cursor: not-allowed;
}

.mini-cart-stock-notice {
  color: #e67e22;
  font-size: 0.85rem;
  margin: 4px 0;
  font-weight: 600;
}
</style>

<script>
jQuery(document).ready(function($) {
  // Fonction pour mettre à jour la quantité
  function updateMiniCartQuantity(cartKey, newQty) {
    var $cartItem = $('.mini-cart-quantity-box[data-cart-key="' + cartKey + '"]').closest('.woocommerce-mini-cart-item');
    $cartItem.addClass('mini-cart-loading');

    $.ajax({
      url: wc_add_to_cart_params.ajax_url,
      type: 'POST',
      data: {
        action: 'update_mini_cart_quantity',
        cart_item_key: cartKey,
        quantity: newQty,
        security: '<?php echo wp_create_nonce('update_mini_cart_quantity'); ?>'
      },
      success: function(response) {
        if (response.success) {
          $(document.body).trigger('wc_fragment_refresh');
        } else {
          alert(response.data || 'Erreur lors de la mise à jour');
          $cartItem.removeClass('mini-cart-loading');
        }
      },
      error: function() {
        alert('Erreur de connexion');
        $cartItem.removeClass('mini-cart-loading');
      }
    });
  }

  // Fonction pour mettre à jour l'état des boutons et afficher les messages
  function updateStockNotice(cartKey, currentQty) {
    var $qtyBox = $('.mini-cart-quantity-box[data-cart-key="' + cartKey + '"]');
    var stockQty = parseInt($qtyBox.data('stock-qty')) || 999999;
    var backorders = $qtyBox.data('backorders');
    var $plusBtn = $qtyBox.find('.mini-qty-plus');
    var $notice = $('.mini-cart-stock-notice[data-cart-key="' + cartKey + '"]');

    // Reset
    $plusBtn.removeClass('at-limit');
    $notice.hide().html('');

    // Si on dépasse le stock
    if (currentQty >= stockQty && stockQty < 999999) {
      if (backorders === 'no') {
        // Stock max atteint, désactiver le bouton +
        $plusBtn.addClass('at-limit');
      } else if (backorders === 'yes' || backorders === 'notify') {
        // Afficher message de réapprovisionnement
        var inStock = stockQty;
        var toOrder = currentQty - stockQty;
        if (toOrder > 0) {
          $notice.html(inStock + ' en stock, ' + toOrder + ' en cours de réapprovisionnement').show();
        }
      }
    }
  }

  // Gérer les clics sur les boutons + et -
  $(document).on('click', '.mini-qty-btn', function(e) {
    e.preventDefault();

    var $btn = $(this);
    var cartKey = $btn.data('cart-key');
    var $qtyBox = $btn.closest('.mini-cart-quantity-box');
    var $qtyInput = $qtyBox.find('.mini-qty-input');
    var currentQty = parseInt($qtyInput.val()) || 1;
    var stockQty = parseInt($qtyBox.data('stock-qty')) || 999999;
    var backorders = $qtyBox.data('backorders');
    var newQty = currentQty;

    if ($btn.hasClass('mini-qty-plus')) {
      newQty = currentQty + 1;

      // Bloquer si stock max atteint et backorders non autorisés
      if (currentQty >= stockQty && backorders === 'no') {
        return;
      }
    } else if ($btn.hasClass('mini-qty-minus')) {
      newQty = Math.max(0, currentQty - 1);
    }

    if (newQty === currentQty) {
      return;
    }

    updateMiniCartQuantity(cartKey, newQty);
  });

  // Gérer la modification manuelle de la quantité
  var inputTimeout;
  $(document).on('change blur', '.mini-qty-input', function(e) {
    clearTimeout(inputTimeout);
    var $input = $(this);
    var cartKey = $input.data('cart-key');
    var $qtyBox = $input.closest('.mini-cart-quantity-box');
    var newQty = parseInt($input.val()) || 1;
    var minQty = parseInt($input.attr('min')) || 1;
    var stockQty = parseInt($qtyBox.data('stock-qty')) || 999999;
    var backorders = $qtyBox.data('backorders');

    // Valider la quantité minimale
    if (newQty < minQty) {
      newQty = minQty;
      $input.val(newQty);
    }

    // Bloquer si dépasse le stock et backorders non autorisés
    if (newQty > stockQty && backorders === 'no') {
      newQty = stockQty;
      $input.val(newQty);
    }

    // Mettre à jour l'affichage
    updateStockNotice(cartKey, newQty);

    inputTimeout = setTimeout(function() {
      updateMiniCartQuantity(cartKey, newQty);
    }, 500);
  });

  // Sélectionner tout le texte au focus
  $(document).on('focus', '.mini-qty-input', function() {
    $(this).select();
  });

  // Mettre à jour les notices au chargement
  $(document).on('input', '.mini-qty-input', function() {
    var $input = $(this);
    var cartKey = $input.data('cart-key');
    var currentQty = parseInt($input.val()) || 1;
    updateStockNotice(cartKey, currentQty);
  });

  // Initialiser les notices au chargement
  $('.mini-qty-input').each(function() {
    var $input = $(this);
    var cartKey = $input.data('cart-key');
    var currentQty = parseInt($input.val()) || 1;
    updateStockNotice(cartKey, currentQty);
  });

  // Empêcher la saisie de caractères non-numériques
  $(document).on('keypress', '.mini-qty-input', function(e) {
    // Autoriser uniquement les chiffres (codes 48-57)
    var charCode = (e.which) ? e.which : e.keyCode;
    if (charCode > 31 && (charCode < 48 || charCode > 57)) {
      e.preventDefault();
      return false;
    }
    return true;
  });
});
</script>
