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
      <!-- LIVRAISON (infos principales uniquement) -->
      <div class="livraison-card">
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