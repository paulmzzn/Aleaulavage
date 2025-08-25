<?php
/**
 * Test pour vérifier si notre template checkout est chargé
 */

// Ajouter une fonction de debug simple
add_action('wp_head', function() {
    if (is_checkout()) {
        echo '<script>console.log("🧪 TEST: On est sur une page checkout");</script>';
        echo '<style>body:before { content: "TEST CHECKOUT DEBUG ACTIF"; background: red; color: white; position: fixed; top: 0; left: 0; z-index: 9999; padding: 5px; }</style>';
    }
});

// Forcer le chargement de notre template
add_filter('template_include', function($template) {
    if (is_checkout()) {
        $custom_template = get_template_directory() . '/woocommerce/checkout/form-checkout.php';
        if (file_exists($custom_template)) {
            echo '<script>console.log("🎯 Template checkout personnalisé trouvé: ' . $custom_template . '");</script>';
            return $custom_template;
        }
    }
    return $template;
}, 99);

// Debug des assets
add_action('wp_enqueue_scripts', function() {
    if (is_checkout()) {
        wp_enqueue_script('test-checkout-debug', '', array('jquery'), '1.0', true);
        wp_add_inline_script('test-checkout-debug', 'console.log("🔧 SCRIPTS CHECKOUT CHARGÉS");');
    }
}, 99);
?>