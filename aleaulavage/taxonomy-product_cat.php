<?php
/**
 * Template for displaying product category archives
 */

get_header();
?>

<div id="content" class="site-content">
  <div id="primary" class="content-area container">
    <main id="main" class="site-main">

      <!-- Breadcrumb -->
      <?php woocommerce_breadcrumb(); ?>

      <header class="woocommerce-products-header">
        <?php if ( apply_filters( 'woocommerce_show_page_title', true ) ) : ?>
          <h1 class="woocommerce-products-header__title page-title"><?php woocommerce_page_title(); ?></h1>
        <?php endif; ?>

        <?php
        // Description courte en haut
        $term = get_queried_object();
        if ( $term && ! empty( $term->description ) ) {
          $description_text = strip_tags($term->description);
          $description_short = substr($description_text, 0, 200);
          $description_full = $term->description;
        ?>
          <div class="category-description-top">
            <div class="category-description-short">
              <?php echo esc_html($description_short); ?>
              <?php if (strlen($description_text) > 200) : ?>
                <span class="dots">...</span>
                <span class="read-more" onclick="scrollToFullDescription()">Lire la suite</span>
              <?php endif; ?>
            </div>
          </div>
        <?php } ?>
      </header>

      <?php
      if ( woocommerce_product_loop() ) {
        
        /**
         * Hook: woocommerce_before_shop_loop.
         *
         * @hooked woocommerce_output_all_notices - 10
         * @hooked woocommerce_result_count - 20
         * @hooked woocommerce_catalog_ordering - 30
         */
        do_action( 'woocommerce_before_shop_loop' );

        woocommerce_product_loop_start();

        if ( wc_get_loop_prop( 'is_shortcode' ) ) {
          $columns = absint( wc_get_loop_prop( 'columns' ) );
        } else {
          $columns = wc_get_default_products_per_row();
        }

        while ( have_posts() ) {
          the_post();

          /**
           * Hook: woocommerce_shop_loop.
           */
          do_action( 'woocommerce_shop_loop' );

          wc_get_template_part( 'content', 'product' );
        }

        woocommerce_product_loop_end();

        /**
         * Hook: woocommerce_after_shop_loop.
         *
         * @hooked woocommerce_pagination - 10
         */
        do_action( 'woocommerce_after_shop_loop' );

      } else {
        /**
         * Hook: woocommerce_no_products_found.
         *
         * @hooked wc_no_products_found - 10
         */
        do_action( 'woocommerce_no_products_found' );
      }

      // Description complète en bas
      if ( $term && ! empty( $term->description ) ) :
      ?>
        <div class="category-description-full" id="full-description">
          <div class="term-description">
            <?php echo wp_kses_post( wpautop( wc_format_content( $term->description ) ) ); ?>
          </div>
        </div>
      <?php endif; ?>

      /**
       * Hook: woocommerce_after_main_content.
       *
       * @hooked woocommerce_output_content_wrapper_end - 10 (outputs closing divs for the content)
       */
      do_action( 'woocommerce_after_main_content' );

    </main><!-- #main -->
  </div><!-- #primary -->
</div><!-- #content -->

<?php
get_footer();