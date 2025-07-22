<?php

/**
 * Woocommerce functions and definitions
 *
 * @package Bootscore
 */


// Woocommerce Templates
function mytheme_add_woocommerce_support() {
  add_theme_support('woocommerce');
}
add_action('after_setup_theme', 'mytheme_add_woocommerce_support');
// Woocommerce Templates END


// Woocommerce Lightbox
add_action('after_setup_theme', 'bootscore');

function bootscore() {
  add_theme_support('wc-product-gallery-zoom');
  add_theme_support('wc-product-gallery-lightbox');
  add_theme_support('wc-product-gallery-slider');
}
// Woocommerce Lightbox End


// Register Ajax Cart
if (!function_exists('register_ajax_cart')) :

  function register_ajax_cart() {
    require_once('ajax-cart/ajax-add-to-cart.php');
  }
  add_action('after_setup_theme', 'register_ajax_cart');

endif;
// Register Ajax Cart End


//Scripts and Styles
function wc_scripts() {

  // Get modification time. Enqueue files with modification date to prevent browser from loading cached scripts and styles when file content changes.
  $modificated_WooCommerceJS = date('YmdHi', filemtime(get_template_directory() . '/woocommerce/js/woocommerce.js'));

  // WooCommerce JS
  wp_enqueue_script('woocommerce-script', get_template_directory_uri() . '/woocommerce/js/woocommerce.js', array(), $modificated_WooCommerceJS, true);

  if (is_singular() && comments_open() && get_option('thread_comments')) {
    wp_enqueue_script('comment-reply');
  }
}
add_action('wp_enqueue_scripts', 'wc_scripts');
//Scripts and styles End


// Minicart Header
if (!function_exists('bs_mini_cart')) :
  function bs_mini_cart($fragments) {

    ob_start();
    $count = WC()->cart->cart_contents_count; ?>
    <span class="cart-content">
      <?php if ($count > 0) { ?>
        <span class="cart-content-count position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-light"><?php echo esc_html($count); ?></span><span class="cart-total ms-1 d-none d-md-inline"><?php echo WC()->cart->get_cart_subtotal(); ?></span>
      <?php } ?>
    </span>

  <?php
    $fragments['span.cart-content'] = ob_get_clean();

    return $fragments;
  }
  add_filter('woocommerce_add_to_cart_fragments', 'bs_mini_cart');

endif;
// Minicart Header End


// WooCommerce Breadcrumb
if (!function_exists('bs_woocommerce_breadcrumbs')) :
  add_filter('woocommerce_breadcrumb_defaults', 'bs_woocommerce_breadcrumbs');
  function bs_woocommerce_breadcrumbs() {
    return array(
      'delimiter'   => ' &nbsp;>&nbsp; ',
      'wrap_before' => '<nav class="breadcrumb mb-4 mt-2 py-2 small opacity-50" itemprop="breadcrumb">',
      'wrap_after'  => '</nav>',
      'before'      => '',
      'after'       => '',
      'home'        => _x('Home', 'breadcrumb', 'woocommerce'),
    );
  }
endif;
// WooCommerce Breadcrumb End


// Bootstrap Billing forms
function iap_wc_bootstrap_form_field_args($args, $key, $value) {

  $args['input_class'][] = 'form-control';
  return $args;
}
add_filter('woocommerce_form_field_args', 'iap_wc_bootstrap_form_field_args', 10, 3);
// Bootstrap Billing forms End


// Ship to a different address closed by default
add_filter('woocommerce_ship_to_different_address_checked', '__return_false');
// Ship to a different address closed by default End


// Remove cross-sells at cart
remove_action('woocommerce_cart_collaterals', 'woocommerce_cross_sell_display');
// Remove cross-sells at cart End


// Remove CSS and/or JS for Select2 used by WooCommerce, see https://gist.github.com/Willem-Siebe/c6d798ccba249d5bf080.
add_action('wp_enqueue_scripts', 'wsis_dequeue_stylesandscripts_select2', 100);

