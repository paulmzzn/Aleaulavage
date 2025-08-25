<?php

/**
 * The template for displaying all WooCommerce pages
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages
 * and that other 'pages' on your WordPress site may use a
 * different template.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Aleaulavage
 */

get_header();
?>

<div id="content" class="site-content<?php // if (is_shop()) echo 'shop-page' ?>">
  <div id="primary" class="content-area container">
    <main id="main" class="site-main">

      <!-- Breadcrumb -->
      <?php woocommerce_breadcrumb(); ?>
      
      <?php woocommerce_content(); ?>
      
      <?php if (is_product_category()) : ?>
        <!-- Description complète en bas -->
        <?php
        $term = get_queried_object();
        if ( $term && ! empty( $term->description ) ) :
        ?>
          <div class="category-description-full" id="full-description">
            <div class="term-description">
              <?php echo wp_kses_post( wpautop( wc_format_content( $term->description ) ) ); ?>
            </div>
          </div>
        <?php endif; ?>
      <?php endif; ?>
      
    </main><!-- #main -->
  </div><!-- #primary -->
</div><!-- #content -->

<script>
// Fonction pour faire défiler vers la description complète
function scrollToFullDescription() {
    console.log('scrollToFullDescription appelée');
    const fullDescription = document.getElementById('full-description');
    console.log('Element trouvé:', fullDescription);
    if (fullDescription) {
        fullDescription.scrollIntoView({ 
            behavior: 'smooth',
            block: 'center'
        });
    } else {
        console.log('Element full-description non trouvé');
        // Fallback: chercher par classe
        const fallback = document.querySelector('.category-description-full');
        if (fallback) {
            console.log('Fallback trouvé, scroll vers fallback');
            fallback.scrollIntoView({ 
                behavior: 'smooth',
                block: 'center'
            });
        }
    }
}
</script>

<?php
get_footer();
