<?php
/**
 * The template for displaying the footer
 */
?>

<footer id="footer" class="pt-5 pb-3 text-light" style="background-color: #14274a;">
    <div class="container">
        <!-- Logo -->
        <img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/logo-white.svg"
             alt="<?php echo esc_attr(get_bloginfo('name')); ?>"
             class="logo mb-4"
             style="max-width: 120px;">

        <div class="row mt-4">
            <!-- Column 1: Contact -->
            <div class="col mt-2">
                <p><strong>Nous contacter</strong></p>
                <?php if (is_active_sidebar('footer_contact_area')) : ?>
                    <div class="col-md-12">
                        <?php dynamic_sidebar('footer_contact_area'); ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Column 2: Footer Menu -->
            <div class="col mt-2">
                <?php
                if (has_nav_menu('footer-menu')) :
                    wp_nav_menu(array(
                        'theme_location' => 'footer-menu',
                        'container' => false,
                        'menu_class' => '',
                        'fallback_cb' => '__return_false',
                        'items_wrap' => '<ul id="footer-menu" class="%2$s">%3$s</ul>'
                    ));
                endif;
                ?>
            </div>

            <!-- Column 3: Social & Schedule -->
            <div class="col mt-2">
                <p><strong>Nos réseaux sociaux</strong></p>
                <?php if (is_active_sidebar('footer_social_area')) : ?>
                    <div class="col-md-12">
                        <?php dynamic_sidebar('footer_social_area'); ?>
                    </div>
                <?php endif; ?>

                <p class="mt-4"><strong>Horaire de contact</strong></p>
                <?php if (is_active_sidebar('footer_schedule_area')) : ?>
                    <div class="col-md-12">
                        <?php dynamic_sidebar('footer_schedule_area'); ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Bottom Row: Legal Menu & Copyright -->
        <div class="md-6 mt-5 row">
            <div class="col">
                <?php
                if (has_nav_menu('footer-legal')) :
                    wp_nav_menu(array(
                        'theme_location' => 'footer-legal',
                        'container' => false,
                        'menu_class' => '',
                        'fallback_cb' => '__return_false',
                        'items_wrap' => '<ul id="footer-legal" class="%2$s">%3$s</ul>'
                    ));
                endif;
                ?>
            </div>
            <div class="col">
                <p class="text-md-end">
                    <?php printf(esc_html__('&copy; %1$s %2$s.', 'aleaulavage-v2'), date_i18n('Y'), get_bloginfo('name', 'display')); ?>
                </p>
            </div>
        </div>
    </div><!-- /.container -->
</footer><!-- #footer -->

</div><!-- #page -->

<?php wp_footer(); ?>

<script>
// Redirection automatique pour le menu déroulant de catégories
document.addEventListener('DOMContentLoaded', function() {
    // Sélectionner tous les selects de catégories WooCommerce
    const categorySelects = document.querySelectorAll('.wc-block-product-categories select, .modern-category-select select');

    categorySelects.forEach(function(select) {
        select.addEventListener('change', function() {
            const url = this.value;
            if (url && url !== 'false' && url !== '') {
                window.location.href = url;
            }
        });
    });

    // Initialiser les icônes Lucide
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
});
</script>

</body>
</html>