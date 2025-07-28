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
          <div class="purchase-qty">
            <?php 
              woocommerce_quantity_input( array(
                'min_value'   => apply_filters( 'woocommerce_quantity_input_min', 1, $product ),
                'max_value'   => apply_filters( 'woocommerce_quantity_input_max', $product->get_max_purchase_quantity(), $product ),
                'input_value' => isset( $_POST['quantity'] ) ? wc_stock_amount( wp_unslash( $_POST['quantity'] ) ) : 1,
              ) ); 
            ?>
          </div>
          <button type="submit" name="add-to-cart" value="<?php echo esc_attr( $product->get_id() ); ?>" class="single_add_to_cart_button button alt">
            <i class="fa-solid fa-basket-shopping me-1"></i>Ajouter au panier
          </button>

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
          // Chercher le tableau du plugin
          var pluginTable = document.querySelector('.xa_sp_table');
          var pluginHeader = document.querySelector('.xa_sp_table_head1');
          var targetContainer = document.getElementById('pricing-table-container');
          
          if (pluginTable && targetContainer) {
              // Créer un nouveau conteneur stylé
              var newContainer = document.createElement('div');
              newContainer.style.cssText = 'background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 8px; padding: 16px;';
              
              // Créer un nouveau titre
              var newTitle = document.createElement('h3');
              newTitle.textContent = 'Prix dégressifs';
              newTitle.style.cssText = 'margin: 0 0 12px 0; font-size: 16px; color: #2A3E6A; font-weight: 600;';
              
              // Cloner le tableau
              var newTable = pluginTable.cloneNode(true);
              
              // Appliquer de nouveaux styles au tableau
              newTable.style.cssText = 'width: 100%; border-collapse: collapse; margin: 0;';
              newTable.className = 'custom-pricing-table';
              
              // Styler l'en-tête
              var thead = newTable.querySelector('thead');
              if (thead) {
                  thead.style.cssText = 'background: #e9ecef;';
                  var headerCells = thead.querySelectorAll('td');
                  headerCells.forEach(function(cell) {
                      cell.style.cssText = 'padding: 8px; font-weight: 600; border-bottom: 1px solid #dee2e6; font-size: 14px; color: #2A3E6A;';
                  });
              }
              
              // Styler le corps du tableau
              var tbody = newTable.querySelector('tbody');
              if (tbody) {
                  var rows = tbody.querySelectorAll('tr');
                  rows.forEach(function(row) {
                      row.style.cssText = 'border-bottom: 1px solid #f1f3f4;';
                      var cells = row.querySelectorAll('td');
                      cells.forEach(function(cell) {
                          cell.style.cssText = 'padding: 8px; border-bottom: 1px solid #f1f3f4; font-size: 14px; font-family: inherit;';
                      });
                  });
              }
              
              // Assembler le nouveau tableau
              newContainer.appendChild(newTitle);
              newContainer.appendChild(newTable);
              targetContainer.appendChild(newContainer);
              
              // Extraire les règles de prix du tableau
              extractPricingRules();
              
              // Cacher l'ancien tableau et titre
              if (pluginHeader) {
                  pluginHeader.style.display = 'none';
              }
              pluginTable.style.display = 'none';
              
              // Activer immédiatement notre gestion pour prendre le contrôle avant ELECX
              elecxOverrideActive = true;
              setupPriceManagement();
          } else {
              // Pas de tableau ELECX, désactiver la logique custom
              elecxOverrideActive = false;
          }
      }
      
      function extractPricingRules() {
          pricingRules = [];
          var tableRows = document.querySelectorAll('.custom-pricing-table tbody tr');
          
          tableRows.forEach(function(row) {
              var cells = row.querySelectorAll('td');
              if (cells.length >= 3) {
                  var minText = cells[0].textContent.trim();
                  var maxText = cells[1].textContent.trim();
                  var discountText = cells[2].textContent.trim();
                  
                  var minQty = parseInt(minText.replace(/\D/g, ''));
                  var maxQty = parseInt(maxText.replace(/\D/g, ''));
                  var discount = parseFloat(discountText.replace(/[^\d.]/g, ''));
                  
                  if (minQty > 0 && maxQty > 0 && discount > 0) {
                      pricingRules.push({
                          min: minQty,
                          max: maxQty,
                          discount: discount
                      });
                  }
              }
          });
          
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
          if (!elecxOverrideActive) return;
          
          var qtyInput = document.querySelector('input[name="quantity"]');
          if (!qtyInput) return;
          
          var currentQty = parseInt(qtyInput.value) || 1;
          
          // Trouver la règle active
          var activeRule = null;
          for (var i = 0; i < pricingRules.length; i++) {
              var rule = pricingRules[i];
              if (currentQty >= rule.min && currentQty <= rule.max) {
                  activeRule = rule;
                  break;
              }
          }
          
          if (activeRule) {
              displayDiscountedPrice(activeRule);
          } else {
              // Afficher le prix original (sans promotion) quand on est hors seuils
              restoreOriginalPrice();
          }
      }
      
      function displayDiscountedPrice(rule) {
          var priceElement = document.querySelector('.price');
          if (!priceElement || !originalPriceData) return;
          
          isUpdatingPrice = true;
          var newPrice = originalPriceData.price * (1 - rule.discount / 100);
          
          priceElement.innerHTML = '<del style="color: #999; text-decoration: line-through;">' + 
                                 originalPriceData.price.toFixed(2).replace('.', ',') + '&nbsp;' + originalPriceData.currency + 
                                 '</del> <span style="color: #5899E2; font-weight: bold;">' + 
                                 newPrice.toFixed(2).replace('.', ',') + '&nbsp;' + originalPriceData.currency + '</span>';
          
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