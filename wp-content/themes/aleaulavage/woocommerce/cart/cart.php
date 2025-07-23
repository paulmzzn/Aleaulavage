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
do_action('woocommerce_before_cart'); ?>

<div class="cart-progress bg-light py-3 rounded-4">
<style>
.cart-progress {
  border-radius: 1.25rem !important;
  overflow: hidden;
}
</style>
  <div class="container">
    <div class="progress" style="height: 8px;">
      <?php 
        $target = 550;
        $cart_subtotal = WC()->cart->get_subtotal();
        $cart_tax_total = WC()->cart->get_taxes_total();
        $total_without_shipping = $cart_subtotal + $cart_tax_total; // Total TTC sans livraison
        $total_with_shipping = $total_without_shipping + 19; // Total TTC avec livraison
        $percent = $total_with_shipping > 0 ? min(100, ($total_with_shipping / $target) * 100) : 0;
      ?>
      <div class="progress-bar" role="progressbar" style="width: <?php echo esc_attr($percent); ?>%; background-color: #5899E2;"></div>
    </div>
    <div class="d-flex justify-content-between mt-2 small text-muted">
      <span class="free-shipping-message">
        <?php 
        $remaining = max(0, 550 - $total_with_shipping);
        if ($total_with_shipping >= 550) {
          echo esc_html__('Livraison offerte !', 'woocommerce');
        } else {
          echo esc_html__('Plus que ', 'woocommerce') . wc_price($remaining) . ' ' . esc_html__('pour profiter de la livraison offerte', 'woocommerce');
        }
        ?>
      </span>
      <span><?php esc_html_e('Étapes : Panier / Livraison / Paiement', 'woocommerce'); ?></span>
    </div>
  </div>
</div>

