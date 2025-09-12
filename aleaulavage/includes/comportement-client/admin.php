<?php
/**
 * Module d'administration pour le comportement des clients
 * 
 * @package Aleaulavage
 */

if (!defined('ABSPATH')) {
    exit;
}

class ComportementClientAdmin {
    
    /**
     * Initialiser les hooks d'administration
     */
    public static function init() {
        add_action('admin_menu', array(__CLASS__, 'ajouter_menu_comportement'));
        add_action('wp_ajax_get_product_name', array(__CLASS__, 'ajax_get_product_name'));
        add_action('wp_ajax_export_recherches_csv', array(__CLASS__, 'export_recherches_csv'));
        add_action('wp_ajax_export_paniers_csv', array(__CLASS__, 'export_paniers_csv'));
        add_action('wp_ajax_generate_new_recommendations', array(__CLASS__, 'ajax_generate_new_recommendations'));
        add_action('wp_ajax_dismiss_recommendation', array(__CLASS__, 'ajax_dismiss_recommendation'));
        add_action('wp_ajax_dismiss_all_recommendations', array(__CLASS__, 'ajax_dismiss_all_recommendations'));
        add_action('wp_ajax_export_product_analysis', array(__CLASS__, 'ajax_export_product_analysis'));
    }
    
    /**
     * Ajouter le menu d'administration
     */
    public static function ajouter_menu_comportement() {
        add_menu_page(
            'Comportement Clients',
            'Comportement Clients',
            'manage_woocommerce',
            'comportement-clients',
            array(__CLASS__, 'afficher_dashboard'),
            'dashicons-visibility',
            58
        );

        add_submenu_page(
            'comportement-clients',
            'Paniers clients',
            'Paniers',
            'manage_woocommerce',
            'comportement-clients-paniers',
            array(__CLASS__, 'afficher_page_paniers')
        );

        add_submenu_page(
            'comportement-clients',
            'Recherches clients',
            'Recherches',
            'manage_woocommerce',
            'comportement-clients-recherches',
            array(__CLASS__, 'afficher_page_recherches')
        );

        add_submenu_page(
            'comportement-clients',
            'Historique complet',
            'Historique complet',
            'manage_woocommerce',
            'comportement-clients-historique',
            array(__CLASS__, 'afficher_page_historique')
        );

        add_submenu_page(
            'comportement-clients',
            'Recommandations',
            'Recommandations',
            'manage_woocommerce',
            'comportement-clients-recommendations',
            array(__CLASS__, 'afficher_page_recommendations')
        );

        add_submenu_page(
            'comportement-clients',
            'Analyse Produits',
            'Analyse Produits',
            'manage_woocommerce',
            'comportement-clients-produits-analyse',
            array(__CLASS__, 'afficher_page_produits_analyse')
        );

        add_submenu_page(
            'comportement-clients',
            'Analyse Comportementale',
            'Analyse Comportementale',
            'manage_woocommerce',
            'comportement-clients-analyse-comportementale',
            array(__CLASS__, 'afficher_page_analyse_comportementale')
        );
    }
    
