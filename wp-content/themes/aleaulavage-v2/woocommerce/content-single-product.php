<?php
/**
 * Template override: single product content
 *
 * Based on old Aleaulavage theme structure
 */

defined('ABSPATH') || exit;
global $product;

/** Notices & mot de passe */
do_action('woocommerce_before_single_product');
if (post_password_required()) {
    echo get_the_password_form();
    return;
}
?>

<div id="product-<?php the_ID(); ?>" <?php wc_product_class('', $product); ?>>
  <div class="product-container">
    <!-- COLONNE GAUCHE : GALERIE + DESCRIPTION -->
    <div class="product-left-column">
      <!-- GALERIE PRODUIT -->
      <div class="product-gallery">
      <div id="selected-color-badge" class="selected-color-badge" style="display:none;">
        <span class="color-dot"></span>
        <span class="color-tooltip">La photo n'affiche pas forcément la couleur sélectionnée</span>
      </div>

      <?php
      /**
       * Sale Flash Badge & Stock Status Badges
       */
      if ($product->is_on_sale()) {
          echo '<span class="onsale">Promo&nbsp;!</span>';
      }

      // Get stock status
      $is_in_stock = $product->is_in_stock();
      $is_on_backorder = $product->is_on_backorder();

      if (!$is_in_stock) {
          echo '<span class="stock-badge backorder"><i class="fa-solid fa-clock me-1"></i>Réappro.</span>';
      } elseif ($is_on_backorder) {
          echo '<span class="stock-badge backorder"><i class="fa-solid fa-clock me-1"></i>Réappro.</span>';
      }

      // Récupérer toutes les images du produit
      $attachment_ids = $product->get_gallery_image_ids();
      $main_image_id = $product->get_image_id();
      
      // Construire le tableau complet des images (image principale + galerie)
      $all_images = array();
      if ($main_image_id) {
          $all_images[] = $main_image_id;
      }
      if (!empty($attachment_ids)) {
          $all_images = array_merge($all_images, $attachment_ids);
      }
      ?>

      <!-- Miniatures à gauche (si plusieurs images) -->
      <?php if (count($all_images) > 1): ?>
      <div class="product-thumbnails">
        <?php foreach ($all_images as $index => $image_id): ?>
          <div class="thumbnail-item <?php echo $index === 0 ? 'active' : ''; ?>" data-image-id="<?php echo esc_attr($image_id); ?>">
            <?php echo wp_get_attachment_image($image_id, 'thumbnail'); ?>
          </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>

      <!-- Image principale au centre -->
      <div class="product-main-image">
        <?php if (!empty($all_images)): ?>
          <?php foreach ($all_images as $index => $image_id): ?>
            <div class="main-image-item <?php echo $index === 0 ? 'active' : ''; ?>" data-image-id="<?php echo esc_attr($image_id); ?>">
              <div class="image-zoom-container">
                <?php echo wp_get_attachment_image($image_id, 'full', false, array('class' => 'zoomable-image')); ?>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <?php echo wc_placeholder_img('full'); ?>
        <?php endif; ?>
      </div>

      <!-- Lightbox overlay -->
      <div id="image-lightbox" class="image-lightbox" style="display:none;">
        <span class="lightbox-close">&times;</span>
        <button class="lightbox-prev">&#10094;</button>
        <button class="lightbox-next">&#10095;</button>
        <img class="lightbox-content" src="" alt="">
        <div class="lightbox-counter"></div>
      </div>

      <script>
      // JavaScript pour la sélection des miniatures et le zoom
      document.addEventListener('DOMContentLoaded', function() {
        var thumbnails = document.querySelectorAll('.product-thumbnails .thumbnail-item');
        var mainImages = document.querySelectorAll('.product-main-image .main-image-item');
        var allImages = <?php echo json_encode(array_map(function($id) { return wp_get_attachment_url($id); }, $all_images)); ?>;
        var currentImageIndex = 0;

        thumbnails.forEach(function(thumbnail) {
          thumbnail.addEventListener('click', function() {
            var imageId = this.getAttribute('data-image-id');

            // Retirer la classe active de toutes les miniatures et images
            thumbnails.forEach(function(t) { t.classList.remove('active'); });
            mainImages.forEach(function(img) { img.classList.remove('active'); });

            // Ajouter la classe active à la miniature cliquée
            this.classList.add('active');

            // Afficher l'image correspondante
            var targetImage = document.querySelector('.product-main-image .main-image-item[data-image-id="' + imageId + '"]');
            if (targetImage) {
              targetImage.classList.add('active');
              initializeZoom(targetImage.querySelector('.image-zoom-container'));
            }
          });
        });

        // Fonction d'initialisation du zoom
        function initializeZoom(container) {
          if (!container) return;
          
          var img = container.querySelector('.zoomable-image');
          if (!img) return;

          container.addEventListener('mousemove', function(e) {
            var rect = container.getBoundingClientRect();
            var x = e.clientX - rect.left;
            var y = e.clientY - rect.top;
            
            var xPercent = (x / rect.width) * 100;
            var yPercent = (y / rect.height) * 100;
            
            img.style.transformOrigin = xPercent + '% ' + yPercent + '%';
            img.style.transform = 'scale(2)';
          });

          container.addEventListener('mouseleave', function() {
            img.style.transform = 'scale(1)';
          });

          // Clic pour ouvrir en lightbox
          container.addEventListener('click', function() {
            // Trouver l'index de l'image active
            var activeItem = container.closest('.main-image-item');
            var allItems = document.querySelectorAll('.product-main-image .main-image-item');
            currentImageIndex = Array.from(allItems).indexOf(activeItem);
            
            openLightbox(currentImageIndex);
          });
        }

        // Initialiser le zoom pour l'image active au chargement
        var activeImage = document.querySelector('.product-main-image .main-image-item.active');
        if (activeImage) {
          initializeZoom(activeImage.querySelector('.image-zoom-container'));
        }

        // Lightbox functions
        var lightbox = document.getElementById('image-lightbox');
        var lightboxImg = document.querySelector('.lightbox-content');
        var closeBtn = document.querySelector('.lightbox-close');
        var prevBtn = document.querySelector('.lightbox-prev');
        var nextBtn = document.querySelector('.lightbox-next');
        var counter = document.querySelector('.lightbox-counter');
        var header = document.querySelector('header, .header, #masthead');

        function openLightbox(index) {
          currentImageIndex = index;
          lightboxImg.src = allImages[currentImageIndex];
          updateCounter();
          lightbox.style.display = 'flex';
          document.body.style.overflow = 'hidden';
          if (header) header.style.display = 'none';
          
          // Mise à jour de la visibilité des boutons
          updateNavigationButtons();
        }

        function closeLightbox() {
          lightbox.style.display = 'none';
          document.body.style.overflow = 'auto';
          if (header) header.style.display = '';
        }

        function updateCounter() {
          if (allImages.length > 1) {
            counter.textContent = (currentImageIndex + 1) + ' / ' + allImages.length;
          }
        }

        function updateNavigationButtons() {
          if (allImages.length <= 1) {
            prevBtn.style.display = 'none';
            nextBtn.style.display = 'none';
          } else {
            prevBtn.style.display = 'flex';
            nextBtn.style.display = 'flex';
          }
        }

        function showPrevImage() {
          currentImageIndex = (currentImageIndex - 1 + allImages.length) % allImages.length;
          lightboxImg.src = allImages[currentImageIndex];
          updateCounter();
        }

        function showNextImage() {
          currentImageIndex = (currentImageIndex + 1) % allImages.length;
          lightboxImg.src = allImages[currentImageIndex];
          updateCounter();
        }

        // Event listeners
        if (closeBtn) {
          closeBtn.addEventListener('click', closeLightbox);
        }

        if (prevBtn) {
          prevBtn.addEventListener('click', showPrevImage);
        }

        if (nextBtn) {
          nextBtn.addEventListener('click', showNextImage);
        }

        // Fermer en cliquant en dehors de l'image
        lightbox.addEventListener('click', function(e) {
          if (e.target === lightbox) {
            closeLightbox();
          }
        });

        // Navigation au clavier
        document.addEventListener('keydown', function(e) {
          if (lightbox.style.display === 'flex') {
            if (e.key === 'Escape') {
              closeLightbox();
            } else if (e.key === 'ArrowLeft') {
              showPrevImage();
            } else if (e.key === 'ArrowRight') {
              showNextImage();
            }
          }
        });
      });
      </script>
    </div>

      <!-- DESCRIPTION PRINCIPALE -->
      <?php
        $product_description = trim(strip_tags(apply_filters('the_content', get_post_field('post_content', $product->get_id()))));
        if (!empty($product_description)) :
      ?>
      <div class="product-main-description" style="width:100%;max-width:none;margin:0;padding:32px 28px 24px 28px;background:#fff;border:1px solid #e3e5e8;border-radius:8px;box-shadow:0 2px 6px rgba(0,0,0,0.05);">
        <h2>Description du produit</h2>
        <?php if (wc_product_sku_enabled() && ($product->get_sku() || $product->is_type('variable'))) : ?>
          <div class="product-sku" style="font-size:14px;color:#6c7a89;margin-bottom:10px;">
            <strong>UGS :</strong> <span><?php echo esc_html($product->get_sku() ? $product->get_sku() : __('N/A', 'woocommerce')); ?></span>
          </div>
        <?php endif; ?>
        <?php echo apply_filters('the_content', get_post_field('post_content', $product->get_id())); ?>
      </div>
      <?php endif; ?>
    </div><!-- /.product-left-column -->

    <!-- COLONNE DROITE : CARTE D'ACHAT & INFOS -->
    <aside class="product-purchase-card">
      <!-- TITRE PRODUIT -->
      <h1 class="product-title"><?php the_title(); ?></h1>
      <?php if (wc_product_sku_enabled() && ($product->get_sku() || $product->is_type('variable'))) : ?>
        <div class="product-sku" style="font-size:14px;color:#6c7a89;margin-bottom:10px;">
          <strong>UGS :</strong> <span><?php echo esc_html($product->get_sku() ? $product->get_sku() : __('N/A', 'woocommerce')); ?></span>
        </div>
      <?php endif; ?>

      <!-- PRIX + VARIATIONS + QTY + AJOUT AU PANIER + OFFRES VRAC -->
      <?php
      // Bulk pricing will be added via filter hook
      $has_bulk_pricing = false;
      $min_bulk_price = null;
      ?>

      <?php if ($product->is_type('variable')) : ?>
        <div class="purchase-header">
          <span class="price-custom">
            <?php
            if ($has_bulk_pricing && $min_bulk_price !== null && $min_bulk_price > 0) {
                echo '<span class="from-price">à partir de </span>';
                echo wc_price($min_bulk_price);
            } else {
                echo $product->get_price_html();
            }
            ?>
          </span>
          <button class="aleaulavage-wishlist-btn wishlist-btn<?php echo is_product_in_wishlist($product->get_id()) ? ' active' : ''; ?>" data-product-id="<?php echo $product->get_id(); ?>" aria-label="Ajouter aux favoris">
            <i class="<?php echo is_product_in_wishlist($product->get_id()) ? 'fa-solid' : 'fa-regular'; ?> fa-heart"></i>
          </button>
        </div>

        <!-- Message custom ajout au panier -->
        <div id="custom-cart-notice" style="display: none; background: #f8e7c2; color: #2A3E6A; border: 1.5px solid #e2c48a; border-radius: 6px; padding: 10px 15px; margin-bottom: 12px; font-size: 13px; position: relative;">
          <i class="fa-solid fa-check-circle" style="color: #2A3E6A; margin-right: 8px;"></i>
          <span id="custom-cart-notice-text"></span>
        </div>

        <?php woocommerce_variable_add_to_cart(); ?>

        <script>
        // Message custom ajout au panier pour produits variables
        document.addEventListener('DOMContentLoaded', function() {
          jQuery(document.body).on('added_to_cart', function(event, fragments, cart_hash, button) {
            var productName = '<?php echo esc_js($product->get_name()); ?>';
            var qtyInput = document.querySelector('input.qty, input[type="number"]');
            var quantity = qtyInput ? qtyInput.value : 1;

            var notice = document.getElementById('custom-cart-notice');
            var noticeText = document.getElementById('custom-cart-notice-text');

            if (notice && noticeText) {
              noticeText.innerHTML = quantity + ' × «' + productName + '» ajouté' + (quantity > 1 ? 's' : '') + ' au panier';
              notice.style.display = 'block';

              setTimeout(function() {
                notice.style.opacity = '0';
                notice.style.transition = 'opacity 0.5s';
                setTimeout(function() {
                  notice.style.display = 'none';
                  notice.style.opacity = '1';
                }, 500);
              }, 5000);
            }
          });

          // Add +/- buttons to variable product quantity field
          function addQuantityButtons() {
            var qtyField = document.querySelector('.variations_form .quantity');
            if (!qtyField || qtyField.querySelector('.minus')) {
              return; // Already has buttons or doesn't exist
            }

            var input = qtyField.querySelector('input.qty');
            if (!input) return;

            var min = parseInt(input.getAttribute('min')) || 1;
            var max = parseInt(input.getAttribute('max')) || 9999;

            // Create minus button
            var minusBtn = document.createElement('button');
            minusBtn.type = 'button';
            minusBtn.className = 'minus';
            minusBtn.setAttribute('aria-label', 'Diminuer la quantité');
            minusBtn.textContent = '−';

            // Create plus button
            var plusBtn = document.createElement('button');
            plusBtn.type = 'button';
            plusBtn.className = 'plus';
            plusBtn.setAttribute('aria-label', 'Augmenter la quantité');
            plusBtn.textContent = '+';

            // Wrap input with buttons
            input.parentNode.insertBefore(minusBtn, input);
            input.parentNode.appendChild(plusBtn);

            // Add event listeners
            minusBtn.addEventListener('click', function(e) {
              e.preventDefault();
              var currentVal = parseInt(input.value) || min;
              if (currentVal > min) {
                input.value = currentVal - 1;
                input.dispatchEvent(new Event('change', { bubbles: true }));
              }
            });

            plusBtn.addEventListener('click', function(e) {
              e.preventDefault();
              var currentVal = parseInt(input.value) || min;
              if (currentVal < max) {
                input.value = currentVal + 1;
                input.dispatchEvent(new Event('change', { bubbles: true }));
              }
            });
          }

          // Initialize buttons on load
          addQuantityButtons();

          // Store original image for reset
          var originalImageSrc = null;
          var originalImageSrcset = null;
          var mainImageContainer = document.querySelector('.product-main-image .main-image-item.active img');
          if (mainImageContainer) {
            originalImageSrc = mainImageContainer.src;
            originalImageSrcset = mainImageContainer.srcset || '';
          }

          // Re-initialize when variation changes (WooCommerce triggers this event)
          jQuery('.variations_form').on('found_variation', function(event, variation) {
            setTimeout(addQuantityButtons, 100);

            // Update price
            var priceHtml = document.querySelector('.purchase-header .price-custom');
            if (priceHtml && variation.price_html) {
              priceHtml.innerHTML = variation.price_html;
            }

            // Update main image if variation has an image
            if (variation.image && variation.image.full_src) {
              var mainImageContainer = document.querySelector('.product-main-image .main-image-item.active img');
              if (mainImageContainer) {
                mainImageContainer.src = variation.image.full_src;
                mainImageContainer.srcset = variation.image.srcset || '';
              }
            }

            // Handle color badge for color attribute
            var colorBadge = document.getElementById('selected-color-badge');
            var colorDot = colorBadge ? colorBadge.querySelector('.color-dot') : null;

            // Check if there's a color attribute (pa_color, pa_couleur, etc.)
            var colorValue = null;
            var colorHex = null;

            if (variation.attributes) {
              // Try different color attribute names (singular and plural)
              var colorAttrNames = ['attribute_pa_color', 'attribute_pa_couleur', 'attribute_pa_couleurs', 'attribute_color', 'attribute_couleur', 'attribute_couleurs'];
              for (var i = 0; i < colorAttrNames.length; i++) {
                if (variation.attributes[colorAttrNames[i]]) {
                  colorValue = variation.attributes[colorAttrNames[i]];
                  break;
                }
              }
            }

            // If we found a color, try to determine its hex value
            if (colorValue) {
              // Common color mappings
              var colorMap = {
                'noir': '#000000',
                'black': '#000000',
                'blanc': '#FFFFFF',
                'white': '#FFFFFF',
                'rouge': '#FF0000',
                'red': '#FF0000',
                'bleu': '#0000FF',
                'blue': '#0000FF',
                'bleu clair': '#87CEEB',
                'light blue': '#87CEEB',
                'vert': '#00FF00',
                'green': '#00FF00',
                'jaune': '#FFFF00',
                'yellow': '#FFFF00',
                'orange': '#FFA500',
                'violet': '#800080',
                'purple': '#800080',
                'rose': '#FFC0CB',
                'pink': '#FFC0CB',
                'gris': '#808080',
                'gray': '#808080',
                'grey': '#808080',
                'marron': '#8B4513',
                'brown': '#8B4513'
              };

              var colorLower = colorValue.toLowerCase().trim();
              colorHex = colorMap[colorLower];

              // If hex not found in map, check if the value itself is a hex color
              if (!colorHex && colorValue.match(/^#[0-9A-Fa-f]{6}$/)) {
                colorHex = colorValue;
              }

              // Show the color badge
              if (colorBadge && colorDot && colorHex) {
                colorDot.style.backgroundColor = colorHex;
                colorBadge.style.display = 'flex';
              }
            } else {
              // No color selected, hide badge
              if (colorBadge) {
                colorBadge.style.display = 'none';
              }
            }
          });

          // Restore original image and hide color badge when variation is reset
          jQuery('.variations_form').on('reset_data', function() {
            // Restore original image
            var mainImageContainer = document.querySelector('.product-main-image .main-image-item.active img');
            if (mainImageContainer && originalImageSrc) {
              mainImageContainer.src = originalImageSrc;
              mainImageContainer.srcset = originalImageSrcset;
            }

            // Hide color badge
            var colorBadge = document.getElementById('selected-color-badge');
            if (colorBadge) {
              colorBadge.style.display = 'none';
            }
          });
        });
        </script>
      <?php else : ?>
        <?php
        // Vérifier le stock disponible et la quantité déjà dans le panier
        $stock_quantity = $product->get_stock_quantity();
        $is_manage_stock = $product->managing_stock();
        $cart_qty = 0;

        // Calculer la quantité déjà dans le panier
        if (WC()->cart) {
            foreach (WC()->cart->get_cart() as $cart_item) {
                if ($cart_item['product_id'] == $product->get_id()) {
                    $cart_qty += $cart_item['quantity'];
                }
            }
        }

        // Déterminer s'il y a du stock disponible
        $available_stock = null;
        $is_out_of_stock = false;

        if ($is_manage_stock && $stock_quantity !== null) {
            $available_stock = $stock_quantity - $cart_qty;
            $is_out_of_stock = $available_stock <= 0;
        } else {
            $is_out_of_stock = !$product->is_in_stock();
        }

        // Vérifier si les backorders sont autorisés
        $backorders = $product->get_backorders();
        $backorders_allowed = ($backorders === 'yes' || $backorders === 'notify');

        // Ne griser le bouton que si hors stock ET backorders non autorisés
        $should_disable = $is_out_of_stock && !$backorders_allowed;

        // Classes et attributs pour les éléments grisés
        $disabled_class = $should_disable ? ' disabled-out-of-stock' : '';
        $disabled_attr = $should_disable ? ' disabled' : '';
        $tooltip_attr = $should_disable ? ' title="Plus de stock disponible" data-toggle="tooltip"' : '';
        ?>

        <form class="cart purchase-form" action="<?php echo esc_url(apply_filters('woocommerce_add_to_cart_form_action', $product->get_permalink())); ?>" method="post" enctype='multipart/form-data'>
          <div class="purchase-header">
            <span class="price-custom">
              <?php
              if ($has_bulk_pricing && $min_bulk_price !== null && $min_bulk_price > 0) {
                  echo '<span class="from-price">à partir de </span>';
                  echo wc_price($min_bulk_price);
              } else {
                  echo $product->get_price_html();
              }
              ?>
            </span>
            <button class="aleaulavage-wishlist-btn wishlist-btn<?php echo is_product_in_wishlist($product->get_id()) ? ' active' : ''; ?>" data-product-id="<?php echo $product->get_id(); ?>" aria-label="Ajouter aux favoris">
              <i class="<?php echo is_product_in_wishlist($product->get_id()) ? 'fa-solid' : 'fa-regular'; ?> fa-heart"></i>
            </button>
          </div>

          <!-- Message custom ajout au panier -->
          <div id="custom-cart-notice" style="display: none; background: #f8e7c2; color: #2A3E6A; border: 1.5px solid #e2c48a; border-radius: 6px; padding: 10px 15px; margin-bottom: 12px; font-size: 13px; position: relative;">
            <i class="fa-solid fa-check-circle" style="color: #2A3E6A; margin-right: 8px;"></i>
            <span id="custom-cart-notice-text"></span>
          </div>

          <!-- Indicateur quantité dans le panier -->
          <?php if ($cart_qty > 0) : ?>
          <div class="product-cart-indicator" style="background: #e6f2ff; color: #2A3E6A; border: 1.5px solid #5899e2; border-radius: 8px; padding: 8px 12px; margin-bottom: 12px; font-size: 13px; display: flex; align-items: center; gap: 8px;">
            <i class="fa-solid fa-basket-shopping" style="color: #5899e2;"></i>
            <span><strong><?php echo $cart_qty; ?></strong> déjà dans votre panier</span>
          </div>
          <?php endif; ?>

          <div class="purchase-qty<?php echo $disabled_class; ?>">
            <?php
              // Paramètres pour le champ quantité
              $min_value = apply_filters('woocommerce_quantity_input_min', 1, $product);
              $max_value = apply_filters('woocommerce_quantity_input_max', $product->get_max_purchase_quantity(), $product);
              $input_value = isset($_POST['quantity']) ? wc_stock_amount(wp_unslash($_POST['quantity'])) : 1;

              // Si max_value est -1 (illimité), utiliser une grande valeur par défaut
              if ($max_value < 0) {
                  $max_value = 9999;
              }

              // Ajuster max_value si on gère le stock
              if ($is_manage_stock && $available_stock !== null && $available_stock > 0) {
                  $max_value = min($max_value, $available_stock);
              }
            ?>
            <div class="quantity" style="float: none; display: flex; width: 100%;">
              <button type="button" class="minus" aria-label="Diminuer la quantité">−</button>
              <input type="number" class="input-text qty text" name="quantity" value="<?php echo esc_attr($input_value); ?>"
                     min="<?php echo esc_attr($min_value); ?>" max="<?php echo esc_attr($max_value); ?>"
                     step="1" inputmode="numeric" autocomplete="off" />
              <button type="button" class="plus" aria-label="Augmenter la quantité">+</button>
            </div>
          </div>

          <div style="height: 1px; clear: both; display: block; width: 100%;"></div>

      <button type="submit"
              name="add-to-cart"
              value="<?php echo esc_attr($product->get_id()); ?>"
              class="single_add_to_cart_button button alt<?php echo $disabled_class; ?>"
              style="clear: both; display: block !important; width: 100%; float: none !important;"
              <?php echo $disabled_attr . $tooltip_attr; ?>>
      <i class="fa-solid fa-basket-shopping me-1"></i>
      <span class="button-text">Ajouter au panier</span>
      <span class="cart-count-badge" style="display: none; background: #fff; color: #f1bb69; font-weight: 700; padding: 2px 8px; border-radius: 12px; margin-left: 8px; font-size: 14px;"></span>
      </button>

      <!-- Tableau règles de prix ADP sous le bouton -->
      <div class="adp-bulk-table-wrapper" style="margin:0;">
        <?php echo do_shortcode('[adp_product_bulk_rules_table]'); ?>
      </div>

        </form>

        <script>
        // Message custom ajout au panier et gestion du badge
        document.addEventListener('DOMContentLoaded', function() {
          var cartCountForProduct = 0;

          // Fonction pour mettre à jour le badge
          function updateCartBadge() {
            var badge = document.querySelector('.single_add_to_cart_button .cart-count-badge');
            if (badge && cartCountForProduct > 0) {
              badge.textContent = cartCountForProduct;
              badge.style.display = 'inline-block';
            }
          }

          // Intercepter l'événement d'ajout au panier
          jQuery(document.body).on('added_to_cart', function(event, fragments, cart_hash, button) {
            var productName = '<?php echo esc_js($product->get_name()); ?>';
            var qtyInput = document.querySelector('.purchase-qty input.qty');
            var quantity = parseInt(qtyInput ? qtyInput.value : 1);

            // Mettre à jour le compteur interne JS pour le calcul de prix
            if (typeof aleaulavageProductData !== 'undefined') {
                aleaulavageProductData.cartQty += quantity;
                
                // Déclencher la mise à jour du prix dynamique avec la nouvelle qté totale
                var qtyField = document.querySelector('input.qty');
                if (qtyField) {
                    qtyField.dispatchEvent(new Event('change', { bubbles: true }));
                }
            }

            // Mettre à jour le compteur visuel "déjà dans votre panier"
            var indicator = document.querySelector('.product-cart-indicator');
            if (indicator) {
                var strong = indicator.querySelector('strong');
                if (strong) {
                    var currentCount = parseInt(strong.textContent) || 0;
                    strong.textContent = currentCount + quantity;
                }
            } else {
                // Créer l'indicateur s'il n'existe pas encore
                var form = document.querySelector('form.cart.purchase-form');
                var purchaseQty = document.querySelector('.purchase-qty');
                
                if (form && purchaseQty) {
                    var newIndicator = document.createElement('div');
                    newIndicator.className = 'product-cart-indicator';
                    newIndicator.style.cssText = 'background: #e6f2ff; color: #2A3E6A; border: 1.5px solid #5899e2; border-radius: 8px; padding: 8px 12px; margin-bottom: 12px; font-size: 13px; display: flex; align-items: center; gap: 8px;';
                    newIndicator.innerHTML = '<i class="fa-solid fa-basket-shopping" style="color: #5899e2;"></i><span><strong>' + quantity + '</strong> déjà dans votre panier</span>';
                    form.insertBefore(newIndicator, purchaseQty);
                }
            }

            // Mettre à jour le badge bouton
            cartCountForProduct += quantity;
            updateCartBadge();

            var notice = document.getElementById('custom-cart-notice');
            var noticeText = document.getElementById('custom-cart-notice-text');

            if (notice && noticeText) {
              noticeText.innerHTML = quantity + ' × «' + productName + '» ajouté' + (quantity > 1 ? 's' : '') + ' au panier';
              notice.style.display = 'block';

              // Faire disparaître après 5 secondes
              setTimeout(function() {
                notice.style.opacity = '0';
                notice.style.transition = 'opacity 0.5s';
                setTimeout(function() {
                  notice.style.display = 'none';
                  notice.style.opacity = '1';
                }, 500);
              }, 5000);
            }
          });
        });

        // Gestion des boutons +/- pour la quantité
        document.addEventListener('DOMContentLoaded', function() {
          function setupQuantityButtons() {
            var qtyContainer = document.querySelector('.purchase-qty .quantity');
            if (!qtyContainer) {
              console.log('Quantity container not found');
              return;
            }

            var input = qtyContainer.querySelector('input.qty, input[type="number"]');
            var minusBtn = qtyContainer.querySelector('.minus, button.minus');
            var plusBtn = qtyContainer.querySelector('.plus, button.plus');

            console.log('Input:', input);
            console.log('Minus btn:', minusBtn);
            console.log('Plus btn:', plusBtn);

            if (!input || !minusBtn || !plusBtn) {
              console.log('Missing elements');
              return;
            }

            var min = parseInt(input.getAttribute('min')) || 1;
            var max = parseInt(input.getAttribute('max')) || 9999;

            console.log('Min:', min, 'Max:', max);

            minusBtn.addEventListener('click', function(e) {
              e.preventDefault();
              e.stopPropagation();
              var currentVal = parseInt(input.value) || min;
              console.log('Minus clicked, current:', currentVal);
              if (currentVal > min) {
                input.value = currentVal - 1;
                input.dispatchEvent(new Event('change', { bubbles: true }));
                console.log('New value:', input.value);
              }
            });

            plusBtn.addEventListener('click', function(e) {
              e.preventDefault();
              e.stopPropagation();
              var currentVal = parseInt(input.value) || min;
              console.log('Plus clicked, current:', currentVal);
              if (currentVal < max) {
                input.value = currentVal + 1;
                input.dispatchEvent(new Event('change', { bubbles: true }));
                console.log('New value:', input.value);
              }
            });

            console.log('Quantity buttons setup complete');
          }

          setupQuantityButtons();
        });
        </script>
      <?php endif; ?>

      <!-- LIVRAISON (infos principales uniquement) -->
      <div class="livraison-card">
        <?php
        // Vérifier si le produit est dans la catégorie "peripheriques"
        $is_peripherique = false;
        $periph_term = get_term_by('slug', 'peripheriques', 'product_cat');
        if ($periph_term && has_term($periph_term->term_id, 'product_cat', $product->get_id())) {
            $is_peripherique = true;
        }
        ?>
        <?php if ($is_peripherique): ?>
      <h2>
        <i class="fa fa-truck"></i> Livraison
        <span class="info-icon" tabindex="0" aria-label="Informations livraison" style="margin-left:6px;cursor:pointer;position:relative;display:inline-block;">
          <i class="fa fa-info-circle"></i>
          <span class="info-tooltip" style="display:none;position:absolute;left:50%;transform:translateX(-50%);top:120%;background:#fff;color:#222;padding:10px 16px;border-radius:8px;box-shadow:0 2px 8px rgba(0,0,0,0.12);font-size:14px;white-space:normal;z-index:1001;min-width:220px;">
            <span>La date de livraison est varible selon vos besoins.</span><br>
          </span>
        </span>
        <span class="fee" style="color:#222;font-weight:600;">Sur devis</span>
      </h2>
      <p style="font-size:15px;color:#222;margin-bottom:8px;">Pour toute demande ou précision, contactez-nous au <a href="tel:0254517688" style="color:#2A3E6A;font-size:1.1em;text-decoration:underline;">02 54 51 76 88</a></p>
            <p style="font-size:14px;color:#6c757d;">Un conseiller vous proposera une solution adaptée et un délai personnalisé.</p>
            <script>
            // Affichage du tooltip info livraison pour peripheriques
            (function() {
              var info = document.querySelector('.info-icon');
              if (!info) return;
              var tooltip = info.querySelector('.info-tooltip');
              function show() { tooltip.style.display = 'block'; }
              function hide() { tooltip.style.display = 'none'; }
              info.addEventListener('mouseenter', show);
              info.addEventListener('mouseleave', hide);
              info.addEventListener('focus', show);
              info.addEventListener('blur', hide);
              info.addEventListener('click', function(e) {
                e.stopPropagation();
                tooltip.style.display = (tooltip.style.display === 'block') ? 'none' : 'block';
              });
              document.addEventListener('click', function(e) {
                if (!info.contains(e.target)) hide();
              });
            })();
            </script>
        <?php else: ?>
            <h2>
              <i class="fa fa-truck"></i> Livraison
              <span class="info-icon" tabindex="0" aria-label="Informations livraison" style="margin-left:6px;cursor:pointer;position:relative;display:inline-block;">
                <i class="fa fa-info-circle"></i>
                <span class="info-tooltip" style="display:none;position:absolute;left:50%;transform:translateX(-50%);top:120%;background:#fff;color:#222;padding:10px 16px;border-radius:8px;box-shadow:0 2px 8px rgba(0,0,0,0.12);font-size:14px;white-space:nowrap;z-index:1001;min-width:220px;">
                  <strong>19&nbsp;€</strong> : France métropolitaine<br>
                  <strong>45&nbsp;€</strong> : Corse
                </span>
              </span>
              <span class="fee">19,00 €</span>
            </h2>
            <?php if ($is_on_backorder): ?>
              <p style="font-size:15px;color:#f39c12;margin-bottom:8px;font-weight:600;">
                <i class="fa-solid fa-clock me-2"></i>La date de livraison peut varier selon les délais de réapprovisionnement.
              </p>
            <?php else: ?>
              <?php
                $now = new DateTime('now', wp_timezone());
                $weekday = (int)$now->format('w'); // 0=dimanche, 6=samedi
                $add_days = 0;
                if ($weekday === 6) { // samedi
                  $add_days = 2;
                } elseif ($weekday === 0) { // dimanche
                  $add_days = 1;
                }
                $date1 = clone $now;
                $date1->modify('+' . (1 + $add_days) . ' day');
                $date3 = clone $now;
                $date3->modify('+' . (3 + $add_days) . ' day');
                $mois = [1=>'janvier','février','mars','avril','mai','juin','juillet','août','septembre','octobre','novembre','décembre'];
                $jour1 = $date1->format('j') . ' ' . $mois[(int)$date1->format('n')];
                $jour3 = $date3->format('j') . ' ' . $mois[(int)$date3->format('n')];
              ?>
              <p>Entre le <?php echo $jour1; ?> – <?php echo $jour3; ?></p>
              <p>Commandez avant 16h30</p>
            <?php endif; ?>
            <script>
            // Affichage du tooltip info livraison
            (function() {
              var info = document.querySelector('.info-icon');
              if (!info) return;
              var tooltip = info.querySelector('.info-tooltip');
              function show() { tooltip.style.display = 'block'; }
              function hide() { tooltip.style.display = 'none'; }
              info.addEventListener('mouseenter', show);
              info.addEventListener('mouseleave', hide);
              info.addEventListener('focus', show);
              info.addEventListener('blur', hide);
              // Pour mobile : toggle au clic
              info.addEventListener('click', function(e) {
                e.stopPropagation();
                tooltip.style.display = (tooltip.style.display === 'block') ? 'none' : 'block';
              });
              document.addEventListener('click', function(e) {
                if (!info.contains(e.target)) hide();
              });
            })();
            </script>
        <?php endif; ?>
      </div>

      <!-- ACCORDÉON DESCRIPTION & INFOS COMPLÉMENTAIRES -->
      <?php
        $desc_tab = apply_filters('woocommerce_product_tabs', array());
        $has_additional_info = false;
        ob_start();
        if (isset($desc_tab['additional_information']['callback'])) {
          call_user_func($desc_tab['additional_information']['callback'], 'additional_information', $desc_tab['additional_information']);
        }
        $additional_info_content = trim(strip_tags(ob_get_clean()));
        if (!empty($additional_info_content)) $has_additional_info = true;
        if ($has_additional_info):
      ?>
      <div class="sidebar-accordion">
        <div class="accordion-item">
          <button class="accordion-toggle" type="button">Informations complémentaires</button>
          <div class="accordion-content">
            <?php
              if (isset($desc_tab['additional_information']['callback'])) {
                call_user_func($desc_tab['additional_information']['callback'], 'additional_information', $desc_tab['additional_information']);
              }
            ?>
          </div>
        </div>
      </div>
      <?php endif; ?>
    </aside>
    <script>
    // Accordéon JS robuste, exécuté après tout le DOM
    (function() {
      function setupAccordion() {
        var sidebar = document.querySelector('.sidebar-accordion');
        if (!sidebar) return;
        var toggles = sidebar.querySelectorAll('.accordion-toggle');
        toggles.forEach(function(btn) {
          btn.addEventListener('click', function() {
            var content = btn.nextElementSibling;
            var isActive = btn.classList.contains('active');
            toggles.forEach(function(b) {
              b.classList.remove('active');
              b.setAttribute('aria-expanded', 'false');
            });
            sidebar.querySelectorAll('.accordion-content').forEach(function(c) {
              c.classList.remove('active');
            });
            if (!isActive && content) {
              btn.classList.add('active');
              btn.setAttribute('aria-expanded', 'true');
              content.classList.add('active');
            }
          });
        });
      }
      if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', setupAccordion);
      } else {
        setupAccordion();
      }
    })();

    // Sticky purchase card only (right column)
    (function() {
      function initProductSticky() {
        var $purchaseCard = jQuery('.product-purchase-card');

        if ($purchaseCard.length === 0) {
          return;
        }

        // Vérifier si on est sur desktop (> 992px)
        if (jQuery(window).width() <= 992) {
          $purchaseCard.css('position', 'static');
          return;
        }

        // Utiliser un offset fixe pour le sticky (header + category-bar + padding)
        var topOffset = 160; // px

        // Purchase card toujours sticky
        $purchaseCard.css({
          'position': 'sticky',
          'top': topOffset + 'px',
          'align-self': 'flex-start'
        });
      }

      // Init au chargement
      if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
          setTimeout(initProductSticky, 100);
        });
      } else {
        setTimeout(initProductSticky, 100);
      }

      // Réinit au resize
      var resizeTimer;
      jQuery(window).on('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function() {
          initProductSticky();
        }, 250);
      });
    })();

    // DYNAMIC PRICE UPDATE BASED ON QUANTITY & BULK TABLES
    (function() {
      function updateDynamicPrice() {
        var qtyInput = document.querySelector('input.qty');
        if (!qtyInput) return;
        
        var currentQty = parseInt(qtyInput.value) || 1;
        var cartQty = (typeof aleaulavageProductData !== 'undefined') ? parseInt(aleaulavageProductData.cartQty) : 0;
        var regularPrice = (typeof aleaulavageProductData !== 'undefined') ? parseFloat(aleaulavageProductData.regularPrice) : 0;
        var regularPriceHtml = (typeof aleaulavageProductData !== 'undefined') ? aleaulavageProductData.regularPriceHtml : '';
        
        var totalQty = currentQty + cartQty;
        
        // 1. Chercher le tableau de prix (ADP / WAD)
        var bulkTable = document.querySelector('.adp-bulk-table-wrapper table, .wdp_pricing_table');
        var activePriceHtml = null;
        var activePriceValue = null;
        
        if (bulkTable) {
            var rows = bulkTable.querySelectorAll('tbody tr');
            rows.forEach(function(row) {
                // Reset style
                row.style.backgroundColor = '';
                
                var cols = row.querySelectorAll('td');
                if (cols.length >= 2) {
                    var rangeText = cols[0].textContent.trim(); // Ex: "1 - 4" ou "10+"
                    // Nettoyer le prix HTML (parfois contient des sauts de ligne)
                    var priceHtml = cols[1].innerHTML.trim();
                    var priceText = cols[1].textContent.trim().replace(/[^\d,.]/g, '').replace(',', '.');
                    var priceVal = parseFloat(priceText);
                    
                    var min = 0, max = Infinity;
                    
                    if (rangeText.indexOf('+') !== -1) {
                        min = parseInt(rangeText.replace('+', ''));
                    } else if (rangeText.indexOf('-') !== -1) {
                        var parts = rangeText.split('-');
                        min = parseInt(parts[0]);
                        max = parseInt(parts[1]);
                    } else {
                        min = parseInt(rangeText);
                        max = min; // Cas égalité stricte
                    }
                    
                    if (totalQty >= min && totalQty <= max) {
                        activePriceHtml = priceHtml;
                        activePriceValue = priceVal;
                        // Highlight active row
                        row.style.backgroundColor = '#f0f7ff';
                        row.style.transition = 'background 0.3s';
                    }
                }
            });
        }
        
        // 2. Mettre à jour le prix
        var finalHtml = activePriceHtml;
        
        // Si on a un prix actif et qu'il est inférieur au prix régulier, on formatte en promo
        if (activePriceValue !== null && regularPrice > 0 && activePriceValue < regularPrice) {
            // On s'assure d'avoir le montant propre pour le <ins>
            var insAmount = extractAmountContent(activePriceHtml);
            // On s'assure d'avoir le montant propre pour le <del> (prix régulier)
            var delAmount = extractAmountContent(regularPriceHtml);
            
            finalHtml = '<del aria-hidden="true"><span class="woocommerce-Price-amount amount">' + delAmount + '</span></del> ' +
                        '<ins><span class="woocommerce-Price-amount amount">' + insAmount + '</span></ins>';
        } else if (!finalHtml && regularPriceHtml) {
             // Si pas de règle trouvée (ex: qté 1 sans règle), remettre le prix de base
             // Mais attention, si le produit est déjà en promo par défaut, regularPriceHtml contient le prix barré ? 
             // Ici on simplifie : si pas de règle bulk activée, on ne touche pas (le prix par défaut de WP reste), 
             // SAUF si on veut forcer le recalcul.
             // Pour l'instant on ne fait rien si pas de match, sauf si on veut réinitialiser.
        }

        if (finalHtml) {
            var priceWrapper = document.querySelector('.purchase-header .price-custom');
            if (priceWrapper) {
                 // Masquer "à partir de" si on a un prix précis
                 var fromPrice = priceWrapper.querySelector('.from-price');
                 if (fromPrice) fromPrice.style.display = 'none';
                 
                 priceWrapper.innerHTML = finalHtml;
            }
        }
      }

      // Helper pour extraire juste le contenu numérique/symbole d'un amount (ex: "10,00 €" sans les spans autour si possible)
      function extractAmountContent(html) {
          var div = document.createElement('div');
          div.innerHTML = html;
          var amount = div.querySelector('.woocommerce-Price-amount');
          if (amount) return amount.innerHTML; // Retourne "10,00&nbsp;€" ou contenu interne
          return html; // Fallback
      }
      
      document.addEventListener('DOMContentLoaded', function() {
          // Init button badge
          var initialCartQty = (typeof aleaulavageProductData !== 'undefined') ? parseInt(aleaulavageProductData.cartQty) : 0;
          var btnBadge = document.querySelector('.single_add_to_cart_button .cart-count-badge');
          if (btnBadge && initialCartQty > 0) {
              btnBadge.textContent = initialCartQty;
              btnBadge.style.display = 'inline-block';
          }

          var qtyInput = document.querySelector('input.qty');
          if (qtyInput) {
              // Init
              updateDynamicPrice();
              
              // Listeners
              qtyInput.addEventListener('change', updateDynamicPrice);
              qtyInput.addEventListener('input', updateDynamicPrice);
              
              // Ecouter les boutons +/- custom s'ils existent
              jQuery(document.body).on('click', '.minus, .plus', function() {
                  setTimeout(updateDynamicPrice, 50); // Léger délai pour que la value soit à jour
              });
          }
          
          // SYNCHRONISATION GLOBALE : Écouter les changements du panier (ex: suppression dans le offcanvas)
          jQuery(document.body).on('wc_fragments_refreshed removed_from_cart updated_cart_totals aleaulavage_cart_updated', function() {
            if (typeof aleaulavageProductData === 'undefined' || !aleaulavageProductData.productId) return;

            // Demander au serveur la quantité réelle actuelle pour ce produit
            jQuery.ajax({
                url: aleaulavageProductData.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'aleaulavage_get_product_cart_qty',
                    product_id: aleaulavageProductData.productId
                },
                success: function(response) {
                    if (response.success) {
                        var newQty = parseInt(response.data.qty);
                        
                        // 1. Mettre à jour la donnée globale
                        aleaulavageProductData.cartQty = newQty;
                        
                        // 2. Mettre à jour l'indicateur visuel "Déjà dans le panier"
                        var indicator = document.querySelector('.product-cart-indicator');
                        
                        // Mise à jour du badge bouton avec la quantité totale
                        var btnBadge = document.querySelector('.single_add_to_cart_button .cart-count-badge');
                        if (btnBadge) {
                            if (newQty > 0) {
                                btnBadge.textContent = newQty;
                                btnBadge.style.display = 'inline-block';
                            } else {
                                btnBadge.style.display = 'none';
                            }
                        }
                        
                        if (newQty > 0) {
                            if (indicator) {
                                var strong = indicator.querySelector('strong');
                                if (strong) {
                                    strong.textContent = newQty;
                                    strong.style.opacity = '1'; // Restaurer l'opacité si elle était réduite
                                }
                                indicator.style.display = 'flex'; // S'assurer qu'il est visible
                            } else {
                                // Créer si inexistant
                                var form = document.querySelector('form.cart.purchase-form');
                                var purchaseQty = document.querySelector('.purchase-qty');
                                if (form && purchaseQty) {
                                    var newIndicator = document.createElement('div');
                                    newIndicator.className = 'product-cart-indicator';
                                    newIndicator.style.cssText = 'background: #e6f2ff; color: #2A3E6A; border: 1.5px solid #5899e2; border-radius: 8px; padding: 8px 12px; margin-bottom: 12px; font-size: 13px; display: flex; align-items: center; gap: 8px;';
                                    newIndicator.innerHTML = '<i class="fa-solid fa-basket-shopping" style="color: #5899e2;"></i><span><strong>' + newQty + '</strong> déjà dans votre panier</span>';
                                    form.insertBefore(newIndicator, purchaseQty);
                                }
                            }
                        } else {
                            // Si 0, masquer l'indicateur
                            if (indicator) indicator.style.display = 'none';
                        }

                        // 3. Recalculer le prix dynamique immédiatement
                        var qtyField = document.querySelector('input.qty');
                        if (qtyField) {
                            // Déclencher un changement pour relancer le calcul de prix
                            qtyField.dispatchEvent(new Event('change', { bubbles: true }));
                            qtyField.dispatchEvent(new Event('input', { bubbles: true }));
                        }
                    }
                }
            });
          });
      });
    })();
    </script>
  </div>

  <?php
    // upsells, produits liés…
    // On retire les tabs WooCommerce ici
    remove_action('woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs', 10);
    do_action('woocommerce_after_single_product_summary');
    do_action('woocommerce_after_single_product');
  ?>
</div>
