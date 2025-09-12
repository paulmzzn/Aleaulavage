<?php
/**
 * Module de gestion de la base de données pour le comportement des clients
 * 
 * @package Aleaulavage
 */

if (!defined('ABSPATH')) {
    exit;
}

class ComportementClientDatabase {
    
    /**
     * Créer les tables nécessaires à l'activation du thème
     */
    public static function creer_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Table pour les paniers anonymes
        $table_paniers = $wpdb->prefix . 'paniers_anonymes';
        $sql_paniers = "CREATE TABLE $table_paniers (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            session_id varchar(100) NOT NULL,
            product_id bigint(20) NOT NULL,
            quantity int(11) NOT NULL,
            device_type varchar(20) DEFAULT 'inconnu',
            date_ajout datetime DEFAULT CURRENT_TIMESTAMP,
            date_modif datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY session_id (session_id),
            KEY product_id (product_id),
            KEY device_type (device_type)
        ) $charset_collate;";
        
        // Table pour les recherches anonymes
        $table_recherches = $wpdb->prefix . 'recherches_anonymes';
        $sql_recherches = "CREATE TABLE $table_recherches (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            session_id varchar(100) NOT NULL,
            user_id bigint(20) DEFAULT NULL,
            terme_recherche varchar(255) NOT NULL,
            device_type varchar(20) DEFAULT 'inconnu',
            resultats_count int(11) DEFAULT 0,
            produits_rupture_found int(11) DEFAULT 0,
            is_no_stock_search tinyint(1) DEFAULT 0,
            date_recherche datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY session_id (session_id),
            KEY user_id (user_id),
            KEY date_recherche (date_recherche),
            KEY device_type (device_type),
            KEY is_no_stock_search (is_no_stock_search)
        ) $charset_collate;";
        
        // Table pour les recherches de produits en rupture de stock
        $table_rupture = $wpdb->prefix . 'recherches_rupture_stock';
        $sql_rupture = "CREATE TABLE $table_rupture (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            session_id varchar(100) NOT NULL,
            user_id bigint(20) DEFAULT NULL,
            terme_recherche varchar(255) NOT NULL,
            product_id bigint(20) NOT NULL,
            product_name varchar(255) NOT NULL,
            device_type varchar(20) DEFAULT 'inconnu',
            date_recherche datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY session_id (session_id),
            KEY user_id (user_id),
            KEY product_id (product_id),
            KEY terme_recherche (terme_recherche),
            KEY device_type (device_type),
            KEY date_recherche (date_recherche)
        ) $charset_collate;";
        
        // Table pour les recommandations comportementales
        $table_recommendations = $wpdb->prefix . 'comportement_recommendations';
        $sql_recommendations = "CREATE TABLE $table_recommendations (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            type_recommendation varchar(50) NOT NULL,
            titre varchar(255) NOT NULL,
            description text NOT NULL,
            priorite enum('haute','moyenne','basse') DEFAULT 'moyenne',
            data_source text,
            actions_suggerees text,
            date_creation datetime DEFAULT CURRENT_TIMESTAMP,
            is_dismissed tinyint(1) DEFAULT 0,
            PRIMARY KEY (id),
            KEY type_recommendation (type_recommendation),
            KEY priorite (priorite),
            KEY date_creation (date_creation),
            KEY is_dismissed (is_dismissed)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql_paniers);
        dbDelta($sql_recherches);
        dbDelta($sql_rupture);
        dbDelta($sql_recommendations);
    }
    
    /**
     * Nettoyer les anciennes données (plus de 90 jours)
     */
    public static function nettoyer_anciennes_donnees() {
        global $wpdb;
        
        $date_limite = date('Y-m-d H:i:s', strtotime('-90 days'));
        
        // Nettoyer les paniers anonymes
        $table_paniers = $wpdb->prefix . 'paniers_anonymes';
        $wpdb->query($wpdb->prepare(
            "DELETE FROM $table_paniers WHERE date_ajout < %s",
            $date_limite
        ));
        
        // Nettoyer les recherches anonymes
        $table_recherches = $wpdb->prefix . 'recherches_anonymes';
        $wpdb->query($wpdb->prepare(
            "DELETE FROM $table_recherches WHERE date_recherche < %s",
            $date_limite
        ));
        
        // Nettoyer les recherches de rupture de stock
        $table_rupture = $wpdb->prefix . 'recherches_rupture_stock';
        $wpdb->query($wpdb->prepare(
            "DELETE FROM $table_rupture WHERE date_recherche < %s",
            $date_limite
        ));
        
        // Nettoyer les anciennes recommandations (plus de 30 jours)
        $date_limite_reco = date('Y-m-d H:i:s', strtotime('-30 days'));
        $table_recommendations = $wpdb->prefix . 'comportement_recommendations';
        $wpdb->query($wpdb->prepare(
            "DELETE FROM $table_recommendations WHERE date_creation < %s AND is_dismissed = 1",
            $date_limite_reco
        ));
    }
}

// Hook pour créer les tables
add_action('after_setup_theme', array('ComportementClientDatabase', 'creer_tables'));

// Hook pour nettoyer les anciennes données
add_action('wp_scheduled_delete', array('ComportementClientDatabase', 'nettoyer_anciennes_donnees'));