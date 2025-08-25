<?php
/**
 * Système d'export avancé pour les données comportementales
 */

if (!defined('ABSPATH')) {
    exit;
}

require_once dirname(__DIR__) . '/core/config.php';

class ComportementExporter {
    
    public static function export_data($type, $format, $filters = []) {
        if (!ComportementConfig::check_permissions()) {
            wp_die('Permissions insuffisantes');
        }
        
        $data = self::get_export_data($type, $filters);
        
        switch ($format) {
            case 'csv':
                return self::export_csv($data, $type);
            case 'json':
                return self::export_json($data, $type);
            case 'pdf':
                return self::export_pdf($data, $type);
            default:
                return self::export_csv($data, $type);
        }
    }
    
    private static function get_export_data($type, $filters) {
        global $wpdb;
        
        switch ($type) {
            case 'paniers':
                return self::get_paniers_data($filters);
            case 'recherches':
                return self::get_recherches_data($filters);
            case 'events':
                return self::get_events_data($filters);
            case 'analytics':
                return self::get_analytics_data($filters);
            case 'users_behavior':
                return self::get_users_behavior_data($filters);
            case 'conversion_funnel':
                return self::get_funnel_data($filters);
            default:
                return [];
        }
    }
    
    private static function get_paniers_data($filters) {
        global $wpdb;
        
        $table_paniers = $wpdb->prefix . ComportementConfig::TABLE_PANIERS;
        $where_clauses = ['1=1'];
        $params = [];
        
        // Filtres de date
        if (!empty($filters['date_from'])) {
            $where_clauses[] = "date_modif >= %s";
            $params[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $where_clauses[] = "date_modif <= %s";
            $params[] = $filters['date_to'] . ' 23:59:59';
        }
        
        // Filtre par statut
        if (!empty($filters['status'])) {
            $where_clauses[] = "status = %s";
            $params[] = $filters['status'];
        }
        
        // Filtre par type d'utilisateur
        if (isset($filters['user_type'])) {
            if ($filters['user_type'] === 'registered') {
                $where_clauses[] = "user_id IS NOT NULL";
            } elseif ($filters['user_type'] === 'anonymous') {
                $where_clauses[] = "user_id IS NULL";
            }
        }
        
        $where_sql = implode(' AND ', $where_clauses);
        $sql = "
            SELECT 
                p.*,
                u.display_name,
                u.user_email,
                pr.post_title as product_name,
                pr.post_status as product_status
            FROM $table_paniers p
            LEFT JOIN {$wpdb->users} u ON p.user_id = u.ID
            LEFT JOIN {$wpdb->posts} pr ON p.product_id = pr.ID
            WHERE $where_sql
            ORDER BY p.date_modif DESC
        ";
        
        if (!empty($params)) {
            return $wpdb->get_results($wpdb->prepare($sql, ...$params));
        } else {
            return $wpdb->get_results($sql);
        }
    }
    
    private static function get_recherches_data($filters) {
        global $wpdb;
        
        $table_recherches = $wpdb->prefix . ComportementConfig::TABLE_RECHERCHES;
        $where_clauses = ['1=1'];
        $params = [];
        
        if (!empty($filters['date_from'])) {
            $where_clauses[] = "date_recherche >= %s";
            $params[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $where_clauses[] = "date_recherche <= %s";
            $params[] = $filters['date_to'] . ' 23:59:59';
        }
        
        if (!empty($filters['term'])) {
            $where_clauses[] = "terme_recherche LIKE %s";
            $params[] = '%' . $filters['term'] . '%';
        }
        
        $where_sql = implode(' AND ', $where_clauses);
        $sql = "
            SELECT 
                r.*,
                u.display_name,
                u.user_email
            FROM $table_recherches r
            LEFT JOIN {$wpdb->users} u ON r.user_id = u.ID
            WHERE $where_sql
            ORDER BY r.date_recherche DESC
        ";
        
        if (!empty($params)) {
            return $wpdb->get_results($wpdb->prepare($sql, ...$params));
        } else {
            return $wpdb->get_results($sql);
        }
    }
    
    private static function get_events_data($filters) {
        global $wpdb;
        
        $table_events = $wpdb->prefix . ComportementConfig::TABLE_EVENTS;
        $where_clauses = ['1=1'];
        $params = [];
        
        if (!empty($filters['date_from'])) {
            $where_clauses[] = "timestamp >= %s";
            $params[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $where_clauses[] = "timestamp <= %s";
            $params[] = $filters['date_to'] . ' 23:59:59';
        }
        
        if (!empty($filters['event_type'])) {
            $where_clauses[] = "event_type = %s";
            $params[] = $filters['event_type'];
        }
        
        $where_sql = implode(' AND ', $where_clauses);
        $sql = "
            SELECT 
                e.*,
                u.display_name,
                u.user_email
            FROM $table_events e
            LEFT JOIN {$wpdb->users} u ON e.user_id = u.ID
            WHERE $where_sql
            ORDER BY e.timestamp DESC
            LIMIT 10000
        ";
        
        if (!empty($params)) {
            return $wpdb->get_results($wpdb->prepare($sql, ...$params));
        } else {
            return $wpdb->get_results($sql);
        }
    }
    
    private static function get_users_behavior_data($filters) {
        global $wpdb;
        
        $users = get_users(['role__in' => ['customer', 'subscriber']]);
        $behavior_data = [];
        
        foreach ($users as $user) {
            $user_data = [
                'user_id' => $user->ID,
                'display_name' => $user->display_name,
                'user_email' => $user->user_email,
                'registration_date' => $user->user_registered
            ];
            
            // Analyser le comportement
            require_once dirname(__DIR__) . '/analytics/analyzer.php';
            $insights = ComportementAnalyzer::generate_user_insights($user->ID);
            $segment = ComportementAnalyzer::analyze_user_segment($user->ID);
            
            $user_data = array_merge($user_data, [
                'segment' => $segment,
                'total_page_views' => $insights['navigation']['total_page_views'] ?? 0,
                'products_viewed' => $insights['products']['products_viewed'] ?? 0,
                'cart_additions' => $insights['products']['cart_additions'] ?? 0,
                'conversion_rate' => $insights['products']['conversion_rate'] ?? 0,
                'engagement_score' => $insights['engagement_score'] ?? 0,
                'total_searches' => $insights['search_behavior']['total_searches'] ?? 0,
                'unique_days_active' => $insights['navigation']['unique_days_active'] ?? 0,
                'avg_session_length' => $insights['navigation']['avg_session_length'] ?? 0
            ]);
            
            $behavior_data[] = (object) $user_data;
        }
        
        return $behavior_data;
    }
    
    private static function get_funnel_data($filters) {
        require_once dirname(__DIR__) . '/analytics/analyzer.php';
        
        $period = $filters['period_days'] ?? 30;
        $funnel_data = ComportementAnalyzer::get_conversion_funnel_data($period);
        
        // Transformer en format d'export
        $export_data = [];
        foreach ($funnel_data as $step => $value) {
            if ($step !== 'rates') {
                $export_data[] = (object) [
                    'funnel_step' => $step,
                    'count' => $value,
                    'percentage' => isset($funnel_data['rates']) ? 
                        ($step === 'visitors' ? 100 : 
                         round(($value / $funnel_data['visitors']) * 100, 2)) : 0
                ];
            }
        }
        
        return $export_data;
    }
    
    private static function get_analytics_data($filters) {
        global $wpdb;
        
        $table_analytics = $wpdb->prefix . ComportementConfig::TABLE_ANALYTICS;
        $where_clauses = ['1=1'];
        $params = [];
        
        if (!empty($filters['date_from'])) {
            $where_clauses[] = "date_record >= %s";
            $params[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $where_clauses[] = "date_record <= %s";
            $params[] = $filters['date_to'];
        }
        
        if (!empty($filters['metric_type'])) {
            $where_clauses[] = "metric_type = %s";
            $params[] = $filters['metric_type'];
        }
        
        $where_sql = implode(' AND ', $where_clauses);
        $sql = "
            SELECT *
            FROM $table_analytics
            WHERE $where_sql
            ORDER BY date_record DESC, metric_type ASC
        ";
        
        if (!empty($params)) {
            return $wpdb->get_results($wpdb->prepare($sql, ...$params));
        } else {
            return $wpdb->get_results($sql);
        }
    }
    
    private static function export_csv($data, $type) {
        if (empty($data)) {
            wp_die('Aucune donnée à exporter');
        }
        
        $filename = "comportement_{$type}_" . date('Y-m-d_H-i-s') . '.csv';
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        $output = fopen('php://output', 'w');
        
        // BOM pour UTF-8
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // En-têtes
        $first_row = (array) $data[0];
        $headers = array_keys($first_row);
        fputcsv($output, $headers, ';');
        
        // Données
        foreach ($data as $row) {
            $row_array = (array) $row;
            // Nettoyer les données JSON
            foreach ($row_array as &$value) {
                if (is_string($value) && self::is_json($value)) {
                    $value = self::flatten_json($value);
                }
            }
            fputcsv($output, $row_array, ';');
        }
        
        fclose($output);
        exit;
    }
    
    private static function export_json($data, $type) {
        $filename = "comportement_{$type}_" . date('Y-m-d_H-i-s') . '.json';
        
        header('Content-Type: application/json; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        $export_data = [
            'export_type' => $type,
            'export_date' => current_time('Y-m-d H:i:s'),
            'total_records' => count($data),
            'data' => $data
        ];
        
        echo wp_json_encode($export_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    private static function export_pdf($data, $type) {
        // Vérifier si TCPDF est disponible
        if (!class_exists('TCPDF')) {
            wp_die('TCPDF requis pour l\'export PDF. Utilisez CSV ou JSON.');
        }
        
        require_once ABSPATH . 'wp-includes/class-phpass.php';
        
        $pdf = new TCPDF();
        $pdf->SetCreator('WordPress - Comportement Client');
        $pdf->SetAuthor(get_bloginfo('name'));
        $pdf->SetTitle('Rapport Comportement - ' . ucfirst($type));
        $pdf->SetSubject('Export des données comportementales');
        
        $pdf->AddPage();
        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->Cell(0, 10, 'Rapport Comportement Client', 0, 1, 'C');
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(0, 10, 'Type: ' . ucfirst($type), 0, 1);
        $pdf->Cell(0, 10, 'Date: ' . current_time('d/m/Y H:i:s'), 0, 1);
        $pdf->Cell(0, 10, 'Total enregistrements: ' . count($data), 0, 1);
        $pdf->Ln(10);
        
        // Tableau de données (limité pour le PDF)
        if (!empty($data)) {
            $headers = array_keys((array) $data[0]);
            $pdf->SetFont('helvetica', 'B', 8);
            
            // Calculer la largeur des colonnes
            $col_width = 180 / count($headers);
            
            foreach ($headers as $header) {
                $pdf->Cell($col_width, 8, $header, 1, 0, 'C');
            }
            $pdf->Ln();
            
            $pdf->SetFont('helvetica', '', 8);
            $max_rows = 50; // Limiter pour éviter les PDF trop lourds
            
            for ($i = 0; $i < min($max_rows, count($data)); $i++) {
                $row = (array) $data[$i];
                foreach ($row as $value) {
                    $display_value = is_string($value) ? 
                        (strlen($value) > 20 ? substr($value, 0, 17) . '...' : $value) : 
                        strval($value);
                    $pdf->Cell($col_width, 6, $display_value, 1, 0, 'L');
                }
                $pdf->Ln();
            }
            
            if (count($data) > $max_rows) {
                $pdf->Ln(5);
                $pdf->Cell(0, 10, '... et ' . (count($data) - $max_rows) . ' autres enregistrements', 0, 1);
            }
        }
        
        $filename = "comportement_{$type}_" . date('Y-m-d_H-i-s') . '.pdf';
        $pdf->Output($filename, 'D');
        exit;
    }
    
    // Méthodes utilitaires
    
    private static function is_json($string) {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }
    
    private static function flatten_json($json_string) {
        $data = json_decode($json_string, true);
        if (!is_array($data)) {
            return $json_string;
        }
        
        $flattened = [];
        array_walk_recursive($data, function($value, $key) use (&$flattened) {
            $flattened[] = $key . ': ' . $value;
        });
        
        return implode('; ', $flattened);
    }
    
    public static function schedule_automatic_exports() {
        // Programmer des exports automatiques
        if (!wp_next_scheduled('comportement_daily_export')) {
            wp_schedule_event(time(), 'daily', 'comportement_daily_export');
        }
        
        if (!wp_next_scheduled('comportement_weekly_export')) {
            wp_schedule_event(time(), 'weekly', 'comportement_weekly_export');
        }
    }
    
    public static function handle_daily_export() {
        $exports_dir = wp_upload_dir()['basedir'] . '/comportement-exports/';
        if (!file_exists($exports_dir)) {
            wp_mkdir_p($exports_dir);
        }
        
        // Export automatique des données critiques
        $types = ['events', 'paniers', 'recherches'];
        foreach ($types as $type) {
            $data = self::get_export_data($type, [
                'date_from' => date('Y-m-d', strtotime('-1 day'))
            ]);
            
            if (!empty($data)) {
                $filename = $exports_dir . "daily_{$type}_" . date('Y-m-d') . '.json';
                file_put_contents($filename, wp_json_encode($data));
            }
        }
        
        // Nettoyer les anciens exports
        self::cleanup_old_exports($exports_dir, 30);
    }
    
    public static function handle_weekly_export() {
        $exports_dir = wp_upload_dir()['basedir'] . '/comportement-exports/weekly/';
        if (!file_exists($exports_dir)) {
            wp_mkdir_p($exports_dir);
        }
        
        // Export hebdomadaire complet
        $types = ['users_behavior', 'conversion_funnel', 'analytics'];
        foreach ($types as $type) {
            $data = self::get_export_data($type, [
                'date_from' => date('Y-m-d', strtotime('-7 days'))
            ]);
            
            if (!empty($data)) {
                $filename = $exports_dir . "weekly_{$type}_" . date('Y-m-d') . '.json';
                file_put_contents($filename, wp_json_encode($data));
            }
        }
    }
    
    private static function cleanup_old_exports($dir, $days_to_keep) {
        if (!is_dir($dir)) return;
        
        $files = glob($dir . '*');
        $cutoff_time = time() - ($days_to_keep * 24 * 60 * 60);
        
        foreach ($files as $file) {
            if (is_file($file) && filemtime($file) < $cutoff_time) {
                unlink($file);
            }
        }
    }
}

// Hooks pour les exports automatiques
add_action('comportement_daily_export', ['ComportementExporter', 'handle_daily_export']);
add_action('comportement_weekly_export', ['ComportementExporter', 'handle_weekly_export']);

// Activer les exports automatiques
ComportementExporter::schedule_automatic_exports();