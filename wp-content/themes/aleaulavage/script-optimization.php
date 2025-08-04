<?php
/**
 * =====================================================
 * OPTIMISATION SCRIPTS WORDPRESS/WOOCOMMERCE
 * =====================================================
 * 
 * Améliore les performances en retardant les scripts non essentiels
 * et en priorisant le rendu HTML pour un First Paint plus rapide
 * 
 * @author Expert WordPress & WooCommerce
 * @version 1.0
 */

// Constante pour désactiver facilement l'optimisation
if (!defined('DISABLE_ASYNC_OPTIMIZATION')) {
    define('DISABLE_ASYNC_OPTIMIZATION', false);
}

// Éviter l'exécution si désactivé ou si on est dans l'admin
if (DISABLE_ASYNC_OPTIMIZATION || is_admin()) {
    return;
}

class ScriptOptimizer {
    
    private static $instance = null;
    
    /**
     * Scripts critiques qui ne doivent JAMAIS être modifiés
     */
    private $critical_scripts = [
        'jquery-core',
        'jquery',
        'wp-embed',
        'wp-polyfill',
        'regenerator-runtime',
        'wp-polyfill-inert'
    ];
    
    /**
     * Scripts WooCommerce critiques pour le panier/checkout
     */
    private $wc_critical_scripts = [
        'woocommerce',
        'wc-checkout',
        'wc-add-to-cart',
        'wc-single-product',
        'wc-cart'
    ];
    
    /**
     * Scripts à mettre en defer (chargement différé)
     */
    private $defer_scripts = [
        'wc-cart-fragments',
        'jquery-migrate',
        'wp-block-library',
        'wp-block-library-theme',
        'contact-form-7',
        'elementor-frontend',
        'elementor-pro-frontend',
        'swiper',
        'photoswipe',
        'selectwoo'
    ];
    