function wsis_dequeue_stylesandscripts_select2() {
  if (class_exists('woocommerce')) {
    wp_dequeue_style('selectWoo');
    wp_deregister_style('selectWoo');

    wp_dequeue_script('selectWoo');
    wp_deregister_script('selectWoo');
  }
}
// Remove CSS and/or JS for Select2 END


// Mini cart widget buttons
remove_action('woocommerce_widget_shopping_cart_buttons', 'woocommerce_widget_shopping_cart_button_view_cart', 10);
remove_action('woocommerce_widget_shopping_cart_buttons', 'woocommerce_widget_shopping_cart_proceed_to_checkout', 20);

function my_woocommerce_widget_shopping_cart_button_view_cart() {
  echo '<a href="' . esc_url(wc_get_cart_url()) . '" class="btn btn-outline-primary d-block mb-2">' . esc_html__('View cart', 'woocommerce') . '</a>';
}
function my_woocommerce_widget_shopping_cart_proceed_to_checkout() {
  echo '<a href="' . esc_url(wc_get_checkout_url()) . '" class="btn btn-primary d-block">' . esc_html__('Checkout', 'woocommerce') . '</a>';
}
add_action('woocommerce_widget_shopping_cart_buttons', 'my_woocommerce_widget_shopping_cart_button_view_cart', 10);
add_action('woocommerce_widget_shopping_cart_buttons', 'my_woocommerce_widget_shopping_cart_proceed_to_checkout', 20);
// Mini cart widget buttons End


// Cart empty message alert
remove_action('woocommerce_cart_is_empty', 'wc_empty_cart_message', 10);
add_action('woocommerce_cart_is_empty', 'custom_empty_cart_message', 10);

function custom_empty_cart_message() {
  $html  = '<div class="cart-empty alert alert-info">';
  $html .= wp_kses_post(apply_filters('wc_empty_cart_message', __('Your cart is currently empty.', 'woocommerce')));
  echo $html . '</div>';
}
// Cart empty message alert End


// Add card-img-top class to product loop
remove_action('woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_thumbnail', 10);
add_action('woocommerce_before_shop_loop_item_title', 'custom_loop_product_thumbnail', 10);
function custom_loop_product_thumbnail() {
  global $product;
  $size = 'woocommerce_thumbnail';
  $code = 'class=card-img-top';

  $image_size = apply_filters('single_product_archive_thumbnail_size', $size);

  echo $product ? $product->get_image($image_size, $code) : '';
}
// Add card-img-top class to product loop End


// Category loop button and badge
if (!function_exists('woocommerce_template_loop_category_title')) :
  function woocommerce_template_loop_category_title($category) {
  ?>
    <h2 class="woocommerce-loop-category__title btn btn-primary w-100 mb-0">
      <?php
      echo $category->name;

      if ($category->count > 0)
        echo apply_filters('woocommerce_subcategory_count_html', ' <mark class="count badge bg-white text-dark">' . $category->count . '</mark>', $category);
      ?>
    </h2>
<?php
  }
endif;
// Category loop button and badge End


// Correct hooked checkboxes in checkout
/**
 * Get the corrected terms for Woocommerce.
 *
 * @param  string $html The original terms.
 * @return string The corrected terms.
 */
function bootscore_wc_get_corrected_terms($html) {
  $doc = new DOMDocument();
  if (!empty($html) && $doc->loadHtml($html)) {
    $documentElement = $doc->documentElement; // Won't find the right child-notes without that line. ads html and body tag as a wrapper
    $somethingWasCorrected = false;
    foreach ($documentElement->childNodes[0]->childNodes as $mainNode) {
      if ($mainNode->childNodes->length && strpos($mainNode->getAttribute("class"), "form-row") !== false) {
        if (strpos($mainNode->getAttribute("class"), "required") !== false) {
          $mainNode->setAttribute("class", "form-row validate-required"); // You could try to keep the original class and only add the string, but I think that could ruin the design
        } else {
          $mainNode->setAttribute("class", "form-row woocommerce-validated");
        }
        $nodesLabel = $mainNode->getElementsByTagName("label");
        if ($nodesLabel->length) {
          $nodesLabel[0]->setAttribute("class", "woocommerce-form__label woocommerce-form__label-for-checkbox checkbox form-check display-inline-block d-inline-block");
        }
        $nodesInput = $mainNode->getElementsByTagName("input");
        if ($nodesInput->length) {
          $nodesInput[0]->setAttribute("class", "woocommerce-form__input woocommerce-form__input-checkbox input-checkbox form-check-input");
        }
        $somethingWasCorrected = true;
      }
    }
    if ($somethingWasCorrected) {
      return $doc->saveHTML();
    } else {
      return $html;
    }
  } else {
    //error maybe return $html?
  }
}

