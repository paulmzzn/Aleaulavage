<?php
/**
 * Template override: single product
 */

defined( 'ABSPATH' ) || exit;
global $product;

/** Notices & mot de passe */
do_action( 'woocommerce_before_single_product' );
if ( post_password_required() ) {
	echo get_the_password_form();
	return;
}
?>

<div id="product-<?php the_ID(); ?>" <?php wc_product_class( '', $product ); ?>>
  <div class="product-container">
    <!-- GALERIE PRODUIT -->
    <div class="product-gallery">
      <div id="selected-color-badge" class="selected-color-badge" style="display:none;">
        <span class="color-dot"></span>
        <span class="color-tooltip">La photo n'affiche pas forcément la couleur sélectionnée</span>
      </div>
      <?php do_action( 'woocommerce_before_single_product_summary' ); ?>
    </div>
    <!-- COLONNE DROITE : CARTE D’ACHAT & INFOS -->
    <aside class="product-purchase-card">
      <!-- TITRE PRODUIT -->
      <h1 class="product-title"><?php the_title(); ?></h1>
      <?php if ( wc_product_sku_enabled() && ( $product->get_sku() || $product->is_type( 'variable' ) ) ) : ?>
        <div class="product-sku" style="font-size:14px;color:#6c7a89;margin-bottom:10px;">
          <strong>UGS :</strong> <span><?php echo esc_html( $product->get_sku() ? $product->get_sku() : __( 'N/A', 'woocommerce' ) ); ?></span>
        </div>
      <?php endif; ?>
      <!-- PRIX + VARIATIONS + QTY + AJOUT AU PANIER + OFFRES VRAC -->
      <?php if ( $product->is_type( 'variable' ) ) : ?>
        <div class="purchase-header">
          <span class="price"><?php echo $product->get_price_html(); ?></span>
          <span class="wishlist-btn<?php echo is_product_in_wishlist($product->get_id()) ? ' active' : ''; ?>" title="Ajouter à la liste de souhaits" data-product-id="<?php echo $product->get_id(); ?>">
            <i class="<?php echo is_product_in_wishlist($product->get_id()) ? 'fa-solid' : 'fa-regular'; ?> fa-heart"></i>
          </span>
        </div>
        <?php woocommerce_variable_add_to_cart(); ?>
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

        // Classes et attributs pour les éléments grisés
        $disabled_class = $is_out_of_stock ? ' disabled-out-of-stock' : '';
        $disabled_attr = $is_out_of_stock ? ' disabled' : '';
        $tooltip_attr = $is_out_of_stock ? ' title="Plus de stock disponible" data-toggle="tooltip"' : '';
        ?>
        
        <form class="cart purchase-form" action="<?php echo esc_url( apply_filters( 'woocommerce_add_to_cart_form_action', $product->get_permalink() ) ); ?>" method="post" enctype='multipart/form-data'>
          <div class="purchase-header">
            <span class="price"><?php echo $product->get_price_html(); ?></span>
            <span class="wishlist-btn<?php echo is_product_in_wishlist($product->get_id()) ? ' active' : ''; ?>" title="Ajouter à la liste de souhaits" data-product-id="<?php echo $product->get_id(); ?>">
              <i class="<?php echo is_product_in_wishlist($product->get_id()) ? 'fa-solid' : 'fa-regular'; ?> fa-heart"></i>
            </span>
          </div>
          <?php
          $bulk_offers = get_post_meta( $product->get_id(), '_bulk_offers', true );
          if ( !empty($bulk_offers) && is_array($bulk_offers) ) : ?>
            <div class="bulk-offers">
              <h3>Offres de produits en vrac</h3>
              <table>
                <thead>
                  <tr><th>Min Achat</th><th>Max Achat</th><th>Offre</th></tr>
                </thead>
                <tbody>
                  <?php foreach($bulk_offers as $offer): ?>
                    <tr>
                      <td><?php echo esc_html($offer['min']); ?> Quantité</td>
                      <td><?php echo esc_html($offer['max']); ?> Quantité</td>
                      <td><?php echo esc_html($offer['discount']); ?> % Remise</td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php endif; ?>
          <div class="purchase-qty<?php echo $disabled_class; ?>">
            <?php 
              // Paramètres pour le champ quantité
              $quantity_args = array(
                'min_value'   => apply_filters( 'woocommerce_quantity_input_min', 1, $product ),
                'max_value'   => apply_filters( 'woocommerce_quantity_input_max', $product->get_max_purchase_quantity(), $product ),
                'input_value' => isset( $_POST['quantity'] ) ? wc_stock_amount( wp_unslash( $_POST['quantity'] ) ) : 1,
              );

              // Ajuster max_value si on gère le stock
              if ($is_manage_stock && $available_stock !== null && $available_stock > 0) {
                  $quantity_args['max_value'] = min($quantity_args['max_value'], $available_stock);
              }

              woocommerce_quantity_input( $quantity_args ); 
            ?>
          </div>
      <button type="submit" 
              name="add-to-cart" 
              value="<?php echo esc_attr( $product->get_id() ); ?>" 
              class="single_add_to_cart_button button alt<?php echo $disabled_class; ?>"
              <?php echo $disabled_attr . $tooltip_attr; ?>>
      <i class="fa-solid fa-basket-shopping me-1"></i>Ajouter au panier
      </button>

      <!-- Tableau règles de prix ADP sous le bouton -->
      <div class="adp-bulk-table-wrapper" style="margin:12px 0 0 0;">
        <?php echo do_shortcode('[adp_product_bulk_rules_table]'); ?>
      </div>
      <style>
        /* Rendre le tableau ADP plus large et fluide */
        .adp-bulk-table-wrapper table {
          width: 100% !important;
          min-width: 320px;
          max-width: 100%;
          margin: 0 !important;
        }
        .adp-bulk-table-wrapper th, .adp-bulk-table-wrapper td {
          padding: 5px 12px !important;
          font-size: 15px !important;
        }
        .adp-bulk-table-wrapper thead th {
          background: #f5f5f5 !important;
        }
        .adp-bulk-table-wrapper tr {
          background: #fff !important;
        }
      </style>
      <script src="<?php echo get_template_directory_uri(); ?>/js/adp-bulk-price-sync.js"></script>

        </form>
      <?php endif; ?>
      
      <!-- TABLEAU DES PRIX DYNAMIQUES -->
      <div id="pricing-table-container" style="margin: 20px 0;"></div>
      
      <?php
      // Laisser ELECX gérer les prix normalement
      // Notre JavaScript interviendra seulement pour les cas spécifiques
      ?>
      
      <script>
      // Variables globales pour la gestion des prix
      var originalPriceData = <?php echo json_encode([
          'price' => floatval($product->get_regular_price()),
          'currency' => get_woocommerce_currency_symbol(),
          'formatted' => wc_price($product->get_regular_price())
      ]); ?>;
      
      var pricingRules = [];
      var elecxOverrideActive = false;
      var isUpdatingPrice = false;
      
      // Déplacer et restyler le tableau des prix du plugin
      document.addEventListener('DOMContentLoaded', function() {
          initializePriceManagement();
      });
      
      function initializePriceManagement() {
          // Chercher le tableau ADP directement
          var adpTable = document.querySelector('.wdp_pricing_table');
          
          if (adpTable) {
              
              // Extraire les règles de prix du tableau ADP
              extractPricingRules();
              
              // Activer notre gestion des prix
              elecxOverrideActive = true;
              setupPriceManagement();
          } else {
              // Pas de tableau ADP, désactiver la logique custom
              elecxOverrideActive = false;
          }
      }
      
      function extractPricingRules() {
          pricingRules = [];
          
          // Essayer de trouver le tableau ADP actuel
          var adpTableRows = document.querySelectorAll('.wdp_pricing_table tbody tr');
          
          if (adpTableRows.length > 0) {
              
              adpTableRows.forEach(function(row) {
                  var cells = row.querySelectorAll('td');
                  
                  if (cells.length >= 3) {
                      // Format 3 colonnes: Quantité | Remise | Prix remisé
                      var quantityText = cells[0].textContent.trim(); // ex: "5 - 9"
                      var discountText = cells[1].textContent.trim(); // ex: "5%"
                      var priceText = cells[2].textContent.trim(); // ex: "4,93 €"
                      
                      
                      // Extraire les quantités min et max
                      var minQty = 0;
                      var maxQty = 999999;
                      
                      if (quantityText.includes(' - ')) {
                          // Format "5 - 9"
                          var parts = quantityText.split(' - ');
                          minQty = parseInt(parts[0].replace(/\D/g, '')) || 0;
                          maxQty = parseInt(parts[1].replace(/\D/g, '')) || 999999;
                      } else if (quantityText.includes(' +')) {
                          // Format "16 +"
                          minQty = parseInt(quantityText.replace(/\D/g, '')) || 0;
                          maxQty = 999999;
                      }
                      
                      // Extraire le prix remisé (3ème colonne)
                      var priceValue = parseFloat(priceText.replace(/[^\d,]/g, '').replace(',', '.'));
                      
                      if (minQty > 0 && priceValue > 0) {
                          pricingRules.push({
                              min: minQty,
                              max: maxQty,
                              price: priceValue
                          });
                      }
                      
                  } else if (cells.length >= 2) {
                      // Format 2 colonnes: Quantité | Prix fixe
                      var quantityText = cells[0].textContent.trim(); // ex: "100 - 199"
                      var priceText = cells[1].textContent.trim(); // ex: "6,50 €"
                      
                      
                      // Extraire les quantités min et max
                      var minQty = 0;
                      var maxQty = 999999;
                      
                      if (quantityText.includes(' - ')) {
                          // Format "100 - 199"
                          var parts = quantityText.split(' - ');
                          minQty = parseInt(parts[0].replace(/\D/g, '')) || 0;
                          maxQty = parseInt(parts[1].replace(/\D/g, '')) || 999999;
                      } else if (quantityText.includes(' +')) {
                          // Format "200 +"
                          minQty = parseInt(quantityText.replace(/\D/g, '')) || 0;
                          maxQty = 999999;
                      }
                      
                      // Extraire le prix fixe (2ème colonne)
                      var priceValue = parseFloat(priceText.replace(/[^\d,]/g, '').replace(',', '.'));
                      
                      if (minQty > 0 && priceValue > 0) {
                          pricingRules.push({
                              min: minQty,
                              max: maxQty,
                              price: priceValue
                          });
                      }
                  }
              });
          }
          
      }
      
      function setupPriceManagement() {
          var qtyInput = document.querySelector('input[name="quantity"]');
          var priceElement = document.querySelector('.price');
          
          if (!qtyInput || !priceElement) return;
          
          // Forcer le prix original immédiatement
          restoreOriginalPrice();
          
          // Écouter les changements de quantité avec debounce
          var quantityTimeout;
          function handleQuantityChangeDebounced() {
              clearTimeout(quantityTimeout);
              quantityTimeout = setTimeout(function() {
                  handleQuantityChange();
              }, 100);
          }
          
          qtyInput.addEventListener('input', handleQuantityChangeDebounced);
          qtyInput.addEventListener('change', handleQuantityChangeDebounced);
          
          // Écouter les clics sur les boutons + et - de quantité
          var qtyPlusBtn = document.querySelector('.quantity .plus, .qty-btn-plus, [data-quantity="plus"]');
          var qtyMinusBtn = document.querySelector('.quantity .minus, .qty-btn-minus, [data-quantity="minus"]');
          
          if (qtyPlusBtn) {
              qtyPlusBtn.addEventListener('click', function() {
                  setTimeout(handleQuantityChangeDebounced, 50);
              });
          }
          
          if (qtyMinusBtn) {
              qtyMinusBtn.addEventListener('click', function() {
                  setTimeout(handleQuantityChangeDebounced, 50);
              });
          }
          
          // Écouter tous les clics dans la zone quantity au cas où
          var quantityDiv = qtyInput.closest('.quantity, .purchase-qty');
          if (quantityDiv) {
              quantityDiv.addEventListener('click', function(e) {
                  // Si c'est un bouton (contient + ou -)
                  if (e.target.tagName === 'BUTTON' || e.target.classList.contains('plus') || e.target.classList.contains('minus')) {
                      setTimeout(handleQuantityChangeDebounced, 100);
                  }
              });
          }
          
          // Observer les modifications ELECX et les corriger
          if (window.MutationObserver) {
              var observer = new MutationObserver(function(mutations) {
                  // Ignorer si on est en train de mettre à jour le prix nous-mêmes
                  if (isUpdatingPrice) return;
                  
                  // Vérifier si ELECX a modifié le prix de manière non désirée
                  var currentContent = priceElement.innerHTML;
                  var currentQty = parseInt(qtyInput.value) || 1;
                  
                  // Observer pour détecter les conflits entre notre gestion et ELECX
                  // Seulement intervenir si on a une règle active qui doit s'appliquer
                  if (pricingRules.length > 0) {
                      var activeRule = null;
                      for (var i = 0; i < pricingRules.length; i++) {
                          if (currentQty >= pricingRules[i].min && currentQty <= pricingRules[i].max) {
                              activeRule = pricingRules[i];
                              break;
                          }
                      }
                      
                      // Si on a une règle active, s'assurer que notre prix s'affiche
                      if (activeRule) {
                          var expectedPrice = (originalPriceData.price * (1 - activeRule.discount / 100)).toFixed(2);
                          if (!currentContent.includes(expectedPrice.replace('.', ','))) {
                              setTimeout(function() {
                                  displayDiscountedPrice(activeRule);
                              }, 50);
                          }
                      }
                      // Si pas de règle active, laisser ELECX faire son travail
                  }
              });
              
              observer.observe(priceElement, {
                  childList: true,
                  subtree: true,
                  characterData: true
              });
          }
          
          // Surveiller les changements de valeur en continu (polling)
          var lastQuantity = parseInt(qtyInput.value) || 1;
          var lastPollingTime = 0;
          setInterval(function() {
              // Ne pas déclencher si on vient de faire une recalculation ELECX
              if (Date.now() - lastPollingTime < 600) return;
              
              var currentQuantity = parseInt(qtyInput.value) || 1;
              if (currentQuantity !== lastQuantity && elecxOverrideActive) {
                  lastQuantity = currentQuantity;
                  lastPollingTime = Date.now();
                  handleQuantityChangeDebounced();
              } else if (currentQuantity !== lastQuantity) {
                  lastQuantity = currentQuantity; // Mettre à jour sans déclencher
              }
          }, 300);
          
          // Application initiale
          setTimeout(handleQuantityChange, 200);
      }
      
      function handleQuantityChange() {
          var qtyInput = document.querySelector('input[name="quantity"]');
          if (!qtyInput) return;
          
          var currentQty = parseInt(qtyInput.value) || 1;
          
          // Trouver la règle active (prix fixe)
          var activeRule = null;
          for (var i = 0; i < pricingRules.length; i++) {
              var rule = pricingRules[i];
              if (currentQty >= rule.min && currentQty <= rule.max) {
                  activeRule = rule;
                  break;
              }
          }
          
          if (activeRule) {
              displayFixedPrice(activeRule);
          } else {
              restoreOriginalPrice();
          }
      }
      
      function displayFixedPrice(rule) {
          var priceElement = document.querySelector('.price');
          if (!priceElement || !originalPriceData) return;
          
          isUpdatingPrice = true;
          var newPrice = rule.price;
          
          
          priceElement.innerHTML = '<del style="color: #999; text-decoration: line-through;">' + 
                                 originalPriceData.price.toFixed(2).replace('.', ',') + '&nbsp;€</del> ' + 
                                 '<span style="color: #5899E2; font-weight: bold;">' + 
                                 newPrice.toFixed(2).replace('.', ',') + '&nbsp;€</span>';
          
          setTimeout(function() { isUpdatingPrice = false; }, 100);
      }
      
      function restoreOriginalPrice() {
          var priceElement = document.querySelector('.price');
          if (!priceElement || !originalPriceData) return;
          
          isUpdatingPrice = true;
          priceElement.innerHTML = originalPriceData.price.toFixed(2).replace('.', ',') + '&nbsp;' + originalPriceData.currency;
          setTimeout(function() { isUpdatingPrice = false; }, 100);
      }
      
      </script>
      
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
    </script>
  </div>

  <!-- Description principale sous la fiche produit -->
  <?php
    $product_description = trim(strip_tags(apply_filters('the_content', get_post_field('post_content', $product->get_id()))));
    if (!empty($product_description)) :
  ?>
  <div class="product-main-description" style="width:100%;max-width:none;margin:40px 0 32px 0;padding:32px 28px 24px 28px;background:#fff;border:1px solid #e3e5e8;border-radius:8px;box-shadow:0 2px 6px rgba(0,0,0,0.05);">
    <h2 style="font-size:1.5em;font-weight:700;color:#0E2141;margin-bottom:18px;">Description du produit</h2>
    <?php if ( wc_product_sku_enabled() && ( $product->get_sku() || $product->is_type( 'variable' ) ) ) : ?>
      <div class="product-sku" style="font-size:14px;color:#6c7a89;margin-bottom:10px;">
        <strong>UGS :</strong> <span><?php echo esc_html( $product->get_sku() ? $product->get_sku() : __( 'N/A', 'woocommerce' ) ); ?></span>
      </div>
    <?php endif; ?>
    <?php echo apply_filters('the_content', get_post_field('post_content', $product->get_id())); ?>
  </div>
  <?php endif; ?>
  <?php
    // upsells, produits liés…
    // On retire les tabs WooCommerce ici
    remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs', 10 );
    do_action( 'woocommerce_after_single_product_summary' );
    do_action( 'woocommerce_after_single_product' );
  ?>
</div>