<?php
/**
 * Gestionnaire de base de données pour le système de comportement client
 */

if (!defined('ABSPATH')) {
    exit;
}

require_once 'config.php';

class ComportementDatabase {
    
    public static function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        $tables_sql = [];
        
        // Table paniers anonymes (améliorée)
        $table_paniers = $wpdb->prefix . ComportementConfig::TABLE_PANIERS;
        $tables_sql[] = "CREATE TABLE $table_paniers (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            session_id varchar(100) NOT NULL,
            user_id bigint(20) DEFAULT NULL,
            product_id bigint(20) NOT NULL,
            variation_id bigint(20) DEFAULT NULL,
            quantity int(11) NOT NULL DEFAULT 1,
            price decimal(10,2) DEFAULT NULL,
            date_ajout datetime DEFAULT CURRENT_TIMESTAMP,
            date_modif datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            ip_address varchar(45) DEFAULT NULL,
            user_agent text DEFAULT NULL,
            referrer text DEFAULT NULL,
            status enum('active', 'abandoned', 'converted') DEFAULT 'active',
            PRIMARY KEY (id),
            KEY session_id (session_id),
            KEY user_id (user_id),
            KEY product_id (product_id),
            KEY date_modif (date_modif),
            KEY status (status)
        ) $charset_collate;";
        
        // Table recherches anonymes (améliorée)
        $table_recherches = $wpdb->prefix . ComportementConfig::TABLE_RECHERCHES;
        $tables_sql[] = "CREATE TABLE $table_recherches (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            session_id varchar(100) NOT NULL,
            user_id bigint(20) DEFAULT NULL,
            terme_recherche varchar(255) NOT NULL,
            resultats_count int(11) DEFAULT 0,
            clicked_result_id bigint(20) DEFAULT NULL,
            clicked_position int(11) DEFAULT NULL,
            date_recherche datetime DEFAULT CURRENT_TIMESTAMP,
            ip_address varchar(45) DEFAULT NULL,
            user_agent text DEFAULT NULL,
            referrer text DEFAULT NULL,
            PRIMARY KEY (id),
            KEY session_id (session_id),
            KEY user_id (user_id),
            KEY terme_recherche (terme_recherche),
            KEY date_recherche (date_recherche),
            FULLTEXT KEY search_terms (terme_recherche)
        ) $charset_collate;";
        
        // Nouvelle table des événements comportementaux
        $table_events = $wpdb->prefix . ComportementConfig::TABLE_EVENTS;
        $tables_sql[] = "CREATE TABLE $table_events (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            session_id varchar(100) NOT NULL,
            user_id bigint(20) DEFAULT NULL,
            event_type varchar(50) NOT NULL,
            event_data longtext DEFAULT NULL,
            page_url text DEFAULT NULL,
            referrer_url text DEFAULT NULL,
            user_agent text DEFAULT NULL,
            ip_address varchar(45) DEFAULT NULL,
            timestamp datetime DEFAULT CURRENT_TIMESTAMP,
            processing_status enum('pending', 'processed', 'error') DEFAULT 'pending',
            PRIMARY KEY (id),
            KEY session_id (session_id),
            KEY user_id (user_id),
            KEY event_type (event_type),
            KEY timestamp (timestamp),
            KEY processing_status (processing_status)
        ) $charset_collate;";
        
        // Table d'analytics quotidiens pour les performances
        $table_analytics = $wpdb->prefix . ComportementConfig::TABLE_ANALYTICS;
        $tables_sql[] = "CREATE TABLE $table_analytics (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            date_record date NOT NULL,
            metric_type varchar(50) NOT NULL,
            metric_value decimal(15,4) NOT NULL,
            additional_data longtext DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY date_metric (date_record, metric_type),
            KEY date_record (date_record),
            KEY metric_type (metric_type)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        foreach ($tables_sql as $sql) {
            dbDelta($sql);
        }
        
        // Ajouter les index supplémentaires si nécessaire
        self::ensure_indexes();
        
        // Mettre à jour la version
        update_option('comportement_db_version', ComportementConfig::VERSION);
    }
    
    private static function ensure_indexes() {
        global $wpdb;
        
        $indexes = [
            // Index composites pour de meilleures performances
            $wpdb->prefix . ComportementConfig::TABLE_PANIERS => [
                'session_date' => 'session_id, date_modif',
                'user_product' => 'user_id, product_id',
                'status_date' => 'status, date_modif'
            ],
            $wpdb->prefix . ComportementConfig::TABLE_RECHERCHES => [
                'session_date' => 'session_id, date_recherche',
                'user_date' => 'user_id, date_recherche'
            ],
            $wpdb->prefix . ComportementConfig::TABLE_EVENTS => [
                'session_timestamp' => 'session_id, timestamp',
                'user_event' => 'user_id, event_type',
                'event_timestamp' => 'event_type, timestamp'
            ]
        ];
        
        foreach ($indexes as $table => $table_indexes) {
            foreach ($table_indexes as $index_name => $columns) {
                $wpdb->query("CREATE INDEX IF NOT EXISTS {$index_name} ON {$table} ({$columns})");
            }
        }
    }
    
    public static function cleanup_old_data() {
        global $wpdb;
        
        $retention_config = [
            ComportementConfig::TABLE_PANIERS => ComportementConfig::RETENTION_PANIERS_JOURS,
            ComportementConfig::TABLE_RECHERCHES => ComportementConfig::RETENTION_RECHERCHES_JOURS,
            ComportementConfig::TABLE_EVENTS => ComportementConfig::RETENTION_EVENTS_JOURS
        ];
        
        foreach ($retention_config as $table => $days) {
            $full_table = $wpdb->prefix . $table;
            $date_column = ($table === ComportementConfig::TABLE_PANIERS) ? 'date_modif' :
                          (($table === ComportementConfig::TABLE_RECHERCHES) ? 'date_recherche' : 'timestamp');
            
            $deleted = $wpdb->query($wpdb->prepare(
                "DELETE FROM {$full_table} WHERE {$date_column} < DATE_SUB(NOW(), INTERVAL %d DAY)",
                $days
            ));
            
            if ($deleted !== false) {
                error_log("Comportement: Supprimé {$deleted} enregistrements anciens de {$table}");
            }
        }
        
        // Optimiser les tables après nettoyage
        self::optimize_tables();
    }
    
    public static function optimize_tables() {
        global $wpdb;
        
        $tables = [
            ComportementConfig::TABLE_PANIERS,
            ComportementConfig::TABLE_RECHERCHES,
            ComportementConfig::TABLE_EVENTS,
            ComportementConfig::TABLE_ANALYTICS
        ];
        
        foreach ($tables as $table) {
            $full_table = $wpdb->prefix . $table;
            $wpdb->query("OPTIMIZE TABLE {$full_table}");
        }
    }
    
    public static function get_database_stats() {
        global $wpdb;
        
        $stats = [];
        $tables = [
            'paniers' => ComportementConfig::TABLE_PANIERS,
            'recherches' => ComportementConfig::TABLE_RECHERCHES,
            'events' => ComportementConfig::TABLE_EVENTS,
            'analytics' => ComportementConfig::TABLE_ANALYTICS
        ];
        
        foreach ($tables as $name => $table) {
            $full_table = $wpdb->prefix . $table;
            
            // Compter les enregistrements
            $count = $wpdb->get_var("SELECT COUNT(*) FROM {$full_table}");
            
            // Obtenir la taille de la table
            $size_query = $wpdb->get_row($wpdb->prepare(
                "SELECT 
                    ROUND(((data_length + index_length) / 1024 / 1024), 2) AS size_mb,
                    data_length,
                    index_length
                FROM information_schema.TABLES 
                WHERE table_schema = %s 
                AND table_name = %s",
                DB_NAME,
                $full_table
            ));
            
            $stats[$name] = [
                'count' => intval($count),
                'size_mb' => $size_query ? floatval($size_query->size_mb) : 0,
                'data_length' => $size_query ? intval($size_query->data_length) : 0,
                'index_length' => $size_query ? intval($size_query->index_length) : 0
            ];
        }
        
        return $stats;
    }
    
    public static function backup_data($table_name, $where_clause = '') {
        global $wpdb;
        
        $full_table = $wpdb->prefix . $table_name;
        $backup_table = $full_table . '_backup_' . date('Ymd_His');
        
        $sql = "CREATE TABLE {$backup_table} AS SELECT * FROM {$full_table}";
        if ($where_clause) {
            $sql .= " WHERE {$where_clause}";
        }
        
        $result = $wpdb->query($sql);
        
        if ($result !== false) {
            return $backup_table;
        }
        
        return false;
    }
    
    public static function check_database_health() {
        global $wpdb;
        
        $health = [
            'status' => 'good',
            'issues' => [],
            'recommendations' => []
        ];
        
        // Vérifier les tables
        $required_tables = [
            ComportementConfig::TABLE_PANIERS,
            ComportementConfig::TABLE_RECHERCHES,
            ComportementConfig::TABLE_EVENTS,
            ComportementConfig::TABLE_ANALYTICS
        ];
        
        foreach ($required_tables as $table) {
            $full_table = $wpdb->prefix . $table;
            $exists = $wpdb->get_var("SHOW TABLES LIKE '{$full_table}'");
            
            if (!$exists) {
                $health['issues'][] = "Table manquante: {$table}";
                $health['status'] = 'error';
            }
        }
        
        // Vérifier la taille des données
        $stats = self::get_database_stats();
        foreach ($stats as $table => $data) {
            if ($data['size_mb'] > 100) { // Plus de 100MB
                $health['recommendations'][] = "Table {$table} volumineuse ({$data['size_mb']} MB) - considérer l'archivage";
            }
            
            if ($data['count'] > 1000000) { // Plus d'1 million d'enregistrements
                $health['recommendations'][] = "Table {$table} avec beaucoup d'enregistrements ({$data['count']}) - optimisation recommandée";
            }
        }
        
        return $health;
    }
}