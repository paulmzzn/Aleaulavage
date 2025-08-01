<?php
/**
 * Module de tracking des recherches
 * 
 * @package Aleaulavage
 */

if (!defined('ABSPATH')) {
    exit;
}

class ComportementClientRechercheTracker {
    
    /**
     * Initialiser les hooks
     */
    public static function init() {
        add_action('wp', array(__CLASS__, 'tracker_recherches'));
    }
    
    /**
     * Tracker les recherches
     */
    public static function tracker_recherches() {
        if (!is_search() || empty(get_search_query())) {
            return;
        }
        
        global $wpdb;
        
        $session_info = ComportementClientSession::obtenir_infos_session();
        $session_id = $session_info['session_id'];
        $device_type = $session_info['device_type'];
        $user_id = get_current_user_id();
        $terme = get_search_query();
        
        // Vérifier si la recherche concerne des produits en rupture de stock
        $produits_rupture = self::verifier_produits_rupture_stock($terme);
        $resultats_count = self::compter_resultats_recherche();
        
        // Stocker dans la table des recherches
        $table_recherches = $wpdb->prefix . 'recherches_anonymes';
        $wpdb->insert(
            $table_recherches,
            array(
                'session_id' => $session_id,
                'user_id' => $user_id ? $user_id : null,
                'terme_recherche' => $terme,
                'device_type' => $device_type,
                'resultats_count' => $resultats_count,
                'produits_rupture_found' => count($produits_rupture),
                'is_no_stock_search' => count($produits_rupture) > 0 && $resultats_count == 0 ? 1 : 0
            )
        );
        
        // Si il y a des produits en rupture trouvés, les enregistrer
        if (!empty($produits_rupture)) {
            self::enregistrer_recherche_rupture_stock($session_id, $user_id, $terme, $produits_rupture, $device_type);
        }
        
        if ($user_id) {
            self::stocker_recherche_utilisateur($user_id, $terme, $device_type);
        }
    }
    
    /**
     * Stocker la recherche pour un utilisateur connecté
     */
    private static function stocker_recherche_utilisateur($user_id, $terme, $device_type) {
        $recherches_user = get_user_meta($user_id, 'recherche_logs', true);
        if (!is_array($recherches_user)) {
            $recherches_user = array();
        }
        
        $recherches_user[] = array(
            'q' => $terme,
            'date' => current_time('mysql'),
            'device_type' => $device_type
        );
        
        // Garder seulement les 20 dernières recherches
        if (count($recherches_user) > 20) {
            $recherches_user = array_slice($recherches_user, -20);
        }
        
        update_user_meta($user_id, 'recherche_logs', $recherches_user);
    }
    
