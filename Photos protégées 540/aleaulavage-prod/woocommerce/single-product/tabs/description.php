<?php
/**
 * Description tab
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/tabs/description.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 2.0.0
 */

defined( 'ABSPATH' ) || exit;

global $post;

$heading = apply_filters( 'woocommerce_product_description_heading', __( 'Description', 'woocommerce' ) );

?>

<?php the_content(); ?>
<div class="bg-primary">
<figure class="wp-block-image aligncenter size-full is-resized"><img loading="lazy" decoding="async" width="493" height="493" src="https://aleaulavage.com/wp-content/uploads/2025/07/colis-offert.png" alt="" class="wp-image-2494" style="width:auto;height:32px;" srcset="https://aleaulavage.com/wp-content/uploads/2025/07/colis-offert.png 493w, https://aleaulavage.com/wp-content/uploads/2025/07/colis-offert-300x300.png 300w, https://aleaulavage.com/wp-content/uploads/2025/07/colis-offert-150x150.png 150w, https://aleaulavage.com/wp-content/uploads/2025/07/colis-offert-100x100.png 100w" sizes="auto, (max-width: 493px) 100vw, 493px"></figure>



<p class="has-text-align-center" style="color:#FFF">Livraison Offerte <br>dès 550 € d’achat.</p>
</div>