    /**
     * Scripts à mettre en async (chargement asynchrone)
     */
    private $async_scripts = [
        'hotjar',
        'gtag',
        'google-analytics',
        'google-tag-manager',
        'facebook-pixel',
        'linkedin-insight',
        'pinterest-tracker',
        'mailchimp',
        'intercom',
        'zendesk',
        'crisp',
        'tawk-to'
    ];
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        // Initialiser les optimisations
        add_action('init', [$this, 'init_optimizations'], 1);
    }
    
    public function init_optimizations() {
        // Optimiser les scripts
        add_filter('script_loader_tag', [$this, 'optimize_script_loading'], 10, 3);
        
        // Optimiser les styles (bonus)
        add_filter('style_loader_tag', [$this, 'optimize_style_loading'], 10, 2);
        
        // Remplacer wc-cart-fragments par une version optimisée
        add_action('wp_enqueue_scripts', [$this, 'optimize_wc_cart_fragments'], 999);
        
        // Retarder les scripts de suivi
        add_action('wp_footer', [$this, 'load_tracking_scripts_delayed'], 999);
        
        // Prioriser le rendu HTML
        add_action('wp_head', [$this, 'add_render_optimization'], 1);
    }
    
    /**
     * Optimisation principale des balises script
     */
    public function optimize_script_loading($tag, $handle, $src) {
        // Ne pas modifier les scripts critiques
        if ($this->is_critical_script($handle)) {
            return $tag;
        }
        
        // Scripts à mettre en defer
        if ($this->should_defer_script($handle, $src)) {
            return $this->add_defer_to_script($tag, $handle);
        }
        
        // Scripts à mettre en async
        if ($this->should_async_script($handle, $src)) {
            return $this->add_async_to_script($tag, $handle);
        }
        
        // Scripts externes de suivi - async par défaut
        if ($this->is_external_tracking_script($src)) {
            return $this->add_async_to_script($tag, $handle);
        }
        
        return $tag;
    }
    
    /**
     * Vérifier si un script est critique
     */
    private function is_critical_script($handle) {
        // Scripts WordPress critiques
        if (in_array($handle, $this->critical_scripts)) {
            return true;
        }
        
        // Scripts WooCommerce critiques sur pages e-commerce
        if ($this->is_ecommerce_page() && in_array($handle, $this->wc_critical_scripts)) {
            return true;
        }
        
        // Scripts du thème critique (personnalisable)
        $theme_critical = apply_filters('script_optimizer_critical_scripts', [
            'main',
            'mainjs',
            'custom-header',
            get_template() . '-script'
        ]);
        
        if (in_array($handle, $theme_critical)) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Vérifier si on doit mettre un script en defer
     */
    private function should_defer_script($handle, $src) {
        return in_array($handle, $this->defer_scripts) || 
               $this->contains_patterns($handle, ['block-ui', 'migrate', 'fragments']);
    }
    
    /**
     * Vérifier si on doit mettre un script en async
     */
    private function should_async_script($handle, $src) {
        return in_array($handle, $this->async_scripts) ||
               $this->contains_patterns($handle, ['tracking', 'analytics', 'pixel', 'tag-manager']);
    }
    
    /**
     * Vérifier si c'est un script de suivi externe
     */
    private function is_external_tracking_script($src) {
        $tracking_domains = [
            'google-analytics.com',
            'googletagmanager.com',
            'hotjar.com',
            'facebook.com',
            'connect.facebook.net',
            'linkedin.com',
            'pinterest.com',
            'twitter.com',
            'doubleclick.net',
            'googlesyndication.com'
        ];
        
        foreach ($tracking_domains as $domain) {
            if (strpos($src, $domain) !== false) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Ajouter defer à un script
     */
    private function add_defer_to_script($tag, $handle) {
        if (strpos($tag, 'defer') !== false) {
            return $tag; // Déjà en defer
        }
        
        return str_replace(' src=', ' defer src=', $tag);
    }
    
    /**
     * Ajouter async à un script
     */
    private function add_async_to_script($tag, $handle) {
        if (strpos($tag, 'async') !== false) {
            return $tag; // Déjà en async
        }
        
        return str_replace(' src=', ' async src=', $tag);
    }
    
    /**
     * Optimisation spéciale pour wc-cart-fragments
     */
    public function optimize_wc_cart_fragments() {
        if (!class_exists('WooCommerce')) {
            return;
        }
        
        // Désenregistrer le script original
        wp_deregister_script('wc-cart-fragments');
        
        // Réenregistrer avec optimisations
        wp_register_script(
            'wc-cart-fragments-optimized',
            WC()->plugin_url() . '/assets/js/frontend/cart-fragments.min.js',
            ['jquery'],
            WC_VERSION,
            true
        );
        
        // Charger seulement si nécessaire
        if ($this->needs_cart_fragments()) {
            wp_enqueue_script('wc-cart-fragments-optimized');
            
            // Configuration optimisée
            wp_localize_script('wc-cart-fragments-optimized', 'wc_cart_fragments_params', [
                'ajax_url' => WC()->ajax_url(),
                'wc_ajax_url' => WC_AJAX::get_endpoint('%%endpoint%%'),
                'cart_hash_key' => apply_filters('woocommerce_cart_hash_key', 'wc_cart_hash_' . md5(get_current_blog_id() . '_' . get_site_url(get_current_blog_id(), '/') . get_template())),
                'fragment_name' => apply_filters('woocommerce_cart_fragment_name', 'wc_fragments_' . md5(get_current_blog_id() . '_' . get_site_url(get_current_blog_id(), '/') . get_template())),
                'request_timeout' => 5000
            ]);
        }
    }
    
    /**
     * Vérifier si cart-fragments est nécessaire
     */
    private function needs_cart_fragments() {
        // Toujours sur les pages WooCommerce
        if (is_woocommerce() || is_cart() || is_checkout() || is_account_page()) {
            return true;
        }
        
        // Si il y a des éléments de panier dans le header
        if (has_nav_menu('primary') || is_active_sidebar('shop-sidebar')) {
            return true;
        }
        
        // Si WooCommerce blocks sont utilisés
        if (has_block('woocommerce/mini-cart') || has_block('woocommerce/cart')) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Charger les scripts de suivi avec délai
     */
    public function load_tracking_scripts_delayed() {
        ?>
        <script>
        // Charger les scripts de suivi après interaction utilisateur ou délai
        document.addEventListener('DOMContentLoaded', function() {
            let scriptsLoaded = false;
            
            function loadTrackingScripts() {
                if (scriptsLoaded) return;
                scriptsLoaded = true;
                
                // Charger Hotjar avec délai
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
                
                // Autres scripts de suivi peuvent être ajoutés ici
                <?php do_action('script_optimizer_delayed_tracking'); ?>
            }
            
            // Charger après interaction utilisateur
            ['mouseenter', 'click', 'scroll', 'keydown'].forEach(function(event) {
                document.addEventListener(event, loadTrackingScripts, {once: true, passive: true});
            });
            
            // Ou après 3 secondes maximum
            setTimeout(loadTrackingScripts, 3000);
        });
        </script>
        <?php
    }
    
    /**
     * Optimisations pour le rendu HTML
     */
    public function add_render_optimization() {
        ?>
        <!-- Optimisations de rendu -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://www.google-analytics.com">
        <link rel="preconnect" href="https://static.hotjar.com">
        <link rel="dns-prefetch" href="//ajax.googleapis.com">
        <link rel="dns-prefetch" href="//cdn.jsdelivr.net">
        
        <style>
        /* CSS critique inline pour First Paint */
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; }
        .header, .navbar { display: block; }
        img { max-width: 100%; height: auto; }
        </style>
        <?php
    }
    
    /**
     * Optimiser le chargement des styles
     */
    public function optimize_style_loading($tag, $handle) {
        // Styles non critiques à charger avec media=print puis all
        $non_critical_styles = [
            'wp-block-library-theme',
            'contact-form-7',
            'elementor-icons',
            'fontawesome'
        ];
        
        if (in_array($handle, $non_critical_styles)) {
            $tag = str_replace("media='all'", "media='print' onload=\"this.media='all'\"", $tag);
            $tag = str_replace('media="all"', 'media="print" onload="this.media=\'all\'"', $tag);
        }
        
        return $tag;
    }
    
    /**
     * Utilitaires
     */
    private function is_ecommerce_page() {
        return class_exists('WooCommerce') && (
            is_woocommerce() || 
            is_cart() || 
            is_checkout() || 
            is_account_page()
        );
    }
    
    private function contains_patterns($string, $patterns) {
        foreach ($patterns as $pattern) {
            if (strpos($string, $pattern) !== false) {
                return true;
            }
        }
        return false;
    }
}

// Initialiser l'optimiseur
ScriptOptimizer::get_instance();

/**
 * =====================================================
 * FONCTIONS UTILITAIRES POUR PERSONNALISATION
 * =====================================================
 */

/**
 * Ajouter des scripts critiques personnalisés
 * 
 * @param array $scripts Liste des handles de scripts critiques
 * @return array
 */
function add_critical_scripts($scripts) {
    $scripts[] = 'mon-script-critique';
    return $scripts;
}
add_filter('script_optimizer_critical_scripts', 'add_critical_scripts');

/**
 * Forcer un script en defer
 * 
 * @param string $handle Handle du script
 */
function force_script_defer($handle) {
    add_filter('script_loader_tag', function($tag, $script_handle) use ($handle) {
        if ($script_handle === $handle) {
            return str_replace(' src=', ' defer src=', $tag);
        }
        return $tag;
    }, 10, 2);
}

/**
 * Forcer un script en async
 * 
 * @param string $handle Handle du script
 */
function force_script_async($handle) {
    add_filter('script_loader_tag', function($tag, $script_handle) use ($handle) {
        if ($script_handle === $handle) {
            return str_replace(' src=', ' async src=', $tag);
        }
        return $tag;
    }, 10, 2);
}

/**
 * =====================================================
 * MONITORING ET DEBUG
 * =====================================================
 */

if (WP_DEBUG && isset($_GET['debug_scripts'])) {
    add_action('wp_footer', function() {
        global $wp_scripts;
        echo '<!-- Scripts Debug -->';
        echo '<script>console.log("Scripts chargés:", ' . json_encode(array_keys($wp_scripts->done)) . ');</script>';
    });
}

/**
 * =====================================================
 * EXEMPLE D'UTILISATION DANS FUNCTIONS.PHP
 * =====================================================
 */
/*
// Inclure le fichier d'optimisation
require_once get_template_directory() . '/script-optimization.php';

// Personnalisations optionnelles
add_action('init', function() {
    // Forcer un script spécifique en defer
    force_script_defer('mon-script-lourd');
    
    // Ajouter des scripts de suivi personnalisés
    add_action('script_optimizer_delayed_tracking', function() {
        echo "console.log('Scripts de suivi chargés');";
    });
});

// Désactiver temporairement si nécessaire
// define('DISABLE_ASYNC_OPTIMIZATION', true);
*/