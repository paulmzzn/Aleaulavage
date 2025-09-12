<?php
/**
 * Configuration centrale pour le système de comportement client
 */

if (!defined('ABSPATH')) {
    exit;
}

class ComportementConfig {
    
    const VERSION = '2.0.0';
    
    // Configuration de base
    const TABLE_PANIERS = 'paniers_anonymes';
    const TABLE_RECHERCHES = 'recherches_anonymes';
    const TABLE_EVENTS = 'comportement_events';
    const TABLE_ANALYTICS = 'comportement_analytics_daily';
    
    // Durées de rétention des données
    const RETENTION_PANIERS_JOURS = 30;
    const RETENTION_RECHERCHES_JOURS = 90;
    const RETENTION_EVENTS_JOURS = 180;
    
    // Limites
    const MAX_RECHERCHES_PAR_USER = 100;
    const MAX_PANIERS_PAR_SESSION = 50;
    const MAX_EVENTS_PAR_JOUR = 1000;
    
    // Sessions
    const SESSION_COOKIE_NAME = 'aleaulavage_session_id';
    const SESSION_DURATION_DAYS = 30;
    
    // Capacités requises
    const REQUIRED_CAPABILITY = 'manage_woocommerce';
    
    // Configuration des événements trackés
    public static function get_tracked_events() {
        return [
            'page_view' => [
                'label' => 'Vue de page',
                'description' => 'Page visitée par l\'utilisateur',
                'retention_days' => 30
            ],
            'product_view' => [
                'label' => 'Vue produit',
                'description' => 'Page produit consultée',
                'retention_days' => 60
            ],
            'category_view' => [
                'label' => 'Vue catégorie',
                'description' => 'Page catégorie consultée',
                'retention_days' => 45
            ],
            'search_performed' => [
                'label' => 'Recherche effectuée',
                'description' => 'Terme de recherche utilisé',
                'retention_days' => 90
            ],
            'cart_add' => [
                'label' => 'Ajout panier',
                'description' => 'Produit ajouté au panier',
                'retention_days' => 60
            ],
            'cart_remove' => [
                'label' => 'Suppression panier',
                'description' => 'Produit retiré du panier',
                'retention_days' => 30
            ],
            'checkout_started' => [
                'label' => 'Checkout initié',
                'description' => 'Processus de commande démarré',
                'retention_days' => 90
            ],
            'order_completed' => [
                'label' => 'Commande terminée',
                'description' => 'Commande finalisée avec succès',
                'retention_days' => 365
            ],
            'wishlist_add' => [
                'label' => 'Ajout favoris',
                'description' => 'Produit ajouté aux favoris',
                'retention_days' => 90
            ],
            'email_signup' => [
                'label' => 'Inscription newsletter',
                'description' => 'Inscription à la newsletter',
                'retention_days' => 365
            ]
        ];
    }
    
    // Configuration des segments utilisateur
    public static function get_user_segments() {
        return [
            'nouveau_visiteur' => [
                'label' => 'Nouveau visiteur',
                'description' => 'Première visite dans les 24h',
                'color' => '#28a745'
            ],
            'visiteur_regulier' => [
                'label' => 'Visiteur régulier',
                'description' => '2-5 visites dans le mois',
                'color' => '#17a2b8'
            ],
            'client_potentiel' => [
                'label' => 'Client potentiel',
                'description' => 'Panier abandonné récent',
                'color' => '#ffc107'
            ],
            'client_actif' => [
                'label' => 'Client actif',
                'description' => 'Commande dans les 30 jours',
                'color' => '#007bff'
            ],
            'client_fidele' => [
                'label' => 'Client fidèle',
                'description' => '3+ commandes dans les 6 mois',
                'color' => '#6f42c1'
            ],
            'client_inactif' => [
                'label' => 'Client inactif',
                'description' => 'Pas d\'activité depuis 90 jours',
                'color' => '#dc3545'
            ]
        ];
    }
    
    // Configuration des alertes automatiques
    public static function get_alert_configs() {
        return [
            'panier_abandonne' => [
                'enabled' => true,
                'threshold_hours' => 24,
                'max_reminders' => 3,
                'email_template' => 'panier-abandonne'
            ],
            'client_inactif' => [
                'enabled' => true,
                'threshold_days' => 60,
                'email_template' => 'reactivation'
            ],
            'stock_bas_interesse' => [
                'enabled' => true,
                'stock_threshold' => 5,
                'email_template' => 'stock-limite'
            ],
            'nouveau_produit_similar' => [
                'enabled' => true,
                'days_since_view' => 7,
                'email_template' => 'nouveau-produit'
            ]
        ];
    }
    
    // Configuration des métriques de performance
    public static function get_performance_metrics() {
        return [
            'taux_conversion' => [
                'label' => 'Taux de conversion',
                'formula' => 'commandes / visiteurs_uniques * 100',
                'format' => 'percentage'
            ],
            'panier_moyen' => [
                'label' => 'Panier moyen',
                'formula' => 'total_ventes / nombre_commandes',
                'format' => 'currency'
            ],
            'taux_abandon_panier' => [
                'label' => 'Taux d\'abandon panier',
                'formula' => 'paniers_abandonnes / paniers_crees * 100',
                'format' => 'percentage'
            ],
            'pages_par_session' => [
                'label' => 'Pages par session',
                'formula' => 'total_pages_vues / sessions_uniques',
                'format' => 'decimal'
            ],
            'duree_session_moyenne' => [
                'label' => 'Durée session moyenne',
                'formula' => 'total_duree_sessions / nombre_sessions',
                'format' => 'time'
            ]
        ];
    }
    
    // Obtenir le nom complet d'une table
    public static function get_table_name($table_constant) {
        global $wpdb;
        return $wpdb->prefix . constant('self::' . $table_constant);
    }
    
    // Vérifier les permissions
    public static function check_permissions() {
        return current_user_can(self::REQUIRED_CAPABILITY);
    }
    
    // Obtenir l'ID de session
    public static function get_session_id() {
        if (!isset($_COOKIE[self::SESSION_COOKIE_NAME])) {
            $session_id = 'visiteur_' . uniqid() . '_' . time();
            $expiry = time() + (self::SESSION_DURATION_DAYS * 24 * 60 * 60);
            setcookie(self::SESSION_COOKIE_NAME, $session_id, $expiry, '/');
            return $session_id;
        }
        return sanitize_text_field($_COOKIE[self::SESSION_COOKIE_NAME]);
    }
    
    // Configuration des exports
    public static function get_export_formats() {
        return [
            'csv' => [
                'label' => 'CSV (Excel)',
                'mime_type' => 'text/csv',
                'extension' => 'csv'
            ],
            'json' => [
                'label' => 'JSON (Données brutes)',
                'mime_type' => 'application/json',
                'extension' => 'json'
            ],
            'pdf' => [
                'label' => 'PDF (Rapport)',
                'mime_type' => 'application/pdf',
                'extension' => 'pdf'
            ]
        ];
    }
}