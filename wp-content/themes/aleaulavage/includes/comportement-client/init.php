<?php
/**
 * Initialisation du système de comportement client
 * 
 * @package Aleaulavage
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Classe principale d'initialisation
 */
class ComportementClientInit {
    
    /**
     * Initialiser tous les modules
     */
    public static function init() {
        // Charger les modules dans l'ordre de dépendance
        self::charger_modules();
        
        // Initialiser les modules
        self::initialiser_modules();
        
        // Hooks spéciaux
        self::setup_hooks();
    }
    
    /**
     * Charger tous les modules
     */
    private static function charger_modules() {
        $modules = array(
            'database.php',      // Base de données (priorité 1)
            'session.php',       // Session et device detection (priorité 2)
            'panier-tracker.php',     // Tracking paniers (priorité 3)
            'recherche-tracker.php',  // Tracking recherches (priorité 3)
            'admin.php'          // Interface admin (priorité 4)
        );
        
        $base_path = __DIR__ . '/';
        
        foreach ($modules as $module) {
            $file_path = $base_path . $module;
            if (file_exists($file_path)) {
                require_once $file_path;
            } else {
                error_log("Erreur: Module comportement client introuvable: {$file_path}");
            }
        }
    }
    
    /**
     * Initialiser les modules qui ont une méthode init()
     */
    private static function initialiser_modules() {
        // Les classes sont déjà initialisées via leurs propres hooks dans les fichiers
        // Cette méthode peut être utilisée pour des initialisations supplémentaires
        
        // Vérifier que les classes principales existent
        $classes_requises = array(
            'ComportementClientDatabase',
            'ComportementClientSession', 
            'ComportementClientPanierTracker',
            'ComportementClientRechercheTracker',
            'ComportementClientAdmin'
        );
        
        foreach ($classes_requises as $classe) {
            if (!class_exists($classe)) {
                error_log("Erreur: Classe comportement client introuvable: {$classe}");
            }
        }
    }
    
    /**
     * Setup des hooks globaux
     */
    private static function setup_hooks() {
        // Hook pour vérifier les mises à jour de la base de données
        add_action('admin_init', array(__CLASS__, 'verifier_mise_a_jour_bd'));
        
        // Hook pour ajouter les styles admin
        add_action('admin_enqueue_scripts', array(__CLASS__, 'enqueue_admin_styles'));
        
        // Hook pour nettoyer périodiquement les données
        if (!wp_next_scheduled('comportement_client_nettoyage')) {
            wp_schedule_event(time(), 'daily', 'comportement_client_nettoyage');
        }
        add_action('comportement_client_nettoyage', array('ComportementClientDatabase', 'nettoyer_anciennes_donnees'));
    }
    
    /**
     * Vérifier si une mise à jour de la base de données est nécessaire
     */
    public static function verifier_mise_a_jour_bd() {
        $version_actuelle = get_option('comportement_client_db_version', '1.0');
        $version_requise = '1.1'; // Version avec device_type
        
        if (version_compare($version_actuelle, $version_requise, '<')) {
            self::mettre_a_jour_base_de_donnees($version_actuelle, $version_requise);
        }
    }
    
    /**
     * Mettre à jour la base de données
     */
    private static function mettre_a_jour_base_de_donnees($version_actuelle, $version_requise) {
        global $wpdb;
        
        // Ajouter la colonne device_type si elle n'existe pas
        $table_paniers = $wpdb->prefix . 'paniers_anonymes';
        $table_recherches = $wpdb->prefix . 'recherches_anonymes';
        
        // Vérifier si les colonnes device_type existent
        $colonne_paniers_existe = $wpdb->get_var("SHOW COLUMNS FROM $table_paniers LIKE 'device_type'");
        $colonne_recherches_existe = $wpdb->get_var("SHOW COLUMNS FROM $table_recherches LIKE 'device_type'");
        
        if (!$colonne_paniers_existe) {
            $wpdb->query("ALTER TABLE $table_paniers ADD COLUMN device_type VARCHAR(20) DEFAULT 'inconnu'");
            $wpdb->query("ALTER TABLE $table_paniers ADD INDEX idx_device_type (device_type)");
        }
        
        if (!$colonne_recherches_existe) {
            $wpdb->query("ALTER TABLE $table_recherches ADD COLUMN device_type VARCHAR(20) DEFAULT 'inconnu'");
            $wpdb->query("ALTER TABLE $table_recherches ADD INDEX idx_device_type (device_type)");
        }
        
        // Mettre à jour la version
        update_option('comportement_client_db_version', $version_requise);
        
        // Log de la mise à jour
        error_log("Base de données comportement client mise à jour de {$version_actuelle} vers {$version_requise}");
    }
    
    /**
     * Enqueue les styles pour l'administration
     */
    public static function enqueue_admin_styles($hook) {
        if (strpos($hook, 'comportement-clients') !== false) {
            wp_enqueue_style(
                'comportement-client-admin',
                get_template_directory_uri() . '/includes/comportement-client/admin.css',
                array(),
                '1.1'
            );
            
            wp_enqueue_script(
                'comportement-client-admin-js',
                get_template_directory_uri() . '/includes/comportement-client/admin.js',
                array('jquery'),
                '1.1',
                true
            );
            
            wp_localize_script('comportement-client-admin-js', 'comportementClientAjax', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('comportement_client_nonce')
            ));
        }
    }
    
    /**
     * Obtenir des informations de debug sur le système
     */
    public static function obtenir_infos_debug() {
        global $wpdb;
        
        $table_paniers = $wpdb->prefix . 'paniers_anonymes';
        $table_recherches = $wpdb->prefix . 'recherches_anonymes';
        
        return array(
            'version_bd' => get_option('comportement_client_db_version', 'inconnue'),
            'table_paniers_existe' => $wpdb->get_var("SHOW TABLES LIKE '$table_paniers'") === $table_paniers,
            'table_recherches_existe' => $wpdb->get_var("SHOW TABLES LIKE '$table_recherches'") === $table_recherches,
            'colonne_device_paniers' => (bool) $wpdb->get_var("SHOW COLUMNS FROM $table_paniers LIKE 'device_type'"),
            'colonne_device_recherches' => (bool) $wpdb->get_var("SHOW COLUMNS FROM $table_recherches LIKE 'device_type'"),
            'total_paniers' => $wpdb->get_var("SELECT COUNT(*) FROM $table_paniers"),
            'total_recherches' => $wpdb->get_var("SELECT COUNT(*) FROM $table_recherches"),
            'classes_chargees' => array(
                'Database' => class_exists('ComportementClientDatabase'),
                'Session' => class_exists('ComportementClientSession'),
                'PanierTracker' => class_exists('ComportementClientPanierTracker'),
                'RechercheTracker' => class_exists('ComportementClientRechercheTracker'),
                'Admin' => class_exists('ComportementClientAdmin')
            )
        );
    }
}

// Initialiser le système
ComportementClientInit::init();