/**
 * Capture the output of a hook.
 *
 * @param  string $hookName The name of the hook to capture.
 * @return string The output of the hook.
 */
function bootscore_wc_capture_hook_output($hookName) {
  ob_start();
  do_action($hookName);
  $hookContent = ob_get_contents();
  ob_end_clean();
  return $hookContent;
}
// Correct hooked checkboxes in checkout End


// Redirect to my-account if offcanvas login failed
add_action('woocommerce_login_failed', 'bootscore_redirect_on_login_failed', 10, 0);
function bootscore_redirect_on_login_failed() {
  // Logout user doesn't have session, we need this to display notices
  if (!WC()->session->has_session()) {
    WC()->session->set_customer_session_cookie(true);
  }
  wp_redirect(wp_validate_redirect(wc_get_page_permalink('myaccount')));
  exit;
}
// Redirect to my-account if offcanvas login failed End


// Redirect to home on logout
add_action('wp_logout', 'bootscore_redirect_after_logout');
function bootscore_redirect_after_logout() {
  wp_redirect(home_url());
  exit();
}
// Redirect to home on logout End


// Redirect to my-account after (un)sucessful registration
add_action('wp_loaded', 'bootscore_redirect_after_registration', 999);
function bootscore_redirect_after_registration() {
  $nonce_value = isset($_POST['_wpnonce']) ? wp_unslash($_POST['_wpnonce']) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
  $nonce_value = isset($_POST['woocommerce-register-nonce']) ? wp_unslash($_POST['woocommerce-register-nonce']) : $nonce_value; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
  if (isset($_POST['register'], $_POST['email']) && wp_verify_nonce($nonce_value, 'woocommerce-register')) {
    if (!WC()->session->has_session()) {
      WC()->session->set_customer_session_cookie(true);
    }
    wp_redirect(wp_validate_redirect(wc_get_page_permalink('myaccount')));
    exit;
  }
}
// Redirect to my-account after (un)sucessful registration End

//add_action( 'woocommerce_after_shop_loop_item_title', 'wc_add_long_description', 9 );
/**
 * WooCommerce, Add Long Description to Products on Shop Page
 *
 *
 */
/**function wc_add_long_description() {
  global $post;
  $limit = 0;
  $text = $post->post_excerpt;
  if (str_word_count($text, 0) > $limit) {
      $arr = str_word_count($text, 2);
      $pos = array_keys($arr);
      $text = substr($text, 0, $pos[$limit]) . '...';
  }

	?>
    <div class="description">
        <?php echo apply_filters( 'the_content', $text ) ?>
    </div>
	<?php
}*/

function prefix_archive_product_title_tag( $tag ) {
	$tag = 'h3';
	return $tag;
}
add_filter( 'woocommerce_product_archive_title_tag', 'prefix_archive_product_title_tag' );

add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_title', 1 );
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_title', 5 );

remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_show_product_loop_sale_flash', 10 );


remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10 );
add_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_price', 9 );


// remove_action( 'woocommerce_before_shop_loop_item', 'woocommerce_template_loop_product_link_open', 10 );
// remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_product_link_close', 5 );

