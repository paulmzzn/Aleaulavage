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
        
        // Debug: Log l'initialisation du tracker
        error_log("Comportement Tracker: Initialisation avec session_id={$this->session_id}, user_id={$this->user_id}");
        
        // Hooks WordPress
        $this->init_hooks();
    }
    
    private function init_hooks() {
        // Debug: Log l'enregistrement des hooks
        error_log("Comportement Tracker: Enregistrement des hooks");
        
        // Tracking automatique des événements WooCommerce
        add_action('woocommerce_add_to_cart', [$this, 'track_cart_add'], 10, 6);
        add_action('woocommerce_cart_item_removed', [$this, 'track_cart_remove'], 10, 2);
        add_action('woocommerce_checkout_order_processed', [$this, 'track_order_completed'], 10, 3);
        add_action('woocommerce_thankyou', [$this, 'track_order_thankyou'], 10, 1);
        
        // Tracking du contenu complet du panier
        add_action('woocommerce_add_to_cart', [$this, 'track_full_cart_content'], 20);
        add_action('woocommerce_cart_item_removed', [$this, 'track_full_cart_content'], 20);
        add_action('woocommerce_before_cart_item_quantity_zero', [$this, 'track_full_cart_content'], 20);
        add_action('woocommerce_cart_item_set_quantity', [$this, 'track_full_cart_content'], 20);
        
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
        
        error_log("Comportement Tracker: Hooks enregistrés - woocommerce_add_to_cart, wp, wp_enqueue_scripts");
    }
    
    public function track_event($event_type, $event_data = [], $custom_session_id = null) {
        if (!$this->tracking_enabled) {
            error_log("Comportement: Tracking désactivé");
            return false;
        }
        
        global $wpdb;
        
        $session_id = $custom_session_id ?: $this->session_id;
        $table = $wpdb->prefix . ComportementConfig::TABLE_EVENTS;
        
        // Vérifier si la table existe
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table'") === $table;
        if (!$table_exists) {
            error_log("Comportement: Table $table n'existe pas");
            return false;
        }
        
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
        
        error_log("Comportement: Tentative d'insertion événement $event_type dans $table");
        
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
        
        if ($result === false) {
            error_log("Comportement: Erreur insertion DB: " . $wpdb->last_error);
        } else {
            error_log("Comportement: Événement $event_type inséré avec succès (ID: " . $wpdb->insert_id . ")");
        }
        
        return $result !== false;
    }
    
    public function track_cart_add($cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data) {
        // Debug: Log l'ajout au panier
        error_log("Comportement: track_cart_add appelé pour produit $product_id, quantité $quantity");
        
        $product = wc_get_product($product_id);
        if (!$product) {
            error_log("Comportement: Produit $product_id non trouvé");
            return;
        }
        
        // Tracking dans la nouvelle table d'événements
        $result = $this->track_event('cart_add', [
            'product_id' => $product_id,
            'variation_id' => $variation_id,
            'quantity' => $quantity,
            'price' => $product->get_price(),
            'product_name' => $product->get_name(),
            'product_category' => $this->get_product_categories($product_id),
            'cart_total' => WC()->cart->get_cart_contents_total()
        ]);
        
        error_log("Comportement: track_event cart_add résultat: " . ($result ? 'succès' : 'échec'));
        
        // Mise à jour de l'ancien système
        $this->update_legacy_cart_tracking($product_id, $quantity, $variation_id);
    }
    
    public function track_cart_remove($cart_item_key, $cart) {
        error_log("Comportement: track_cart_remove appelé pour item $cart_item_key");
        
        $cart_item = $cart->removed_cart_contents[$cart_item_key] ?? null;
        if (!$cart_item) {
            error_log("Comportement: Cart item $cart_item_key non trouvé dans removed_cart_contents");
            return;
        }
        
        // Vérifier que l'objet produit existe
        $product_name = 'Produit inconnu';
        $product_price = 0;
        if (isset($cart_item['data']) && $cart_item['data'] && method_exists($cart_item['data'], 'get_name')) {
            $product_name = $cart_item['data']->get_name();
            $product_price = floatval($cart_item['data']->get_price());
        } elseif (isset($cart_item['product_id'])) {
            // Récupérer le produit par ID si l'objet data n'est pas disponible
            $product = wc_get_product($cart_item['product_id']);
            if ($product) {
                $product_name = $product->get_name();
                $product_price = floatval($product->get_price());
            }
        }
        
        $remove_data = [
            'product_id' => $cart_item['product_id'] ?? null,
            'variation_id' => $cart_item['variation_id'] ?? null,
            'quantity' => $cart_item['quantity'] ?? 1,
            'product_name' => $product_name,
            'product_price' => $product_price,
            'line_total' => floatval($cart_item['line_total'] ?? 0),
            'reason' => 'user_action',
            'removed_at' => current_time('mysql'),
            'cart_total_before' => WC()->cart->get_cart_contents_total()
        ];
        
        // Tracker l'événement de suppression
        $this->track_event('cart_remove', $remove_data);
        
        // Sauvegarder dans l'historique des suppressions
        $this->save_cart_removal_history($remove_data);
        
        error_log("Comportement: Cart remove tracké pour produit: $product_name (quantité: {$cart_item['quantity']})");
    }
    
    private function save_cart_removal_history($remove_data) {
        global $wpdb;
        
        // Créer une table d'historique des suppressions si elle n'existe pas
        $table_removals = $wpdb->prefix . 'comportement_cart_removals';
        
        // Créer la table si nécessaire
        $wpdb->query("CREATE TABLE IF NOT EXISTS $table_removals (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            session_id varchar(255) NOT NULL,
            user_id bigint(20) NULL,
            product_id bigint(20) NOT NULL,
            variation_id bigint(20) NULL,
            product_name varchar(255) NOT NULL,
            quantity int(11) NOT NULL DEFAULT 1,
            product_price decimal(10,2) NULL,
            line_total decimal(10,2) NULL,
            reason varchar(100) DEFAULT 'user_action',
            removed_at datetime DEFAULT CURRENT_TIMESTAMP,
            ip_address varchar(45) NULL,
            user_agent text NULL,
            cart_total_before decimal(10,2) NULL,
            PRIMARY KEY (id),
            KEY session_id (session_id),
            KEY user_id (user_id),
            KEY product_id (product_id),
            KEY removed_at (removed_at)
        ) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        
        // Insérer l'historique de suppression
        $insert_data = [
            'session_id' => $this->session_id,
            'user_id' => $this->user_id ?: null,
            'product_id' => $remove_data['product_id'],
            'variation_id' => $remove_data['variation_id'] ?: null,
            'product_name' => $remove_data['product_name'],
            'quantity' => $remove_data['quantity'],
            'product_price' => $remove_data['product_price'],
            'line_total' => $remove_data['line_total'],
            'reason' => $remove_data['reason'],
            'removed_at' => $remove_data['removed_at'],
            'ip_address' => $this->get_client_ip(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'cart_total_before' => $remove_data['cart_total_before']
        ];
        
        $result = $wpdb->insert($table_removals, $insert_data);
        
        if ($result === false) {
            error_log("Comportement: Erreur insertion historique suppression: " . $wpdb->last_error);
        } else {
            error_log("Comportement: Historique suppression sauvegardé - ID: {$wpdb->insert_id}");
        }
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
        
        // Éviter les duplications avec une clé unique par session et utilisateur
        $search_key = 'search_tracked_' . $this->session_id . '_' . ($this->user_id ?: 0) . '_' . md5($search_term);
        if (get_transient($search_key)) {
            error_log('Comportement: Duplication de recherche évitée pour: ' . $search_term);
            return;
        }
        set_transient($search_key, true, 300); // Éviter les duplications pendant 5 minutes
        
        // Compter les résultats
        $result_count = $query->found_posts ?? 0;
        
        error_log('Comportement: Tracking recherche: ' . $search_term . ' (résultats: ' . $result_count . ')');
        
        $this->track_event('search_performed', [
            'search_term' => $search_term,
            'result_count' => $result_count,
            'search_type' => 'site_search',
            'filters_applied' => $this->get_active_filters()
        ]);
        
        // Mise à jour de l'ancien système
        $this->update_legacy_search_tracking($search_term, $result_count);
    }
    
    public function track_search_alternative() {
        if (!is_search() || is_admin()) {
            return;
        }
        
        $search_term = get_search_query();
        if (empty($search_term)) return;
        
        global $wp_query;
        $result_count = $wp_query->found_posts ?? 0;
        
        $this->track_event('search_performed', [
            'search_term' => $search_term,
            'result_count' => $result_count,
            'search_type' => 'site_search_alternative',
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
        
        // Debug: Log que le script est appelé
        error_log('Comportement: Enqueue tracking scripts appelé');
        
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
        
        // Debug: Log les paramètres
        error_log('Comportement: Script configuré avec session_id=' . $this->session_id);
    }
    
    public function track_full_cart_content() {
        // Protection anti-duplication plus robuste
        $cart_hash_key = 'cart_hash_' . $this->session_id;
        $last_track_key = 'cart_tracked_' . $this->session_id;
        
        $current_time = time();
        $last_cart_track = get_transient($last_track_key);
        
        // Si on a tracké récemment (moins de 3 secondes), ignorer
        if ($last_cart_track && ($current_time - $last_cart_track) < 3) {
            error_log('Comportement: Track cart ignoré - trop récent (' . ($current_time - $last_cart_track) . 's)');
            return;
        }
        
        if (!WC()->cart) {
            error_log('Comportement: WC Cart non disponible pour track_full_cart_content');
            return;
        }
        
        // Calculer un hash du contenu du panier pour détecter les vrais changements
        $cart = WC()->cart;
        $cart_contents = $cart->get_cart();
        $cart_signature = md5(serialize($cart_contents));
        $last_cart_hash = get_transient($cart_hash_key);
        
        // Si le contenu n'a pas changé, ne pas tracker
        if ($last_cart_hash === $cart_signature) {
            error_log('Comportement: Track cart ignoré - contenu identique');
            return;
        }
        
        // Mettre à jour les transients
        set_transient($last_track_key, $current_time, 60);
        set_transient($cart_hash_key, $cart_signature, 60);
        $cart_contents = $cart->get_cart();
        
        if (empty($cart_contents)) {
            error_log('Comportement: Panier vide - tracking panier vide');
            // Tracker le panier vide
            $this->track_event('cart_snapshot', [
                'cart_empty' => true,
                'cart_total' => 0,
                'cart_count' => 0,
                'items' => []
            ]);
            return;
        }
        
        $cart_data = [
            'cart_empty' => false,
            'cart_total' => floatval($cart->get_cart_contents_total()),
            'cart_subtotal' => floatval($cart->get_subtotal()),
            'cart_tax' => floatval($cart->get_total_tax()),
            'cart_shipping' => floatval($cart->get_shipping_total()),
            'cart_discount' => floatval($cart->get_discount_total()),
            'cart_count' => $cart->get_cart_contents_count(),
            'cart_weight' => floatval($cart->get_cart_contents_weight()),
            'items' => []
        ];
        
        foreach ($cart_contents as $cart_item_key => $cart_item) {
            $product = $cart_item['data'];
            if (!$product) continue;
            
            $item_data = [
                'cart_item_key' => $cart_item_key,
                'product_id' => $cart_item['product_id'],
                'variation_id' => $cart_item['variation_id'] ?? 0,
                'quantity' => $cart_item['quantity'],
                'product_name' => $product->get_name(),
                'product_price' => floatval($product->get_price()),
                'line_total' => floatval($cart_item['line_total']),
                'line_tax' => floatval($cart_item['line_tax'] ?? 0),
                'product_sku' => $product->get_sku(),
                'product_weight' => floatval($product->get_weight()),
                'product_categories' => $this->get_product_categories($cart_item['product_id'])
            ];
            
            // Ajouter les variations si c'est un produit variable
            if ($cart_item['variation_id'] && !empty($cart_item['variation'])) {
                $item_data['variation'] = $cart_item['variation'];
            }
            
            $cart_data['items'][] = $item_data;
        }
        
        // Tracker l'événement complet du panier
        $result = $this->track_event('cart_snapshot', $cart_data);
        
        error_log('Comportement: Panier complet tracké - ' . count($cart_data['items']) . ' items, total: ' . $cart_data['cart_total'] . '€');
        
        // Également mettre à jour l'ancien système avec le panier complet
        $this->update_legacy_full_cart_tracking($cart_data);
    }
    
    private function update_legacy_full_cart_tracking($cart_data) {
        global $wpdb;
        
        $table_paniers = $wpdb->prefix . ComportementConfig::TABLE_PANIERS;
        
        // Utiliser une transaction pour éviter les conditions de course
        $wpdb->query('START TRANSACTION');
        
        try {
            // Nettoyer TOUS les anciens items de ce panier (session/user)
            $where_conditions = ['session_id' => $this->session_id];
            if ($this->user_id) {
                $where_conditions['user_id'] = $this->user_id;
            } else {
                // Pour les utilisateurs anonymes, on ne nettoie que par session_id
                // et on s'assure que user_id est NULL ou 0
                $deleted = $wpdb->query($wpdb->prepare(
                    "DELETE FROM $table_paniers WHERE session_id = %s AND (user_id IS NULL OR user_id = 0)",
                    $this->session_id
                ));
            }
            
            if ($this->user_id) {
                $wpdb->delete($table_paniers, $where_conditions);
            }
            
            $current_time = current_time('mysql');
            
            // Insérer tous les items actuels du panier
            foreach ($cart_data['items'] as $item) {
                $insert_data = [
                    'session_id' => $this->session_id,
                    'user_id' => $this->user_id ?: null,
                    'product_id' => $item['product_id'],
                    'variation_id' => $item['variation_id'] ?: null,
                    'quantity' => $item['quantity'],
                    'price' => $item['product_price'],
                    'date_ajout' => $current_time,
                    'date_modif' => $current_time,
                    'ip_address' => $this->get_client_ip(),
                    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
                    'status' => 'active'
                ];
                
                $result = $wpdb->insert($table_paniers, $insert_data);
                if ($result === false) {
                    throw new Exception('Erreur insertion panier: ' . $wpdb->last_error);
                }
            }
            
            $wpdb->query('COMMIT');
            error_log('Comportement: Legacy cart tracking mis à jour avec succès - ' . count($cart_data['items']) . ' items pour session ' . $this->session_id);
            
        } catch (Exception $e) {
            $wpdb->query('ROLLBACK');
            error_log('Comportement: Erreur transaction panier: ' . $e->getMessage());
        }
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