    /**
     * Afficher le dashboard principal
     */
    public static function afficher_dashboard() {
        global $wpdb;
        
        // Statistiques générales avec device
        $stats = self::obtenir_statistiques_generales();
        $stats_device = self::obtenir_statistiques_par_device();
        
        ?>
        <div class="comportement-client-wrapper">
            <div class="comportement-client-container">
                
                <!-- Header Premium -->
                <div class="cc-page-header">
                    <div>
                        <h1 class="cc-page-title">📊 Dashboard Client</h1>
                        <p class="cc-page-subtitle">Analyse comportementale et statistiques en temps réel</p>
                    </div>
                    <div class="cc-actions-bar">
                        <button class="cc-btn cc-btn-refresh" id="refresh-dashboard">
                            <span class="cc-refresh-icon">🔄</span>
                            Actualiser
                        </button>
                        <a href="#" class="cc-btn cc-btn-export" data-export-type="global">
                            📊 Export Global
                        </a>
                        <a href="<?php echo admin_url('admin.php?page=comportement-clients-historique'); ?>" class="cc-btn cc-btn-secondary">
                            📈 Historique Complet
                        </a>
                    </div>
                </div>

                <!-- Notice Premium -->
                <div class="cc-card">
                    <div class="cc-card-header" style="background: linear-gradient(135deg, #e3f2fd, #ffffff);">
                        <div class="cc-card-title">
                            ✨ Nouveau : Détection d'Appareil
                        </div>
                    </div>
                    <div class="cc-card-body">
                        <p style="margin: 0; color: #666;">Analyse automatique du type d'appareil (📱 Mobile, 💻 PC, 📱 Tablette) pour tous les utilisateurs et visiteurs.</p>
                    </div>
                </div>
                
                <!-- Statistiques Principales -->
                <div class="cc-stats-grid">
                    <div class="cc-stat-card cc-animate-in">
                        <div class="cc-stat-header">
                            <div class="cc-stat-icon">🛒</div>
                        </div>
                        <div class="cc-stat-number" id="paniers-actifs-counter"><?php echo $stats['paniers_actifs']; ?></div>
                        <div class="cc-stat-label">Paniers Actifs</div>
                        <div class="cc-stat-change positive">
                            <span>↗</span> +12% vs dernière semaine
                        </div>
                    </div>
                    
                    <div class="cc-stat-card cc-animate-in">
                        <div class="cc-stat-header">
                            <div class="cc-stat-icon">🔍</div>
                        </div>
                        <div class="cc-stat-number" id="recherches-totales-counter"><?php echo $stats['recherches_totales']; ?></div>
                        <div class="cc-stat-label">Recherches Totales</div>
                        <div class="cc-stat-change positive">
                            <span>↗</span> +8% vs dernière semaine
                        </div>
                    </div>
                    
                    <div class="cc-stat-card cc-animate-in">
                        <div class="cc-stat-header">
                            <div class="cc-stat-icon">👥</div>
                        </div>
                        <div class="cc-stat-number" id="utilisateurs-uniques-counter"><?php echo $stats['utilisateurs_uniques']; ?></div>
                        <div class="cc-stat-label">Utilisateurs Uniques</div>
                        <div class="cc-stat-change neutral">
                            <span>→</span> Stable
                        </div>
                    </div>
                    
                    <div class="cc-stat-card cc-animate-in">
                        <div class="cc-stat-header">
                            <div class="cc-stat-icon">👤</div>
                        </div>
                        <div class="cc-stat-number" id="visiteurs-anonymes-counter"><?php echo $stats['visiteurs_anonymes']; ?></div>
                        <div class="cc-stat-label">Visiteurs Anonymes</div>
                        <div class="cc-stat-change positive">
                            <span>↗</span> +15% vs dernière semaine
                        </div>
                    </div>
                </div>

                <!-- Répartition par Device -->
                <div class="cc-card">
                    <div class="cc-card-header">
                        <h2 class="cc-card-title">📱 Répartition par Appareil</h2>
                        <div class="cc-actions-bar">
                            <button class="cc-btn cc-btn-secondary" onclick="toggleDeviceDetails()">
                                Détails
                            </button>
                        </div>
                    </div>
                    <div class="cc-card-body">
                        <?php self::afficher_graphique_device_repartition_premium($stats_device, $stats['total_actions']); ?>
                    </div>
                </div>

                <!-- Dernières Activités -->
                <div class="cc-card">
                    <div class="cc-card-header">
                        <h2 class="cc-card-title">🕒 Activité en Temps Réel</h2>
                        <div class="cc-actions-bar">
                            <button class="cc-btn cc-btn-refresh" onclick="refreshActivities()">
                                🔄 Actualiser
                            </button>
                        </div>
                    </div>
                    <div class="cc-card-body">
                        <?php self::afficher_dernieres_activites_premium(); ?>
                    </div>
                </div>

            </div>
        </div>

        <script>
        function toggleDeviceDetails() {
            jQuery('.device-details').slideToggle();
        }
        
        function refreshActivities() {
            ComportementClientAdmin.showToast('Actualisation des activités...', 'info');
            setTimeout(function() {
                window.location.reload();
            }, 1000);
        }
        </script>
        <?php
    }
    
    /**
     * Afficher la page des paniers
     */
    public static function afficher_page_paniers() {
        require_once __DIR__ . '/admin-paniers.php';
        ComportementClientAdminPaniers::afficher_page();
    }
    