// Pagination Categories
if (!function_exists('aleaulavage_pagination')) :

  function aleaulavage_pagination($pages = '', $range = 2) {
    $showitems = ($range * 2) + 1;
    global $paged;
    // default page to one if not provided
    if(empty($paged)) $paged = 1;
    if ($pages == '') {
      global $wp_query;
      $pages = $wp_query->max_num_pages;

      if (!$pages)
        $pages = 1;
    }

    if (1 != $pages) {
      echo '<nav aria-label="Page navigation" role="navigation">';
      echo '<span class="sr-only">Page navigation</span>';
      echo '<ul class="pagination justify-content-center ft-wpbs mb-4">';


      if ($paged > 2 && $paged > $range + 1 && $showitems < $pages)
        echo '<li class="page-item"><a class="page-link" href="' . get_pagenum_link(1) . '" aria-label="First Page">&laquo;</a></li>';

      if ($paged > 1 && $showitems < $pages)
        echo '<li class="page-item"><a class="page-link" href="' . get_pagenum_link($paged - 1) . '" aria-label="Previous Page">&lsaquo;</a></li>';

      for ($i = 1; $i <= $pages; $i++) {
        if (1 != $pages && (!($i >= $paged + $range + 1 || $i <= $paged - $range - 1) || $pages <= $showitems))
          echo ($paged == $i) ? '<li class="page-item active"><span class="page-link"><span class="sr-only">Current Page </span>' . $i . '</span></li>' : '<li class="page-item"><a class="page-link" href="' . get_pagenum_link($i) . '"><span class="sr-only">Page </span>' . $i . '</a></li>';
      }

      if ($paged < $pages && $showitems < $pages)
        echo '<li class="page-item"><a class="page-link" href="' . get_pagenum_link(($paged === 0 ? 1 : $paged) + 1) . '" aria-label="Next Page">&rsaquo;</a></li>';

      if ($paged < $pages - 1 &&  $paged + $range - 1 < $pages && $showitems < $pages)
        echo '<li class="page-item"><a class="page-link" href="' . get_pagenum_link($pages) . '" aria-label="Last Page">&raquo;</a></li>';

      echo '</ul>';
      echo '</nav>';
      // Uncomment this if you want to show [Page 2 of 30]
      // echo '<div class="pagination-info mb-5 text-center">[ <span class="text-muted">Page</span> '.$paged.' <span class="text-muted">of</span> '.$pages.' ]</div>';
    }
  }

endif;
//Pagination Categories END

add_action( 'wp_footer', 'woocommerce_show_coupon', 99 );
function woocommerce_show_coupon() {
  echo '
  <script type="text/javascript">
  jQuery(document).ready(function($) {
  $(\'.checkout_coupon\').show();
  });
  </script>
  ';
}

add_filter( 'woocommerce_blocks_product_grid_item_html', 'ssu_custom_render_product_block', 10, 3);

function ssu_custom_render_product_block( $html, $data, $post ) {
    $productID = url_to_postid( $data->permalink );
    $product = wc_get_product( $productID );

    $limit = 4;
    $text = $product->get_short_description();
    if (str_word_count($text, 0) > $limit) {
        $arr = str_word_count($text, 2);
        $pos = array_keys($arr);
        $text = substr($text, 0, $pos[$limit]) . '...';
    }

    return
    '
    <li class="col-md-6 col-lg-4 col-xxl-3 mb-4" style="max-width: 302px;">
      <div class="card h-100 d-flex product type-product post-900 status-publish instock product_cat-default has-post-thumbnail taxable shipping-taxable purchasable product-type-simple">
        <a href="'.$product->get_permalink().'" class="woocommerce-LoopProduct-link woocommerce-loop-product__link">'.$product->get_image().'</a>
        <div class="card-body d-flex flex-column">
          <a href="'.$product->get_permalink().'" class="woocommerce-LoopProduct-link woocommerce-loop-product__link"></a>
          <a href="'.$product->get_permalink().'" class="woocommerce-LoopProduct-link woocommerce-loop-product__link">
            <span class="woocommerce-loop-product__title">'.$product->get_title().'</span>
          </a>
          <div class="description">
            '.$text.'
          </div>
        </div>
        <div class="price ms-3 mb-3">'.$product->get_price().'€</div>
      </div>
    </li>
    ';
}


add_filter( 'woocommerce_single_product_carousel_options', 'sf_update_woo_flexslider_options' );
/**
 * Filer WooCommerce Flexslider options - Add Dot Pagination Instead of Thumbnails
 */
function sf_update_woo_flexslider_options( $options ) {

    $options['controlNav'] = true;

    return $options;
}