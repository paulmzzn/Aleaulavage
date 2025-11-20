<?php
/**
 * Template part for displaying the promo banner
 */

$promo_banner = get_theme_mod('promo_banner_message');

if (!empty($promo_banner)): ?>
    <div class="promo-banner">
        <?php echo wp_kses_post($promo_banner); ?>
    </div>
<?php endif; ?>