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

<div id="content" class="site-content shop-page<?php // if (is_shop()) echo 'shop-page' ?>">
  <div id="primary" class="content-area container">
    <main id="main" class="site-main">

      <!-- Breadcrumb -->
      <?php woocommerce_breadcrumb(); ?>
      <?php woocommerce_content(); ?>
    </main><!-- #main -->
  </div><!-- #primary -->
</div><!-- #content -->
<?php
get_footer();
