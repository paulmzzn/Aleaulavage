<?php
/**
 * Système de tracking avancé pour le comportement client
 */

if (!defined('ABSPATH')) {
    exit;
}

require_once dirname(__DIR__) . '/core/config.php';

class ComportementTracker {
    
    private static $instance = null;
    private $session_id;
    private $user_id;
    private $tracking_enabled = true;
    
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->session_id = ComportementConfig::get_session_id();
        $this->user_id = get_current_user_id() ?: null;
        
        // Hooks WordPress
        $this->init_hooks();
    }
    
    private function init_hooks() {
        // Tracking automatique des événements WooCommerce
        add_action('woocommerce_add_to_cart', [$this, 'track_cart_add'], 10, 6);
        add_action('woocommerce_cart_item_removed', [$this, 'track_cart_remove'], 10, 2);
        add_action('woocommerce_checkout_order_processed', [$this, 'track_order_completed'], 10, 3);
        add_action('woocommerce_thankyou', [$this, 'track_order_thankyou'], 10, 1);
        
        // Tracking des vues de pages
        add_action('wp', [$this, 'track_page_view']);
        add_action('woocommerce_single_product_summary', [$this, 'track_product_view'], 1);
        
        // Tracking des recherches
        add_action('pre_get_posts', [$this, 'track_search_query']);
        
        // Tracking AJAX pour les interactions front-end
        add_action('wp_ajax_track_comportement', [$this, 'handle_ajax_tracking']);
        add_action('wp_ajax_nopriv_track_comportement', [$this, 'handle_ajax_tracking']);
        
        // Scripts front-end
        add_action('wp_enqueue_scripts', [$this, 'enqueue_tracking_scripts']);
    }
    
    public function track_event($event_type, $event_data = [], $custom_session_id = null) {
        if (!$this->tracking_enabled) {
            return false;
        }
        
        global $wpdb;
        
        $session_id = $custom_session_id ?: $this->session_id;
        
        // Enrichir les données d'événement
        $enriched_data = array_merge($event_data, [
            'user_id' => $this->user_id,
            'timestamp' => current_time('mysql'),
            'page_url' => $this->get_current_url(),
            'referrer' => wp_get_referer(),
            'user_agent' => $this->get_user_agent(),
            'ip_address' => $this->get_client_ip(),
            'device_info' => $this->get_device_info()
        ]);
        
        $table = $wpdb->prefix . ComportementConfig::TABLE_EVENTS;
        
        $result = $wpdb->insert(
            $table,
            [
                'session_id' => $session_id,
                'user_id' => $this->user_id,
                'event_type' => sanitize_text_field($event_type),
                'event_data' => wp_json_encode($enriched_data),
                'page_url' => $this->get_current_url(),
                'referrer_url' => wp_get_referer(),
                'user_agent' => $this->get_user_agent(),
                'ip_address' => $this->get_client_ip(),
                'timestamp' => current_time('mysql')
            ],
            ['%s', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s']
        );
        
        return $result !== false;
    }
    
    public function track_cart_add($cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data) {
        $product = wc_get_product($product_id);
        if (!$product) return;
        
        // Tracking dans la nouvelle table d'événements
        $this->track_event('cart_add', [
            'product_id' => $product_id,
            'variation_id' => $variation_id,
            'quantity' => $quantity,
            'price' => $product->get_price(),
            'product_name' => $product->get_name(),
            'product_category' => $this->get_product_categories($product_id),
            'cart_total' => WC()->cart->get_cart_contents_total()
        ]);
        
        // Mise à jour de l'ancien système
        $this->update_legacy_cart_tracking($product_id, $quantity, $variation_id);
    }
    
    public function track_cart_remove($cart_item_key, $cart) {
        $cart_item = $cart->removed_cart_contents[$cart_item_key] ?? null;
        if (!$cart_item) return;
        
        $this->track_event('cart_remove', [
            'product_id' => $cart_item['product_id'],
            'variation_id' => $cart_item['variation_id'] ?? null,
            'quantity' => $cart_item['quantity'],
            'product_name' => $cart_item['data']->get_name(),
            'reason' => 'user_action'
        ]);
    }
    
    public function track_order_completed($order_id, $posted_data, $order) {
        if (!$order) return;
        
        $order_items = [];
        foreach ($order->get_items() as $item) {
            $order_items[] = [
                'product_id' => $item->get_product_id(),
                'variation_id' => $item->get_variation_id(),
                'quantity' => $item->get_quantity(),
                'total' => $item->get_total()
            ];
        }
        
        $this->track_event('order_completed', [
            'order_id' => $order_id,
            'total' => $order->get_total(),
            'currency' => $order->get_currency(),
            'payment_method' => $order->get_payment_method(),
            'items' => $order_items,
            'customer_email' => $order->get_billing_email(),
            'customer_type' => $this->user_id ? 'registered' : 'guest'
        ]);
        
        // Marquer les paniers comme convertis
        $this->mark_cart_as_converted();
    }
    
    public function track_page_view() {
        if (is_admin() || wp_doing_ajax() || wp_doing_cron()) {
            return;
        }
        
        $page_type = $this->get_page_type();
        $page_data = [
            'page_type' => $page_type,
            'page_title' => get_the_title() ?: wp_title('', false),
            'is_mobile' => wp_is_mobile(),
            'load_time' => null // À remplir via JavaScript
        ];
        
        // Données spécifiques selon le type de page
        if (is_product()) {
            global $product;
            // S'assurer que nous avons un objet produit valide
            if (!$product || !is_object($product)) {
                $product = wc_get_product(get_the_ID());
            }
            if ($product && is_object($product) && method_exists($product, 'get_id')) {
                $page_data['product_id'] = $product->get_id();
                $page_data['product_name'] = $product->get_name();
                $page_data['product_price'] = $product->get_price();
                $page_data['product_stock'] = $product->get_stock_status();
                $page_data['product_categories'] = $this->get_product_categories($product->get_id());
            }
        } elseif (is_product_category()) {
            $category = get_queried_object();
            if ($category) {
                $page_data['category_id'] = $category->term_id;
                $page_data['category_name'] = $category->name;
            }
        }
        
        $this->track_event('page_view', $page_data);
    }
    
    public function track_product_view() {
        if (!is_product()) return;
        
        global $product;
        // S'assurer que nous avons un objet produit valide
        if (!$product || !is_object($product)) {
            $product = wc_get_product(get_the_ID());
        }
        if (!$product || !is_object($product) || !method_exists($product, 'get_id')) return;
        
        $this->track_event('product_view', [
            'product_id' => $product->get_id(),
            'product_name' => $product->get_name(),
            'product_price' => $product->get_price(),
            'product_type' => $product->get_type(),
            'product_categories' => $this->get_product_categories($product->get_id()),
            'product_tags' => $this->get_product_tags($product->get_id()),
            'is_on_sale' => $product->is_on_sale(),
            'stock_status' => $product->get_stock_status(),
            'view_source' => $this->get_view_source()
        ]);
    }
    
    public function track_search_query($query) {
        if (!$query->is_main_query() || !$query->is_search() || is_admin()) {
            return;
        }
        
        $search_term = get_search_query();
        if (empty($search_term)) return;
        
        // Compter les résultats
        $result_count = $query->found_posts ?? 0;
        
        $this->track_event('search_performed', [
            'search_term' => $search_term,
            'result_count' => $result_count,
            'search_type' => 'site_search',
            'filters_applied' => $this->get_active_filters()
        ]);
        
        // Mise à jour de l'ancien système
        $this->update_legacy_search_tracking($search_term, $result_count);
    }
    
    public function handle_ajax_tracking() {
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'comportement_tracking')) {
            wp_die('Sécurité échouée');
        }
        
        $event_type = sanitize_text_field($_POST['event_type'] ?? '');
        $event_data = json_decode(stripslashes($_POST['event_data'] ?? '{}'), true);
        
        if (!$event_type) {
            wp_send_json_error('Type d\'événement manquant');
        }
        
        $result = $this->track_event($event_type, $event_data);
        
        wp_send_json_success(['tracked' => $result]);
    }
    
    public function enqueue_tracking_scripts() {
        if (is_admin()) return;
        
        wp_enqueue_script(
            'comportement-tracking',
            get_template_directory_uri() . '/includes/comportement/assets/tracking.js',
            ['jquery'],
            ComportementConfig::VERSION,
            true
        );
        
        wp_localize_script('comportement-tracking', 'ComportementTracker', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('comportement_tracking'),
            'session_id' => $this->session_id,
            'user_id' => $this->user_id,
            'events_enabled' => $this->tracking_enabled
        ]);
    }
    
    // Méthodes utilitaires
    
    private function get_current_url() {
        return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . 
               '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    }
    
    private function get_user_agent() {
        return sanitize_text_field($_SERVER['HTTP_USER_AGENT'] ?? '');
    }
    
    private function get_client_ip() {
        $ip_keys = ['HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'];
        
        foreach ($ip_keys as $key) {
            if (!empty($_SERVER[$key])) {
                $ips = explode(',', $_SERVER[$key]);
                $ip = trim($ips[0]);
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? '';
    }
    
    private function get_device_info() {
        $user_agent = $this->get_user_agent();
        
        return [
            'is_mobile' => wp_is_mobile(),
            'browser' => $this->detect_browser($user_agent),
            'os' => $this->detect_os($user_agent)
        ];
    }
    
    private function detect_browser($user_agent) {
        $browsers = [
            'Chrome' => '/Chrome/i',
            'Firefox' => '/Firefox/i',
            'Safari' => '/Safari/i',
            'Edge' => '/Edge/i',
            'Opera' => '/Opera/i'
        ];
        
        foreach ($browsers as $browser => $pattern) {
            if (preg_match($pattern, $user_agent)) {
                return $browser;
            }
        }
        
        return 'Unknown';
    }
    
    private function detect_os($user_agent) {
        $os_array = [
            'Windows' => '/Windows/i',
            'Mac' => '/Mac/i',
            'Linux' => '/Linux/i',
            'iOS' => '/iPhone|iPad/i',
            'Android' => '/Android/i'
        ];
        
        foreach ($os_array as $os => $pattern) {
            if (preg_match($pattern, $user_agent)) {
                return $os;
            }
        }
        
        return 'Unknown';
    }
    
    private function get_page_type() {
        if (is_front_page()) return 'home';
        if (is_shop()) return 'shop';
        if (is_product()) return 'product';
        if (is_product_category()) return 'category';
        if (is_product_tag()) return 'tag';
        if (is_cart()) return 'cart';
        if (is_checkout()) return 'checkout';
        if (is_account_page()) return 'account';
        if (is_search()) return 'search';
        if (is_404()) return '404';
        
        return 'other';
    }
    
    private function get_product_categories($product_id) {
        $terms = get_the_terms($product_id, 'product_cat');
        if (!$terms || is_wp_error($terms)) return [];
        
        return array_map(function($term) {
            return ['id' => $term->term_id, 'name' => $term->name];
        }, $terms);
    }
    
    private function get_product_tags($product_id) {
        $terms = get_the_terms($product_id, 'product_tag');
        if (!$terms || is_wp_error($terms)) return [];
        
        return array_map(function($term) {
            return ['id' => $term->term_id, 'name' => $term->name];
        }, $terms);
    }
    
    private function get_view_source() {
        $referrer = wp_get_referer();
        if (!$referrer) return 'direct';
        
        if (strpos($referrer, get_home_url()) === 0) {
            return 'internal';
        }
        
        // Détecter les moteurs de recherche
        $search_engines = [
            'google' => 'google.com',
            'bing' => 'bing.com',
            'yahoo' => 'yahoo.com',
            'duckduckgo' => 'duckduckgo.com'
        ];
        
        foreach ($search_engines as $engine => $domain) {
            if (strpos($referrer, $domain) !== false) {
                return 'search_' . $engine;
            }
        }
        
        return 'external';
    }
    
    private function get_active_filters() {
        $filters = [];
        
        // WooCommerce filters
        if (isset($_GET['min_price'])) $filters['min_price'] = sanitize_text_field($_GET['min_price']);
        if (isset($_GET['max_price'])) $filters['max_price'] = sanitize_text_field($_GET['max_price']);
        if (isset($_GET['orderby'])) $filters['orderby'] = sanitize_text_field($_GET['orderby']);
        
        return $filters;
    }
    
    // Méthodes de compatibilité avec l'ancien système
    
    private function update_legacy_cart_tracking($product_id, $quantity, $variation_id) {
        global $wpdb;
        
        $table_paniers = $wpdb->prefix . ComportementConfig::TABLE_PANIERS;
        
        $existing = $wpdb->get_row($wpdb->prepare(
            "SELECT id FROM $table_paniers WHERE session_id = %s AND product_id = %d AND (variation_id = %d OR variation_id IS NULL)",
            $this->session_id, $product_id, $variation_id
        ));
        
        if ($existing) {
            $wpdb->update(
                $table_paniers,
                [
                    'quantity' => $quantity,
                    'user_id' => $this->user_id,
                    'date_modif' => current_time('mysql'),
                    'ip_address' => $this->get_client_ip(),
                    'status' => 'active'
                ],
                ['id' => $existing->id],
                ['%d', '%d', '%s', '%s', '%s'],
                ['%d']
            );
        } else {
            $product = wc_get_product($product_id);
            $wpdb->insert(
                $table_paniers,
                [
                    'session_id' => $this->session_id,
                    'user_id' => $this->user_id,
                    'product_id' => $product_id,
                    'variation_id' => $variation_id,
                    'quantity' => $quantity,
                    'price' => $product ? $product->get_price() : null,
                    'ip_address' => $this->get_client_ip(),
                    'user_agent' => $this->get_user_agent(),
                    'referrer' => wp_get_referer(),
                    'status' => 'active'
                ],
                ['%s', '%d', '%d', '%d', '%d', '%f', '%s', '%s', '%s', '%s']
            );
        }
    }
    
    private function update_legacy_search_tracking($search_term, $result_count) {
        global $wpdb;
        
        $table_recherches = $wpdb->prefix . ComportementConfig::TABLE_RECHERCHES;
        
        $wpdb->insert(
            $table_recherches,
            [
                'session_id' => $this->session_id,
                'user_id' => $this->user_id,
                'terme_recherche' => $search_term,
                'resultats_count' => $result_count,
                'ip_address' => $this->get_client_ip(),
                'user_agent' => $this->get_user_agent(),
                'referrer' => wp_get_referer()
            ],
            ['%s', '%d', '%s', '%d', '%s', '%s', '%s']
        );
    }
    
    private function mark_cart_as_converted() {
        global $wpdb;
        
        $table_paniers = $wpdb->prefix . ComportementConfig::TABLE_PANIERS;
        
        $wpdb->update(
            $table_paniers,
            ['status' => 'converted'],
            ['session_id' => $this->session_id, 'status' => 'active'],
            ['%s'],
            ['%s', '%s']
        );
    }
    
    // Méthodes publiques pour contrôler le tracking
    
    public function enable_tracking() {
        $this->tracking_enabled = true;
    }
    
    public function disable_tracking() {
        $this->tracking_enabled = false;
    }
    
    public function is_tracking_enabled() {
        return $this->tracking_enabled;
    }
}