    /**
     * Obtenir les statistiques de recherche par device
     */
    public static function obtenir_stats_recherche_par_device($periode_jours = 30) {
        global $wpdb;
        
        $date_limite = date('Y-m-d H:i:s', strtotime("-{$periode_jours} days"));
        $table_recherches = $wpdb->prefix . 'recherches_anonymes';
        
        $stats = $wpdb->get_results($wpdb->prepare("
            SELECT 
                device_type,
                COUNT(*) as total_recherches,
                COUNT(DISTINCT terme_recherche) as termes_uniques,
                COUNT(DISTINCT COALESCE(user_id, session_id)) as utilisateurs_uniques
            FROM $table_recherches 
            WHERE date_recherche >= %s 
            GROUP BY device_type
            ORDER BY total_recherches DESC
        ", $date_limite));
        
        return $stats;
    }
    
    /**
     * Obtenir les termes les plus recherchés par device
     */
    public static function obtenir_termes_populaires_par_device($device_type = null, $limite = 10) {
        global $wpdb;
        
        $table_recherches = $wpdb->prefix . 'recherches_anonymes';
        $where_clause = '';
        $params = array();
        
        if ($device_type) {
            $where_clause = "WHERE device_type = %s";
            $params[] = $device_type;
        }
        
        $params[] = $limite;
        
        $termes = $wpdb->get_results($wpdb->prepare("
            SELECT 
                terme_recherche,
                device_type,
                COUNT(*) as total_recherches,
                COUNT(DISTINCT COALESCE(user_id, session_id)) as utilisateurs_uniques
            FROM $table_recherches 
            $where_clause
            GROUP BY terme_recherche, device_type
            ORDER BY total_recherches DESC
            LIMIT %d
        ", $params));
        
        return $termes;
    }
    
    /**
     * Vérifier si une recherche concerne des produits en rupture de stock
     */
    private static function verifier_produits_rupture_stock($terme) {
        $args = array(
            'post_type' => 'product',
            'post_status' => 'publish',
            's' => $terme,
            'meta_query' => array(
                array(
                    'key' => '_stock_status',
                    'value' => 'outofstock',
                    'compare' => '='
                )
            ),
            'fields' => 'ids',
            'posts_per_page' => 50
        );
        
        return get_posts($args);
    }
    
    /**
     * Compter les résultats de recherche actuels
     */
    private static function compter_resultats_recherche() {
        $terme = get_search_query();
        if (empty($terme)) {
            return 0;
        }
        
        // Faire une vraie requête pour compter les résultats
        $args = array(
            'post_type' => array('product', 'post', 'page'),
            'post_status' => 'publish',
            's' => $terme,
            'fields' => 'ids',
            'posts_per_page' => 1
        );
        
        $search_query = new WP_Query($args);
        return $search_query->found_posts;
    }
    
    /**
     * Enregistrer une recherche liée à des produits en rupture
     */
    private static function enregistrer_recherche_rupture_stock($session_id, $user_id, $terme, $produits_rupture, $device_type) {
        global $wpdb;
        
        $table_rupture = $wpdb->prefix . 'recherches_rupture_stock';
        
        foreach ($produits_rupture as $product_id) {
            $product = wc_get_product($product_id);
            if ($product) {
                $wpdb->insert(
                    $table_rupture,
                    array(
                        'session_id' => $session_id,
                        'user_id' => $user_id,
                        'terme_recherche' => $terme,
                        'product_id' => $product_id,
                        'product_name' => $product->get_name(),
                        'device_type' => $device_type,
                        'date_recherche' => current_time('mysql')
                    )
                );
            }
        }
    }
    
    /**
     * Obtenir les recherches de produits en rupture les plus fréquentes
     */
    public static function obtenir_recherches_rupture_populaires($limite = 20) {
        global $wpdb;
        
        $table_rupture = $wpdb->prefix . 'recherches_rupture_stock';
        
        return $wpdb->get_results($wpdb->prepare("
            SELECT 
                terme_recherche,
                product_name,
                COUNT(*) as total_recherches,
                COUNT(DISTINCT COALESCE(user_id, session_id)) as utilisateurs_uniques,
                MAX(date_recherche) as derniere_recherche
            FROM $table_rupture 
            WHERE date_recherche >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            GROUP BY terme_recherche, product_id
            ORDER BY total_recherches DESC
            LIMIT %d
        ", $limite));
    }
    
    /**
     * Obtenir les produits jamais achetés mais souvent recherchés
     */
    public static function obtenir_produits_jamais_achetes() {
        global $wpdb;
        
        return $wpdb->get_results("
            SELECT DISTINCT
                p.ID as product_id,
                p.post_title as product_name,
                COUNT(r.terme_recherche) as recherches_count
            FROM {$wpdb->posts} p
            LEFT JOIN {$wpdb->prefix}recherches_rupture_stock r ON p.ID = r.product_id
            WHERE p.post_type = 'product'
            AND p.post_status = 'publish'
            AND p.ID NOT IN (
                SELECT DISTINCT product_id 
                FROM {$wpdb->prefix}woocommerce_order_items oi
                JOIN {$wpdb->prefix}woocommerce_order_itemmeta oim ON oi.order_item_id = oim.order_item_id
                WHERE oim.meta_key = '_product_id'
            )
            GROUP BY p.ID, p.post_title
            HAVING recherches_count > 0
            ORDER BY recherches_count DESC
            LIMIT 50
        ");
    }
    
    /**
     * Obtenir les produits souvent ajoutés au panier mais jamais achetés
     */
    public static function obtenir_produits_panier_non_achetes() {
        global $wpdb;
        
        $table_paniers = $wpdb->prefix . 'paniers_anonymes';
        
        return $wpdb->get_results("
            SELECT 
                p.product_id,
                pr.post_title as product_name,
                COUNT(*) as ajouts_panier,
                COUNT(DISTINCT p.session_id) as sessions_uniques
            FROM $table_paniers p
            JOIN {$wpdb->posts} pr ON p.product_id = pr.ID
            WHERE p.product_id NOT IN (
                SELECT DISTINCT product_id 
                FROM {$wpdb->prefix}woocommerce_order_items oi
                JOIN {$wpdb->prefix}woocommerce_order_itemmeta oim ON oi.order_item_id = oim.order_item_id
                WHERE oim.meta_key = '_product_id'
            )
            AND p.date_ajout >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            GROUP BY p.product_id, pr.post_title
            ORDER BY ajouts_panier DESC
            LIMIT 50
        ");
    }
}

// Initialiser le tracker
ComportementClientRechercheTracker::init();