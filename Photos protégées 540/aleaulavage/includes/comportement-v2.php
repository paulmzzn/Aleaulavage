<?php
/**
 * Système de comportement client v2.0
 * Fichier principal qui charge tous les modules
 */

if (!defined('ABSPATH')) {
    exit;
}

// Chargement des modules principaux
require_once __DIR__ . '/comportement/core/config.php';
require_once __DIR__ . '/comportement/core/database.php';
require_once __DIR__ . '/comportement/tracking/tracker.php';
require_once __DIR__ . '/comportement/analytics/analyzer.php';
require_once __DIR__ . '/comportement/export/exporter.php';

class ComportementSystemV2 {
    
    private static $instance = null;
    private $tracker;
    
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->init();
    }
    
    public function init() {
        // Vérifier et créer les tables si nécessaire
        add_action('after_setup_theme', [$this, 'maybe_create_tables']);
        
        // Initialiser le tracker
        $this->tracker = ComportementTracker::get_instance();
        
        // Hooks admin
        add_action('admin_menu', [$this, 'add_admin_menus']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts']);
        
        // Hooks pour la maintenance
        add_action('wp_scheduled_delete', [$this, 'daily_maintenance']);
        
        // AJAX handlers
        add_action('wp_ajax_comportement_export', [$this, 'handle_export_ajax']);
        add_action('wp_ajax_comportement_get_stats', [$this, 'handle_stats_ajax']);
        add_action('wp_ajax_comportement_get_user_insights', [$this, 'handle_user_insights_ajax']);
        
        // Hooks de migration depuis l'ancien système
        add_action('admin_init', [$this, 'maybe_migrate_old_data']);
    }
    
    public function maybe_create_tables() {
        $current_version = get_option('comportement_db_version', '0');
        
        if (version_compare($current_version, ComportementConfig::VERSION, '<')) {
            ComportementDatabase::create_tables();
        }
    }
    
    public function add_admin_menus() {
        if (!ComportementConfig::check_permissions()) {
            return;
        }
        
        // Menu principal
        add_menu_page(
            'Comportement Clients v2',
            'Comportement v2',
            ComportementConfig::REQUIRED_CAPABILITY,
            'comportement-v2',
            [$this, 'render_dashboard'],
            'dashicons-analytics',
            59
        );
        
        // Sous-menus
        add_submenu_page(
            'comportement-v2',
            'Analytics Avancées',
            'Analytics',
            ComportementConfig::REQUIRED_CAPABILITY,
            'comportement-v2-analytics',
            [$this, 'render_analytics']
        );
        
        add_submenu_page(
            'comportement-v2',
            'Segmentation Utilisateurs',
            'Segmentation',
            ComportementConfig::REQUIRED_CAPABILITY,
            'comportement-v2-segmentation',
            [$this, 'render_segmentation']
        );
        
        add_submenu_page(
            'comportement-v2',
            'Paniers Clients',
            'Paniers',
            ComportementConfig::REQUIRED_CAPABILITY,
            'comportement-v2-paniers',
            [$this, 'render_paniers']
        );
        
        add_submenu_page(
            'comportement-v2',
            'Recherches Clients',
            'Recherches',
            ComportementConfig::REQUIRED_CAPABILITY,
            'comportement-v2-recherches',
            [$this, 'render_recherches']
        );
        
        add_submenu_page(
            'comportement-v2',
            'Temps Réel',
            'Temps Réel',
            ComportementConfig::REQUIRED_CAPABILITY,
            'comportement-v2-realtime',
            [$this, 'render_realtime']
        );
        
        add_submenu_page(
            'comportement-v2',
            'Exports & Rapports',
            'Exports',
            ComportementConfig::REQUIRED_CAPABILITY,
            'comportement-v2-exports',
            [$this, 'render_exports']
        );
        
        add_submenu_page(
            'comportement-v2',
            'Configuration',
            'Configuration',
            ComportementConfig::REQUIRED_CAPABILITY,
            'comportement-v2-config',
            [$this, 'render_config']
        );
        
        add_submenu_page(
            'comportement-v2',
            'Maintenance & Debug',
            'Maintenance',
            ComportementConfig::REQUIRED_CAPABILITY,
            'comportement-v2-maintenance',
            [$this, 'render_maintenance']
        );
    }
    
    public function enqueue_admin_scripts($hook) {
        if (strpos($hook, 'comportement-v2') === false) {
            return;
        }
        
        wp_enqueue_script('chart-js', 'https://cdn.jsdelivr.net/npm/chart.js', [], '3.9.1');
        wp_enqueue_script(
            'comportement-admin',
            get_template_directory_uri() . '/includes/comportement/assets/admin.js',
            ['jquery', 'chart-js'],
            ComportementConfig::VERSION,
            true
        );
        
        wp_enqueue_style(
            'comportement-admin',
            get_template_directory_uri() . '/includes/comportement/assets/admin.css',
            [],
            ComportementConfig::VERSION
        );
        
        wp_localize_script('comportement-admin', 'ComportementAdmin', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('comportement_admin'),
            'strings' => [
                'loading' => 'Chargement...',
                'error' => 'Une erreur s\'est produite',
                'export_started' => 'Export démarré',
                'no_data' => 'Aucune donnée disponible'
            ]
        ]);
    }
    
    public function render_dashboard() {
        $stats = ComportementAnalyzer::get_real_time_stats();
        $funnel_data = ComportementAnalyzer::get_conversion_funnel_data(30);
        $db_stats = ComportementDatabase::get_database_stats();
        
        include __DIR__ . '/comportement/admin/dashboard.php';
    }
    
    public function render_analytics() {
        $cohort_data = ComportementAnalyzer::get_cohort_analysis(12);
        $segments = ComportementConfig::get_user_segments();
        
        include __DIR__ . '/comportement/admin/analytics.php';
    }
    
    public function render_segmentation() {
        global $wpdb;
        
        // Récupérer les utilisateurs avec leurs segments
        $users = get_users(['role__in' => ['customer', 'subscriber'], 'number' => 100]);
        $segmented_users = [];
        
        foreach ($users as $user) {
            $segment = ComportementAnalyzer::analyze_user_segment($user->ID);
            $insights = ComportementAnalyzer::generate_user_insights($user->ID);
            
            $segmented_users[] = [
                'user' => $user,
                'segment' => $segment,
                'insights' => $insights
            ];
        }
        
        $segments_config = ComportementConfig::get_user_segments();
        
        include __DIR__ . '/comportement/admin/segmentation.php';
    }
    
    public function render_paniers() {
        global $wpdb;
        
        $table_paniers = $wpdb->prefix . ComportementConfig::TABLE_PANIERS;
        
        // Récupérer tous les paniers avec les informations utilisateurs
        $paniers_data = $this->get_paniers_with_users();
        
        include __DIR__ . '/comportement/admin/paniers.php';
    }
    
    public function render_recherches() {
        global $wpdb;
        
        $table_recherches = $wpdb->prefix . ComportementConfig::TABLE_RECHERCHES;
        
        // Récupérer toutes les recherches avec les informations utilisateurs
        $recherches_data = $this->get_recherches_with_users();
        
        include __DIR__ . '/comportement/admin/recherches.php';
    }
    
    public function render_realtime() {
        $realtime_stats = ComportementAnalyzer::get_real_time_stats();
        
        include __DIR__ . '/comportement/admin/realtime.php';
    }
    
    public function render_exports() {
        $export_formats = ComportementConfig::get_export_formats();
        $tracked_events = ComportementConfig::get_tracked_events();
        
        include __DIR__ . '/comportement/admin/exports.php';
    }
    
    public function render_config() {
        $current_config = [
            'tracking_enabled' => get_option('comportement_tracking_enabled', true),
            'retention_days' => get_option('comportement_retention_days', 90),
            'alert_configs' => ComportementConfig::get_alert_configs()
        ];
        
        // Traitement du formulaire
        if (isset($_POST['save_config']) && wp_verify_nonce($_POST['_wpnonce'], 'comportement_config')) {
            update_option('comportement_tracking_enabled', !empty($_POST['tracking_enabled']));
            update_option('comportement_retention_days', intval($_POST['retention_days']));
            
            echo '<div class="notice notice-success"><p>Configuration sauvegardée</p></div>';
        }
        
        include __DIR__ . '/comportement/admin/config.php';
    }
    
    public function render_maintenance() {
        $db_health = ComportementDatabase::check_database_health();
        $db_stats = ComportementDatabase::get_database_stats();
        
        // Traitement des actions de maintenance
        if (isset($_POST['action']) && wp_verify_nonce($_POST['_wpnonce'], 'comportement_maintenance')) {
            switch ($_POST['action']) {
                case 'cleanup':
                    ComportementDatabase::cleanup_old_data();
                    echo '<div class="notice notice-success"><p>Nettoyage effectué</p></div>';
                    break;
                    
                case 'optimize':
                    ComportementDatabase::optimize_tables();
                    echo '<div class="notice notice-success"><p>Tables optimisées</p></div>';
                    break;
                    
                case 'backup':
                    $backup_table = ComportementDatabase::backup_data(ComportementConfig::TABLE_EVENTS);
                    if ($backup_table) {
                        echo '<div class="notice notice-success"><p>Sauvegarde créée: ' . $backup_table . '</p></div>';
                    } else {
                        echo '<div class="notice notice-error"><p>Échec de la sauvegarde</p></div>';
                    }
                    break;
            }
        }
        
        include __DIR__ . '/comportement/admin/maintenance.php';
    }
    
    public function handle_export_ajax() {
        if (!wp_verify_nonce($_POST['nonce'], 'comportement_admin') || !ComportementConfig::check_permissions()) {
            wp_send_json_error('Permissions insuffisantes');
        }
        
        $type = sanitize_text_field($_POST['type']);
        $format = sanitize_text_field($_POST['format']);
        $filters = $_POST['filters'] ?? [];
        
        try {
            ComportementExporter::export_data($type, $format, $filters);
        } catch (Exception $e) {
            wp_send_json_error('Erreur d\'export: ' . $e->getMessage());
        }
    }
    
    public function handle_stats_ajax() {
        if (!wp_verify_nonce($_POST['nonce'], 'comportement_admin') || !ComportementConfig::check_permissions()) {
            wp_send_json_error('Permissions insuffisantes');
        }
        
        $type = sanitize_text_field($_POST['type']);
        
        switch ($type) {
            case 'realtime':
                $data = ComportementAnalyzer::get_real_time_stats();
                break;
                
            case 'funnel':
                $period = intval($_POST['period'] ?? 30);
                $data = ComportementAnalyzer::get_conversion_funnel_data($period);
                break;
                
            case 'cohort':
                $months = intval($_POST['months'] ?? 12);
                $data = ComportementAnalyzer::get_cohort_analysis($months);
                break;
                
            default:
                wp_send_json_error('Type de statistique non supporté');
        }
        
        wp_send_json_success($data);
    }
    
    public function handle_user_insights_ajax() {
        if (!wp_verify_nonce($_POST['nonce'], 'comportement_admin') || !ComportementConfig::check_permissions()) {
            wp_send_json_error('Permissions insuffisantes');
        }
        
        $user_id = intval($_POST['user_id']);
        if (!$user_id) {
            wp_send_json_error('ID utilisateur manquant');
        }
        
        $insights = ComportementAnalyzer::generate_user_insights($user_id);
        $segment = ComportementAnalyzer::analyze_user_segment($user_id);
        
        wp_send_json_success([
            'insights' => $insights,
            'segment' => $segment
        ]);
    }
    
    public function daily_maintenance() {
        // Nettoyage automatique des anciennes données
        ComportementDatabase::cleanup_old_data();
        
        // Détection des paniers abandonnés
        $abandoned_carts = ComportementAnalyzer::detect_abandoned_carts();
        
        // Log des statistiques de maintenance
        error_log('Comportement v2: Maintenance quotidienne - ' . count($abandoned_carts) . ' paniers abandonnés détectés');
    }
    
    public function maybe_migrate_old_data() {
        if (get_option('comportement_v2_migrated', false)) {
            return;
        }
        
        // Migration progressive des anciennes données
        $this->migrate_legacy_data();
        
        update_option('comportement_v2_migrated', true);
    }
    
    private function migrate_legacy_data() {
        global $wpdb;
        
        // Migrer les données de l'ancien système si elles existent
        $old_paniers_table = $wpdb->prefix . 'paniers_anonymes';
        $old_recherches_table = $wpdb->prefix . 'recherches_anonymes';
        
        // Vérifier si les anciennes tables existent
        $old_paniers_exists = $wpdb->get_var("SHOW TABLES LIKE '$old_paniers_table'") === $old_paniers_table;
        $old_recherches_exists = $wpdb->get_var("SHOW TABLES LIKE '$old_recherches_table'") === $old_recherches_table;
        
        if ($old_paniers_exists) {
            // Les nouvelles tables utilisent les mêmes noms, donc juste s'assurer qu'elles ont les nouvelles colonnes
            $wpdb->query("ALTER TABLE $old_paniers_table 
                ADD COLUMN IF NOT EXISTS variation_id bigint(20) DEFAULT NULL,
                ADD COLUMN IF NOT EXISTS price decimal(10,2) DEFAULT NULL,
                ADD COLUMN IF NOT EXISTS ip_address varchar(45) DEFAULT NULL,
                ADD COLUMN IF NOT EXISTS user_agent text DEFAULT NULL,
                ADD COLUMN IF NOT EXISTS referrer text DEFAULT NULL,
                ADD COLUMN IF NOT EXISTS status enum('active', 'abandoned', 'converted') DEFAULT 'active'");
        }
        
        if ($old_recherches_exists) {
            $wpdb->query("ALTER TABLE $old_recherches_table 
                ADD COLUMN IF NOT EXISTS resultats_count int(11) DEFAULT 0,
                ADD COLUMN IF NOT EXISTS clicked_result_id bigint(20) DEFAULT NULL,
                ADD COLUMN IF NOT EXISTS clicked_position int(11) DEFAULT NULL,
                ADD COLUMN IF NOT EXISTS ip_address varchar(45) DEFAULT NULL,
                ADD COLUMN IF NOT EXISTS user_agent text DEFAULT NULL,
                ADD COLUMN IF NOT EXISTS referrer text DEFAULT NULL");
        }
        
        error_log('Comportement v2: Migration des anciennes données terminée');
    }
    
    public function get_tracker() {
        return $this->tracker;
    }
    
    private function get_paniers_with_users() {
        global $wpdb;
        
        $table_paniers = $wpdb->prefix . ComportementConfig::TABLE_PANIERS;
        
        // Récupérer tous les paniers avec informations utilisateur
        $paniers_bruts = $wpdb->get_results("
            SELECT 
                p.*,
                u.display_name,
                u.user_email,
                pr.post_title as product_name,
                pr.post_status as product_status
            FROM $table_paniers p
            LEFT JOIN {$wpdb->users} u ON p.user_id = u.ID
            LEFT JOIN {$wpdb->posts} pr ON p.product_id = pr.ID
            WHERE p.date_modif >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            ORDER BY p.date_modif DESC
            LIMIT 500
        ");
        
        // Grouper par utilisateur/session
        $paniers_groupes = [];
        
        foreach ($paniers_bruts as $item) {
            $key = $item->user_id ? 'user_' . $item->user_id : 'session_' . $item->session_id;
            
            if (!isset($paniers_groupes[$key])) {
                $paniers_groupes[$key] = [
                    'type' => $item->user_id ? 'connecte' : 'anonyme',
                    'user_id' => $item->user_id,
                    'session_id' => $item->session_id,
                    'display_name' => $item->display_name ?: ('Session: ' . substr($item->session_id, -8)),
                    'user_email' => $item->user_email ?: '',
                    'items' => [],
                    'total_items' => 0,
                    'derniere_modif' => $item->date_modif,
                    'status' => $item->status ?: 'active'
                ];
            }
            
            if ($item->product_name) {
                $paniers_groupes[$key]['items'][] = [
                    'product_id' => $item->product_id,
                    'product_name' => $item->product_name,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'date_ajout' => $item->date_ajout,
                    'variation_id' => $item->variation_id
                ];
                $paniers_groupes[$key]['total_items'] += $item->quantity;
            }
            
            // Garder la date la plus récente
            if ($item->date_modif > $paniers_groupes[$key]['derniere_modif']) {
                $paniers_groupes[$key]['derniere_modif'] = $item->date_modif;
            }
        }
        
        // Trier par date de modification décroissante
        uasort($paniers_groupes, function($a, $b) {
            return strtotime($b['derniere_modif']) - strtotime($a['derniere_modif']);
        });
        
        return array_values($paniers_groupes);
    }
    
    private function get_recherches_with_users() {
        global $wpdb;
        
        $table_recherches = $wpdb->prefix . ComportementConfig::TABLE_RECHERCHES;
        
        // Récupérer toutes les recherches avec informations utilisateur
        $recherches_brutes = $wpdb->get_results("
            SELECT 
                r.*,
                u.display_name,
                u.user_email
            FROM $table_recherches r
            LEFT JOIN {$wpdb->users} u ON r.user_id = u.ID
            WHERE r.date_recherche >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            ORDER BY r.date_recherche DESC
            LIMIT 1000
        ");
        
        // Grouper par utilisateur/session et par terme
        $recherches_groupees = [];
        $recherches_par_utilisateur = [];
        
        foreach ($recherches_brutes as $recherche) {
            // Grouper par terme de recherche
            $terme = $recherche->terme_recherche;
            if (!isset($recherches_groupees[$terme])) {
                $recherches_groupees[$terme] = [
                    'terme' => $terme,
                    'total_recherches' => 0,
                    'utilisateurs_uniques' => [],
                    'derniere_recherche' => $recherche->date_recherche,
                    'details' => []
                ];
            }
            
            $recherches_groupees[$terme]['total_recherches']++;
            $recherches_groupees[$terme]['details'][] = [
                'user_id' => $recherche->user_id,
                'session_id' => $recherche->session_id,
                'display_name' => $recherche->display_name ?: ('Session: ' . substr($recherche->session_id, -8)),
                'user_email' => $recherche->user_email ?: '',
                'date_recherche' => $recherche->date_recherche,
                'resultats_count' => $recherche->resultats_count ?: 0,
                'type' => $recherche->user_id ? 'connecte' : 'anonyme'
            ];
            
            // Compter utilisateurs uniques
            $user_key = $recherche->user_id ? 'user_' . $recherche->user_id : 'session_' . $recherche->session_id;
            $recherches_groupees[$terme]['utilisateurs_uniques'][$user_key] = true;
            
            // Grouper par utilisateur
            if (!isset($recherches_par_utilisateur[$user_key])) {
                $recherches_par_utilisateur[$user_key] = [
                    'type' => $recherche->user_id ? 'connecte' : 'anonyme',
                    'user_id' => $recherche->user_id,
                    'session_id' => $recherche->session_id,
                    'display_name' => $recherche->display_name ?: ('Session: ' . substr($recherche->session_id, -8)),
                    'user_email' => $recherche->user_email ?: '',
                    'recherches' => [],
                    'total_recherches' => 0,
                    'derniere_recherche' => $recherche->date_recherche
                ];
            }
            
            $recherches_par_utilisateur[$user_key]['recherches'][] = [
                'terme' => $terme,
                'date_recherche' => $recherche->date_recherche,
                'resultats_count' => $recherche->resultats_count ?: 0
            ];
            $recherches_par_utilisateur[$user_key]['total_recherches']++;
            
            if ($recherche->date_recherche > $recherches_par_utilisateur[$user_key]['derniere_recherche']) {
                $recherches_par_utilisateur[$user_key]['derniere_recherche'] = $recherche->date_recherche;
            }
        }
        
        // Convertir utilisateurs uniques en count
        foreach ($recherches_groupees as &$groupe) {
            $groupe['utilisateurs_uniques'] = count($groupe['utilisateurs_uniques']);
        }
        
        // Trier par popularité
        uasort($recherches_groupees, function($a, $b) {
            return $b['total_recherches'] - $a['total_recherches'];
        });
        
        // Trier utilisateurs par dernière recherche
        uasort($recherches_par_utilisateur, function($a, $b) {
            return strtotime($b['derniere_recherche']) - strtotime($a['derniere_recherche']);
        });
        
        return [
            'par_terme' => array_values($recherches_groupees),
            'par_utilisateur' => array_values($recherches_par_utilisateur)
        ];
    }
}

// Initialiser le système
$comportement_system_v2 = ComportementSystemV2::get_instance();

// Hook pour désactiver l'ancien système si présent (commenté pour coexistence)
/*
add_action('init', function() {
    if (has_action('after_setup_theme', 'creer_tables_comportement_clients')) {
        remove_action('after_setup_theme', 'creer_tables_comportement_clients');
    }
    
    // Supprimer les anciens menus admin
    if (function_exists('ajouter_menu_comportement_utilisateur')) {
        remove_action('admin_menu', 'ajouter_menu_comportement_utilisateur');
    }
}, 5);
*/