<div class="container my-5">
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
    <div class="col-lg-8">
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
            <div class="table-responsive">
              <table class="table cart-table mb-0">
                <thead class="table-light cart-table-head-rounded">
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
                    <tr class="cart_item align-middle" data-cart-key="<?php echo esc_attr($cart_item_key); ?>">
                      <td class="product-info align-middle">
                        <div class="d-flex align-items-center">
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
                            ?>
                          </div>
                        </div>
                      </td>
                      <td class="text-center align-middle product-price">
                        <?php echo WC()->cart->get_product_price($_product); ?>
                      </td>
                      <td class="text-center align-middle">
                        <?php if ($_product->is_sold_individually()) : ?>
                          <span class="qty-display">1</span>
                        <?php else : ?>
                          <div class="quantity-controls quantity-group d-flex align-items-center justify-content-center">
        <?php
          if ($_product->is_sold_individually()) {
            echo '<span class="qty-display">1</span>';
          } else {
            if ($_product->managing_stock()) {
              $max_qty = $_product->get_stock_quantity();
            } else {
              $max_qty = 20; // Limite raisonnable pour un select
            }
            $current_qty = (int) $cart_item['quantity'];
        ?>
            <div class="custom-qty-box d-flex align-items-center justify-content-center" data-cart-key="<?php echo esc_attr($cart_item_key); ?>" data-max="<?php echo $max_qty; ?>">
              <button type="button" class="qty-btn qty-minus" aria-label="Diminuer la quantité">
                <span>&minus;</span>
              </button>
              <input type="number" class="qty-input mx-2" value="<?php echo $current_qty; ?>" min="1" max="<?php echo $max_qty; ?>" data-cart-key="<?php echo esc_attr($cart_item_key); ?>">
              <button type="button" class="qty-btn qty-plus" aria-label="Augmenter la quantité">
                <span>+</span>
              </button>
            </div>
        <?php } ?>
                          </div>
                        <?php endif; ?>
                      </td>
                      <td class="text-end align-middle item-subtotal">
                        <?php echo WC()->cart->get_product_subtotal($_product, $cart_item['quantity']); ?>
                      </td>
                      <td class="text-center align-middle">
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
    <div class="col-lg-4">
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
                  if ($total_with_shipping >= 550) {
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
                        // Mettre à jour le sous-total de la ligne
                        const $row = $(`tr[data-cart-key="${cartKey}"]`);
                        if (response.data.item_subtotal) {
                            $row.find('.item-subtotal').html(response.data.item_subtotal);
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


  // Gestion des boutons + et - pour la quantité
  $(document).on('click', '.custom-qty-box .qty-btn', function() {
    const $box = $(this).closest('.custom-qty-box');
    const $input = $box.find('.qty-input');
    const cartKey = $box.data('cart-key');
    let qty = parseInt($input.val()) || 1;
    const isPlus = $(this).hasClass('qty-plus');
    const isMinus = $(this).hasClass('qty-minus');
    let max = parseInt($input.attr('max')) || 20;
    let min = parseInt($input.attr('min')) || 1;
    
    if (isPlus && qty < max) {
      qty++;
    } else if (isPlus && qty >= max) {
      // Feedback visuel quand on atteint la limite max
      showMessage('Quantité maximale atteinte: ' + max, 'error');
      return;
    }
    
    if (isMinus && qty > min) {
      qty--;
    } else if (isMinus && qty <= min) {
      // Feedback visuel quand on atteint la limite min
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
    
    // Met à jour la couleur du bouton plus si on atteint le max
    const $plusBtn = $box.find('.qty-plus');
    if (qty >= max) {
      $plusBtn.addClass('disabled').prop('disabled', true);
    } else {
      $plusBtn.removeClass('disabled').prop('disabled', false);
    }
  });

  // Gestion de la saisie directe dans le champ quantité
  $(document).on('change blur', '.qty-input', function() {
    const $input = $(this);
    const $box = $input.closest('.custom-qty-box');
    const cartKey = $input.data('cart-key');
    let qty = parseInt($input.val()) || 1;
    let originalQty = qty;
    const min = parseInt($input.attr('min')) || 1;
    const max = parseInt($input.attr('max')) || 20;
    
    // Validation des limites
    if (qty < min) {
      qty = min;
      showMessage('Quantité minimale: ' + min, 'error');
    }
    if (qty > max) {
      qty = max;
      showMessage('Quantité maximale disponible: ' + max, 'error');
    }
    
    // Seulement mettre à jour si la valeur a changé
    if (originalQty !== qty) {
      $input.val(qty);
    }
    
    updateCart(cartKey, qty);
    
    // Met à jour la couleur du bouton moins
    const $minusBtn = $box.find('.qty-minus');
    if (qty === 1) {
      $minusBtn.addClass('danger');
    } else {
      $minusBtn.removeClass('danger');
    }
    
    // Met à jour la couleur du bouton plus
    const $plusBtn = $box.find('.qty-plus');
    if (qty >= max) {
      $plusBtn.addClass('disabled').prop('disabled', true);
    } else {
      $plusBtn.removeClass('disabled').prop('disabled', false);
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

    // Gestion de la suppression
    $(document).on('click', '.remove-item', function(e) {
        e.preventDefault();
        const cartKey = $(this).data('cart-key');
        
        if (confirm('Êtes-vous sûr de vouloir supprimer cet article ?')) {
            updateCart(cartKey, 0);
        }
    });

    // Initialiser l'état des boutons au chargement
    function initializeButtonStates() {
        $('.custom-qty-box').each(function() {
            const $box = $(this);
            const $input = $box.find('.qty-input');
            const qty = parseInt($input.val()) || 1;
            const min = parseInt($input.attr('min')) || 1;
            const max = parseInt($input.attr('max')) || 20;
            
            // État du bouton moins
            const $minusBtn = $box.find('.qty-minus');
            if (qty <= min) {
                $minusBtn.addClass('danger');
            } else {
                $minusBtn.removeClass('danger');
            }
            
            // État du bouton plus
            const $plusBtn = $box.find('.qty-plus');
            if (qty >= max) {
                $plusBtn.addClass('disabled').prop('disabled', true);
            } else {
                $plusBtn.removeClass('disabled').prop('disabled', false);
            }
        });
    }
    
    // Initialiser au chargement
    initializeButtonStates();

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
    .quantity-controls {
        flex-direction: column !important;
    }
    
    .quantity-controls .mx-2 {
        margin: 0.25rem 0 !important;
    }
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