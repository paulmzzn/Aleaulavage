<?php
/**
 * Système d'analyse et de segmentation des comportements clients
 */

if (!defined('ABSPATH')) {
    exit;
}

require_once dirname(__DIR__) . '/core/config.php';

class ComportementAnalyzer {
    
    public static function analyze_user_segment($user_id = null, $session_id = null) {
        global $wpdb;
        
        if (!$user_id && !$session_id) {
            return 'unknown';
        }
        
        $segments = ComportementConfig::get_user_segments();
        $table_events = $wpdb->prefix . ComportementConfig::TABLE_EVENTS;
        $table_paniers = $wpdb->prefix . ComportementConfig::TABLE_PANIERS;
        
        // Analyser l'activité récente
        $where_clause = $user_id ? 
            $wpdb->prepare("user_id = %d", $user_id) : 
            $wpdb->prepare("session_id = %s AND user_id IS NULL", $session_id);
        
        // Compter les visites récentes
        $recent_visits = $wpdb->get_var($wpdb->prepare("
            SELECT COUNT(DISTINCT DATE(timestamp)) 
            FROM $table_events 
            WHERE $where_clause 
            AND event_type = 'page_view' 
            AND timestamp >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        "));
        
        // Vérifier les commandes récentes (pour utilisateurs connectés)
        $recent_orders = 0;
        if ($user_id) {
            $orders = wc_get_orders([
                'customer' => $user_id,
                'status' => ['completed', 'processing'],
                'date_created' => '>=' . strtotime('-30 days'),
                'limit' => -1
            ]);
            $recent_orders = count($orders);
        }
        
        // Vérifier les paniers abandonnés
        $abandoned_cart = $wpdb->get_var($wpdb->prepare("
            SELECT COUNT(*) 
            FROM $table_paniers 
            WHERE $where_clause 
            AND status = 'abandoned'
            AND date_modif >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        "));
        
        // Première visite
        $first_visit = $wpdb->get_var($wpdb->prepare("
            SELECT MIN(timestamp) 
            FROM $table_events 
            WHERE $where_clause
        "));
        
        $days_since_first_visit = $first_visit ? 
            (time() - strtotime($first_visit)) / (24 * 60 * 60) : 0;
        
        // Logique de segmentation
        if ($days_since_first_visit <= 1) {
            return 'nouveau_visiteur';
        }
        
        if ($recent_orders > 0) {
            if ($recent_orders >= 3) {
                return 'client_fidele';
            }
            return 'client_actif';
        }
        
        if ($abandoned_cart > 0) {
            return 'client_potentiel';
        }
        
        if ($recent_visits >= 2 && $recent_visits <= 5) {
            return 'visiteur_regulier';
        }
        
        if ($recent_visits == 0 || $days_since_first_visit > 90) {
            return 'client_inactif';
        }
        
        return 'visiteur_regulier';
    }
    
    public static function generate_user_insights($user_id) {
        global $wpdb;
        
        $table_events = $wpdb->prefix . ComportementConfig::TABLE_EVENTS;
        $insights = [];
        
        // Récupérer les données utilisateur
        $user_events = $wpdb->get_results($wpdb->prepare("
            SELECT event_type, event_data, timestamp 
            FROM $table_events 
            WHERE user_id = %d 
            AND timestamp >= DATE_SUB(NOW(), INTERVAL 90 DAY)
            ORDER BY timestamp DESC
        ", $user_id));
        
        if (empty($user_events)) {
            return ['message' => 'Aucune donnée comportementale disponible'];
        }
        
        // Analyser les patterns de navigation
        $page_views = array_filter($user_events, function($e) { return $e->event_type === 'page_view'; });
        $product_views = array_filter($user_events, function($e) { return $e->event_type === 'product_view'; });
        $searches = array_filter($user_events, function($e) { return $e->event_type === 'search_performed'; });
        $cart_adds = array_filter($user_events, function($e) { return $e->event_type === 'cart_add'; });
        
        // Insights sur la navigation
        $insights['navigation'] = [
            'total_page_views' => count($page_views),
            'unique_days_active' => count(array_unique(array_map(function($e) { 
                return date('Y-m-d', strtotime($e->timestamp)); 
            }, $user_events))),
            'avg_session_length' => self::calculate_avg_session_length($user_events),
            'most_viewed_pages' => self::get_most_viewed_pages($page_views)
        ];
        
        // Insights sur les produits
        $insights['products'] = [
            'products_viewed' => count($product_views),
            'unique_products' => self::get_unique_products_viewed($product_views),
            'favorite_categories' => self::get_favorite_categories($product_views),
            'cart_additions' => count($cart_adds),
            'conversion_rate' => count($cart_adds) > 0 ? 
                (count($cart_adds) / count($product_views)) * 100 : 0
        ];
        
        // Insights sur les recherches
        if (!empty($searches)) {
            $search_terms = array_map(function($e) {
                $data = json_decode($e->event_data, true);
                return $data['search_term'] ?? '';
            }, $searches);
            
            $insights['search_behavior'] = [
                'total_searches' => count($searches),
                'unique_terms' => count(array_unique($search_terms)),
                'most_searched_terms' => array_count_values($search_terms),
                'search_to_view_ratio' => count($searches) > 0 ? 
                    count($product_views) / count($searches) : 0
            ];
        }
        
        // Recommandations personnalisées
        $insights['recommendations'] = self::generate_recommendations($insights, $user_id);
        
        // Score d'engagement
        $insights['engagement_score'] = self::calculate_engagement_score($insights);
        
        return $insights;
    }
    
    public static function get_conversion_funnel_data($period_days = 30) {
        global $wpdb;
        
        $table_events = $wpdb->prefix . ComportementConfig::TABLE_EVENTS;
        $date_from = date('Y-m-d', strtotime("-{$period_days} days"));
        
        // Étapes du funnel
        $funnel_steps = [
            'visitors' => "SELECT COUNT(DISTINCT session_id) FROM $table_events WHERE event_type = 'page_view' AND timestamp >= '$date_from'",
            'product_viewers' => "SELECT COUNT(DISTINCT session_id) FROM $table_events WHERE event_type = 'product_view' AND timestamp >= '$date_from'",
            'cart_additions' => "SELECT COUNT(DISTINCT session_id) FROM $table_events WHERE event_type = 'cart_add' AND timestamp >= '$date_from'",
            'checkouts' => "SELECT COUNT(DISTINCT session_id) FROM $table_events WHERE event_type = 'checkout_started' AND timestamp >= '$date_from'",
            'orders' => "SELECT COUNT(DISTINCT session_id) FROM $table_events WHERE event_type = 'order_completed' AND timestamp >= '$date_from'"
        ];
        
        $funnel_data = [];
        foreach ($funnel_steps as $step => $query) {
            $count = $wpdb->get_var($query);
            $funnel_data[$step] = intval($count);
        }
        
        // Calculer les taux de conversion entre étapes
        $funnel_data['rates'] = [
            'visitor_to_product' => $funnel_data['visitors'] > 0 ? 
                ($funnel_data['product_viewers'] / $funnel_data['visitors']) * 100 : 0,
            'product_to_cart' => $funnel_data['product_viewers'] > 0 ? 
                ($funnel_data['cart_additions'] / $funnel_data['product_viewers']) * 100 : 0,
            'cart_to_checkout' => $funnel_data['cart_additions'] > 0 ? 
                ($funnel_data['checkouts'] / $funnel_data['cart_additions']) * 100 : 0,
            'checkout_to_order' => $funnel_data['checkouts'] > 0 ? 
                ($funnel_data['orders'] / $funnel_data['checkouts']) * 100 : 0,
            'overall_conversion' => $funnel_data['visitors'] > 0 ? 
                ($funnel_data['orders'] / $funnel_data['visitors']) * 100 : 0
        ];
        
        return $funnel_data;
    }
    
    public static function get_cohort_analysis($months = 12) {
        global $wpdb;
        
        $table_events = $wpdb->prefix . ComportementConfig::TABLE_EVENTS;
        $cohorts = [];
        
        // Récupérer les utilisateurs par cohorte (mois de première visite)
        $cohort_users = $wpdb->get_results($wpdb->prepare("
            SELECT 
                user_id,
                DATE_FORMAT(MIN(timestamp), '%%Y-%%m') as cohort_month,
                MIN(timestamp) as first_visit
            FROM $table_events 
            WHERE user_id IS NOT NULL 
            AND timestamp >= DATE_SUB(NOW(), INTERVAL %d MONTH)
            GROUP BY user_id
        ", $months));
        
        // Grouper par cohorte
        $cohort_groups = [];
        foreach ($cohort_users as $user) {
            $cohort_groups[$user->cohort_month][] = $user->user_id;
        }
        
        // Analyser la rétention pour chaque cohorte
        foreach ($cohort_groups as $month => $user_ids) {
            $cohorts[$month] = [
                'size' => count($user_ids),
                'retention' => []
            ];
            
            // Calculer la rétention pour chaque mois suivant
            for ($i = 0; $i <= 12; $i++) {
                $target_month = date('Y-m', strtotime($month . '-01 +' . $i . ' months'));
                
                $active_users = $wpdb->get_var($wpdb->prepare("
                    SELECT COUNT(DISTINCT user_id)
                    FROM $table_events 
                    WHERE user_id IN (" . implode(',', array_map('intval', $user_ids)) . ")
                    AND DATE_FORMAT(timestamp, '%%Y-%%m') = %s
                ", $target_month));
                
                $retention_rate = count($user_ids) > 0 ? 
                    ($active_users / count($user_ids)) * 100 : 0;
                
                $cohorts[$month]['retention'][$i] = round($retention_rate, 2);
            }
        }
        
        return $cohorts;
    }
    
    public static function detect_abandoned_carts($hours_threshold = 24) {
        global $wpdb;
        
        $table_paniers = $wpdb->prefix . ComportementConfig::TABLE_PANIERS;
        $threshold_date = date('Y-m-d H:i:s', strtotime("-{$hours_threshold} hours"));
        
        // Récupérer les paniers abandonnés
        $abandoned_carts = $wpdb->get_results($wpdb->prepare("
            SELECT 
                p.*,
                u.user_email,
                u.display_name,
                COUNT(*) as items_count,
                SUM(p.quantity * p.price) as cart_total
            FROM $table_paniers p
            LEFT JOIN {$wpdb->users} u ON p.user_id = u.ID
            WHERE p.status = 'active'
            AND p.date_modif < %s
            AND p.date_modif >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            GROUP BY p.session_id, p.user_id
            ORDER BY p.date_modif DESC
        ", $threshold_date));
        
        // Enrichir avec des données supplémentaires
        foreach ($abandoned_carts as &$cart) {
            // Récupérer les produits du panier
            $cart_items = $wpdb->get_results($wpdb->prepare("
                SELECT product_id, variation_id, quantity, price
                FROM $table_paniers 
                WHERE session_id = %s 
                AND (user_id = %d OR user_id IS NULL)
                AND status = 'active'
            ", $cart->session_id, $cart->user_id));
            
            $cart->items = [];
            foreach ($cart_items as $item) {
                $product = wc_get_product($item->product_id);
                if ($product) {
                    $cart->items[] = [
                        'product_id' => $item->product_id,
                        'name' => $product->get_name(),
                        'price' => $item->price,
                        'quantity' => $item->quantity,
                        'total' => $item->price * $item->quantity
                    ];
                }
            }
            
            // Marquer comme abandonné
            $wpdb->update(
                $table_paniers,
                ['status' => 'abandoned'],
                [
                    'session_id' => $cart->session_id,
                    'status' => 'active'
                ]
            );
        }
        
        return $abandoned_carts;
    }
    
    public static function get_real_time_stats() {
        global $wpdb;
        
        $table_events = $wpdb->prefix . ComportementConfig::TABLE_EVENTS;
        
        // Vérifier si la table existe
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_events'") === $table_events;
        if (!$table_exists) {
            return [
                'active_sessions' => 0,
                'recent_page_views' => 0,
                'recent_product_views' => 0,
                'recent_cart_adds' => 0,
                'top_current_pages' => []
            ];
        }
        
        $stats = [];
        
        // Activité des dernières 30 minutes
        $stats['active_sessions'] = intval($wpdb->get_var("
            SELECT COUNT(DISTINCT session_id)
            FROM $table_events 
            WHERE timestamp >= DATE_SUB(NOW(), INTERVAL 30 MINUTE)
        ") ?? 0);
        
        // Pages vues dernière heure
        $stats['recent_page_views'] = intval($wpdb->get_var("
            SELECT COUNT(*)
            FROM $table_events 
            WHERE event_type = 'page_view'
            AND timestamp >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
        ") ?? 0);
        
        // Produits vus dernière heure
        $stats['recent_product_views'] = intval($wpdb->get_var("
            SELECT COUNT(*)
            FROM $table_events 
            WHERE event_type = 'product_view'
            AND timestamp >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
        ") ?? 0);
        
        // Ajouts au panier dernière heure
        $stats['recent_cart_adds'] = intval($wpdb->get_var("
            SELECT COUNT(*)
            FROM $table_events 
            WHERE event_type = 'cart_add'
            AND timestamp >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
        ") ?? 0);
        
        // Top pages actuelles - version simple pour éviter les erreurs JSON
        $stats['top_current_pages'] = $wpdb->get_results("
            SELECT 
                page_url,
                COUNT(*) as views
            FROM $table_events 
            WHERE event_type = 'page_view'
            AND timestamp >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
            AND page_url IS NOT NULL
            GROUP BY page_url
            ORDER BY views DESC
            LIMIT 10
        ") ?? [];
        
        return $stats;
    }
    
    // Méthodes utilitaires privées
    
    private static function calculate_avg_session_length($events) {
        $sessions = [];
        foreach ($events as $event) {
            $date = date('Y-m-d', strtotime($event->timestamp));
            if (!isset($sessions[$date])) {
                $sessions[$date] = ['start' => $event->timestamp, 'end' => $event->timestamp];
            }
            if ($event->timestamp > $sessions[$date]['end']) {
                $sessions[$date]['end'] = $event->timestamp;
            }
        }
        
        $total_duration = 0;
        foreach ($sessions as $session) {
            $duration = strtotime($session['end']) - strtotime($session['start']);
            $total_duration += $duration;
        }
        
        return count($sessions) > 0 ? round($total_duration / count($sessions) / 60) : 0; // en minutes
    }
    
    private static function get_most_viewed_pages($page_views) {
        $pages = [];
        foreach ($page_views as $view) {
            $data = json_decode($view->event_data, true);
            $url = $data['page_url'] ?? 'unknown';
            $pages[$url] = ($pages[$url] ?? 0) + 1;
        }
        arsort($pages);
        return array_slice($pages, 0, 5, true);
    }
    
    private static function get_unique_products_viewed($product_views) {
        $products = [];
        foreach ($product_views as $view) {
            $data = json_decode($view->event_data, true);
            $product_id = $data['product_id'] ?? null;
            if ($product_id) {
                $products[$product_id] = true;
            }
        }
        return count($products);
    }
    
    private static function get_favorite_categories($product_views) {
        $categories = [];
        foreach ($product_views as $view) {
            $data = json_decode($view->event_data, true);
            $product_categories = $data['product_categories'] ?? [];
            foreach ($product_categories as $cat) {
                $categories[$cat['name']] = ($categories[$cat['name']] ?? 0) + 1;
            }
        }
        arsort($categories);
        return array_slice($categories, 0, 3, true);
    }
    
    private static function generate_recommendations($insights, $user_id) {
        $recommendations = [];
        
        // Recommandations basées sur le comportement
        if (isset($insights['products']['conversion_rate']) && $insights['products']['conversion_rate'] < 5) {
            $recommendations[] = [
                'type' => 'low_conversion',
                'message' => 'Utilisateur avec faible taux de conversion - envoyer des offres personnalisées',
                'priority' => 'high'
            ];
        }
        
        if (isset($insights['search_behavior']['search_to_view_ratio']) && $insights['search_behavior']['search_to_view_ratio'] < 1) {
            $recommendations[] = [
                'type' => 'search_difficulty',
                'message' => 'Difficulté à trouver les produits recherchés - améliorer les résultats de recherche',
                'priority' => 'medium'
            ];
        }
        
        if (isset($insights['navigation']['avg_session_length']) && $insights['navigation']['avg_session_length'] > 30) {
            $recommendations[] = [
                'type' => 'engaged_user',
                'message' => 'Utilisateur très engagé - proposer un programme de fidélité',
                'priority' => 'low'
            ];
        }
        
        return $recommendations;
    }
    
    private static function calculate_engagement_score($insights) {
        $score = 0;
        
        // Points pour l'activité
        $score += min($insights['navigation']['total_page_views'] ?? 0, 100);
        $score += min(($insights['products']['products_viewed'] ?? 0) * 5, 50);
        $score += min(($insights['search_behavior']['total_searches'] ?? 0) * 3, 30);
        
        // Bonus pour la conversion
        if (isset($insights['products']['conversion_rate']) && $insights['products']['conversion_rate'] > 10) {
            $score += 50;
        }
        
        // Normaliser sur 100
        return min($score, 100);
    }
}