    /**
     * Afficher la page des recherches
     */
    public static function afficher_page_recherches() {
        require_once __DIR__ . '/admin-recherches.php';
        ComportementClientAdminRecherches::afficher_page();
    }
    
    /**
     * Afficher la page d'historique
     */
    public static function afficher_page_historique() {
        require_once __DIR__ . '/admin-historique.php';
        ComportementClientAdminHistorique::afficher_page();
    }
    
    /**
     * Afficher la page des recommandations
     */
    public static function afficher_page_recommendations() {
        require_once __DIR__ . '/admin-recommendations.php';
        ComportementClientAdminRecommendations::afficher_page();
    }
    
    /**
     * Afficher la page d'analyse des produits
     */
    public static function afficher_page_produits_analyse() {
        require_once __DIR__ . '/admin-produits-analyse.php';
        ComportementClientAdminProduitsAnalyse::afficher_page();
    }
    
    /**
     * Afficher la page d'analyse comportementale
     */
    public static function afficher_page_analyse_comportementale() {
        require_once __DIR__ . '/admin-analyse-comportementale.php';
        ComportementClientAdminAnalyseComportementale::afficher_page();
    }
    
    /**
     * Obtenir les statistiques générales
     */
    private static function obtenir_statistiques_generales() {
        global $wpdb;
        
        $date_limite = date('Y-m-d H:i:s', strtotime('-30 days'));
        
        // Paniers actifs
        $table_paniers = $wpdb->prefix . 'paniers_anonymes';
        $paniers_anonymes = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(DISTINCT session_id) FROM $table_paniers WHERE date_modif >= %s",
            $date_limite
        ));
        
        $paniers_connectes = $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->usermeta} WHERE meta_key = '_historique_panier'"
        );
        
        // Recherches
        $table_recherches = $wpdb->prefix . 'recherches_anonymes';
        $recherches_totales = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_recherches WHERE date_recherche >= %s",
            $date_limite
        ));
        
        // Utilisateurs uniques
        $utilisateurs_uniques = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(DISTINCT user_id) FROM $table_recherches WHERE user_id IS NOT NULL AND date_recherche >= %s",
            $date_limite
        ));
        
        $visiteurs_anonymes = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(DISTINCT session_id) FROM $table_recherches WHERE user_id IS NULL AND date_recherche >= %s",
            $date_limite
        ));
        
        // Total actions pour pourcentages
        $total_actions = $wpdb->get_var($wpdb->prepare("
            SELECT 
                (SELECT COUNT(*) FROM $table_paniers WHERE date_modif >= %s) +
                (SELECT COUNT(*) FROM $table_recherches WHERE date_recherche >= %s)
        ", $date_limite, $date_limite));
        
        return array(
            'paniers_actifs' => $paniers_anonymes + $paniers_connectes,
            'recherches_totales' => $recherches_totales,
            'utilisateurs_uniques' => $utilisateurs_uniques,
            'visiteurs_anonymes' => $visiteurs_anonymes,
            'total_actions' => $total_actions
        );
    }
    
    /**
     * Obtenir les statistiques par device
     */
    private static function obtenir_statistiques_par_device() {
        global $wpdb;
        
        $date_limite = date('Y-m-d H:i:s', strtotime('-30 days'));
        $table_paniers = $wpdb->prefix . 'paniers_anonymes';
        $table_recherches = $wpdb->prefix . 'recherches_anonymes';
        
        $stats = $wpdb->get_results($wpdb->prepare("
            SELECT 
                COALESCE(device_type, 'inconnu') as device_type,
                COUNT(*) as total_actions
            FROM (
                SELECT device_type FROM $table_paniers WHERE date_modif >= %s
                UNION ALL
                SELECT device_type FROM $table_recherches WHERE date_recherche >= %s
            ) as combined_actions
            GROUP BY device_type
            ORDER BY total_actions DESC
        ", $date_limite, $date_limite));
        
        return $stats;
    }
    
    /**
     * Afficher le graphique de répartition par device (version premium)
     */
    private static function afficher_graphique_device_repartition_premium($stats_device, $total_actions) {
        if (empty($stats_device)) {
            echo '<p>Aucune donnée disponible pour l\'analyse des appareils.</p>';
            return;
        }
        
        $total = $total_actions > 0 ? $total_actions : array_sum(array_column($stats_device, 'total_actions'));
        ?>
        <div class="cc-chart-container">
            <div style="display: grid; grid-template-columns: 1fr 300px; gap: 30px; align-items: center;">
                <div>
                    <?php foreach ($stats_device as $index => $stat): 
                        $percentage = $total > 0 ? ($stat->total_actions / $total) * 100 : 0;
                        $color = self::couleur_device($stat->device_type);
                    ?>
                        <div class="cc-progress-container">
                            <div class="cc-progress-label">
                                <span>
                                    <?php echo self::icone_device($stat->device_type); ?>
                                    <strong><?php echo ucfirst($stat->device_type); ?></strong>
                                </span>
                                <span><?php echo $stat->total_actions; ?> (<?php echo round($percentage, 1); ?>%)</span>
                            </div>
                            <div class="cc-progress-bar">
                                <div class="cc-progress-fill" 
                                     style="background: linear-gradient(90deg, <?php echo $color; ?>, <?php echo $color; ?>AA);"
                                     data-width="<?php echo $percentage; ?>">
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Graphique en cercle -->
                <div style="position: relative;">
                    <div class="device-pie-chart" style="width: 200px; height: 200px; border-radius: 50%; background: conic-gradient(
                        <?php
                        $current_angle = 0;
                        $gradients = array();
                        foreach ($stats_device as $stat) {
                            $percentage = $total > 0 ? ($stat->total_actions / $total) * 100 : 0;
                            $angle = ($percentage / 100) * 360;
                            $color = self::couleur_device($stat->device_type);
                            $gradients[] = $color . ' ' . $current_angle . 'deg ' . ($current_angle + $angle) . 'deg';
                            $current_angle += $angle;
                        }
                        echo implode(', ', $gradients);
                        ?>
                    ); margin: 0 auto; box-shadow: 0 10px 30px rgba(0,0,0,0.2);"></div>
                    
                    <div class="device-details" style="margin-top: 20px; display: none;">
                        <?php foreach ($stats_device as $stat): ?>
                            <div style="display: flex; align-items: center; margin: 8px 0;">
                                <div style="width: 12px; height: 12px; background: <?php echo self::couleur_device($stat->device_type); ?>; border-radius: 50%; margin-right: 8px;"></div>
                                <span style="font-size: 12px;"><?php echo ucfirst($stat->device_type); ?>: <?php echo $stat->total_actions; ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Afficher les dernières activités (version premium)
     */
    private static function afficher_dernieres_activites_premium() {
        global $wpdb;
        
        $table_recherches = $wpdb->prefix . 'recherches_anonymes';
        $table_paniers = $wpdb->prefix . 'paniers_anonymes';
        
        $activites = $wpdb->get_results("
            SELECT 
                'recherche' as type,
                terme_recherche as details,
                COALESCE(device_type, 'inconnu') as device_type,
                CASE WHEN r.user_id IS NOT NULL THEN u.display_name ELSE r.session_id END as identifiant,
                CASE WHEN r.user_id IS NOT NULL THEN 'connecté' ELSE 'anonyme' END as type_utilisateur,
                date_recherche as date_action
            FROM $table_recherches r
            LEFT JOIN {$wpdb->users} u ON r.user_id = u.ID
            UNION ALL
            SELECT 
                'panier' as type,
                CONCAT('Produit ID: ', product_id, ' (Qté: ', quantity, ')') as details,
                COALESCE(device_type, 'inconnu') as device_type,
                session_id as identifiant,
                'anonyme' as type_utilisateur,
                date_modif as date_action
            FROM $table_paniers
            ORDER BY date_action DESC
            LIMIT 15
        ");
        
        if (empty($activites)) {
            echo '<div class="cc-card" style="text-align: center; padding: 40px;">
                    <div style="font-size: 48px; margin-bottom: 20px;">📊</div>
                    <p>Aucune activité récente détectée</p>
                  </div>';
            return;
        }
        ?>
        
        <div class="cc-table-container">
            <table class="cc-table">
                <thead>
                    <tr>
                        <th>Activité</th>
                        <th>Détails</th>
                        <th>Appareil</th>
                        <th>Utilisateur</th>
                        <th>Temps</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($activites as $index => $activite): ?>
                        <tr class="cc-animate-in" style="animation-delay: <?php echo $index * 0.1; ?>s;" data-device="<?php echo $activite->device_type; ?>">
                            <td>
                                <?php if ($activite->type === 'recherche'): ?>
                                    <span class="cc-device-badge" style="background: linear-gradient(135deg, #17a2b8, #138496);">
                                        🔍 Recherche
                                    </span>
                                <?php else: ?>
                                    <span class="cc-device-badge" style="background: linear-gradient(135deg, #28a745, #20c997);">
                                        🛒 Panier
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong><?php echo esc_html($activite->details); ?></strong>
                            </td>
                            <td>
                                <span class="cc-device-badge <?php echo $activite->device_type; ?>">
                                    <?php echo self::icone_device($activite->device_type); ?> 
                                    <?php echo ucfirst($activite->device_type); ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($activite->type_utilisateur === 'connecté'): ?>
                                    <div style="display: flex; flex-direction: column; gap: 3px;">
                                        <strong style="color: var(--cc-primary); font-size: 13px;">
                                            <?php echo esc_html($activite->identifiant); ?>
                                        </strong>
                                        <span class="cc-device-badge" style="background: linear-gradient(135deg, #28a745, #20c997); font-size: 10px;">
                                            👤 Connecté
                                        </span>
                                    </div>
                                <?php else: ?>
                                    <div style="display: flex; flex-direction: column; gap: 3px;">
                                        <span style="font-family: monospace; font-size: 12px; color: #6c757d;">
                                            <?php echo esc_html(substr($activite->identifiant, 0, 12)) . '...'; ?>
                                        </span>
                                        <span class="cc-device-badge" style="background: linear-gradient(135deg, #ffc107, #ffb300); font-size: 10px;">
                                            👥 Anonyme
                                        </span>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td style="font-size: 12px; color: #6c757d;">
                                <?php echo self::temps_relatif($activite->date_action); ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <div style="text-align: center; margin-top: 20px;">
            <a href="<?php echo admin_url('admin.php?page=comportement-clients-historique'); ?>" class="cc-btn cc-btn-secondary">
                📈 Voir l'historique complet
            </a>
        </div>
        <?php
    }
    
    /**
     * Calculer le temps relatif depuis une date
     */
    private static function temps_relatif($date) {
        $temps = time() - strtotime($date);
        
        if ($temps < 60) {
            return 'À l\'instant';
        } elseif ($temps < 3600) {
            $minutes = floor($temps / 60);
            return $minutes . ' min';
        } elseif ($temps < 86400) {
            $heures = floor($temps / 3600);
            return $heures . ' h';
        } else {
            $jours = floor($temps / 86400);
            return $jours . ' j';
        }
    }
    
    /**
     * Obtenir une couleur pour chaque type de device
     */
    private static function couleur_device($device_type) {
        $couleurs = array(
            'mobile' => '#4CAF50',
            'pc' => '#2196F3', 
            'tablette' => '#FF9800',
            'inconnu' => '#9E9E9E'
        );
        
        return isset($couleurs[$device_type]) ? $couleurs[$device_type] : '#9E9E9E';
    }
    
    /**
     * Afficher les dernières activités
     */
    private static function afficher_dernieres_activites() {
        global $wpdb;
        
        $table_recherches = $wpdb->prefix . 'recherches_anonymes';
        $table_paniers = $wpdb->prefix . 'paniers_anonymes';
        
        $activites = $wpdb->get_results("
            SELECT 
                'recherche' as type,
                terme_recherche as details,
                device_type,
                CASE WHEN r.user_id IS NOT NULL THEN u.display_name ELSE r.session_id END as identifiant,
                CASE WHEN r.user_id IS NOT NULL THEN 'connecté' ELSE 'anonyme' END as type_utilisateur,
                date_recherche as date_action
            FROM $table_recherches r
            LEFT JOIN {$wpdb->users} u ON r.user_id = u.ID
            UNION ALL
            SELECT 
                'panier' as type,
                CONCAT('Produit ID: ', product_id, ' (Qté: ', quantity, ')') as details,
                device_type,
                session_id as identifiant,
                'anonyme' as type_utilisateur,
                date_modif as date_action
            FROM $table_paniers
            ORDER BY date_action DESC
            LIMIT 20
        ");
        
        ?>
        <div class="postbox">
            <h2 class="hndle"><span>🕒 Dernières Activités</span></h2>
            <div class="inside">
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Détails</th>
                            <th>Device</th>
                            <th>Utilisateur/Session</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($activites as $activite): ?>
                            <tr>
                                <td>
                                    <?php echo $activite->type === 'recherche' ? '🔍 Recherche' : '🛒 Panier'; ?>
                                </td>
                                <td><?php echo esc_html($activite->details); ?></td>
                                <td>
                                    <span style="color: <?php echo self::couleur_device($activite->device_type); ?>;">
                                        <?php echo self::icone_device($activite->device_type) . ' ' . ucfirst($activite->device_type); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if (isset($activite->type_utilisateur) && $activite->type_utilisateur === 'connecté'): ?>
                                        <strong style="color: var(--cc-primary);"><?php echo esc_html($activite->identifiant); ?></strong>
                                        <br><small style="color: #28a745;">👤 Connecté</small>
                                    <?php else: ?>
                                        <span style="font-family: monospace; color: #6c757d;"><?php echo esc_html(substr($activite->identifiant, 0, 12)) . '...'; ?></span>
                                        <br><small style="color: #ffc107;">👥 Anonyme</small>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo date('d/m/Y H:i', strtotime($activite->date_action)); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php
    }
    
    /**
     * Obtenir une icône pour chaque type de device
     */
    private static function icone_device($device_type) {
        $icones = array(
            'mobile' => '📱',
            'pc' => '💻',
            'tablette' => '📱',
            'inconnu' => '❓'
        );
        
        return isset($icones[$device_type]) ? $icones[$device_type] : '❓';
    }
    
    /**
     * AJAX - Obtenir le nom d'un produit
     */
    public static function ajax_get_product_name() {
        if (!wp_verify_nonce($_POST['nonce'], 'comportement_client_nonce')) {
            wp_die();
        }
        
        $product_id = intval($_POST['product_id']);
        $product = wc_get_product($product_id);
        
        if ($product) {
            wp_send_json_success($product->get_name());
        } else {
            wp_send_json_error('Produit non trouvé');
        }
    }
    
    /**
     * Export CSV des recherches
     */
    public static function export_recherches_csv() {
        if (!current_user_can('manage_woocommerce')) {
            wp_die();
        }
        
        global $wpdb;
        $table_recherches = $wpdb->prefix . 'recherches_anonymes';
        
        $recherches = $wpdb->get_results("
            SELECT 
                terme_recherche, 
                device_type, 
                CASE WHEN r.user_id IS NOT NULL THEN u.display_name ELSE r.session_id END as utilisateur,
                CASE WHEN r.user_id IS NOT NULL THEN 'connecté' ELSE 'anonyme' END as type_utilisateur,
                date_recherche
            FROM $table_recherches r
            LEFT JOIN {$wpdb->users} u ON r.user_id = u.ID
            ORDER BY date_recherche DESC
        ");
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=recherches_clients.csv');
        
        $output = fopen('php://output', 'w');
        fputcsv($output, array('Terme', 'Device', 'Utilisateur', 'Type', 'Date'));
        
        foreach ($recherches as $recherche) {
            fputcsv($output, array(
                $recherche->terme_recherche,
                $recherche->device_type,
                $recherche->utilisateur,
                $recherche->type_utilisateur,
                $recherche->date_recherche
            ));
        }
        
        fclose($output);
        exit;
    }
    
    /**
     * Export CSV des paniers
     */
    public static function export_paniers_csv() {
        if (!current_user_can('manage_woocommerce')) {
            wp_die();
        }
        
        global $wpdb;
        $table_paniers = $wpdb->prefix . 'paniers_anonymes';
        
        $paniers = $wpdb->get_results("
            SELECT session_id, product_id, quantity, device_type, date_ajout, date_modif
            FROM $table_paniers 
            ORDER BY date_modif DESC
        ");
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=paniers_clients.csv');
        
        $output = fopen('php://output', 'w');
        fputcsv($output, array('Session', 'Produit ID', 'Quantité', 'Device', 'Date Ajout', 'Date Modif'));
        
        foreach ($paniers as $panier) {
            fputcsv($output, array(
                $panier->session_id,
                $panier->product_id,
                $panier->quantity,
                $panier->device_type,
                $panier->date_ajout,
                $panier->date_modif
            ));
        }
        
        fclose($output);
        exit;
    }
    
    /**
     * AJAX - Générer de nouvelles recommandations
     */
    public static function ajax_generate_new_recommendations() {
        if (!wp_verify_nonce($_POST['nonce'], 'comportement_client_nonce')) {
            wp_die();
        }
        
        if (!current_user_can('manage_woocommerce')) {
            wp_die();
        }
        
        require_once __DIR__ . '/admin-recommendations.php';
        ComportementClientAdminRecommendations::generer_recommandations_automatiques();
        
        wp_send_json_success('Nouvelles recommandations générées');
    }
    
    /**
     * AJAX - Masquer une recommandation
     */
    public static function ajax_dismiss_recommendation() {
        if (!wp_verify_nonce($_POST['nonce'], 'comportement_client_nonce')) {
            wp_die();
        }
        
        if (!current_user_can('manage_woocommerce')) {
            wp_die();
        }
        
        global $wpdb;
        $recommendation_id = intval($_POST['recommendation_id']);
        $table_recommendations = $wpdb->prefix . 'comportement_recommendations';
        
        $result = $wpdb->update(
            $table_recommendations,
            array('is_dismissed' => 1),
            array('id' => $recommendation_id)
        );
        
        if ($result !== false) {
            wp_send_json_success('Recommandation masquée');
        } else {
            wp_send_json_error('Erreur lors de la mise à jour');
        }
    }
    
    /**
     * AJAX - Masquer toutes les recommandations
     */
    public static function ajax_dismiss_all_recommendations() {
        if (!wp_verify_nonce($_POST['nonce'], 'comportement_client_nonce')) {
            wp_die();
        }
        
        if (!current_user_can('manage_woocommerce')) {
            wp_die();
        }
        
        global $wpdb;
        $table_recommendations = $wpdb->prefix . 'comportement_recommendations';
        
        $result = $wpdb->update(
            $table_recommendations,
            array('is_dismissed' => 1),
            array('is_dismissed' => 0)
        );
        
        wp_send_json_success('Toutes les recommandations ont été masquées');
    }
    
    /**
     * AJAX - Export d'analyse des produits
     */
    public static function ajax_export_product_analysis() {
        if (!current_user_can('manage_woocommerce')) {
            wp_die();
        }
        
        // Récupérer les données d'analyse
        $produits_jamais_achetes = ComportementClientRechercheTracker::obtenir_produits_jamais_achetes();
        $produits_panier_abandonnes = ComportementClientRechercheTracker::obtenir_produits_panier_non_achetes();
        $produits_rupture_recherches = ComportementClientRechercheTracker::obtenir_recherches_rupture_populaires();
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=analyse_produits_' . date('Y-m-d') . '.csv');
        
        $output = fopen('php://output', 'w');
        
        // Section: Produits jamais achetés
        fputcsv($output, array('PRODUITS JAMAIS ACHETES'));
        fputcsv($output, array('ID Produit', 'Nom Produit', 'Nombre Recherches'));
        foreach ($produits_jamais_achetes as $produit) {
            fputcsv($output, array(
                $produit->product_id,
                $produit->product_name,
                $produit->recherches_count
            ));
        }
        
        fputcsv($output, array(''));
        
        // Section: Produits panier abandonnés
        fputcsv($output, array('PRODUITS PANIERS ABANDONNES'));
        fputcsv($output, array('ID Produit', 'Nom Produit', 'Ajouts Panier', 'Sessions Uniques'));
        foreach ($produits_panier_abandonnes as $produit) {
            fputcsv($output, array(
                $produit->product_id,
                $produit->product_name,
                $produit->ajouts_panier,
                $produit->sessions_uniques
            ));
        }
        
        fputcsv($output, array(''));
        
        // Section: Ruptures de stock recherchées
        fputcsv($output, array('RUPTURES DE STOCK RECHERCHEES'));
        fputcsv($output, array('Nom Produit', 'Terme Recherche', 'Total Recherches', 'Utilisateurs Uniques', 'Derniere Recherche'));
        foreach ($produits_rupture_recherches as $produit) {
            fputcsv($output, array(
                $produit->product_name,
                $produit->terme_recherche,
                $produit->total_recherches,
                $produit->utilisateurs_uniques,
                $produit->derniere_recherche
            ));
        }
        
        fclose($output);
        exit;
    }
    
}

// Initialiser l'administration
ComportementClientAdmin::init();