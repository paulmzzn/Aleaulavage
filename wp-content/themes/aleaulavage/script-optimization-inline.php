<?php
/**
 * VERSION SIMPLIFIÉE - À COPIER/COLLER DANS FUNCTIONS.PHP
 * =====================================================
 * Optimisation Scripts WordPress/WooCommerce
 * Améliore les performances avec defer/async
 */

// Constante pour désactiver facilement (optionnel)
// define('DISABLE_ASYNC_OPTIMIZATION', true);

if (!defined('DISABLE_ASYNC_OPTIMIZATION') || !DISABLE_ASYNC_OPTIMIZATION) {

    /**
     * Optimiser le chargement des scripts
     */
    add_filter('script_loader_tag', function($tag, $handle, $src) {
        
        // Scripts critiques à ne jamais modifier
        $critical_scripts = [
            'jquery-core', 'jquery', 'woocommerce', 'wc-checkout', 
            'wc-add-to-cart', 'wc-single-product', 'custom-header'
        ];
        
        if (in_array($handle, $critical_scripts)) {
            return $tag;
        }
        
        // Scripts à mettre en defer
        $defer_scripts = [
            'wc-cart-fragments', 'jquery-migrate', 'wp-block-library',
            'contact-form-7', 'lucide-icons', 'variation-color-swatches'
        ];
        
        if (in_array($handle, $defer_scripts)) {
            return str_replace(' src=', ' defer src=', $tag);
        }
        
        // Scripts de suivi en async  
        $async_patterns = ['hotjar', 'gtag', 'analytics', 'pixel', 'tracking'];
        foreach ($async_patterns as $pattern) {
            if (strpos($handle, $pattern) !== false || strpos($src, $pattern) !== false) {
                return str_replace(' src=', ' async src=', $tag);
            }
        }
        
        // Scripts externes de suivi
        $tracking_domains = [
            'google-analytics.com', 'googletagmanager.com', 'hotjar.com',
            'facebook.com', 'connect.facebook.net'
        ];
        
        foreach ($tracking_domains as $domain) {
            if (strpos($src, $domain) !== false) {
                return str_replace(' src=', ' async src=', $tag);
            }
        }
        
        return $tag;
        
    }, 10, 3);

    /**
     * Optimiser wc-cart-fragments
     */
    add_action('wp_enqueue_scripts', function() {
        if (!class_exists('WooCommerce')) return;
        
        // Seulement sur les pages qui en ont besoin
        if (is_woocommerce() || is_cart() || is_checkout() || is_account_page()) {
            // Le script sera automatiquement mis en defer par le filtre ci-dessus
        }
    }, 999);

    /**
     * Charger les scripts de suivi avec délai
     */
    add_action('wp_footer', function() {
        ?>
        <script>
        // Charger scripts de suivi après interaction ou 3s
        document.addEventListener('DOMContentLoaded', function() {
            let loaded = false;
            function loadTracking() {
                if (loaded) return;
                loaded = true;
                
                // Hotjar avec délai
                setTimeout(function() {
                    if (typeof hj === 'undefined') {
                        (function(h,o,t,j,a,r){
                            h.hj=h.hj||function(){(h.hj.q=h.hj.q||[]).push(arguments)};
                            h._hjSettings={hjid:3224976,hjsv:6};
                            a=o.getElementsByTagName('head')[0];
                            r=o.createElement('script');r.async=1;
                            r.src=t+h._hjSettings.hjid+j+h._hjSettings.hjsv;
                            a.appendChild(r);
                        })(window,document,'https://static.hotjar.com/c/hotjar-','.js?sv=');
                    }
                }, 2000);
            }
            
            ['mouseenter','click','scroll','keydown'].forEach(e => 
                document.addEventListener(e, loadTracking, {once:true, passive:true})
            );
            setTimeout(loadTracking, 3000);
        });
        </script>
        <?php
    }, 999);

    /**
     * Optimisations de rendu HTML
     */
    add_action('wp_head', function() {
        ?>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://www.google-analytics.com">
        <link rel="preconnect" href="https://static.hotjar.com">
        <style>
        body{font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif}
        .header,.navbar{display:block}
        img{max-width:100%;height:auto}
        </style>
        <?php
    }, 1);

    /**
     * Optimiser les styles non critiques
     */
    add_filter('style_loader_tag', function($tag, $handle) {
        $non_critical = ['wp-block-library-theme', 'contact-form-7', 'fontawesome'];
        
        if (in_array($handle, $non_critical)) {
            return str_replace("media='all'", "media='print' onload=\"this.media='all'\"", $tag);
        }
        
        return $tag;
    }, 10, 2);

}

// Fonctions utilitaires
function force_script_defer($handle) {
    add_filter('script_loader_tag', function($tag, $script_handle) use ($handle) {
        return $script_handle === $handle ? str_replace(' src=', ' defer src=', $tag) : $tag;
    }, 10, 2);
}

function force_script_async($handle) {
    add_filter('script_loader_tag', function($tag, $script_handle) use ($handle) {
        return $script_handle === $handle ? str_replace(' src=', ' async src=', $tag) : $tag;
    }, 10, 2);
}

/* 
UTILISATION:
1. Copier ce code dans functions.php
2. Tester avec ?debug_perf=1 en étant admin
3. Désactiver si problème avec: define('DISABLE_ASYNC_OPTIMIZATION', true);
4. Personnaliser les arrays $critical_scripts, $defer_scripts selon vos besoins
*/