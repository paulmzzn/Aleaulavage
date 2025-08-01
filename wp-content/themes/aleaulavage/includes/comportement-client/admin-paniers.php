<?php
/**
 * Page d'administration des paniers clients
 * 
 * @package Aleaulavage
 */

if (!defined('ABSPATH')) {
    exit;
}

class ComportementClientAdminPaniers {
    
    /**
     * Afficher la page des paniers
     */
    public static function afficher_page() {
        global $wpdb;
        
        ?>
        <div class="comportement-client-wrapper">
            <div class="comportement-client-container">
                
                <!-- Header Premium -->
                <div class="cc-page-header">
                    <div>
                        <h1 class="cc-page-title">🛒 Analyse des Paniers</h1>
                        <p class="cc-page-subtitle">Suivi des comportements d'achat par appareil</p>
                    </div>
                    <div class="cc-actions-bar">
                        <button class="cc-btn cc-btn-refresh" id="refresh-paniers">
                            <span class="cc-refresh-icon">🔄</span>
                            Actualiser
                        </button>
                        <button class="cc-btn cc-btn-export" data-export-type="paniers">
                            📊 Export CSV
                        </button>
                        <a href="<?php echo admin_url('admin.php?page=comportement-clients'); ?>" class="cc-btn cc-btn-secondary">
                            ← Dashboard
                        </a>
                    </div>
                </div>

                <!-- Statistiques des paniers -->
                <?php self::afficher_stats_paniers(); ?>
                
                <!-- Filtres modernes -->
                <?php self::afficher_filtres_premium(); ?>
                
                <!-- Section utilisateurs connectés -->
                <div class="cc-card">
                    <div class="cc-card-header">
                        <h2 class="cc-card-title">👤 Utilisateurs Connectés</h2>
                        <div class="cc-actions-bar">
                            <button class="cc-btn cc-btn-secondary" onclick="toggleConnectedUsers()">
                                <span id="toggle-connected-text">Masquer</span>
                            </button>
                        </div>
                    </div>
                    <div class="cc-card-body" id="connected-users-section">
                        <?php self::afficher_section_utilisateurs_connectes_premium(); ?>
                    </div>
                </div>
                
                <!-- Section visiteurs anonymes -->
                <div class="cc-card">
                    <div class="cc-card-header">
                        <h2 class="cc-card-title">👥 Visiteurs Anonymes</h2>
                        <div class="cc-actions-bar">
                            <button class="cc-btn cc-btn-secondary" onclick="toggleAnonymousUsers()">
                                <span id="toggle-anonymous-text">Masquer</span>
                            </button>
                        </div>
                    </div>
                    <div class="cc-card-body" id="anonymous-users-section">
                        <?php self::afficher_section_visiteurs_anonymes_premium(); ?>
                    </div>
                </div>

            </div>
        </div>

        <script>
        function toggleConnectedUsers() {
            jQuery('#connected-users-section').slideToggle();
            var text = jQuery('#toggle-connected-text');
            text.text(text.text() === 'Masquer' ? 'Afficher' : 'Masquer');
        }
        
        function toggleAnonymousUsers() {
            jQuery('#anonymous-users-section').slideToggle();
            var text = jQuery('#toggle-anonymous-text');
            text.text(text.text() === 'Masquer' ? 'Afficher' : 'Masquer');
        }
        </script>
        <?php
    }
    
    /**
     * Afficher les statistiques des paniers
     */
    private static function afficher_stats_paniers() {
        global $wpdb;
        
        // Récupérer les stats
        $table_paniers = $wpdb->prefix . 'paniers_anonymes';
        $date_limite = date('Y-m-d H:i:s', strtotime('-30 days'));
        
        // Paniers anonymes
        $paniers_anonymes = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(DISTINCT session_id) FROM $table_paniers WHERE date_modif >= %s",
            $date_limite
        ));
        
        // Paniers connectés
        $paniers_connectes = $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->usermeta} WHERE meta_key = '_historique_panier'"
        );
        
        // Produits les plus ajoutés
        $produit_populaire = $wpdb->get_row($wpdb->prepare(
            "SELECT product_id, SUM(quantity) as total FROM $table_paniers WHERE date_modif >= %s GROUP BY product_id ORDER BY total DESC LIMIT 1",
            $date_limite
        ));
        
        // Stats par device
        $stats_device = $wpdb->get_results($wpdb->prepare(
            "SELECT COALESCE(device_type, 'inconnu') as device, COUNT(*) as total FROM $table_paniers WHERE date_modif >= %s GROUP BY device_type ORDER BY total DESC",
            $date_limite
        ));
        
        ?>
        <div class="cc-stats-grid">
            <div class="cc-stat-card cc-animate-in">
                <div class="cc-stat-header">
                    <div class="cc-stat-icon">👥</div>
                </div>
                <div class="cc-stat-number"><?php echo $paniers_anonymes; ?></div>
                <div class="cc-stat-label">Paniers Anonymes</div>
                <div class="cc-stat-change positive">↗ Actifs ce mois</div>
            </div>
            
            <div class="cc-stat-card cc-animate-in">
                <div class="cc-stat-header">
                    <div class="cc-stat-icon">👤</div>
                </div>
                <div class="cc-stat-number"><?php echo $paniers_connectes; ?></div>
                <div class="cc-stat-label">Utilisateurs Connectés</div>
                <div class="cc-stat-change neutral">→ Avec historique</div>
            </div>
            
            <div class="cc-stat-card cc-animate-in">
                <div class="cc-stat-header">
                    <div class="cc-stat-icon">🏆</div>
                </div>
                <div class="cc-stat-number"><?php echo $produit_populaire ? $produit_populaire->total : 0; ?></div>
                <div class="cc-stat-label">Produit le Plus Ajouté</div>
                <div class="cc-stat-change positive">↗ ID: <?php echo $produit_populaire ? $produit_populaire->product_id : 'N/A'; ?></div>
            </div>
            
            <div class="cc-stat-card cc-animate-in">
                <div class="cc-stat-header">
                    <div class="cc-stat-icon">📱</div>
                </div>
                <div class="cc-stat-number"><?php echo !empty($stats_device) ? $stats_device[0]->total : 0; ?></div>
                <div class="cc-stat-label">Device Principal</div>
                <div class="cc-stat-change neutral">
                    <?php 
                    if (!empty($stats_device)) {
                        echo self::get_device_icon($stats_device[0]->device) . ' ' . ucfirst($stats_device[0]->device);
                    } else {
                        echo '❓ Aucun';
                    }
                    ?>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Afficher les filtres premium
     */
    private static function afficher_filtres_premium() {
        ?>
        <div class="cc-filters-bar">
            <div class="cc-filter-group">
                <span class="cc-filter-label">Filtrer par appareil :</span>
                <button class="cc-filter-btn active" data-device="all">Tous</button>
                <button class="cc-filter-btn" data-device="mobile">📱 Mobile</button>
                <button class="cc-filter-btn" data-device="pc">💻 PC</button>
                <button class="cc-filter-btn" data-device="tablette">📱 Tablette</button>
                <button class="cc-filter-btn" data-device="inconnu">❓ Inconnu</button>
            </div>
            
            <div class="cc-filter-group">
                <span class="cc-filter-label">Rechercher :</span>
                <div class="cc-search-container">
                    <input type="text" class="cc-search-input" placeholder="Nom d'utilisateur, produit..." id="panier-search">
                </div>
            </div>
            
            <div class="cc-filter-group">
                <span class="cc-filter-label">Statut :</span>
                <button class="cc-filter-btn" data-status="all">Tous</button>
                <button class="cc-filter-btn" data-status="active">Actifs</button>
                <button class="cc-filter-btn" data-status="converted">Convertis</button>
                <button class="cc-filter-btn" data-status="abandoned">Abandonnés</button>
            </div>
        </div>
        <?php
    }
    
    /**
     * Afficher la section des utilisateurs connectés (version premium)
     */
    private static function afficher_section_utilisateurs_connectes_premium() {
        $users = get_users(['role__in' => ['customer', 'subscriber']]);
        $paniers_connectes = 0;
        
        foreach ($users as $user) {
            $panier = get_user_meta($user->ID, '_historique_panier', true);
            if (!$panier || !is_array($panier)) continue;
            
            $paniers_connectes++;
            self::afficher_panier_utilisateur_connecte_premium($user, $panier);
        }
        
        if ($paniers_connectes == 0) {
            echo '<div style="text-align: center; padding: 60px 20px; color: #6c757d;">';
            echo '<div style="font-size: 48px; margin-bottom: 20px;">🛒</div>';
            echo '<h3 style="margin: 0 0 10px 0;">Aucun panier utilisateur</h3>';
            echo '<p style="margin: 0;">Les paniers des utilisateurs connectés apparaîtront ici</p>';
            echo '</div>';
        }
    }
    
    /**
     * Afficher un panier d'utilisateur connecté (version premium)
     */
    private static function afficher_panier_utilisateur_connecte_premium($user, $panier) {
        $total_produits = 0;
        $derniere_modif = '';
        $devices_utilises = array();
        $valeur_panier = 0;
        
        // Calculer les statistiques
        foreach ($panier as $item) {
            $product = wc_get_product($item['product_id']);
            if ($product) {
                $device_type = isset($item['device_type']) ? $item['device_type'] : 'inconnu';
                $devices_utilises[] = $device_type;
                $total_produits += $item['quantity'];
                $valeur_panier += $product->get_price() * $item['quantity'];
                
                if (isset($item['date_modif']) && ($derniere_modif == '' || $item['date_modif'] > $derniere_modif)) {
                    $derniere_modif = $item['date_modif'];
                }
            }
        }
        
        $devices_uniques = array_unique($devices_utilises);
        $statut = self::determiner_statut_panier('connecte', $user->ID, $derniere_modif);
        
        ?>
        <div class="cc-card" style="margin-bottom: 20px; border-left: 4px solid #28a745;" data-device="<?php echo !empty($devices_uniques) ? $devices_uniques[0] : 'inconnu'; ?>">
            <div class="cc-card-header" style="background: linear-gradient(135deg, #f8fff9, #ffffff);">
                <div>
                    <h3 style="margin: 0; color: #28a745;">
                        👤 <?php echo esc_html($user->display_name); ?>
                    </h3>
                    <p style="margin: 5px 0 0 0; font-size: 13px; color: #6c757d;">
                        <?php echo esc_html($user->user_email); ?>
                    </p>
                </div>
                <div class="cc-actions-bar">
                    <span class="cc-device-badge" style="background: <?php echo self::get_statut_color($statut); ?>;">
                        <?php echo ucfirst($statut); ?>
                    </span>
                    <button class="cc-btn cc-btn-secondary" onclick="toggleUserDetails('user-<?php echo $user->ID; ?>')">
                        Détails
                    </button>
                </div>
            </div>
            
            <div class="cc-card-body">
                <!-- Résumé rapide -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); gap: 15px; margin-bottom: 20px;">
                    <div style="text-align: center;">
                        <div style="font-size: 20px; font-weight: bold; color: #28a745;"><?php echo $total_produits; ?></div>
                        <div style="font-size: 12px; color: #6c757d;">Produits</div>
                    </div>
                    <div style="text-align: center;">
                        <div style="font-size: 20px; font-weight: bold; color: #17a2b8;"><?php echo number_format($valeur_panier, 2); ?>€</div>
                        <div style="font-size: 12px; color: #6c757d;">Valeur</div>
                    </div>
                    <div style="text-align: center;">
                        <div style="font-size: 14px; font-weight: bold;">
                            <?php foreach ($devices_uniques as $device): ?>
                                <span class="cc-device-badge <?php echo $device; ?>" style="margin: 2px;">
                                    <?php echo self::get_device_icon($device); ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                        <div style="font-size: 12px; color: #6c757d;">Appareils</div>
                    </div>
                    <div style="text-align: center;">
                        <div style="font-size: 14px; font-weight: bold; color: #6c757d;">
                            <?php echo $derniere_modif ? date('d/m H:i', strtotime($derniere_modif)) : 'N/A'; ?>
                        </div>
                        <div style="font-size: 12px; color: #6c757d;">Dernière activité</div>
                    </div>
                </div>
                
                <!-- Détails des produits -->
                <div id="user-<?php echo $user->ID; ?>-details" style="display: none;">
                    <h4 style="margin: 0 0 15px 0; padding-bottom: 10px; border-bottom: 1px solid #e9ecef;">
                        📦 Contenu du panier
                    </h4>
                    <div class="cc-table-container">
                        <table class="cc-table">
                            <thead>
                                <tr>
                                    <th>Produit</th>
                                    <th>Quantité</th>
                                    <th>Prix</th>
                                    <th>Device</th>
                                    <th>Ajouté le</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($panier as $item): 
                                    $product = wc_get_product($item['product_id']);
                                    if ($product):
                                        $device_type = isset($item['device_type']) ? $item['device_type'] : 'inconnu';
                                ?>
                                    <tr>
                                        <td><strong><?php echo esc_html($product->get_name()); ?></strong></td>
                                        <td><?php echo intval($item['quantity']); ?></td>
                                        <td><?php echo number_format($product->get_price(), 2); ?>€</td>
                                        <td>
                                            <span class="cc-device-badge <?php echo $device_type; ?>">
                                                <?php echo self::get_device_icon($device_type); ?> <?php echo ucfirst($device_type); ?>
                                            </span>
                                        </td>
                                        <td style="font-size: 12px;">
                                            <?php echo date('d/m/Y H:i', strtotime($item['date_ajout'])); ?>
                                        </td>
                                    </tr>
                                <?php endif; endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <script>
        function toggleUserDetails(userId) {
            jQuery('#' + userId + '-details').slideToggle();
        }
        </script>
        <?php
    }
    
    /**
     * Afficher la section des visiteurs anonymes (version premium)
     */
    private static function afficher_section_visiteurs_anonymes_premium() {
        global $wpdb;
        
        $table_paniers = $wpdb->prefix . 'paniers_anonymes';
        $paniers_anonymes = $wpdb->get_results("
            SELECT session_id, COUNT(*) as nb_produits, 
                   MAX(date_modif) as derniere_modif,
                   GROUP_CONCAT(DISTINCT COALESCE(device_type, 'inconnu')) as devices,
                   SUM(quantity) as total_quantity
            FROM $table_paniers 
            GROUP BY session_id 
            ORDER BY derniere_modif DESC
            LIMIT 20
        ");
        
        if (empty($paniers_anonymes)) {
            echo '<div style="text-align: center; padding: 60px 20px; color: #6c757d;">';
            echo '<div style="font-size: 48px; margin-bottom: 20px;">👥</div>';
            echo '<h3 style="margin: 0 0 10px 0;">Aucun panier anonyme</h3>';
            echo '<p style="margin: 0;">Les paniers des visiteurs anonymes apparaîtront ici</p>';
            echo '</div>';
            return;
        }
        
        // Grille de paniers anonymes
        echo '<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 20px;">';
        
        foreach ($paniers_anonymes as $index => $panier_info) {
            self::afficher_panier_visiteur_anonyme_premium($panier_info, $index);
        }
        
        echo '</div>';
        
        if (count($paniers_anonymes) >= 20) {
            echo '<div style="text-align: center; margin-top: 30px;">';
            echo '<button class="cc-btn cc-btn-secondary" onclick="loadMoreAnonymous()">Charger plus de paniers</button>';
            echo '</div>';
        }
    }
    
    /**
     * Afficher un panier de visiteur anonyme (version premium)
     */
    private static function afficher_panier_visiteur_anonyme_premium($panier_info, $index) {
        global $wpdb;
        
        $table_paniers = $wpdb->prefix . 'paniers_anonymes';
        $items_panier = $wpdb->get_results($wpdb->prepare("
            SELECT product_id, quantity, date_ajout, date_modif, COALESCE(device_type, 'inconnu') as device_type
            FROM $table_paniers 
            WHERE session_id = %s 
            ORDER BY date_modif DESC
        ", $panier_info->session_id));
        
        $devices_utilises = array_filter(explode(',', $panier_info->devices));
        $device_principal = !empty($devices_utilises) ? $devices_utilises[0] : 'inconnu';
        $statut = self::determiner_statut_panier('anonyme', $panier_info->session_id, $panier_info->derniere_modif);
        
        // Calculer la valeur du panier
        $valeur_panier = 0;
        foreach ($items_panier as $item) {
            $product = wc_get_product($item->product_id);
            if ($product) {
                $valeur_panier += $product->get_price() * $item->quantity;
            }
        }
        
        ?>
        <div class="cc-card cc-animate-in" 
             style="border-left: 4px solid #ffc107; animation-delay: <?php echo $index * 0.1; ?>s;" 
             data-device="<?php echo esc_attr($device_principal); ?>">
            
            <div class="cc-card-header" style="background: linear-gradient(135deg, #fffcf0, #ffffff);">
                <div>
                    <h3 style="margin: 0; color: #856404;">
                        👥 Session Anonyme
                    </h3>
                    <p style="margin: 5px 0 0 0; font-size: 11px; color: #6c757d; font-family: monospace;">
                        <?php echo esc_html(substr($panier_info->session_id, 0, 20)) . '...'; ?>
                    </p>
                </div>
                <div class="cc-actions-bar">
                    <span class="cc-device-badge" style="background: <?php echo self::get_statut_color($statut); ?>;">
                        <?php echo ucfirst($statut); ?>
                    </span>
                </div>
            </div>
            
            <div class="cc-card-body">
                <!-- Métriques rapides -->
                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; margin-bottom: 15px;">
                    <div style="text-align: center; padding: 10px; background: #f8f9fa; border-radius: 8px;">
                        <div style="font-size: 18px; font-weight: bold; color: #856404;">
                            <?php echo $panier_info->total_quantity; ?>
                        </div>
                        <div style="font-size: 11px; color: #6c757d;">Produits total</div>
                    </div>
                    <div style="text-align: center; padding: 10px; background: #f8f9fa; border-radius: 8px;">
                        <div style="font-size: 18px; font-weight: bold; color: #17a2b8;">
                            <?php echo number_format($valeur_panier, 2); ?>€
                        </div>
                        <div style="font-size: 11px; color: #6c757d;">Valeur estimée</div>
                    </div>
                </div>
                
                <!-- Appareils utilisés -->
                <div style="margin-bottom: 15px;">
                    <div style="font-size: 12px; color: #6c757d; margin-bottom: 8px;">Appareils utilisés :</div>
                    <div>
                        <?php foreach ($devices_utilises as $device): ?>
                            <span class="cc-device-badge <?php echo trim($device); ?>" style="margin-right: 5px; font-size: 10px;">
                                <?php echo self::get_device_icon(trim($device)); ?> <?php echo ucfirst(trim($device)); ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- Dernière activité -->
                <div style="padding: 10px; background: #f8f9fa; border-radius: 6px; border-left: 3px solid #17a2b8;">
                    <div style="font-size: 11px; color: #6c757d;">Dernière activité</div>
                    <div style="font-size: 12px; font-weight: bold; color: #495057;">
                        <?php
                        $temps_ecoule = time() - strtotime($panier_info->derniere_modif);
                        if ($temps_ecoule < 3600) {
                            echo floor($temps_ecoule / 60) . ' min';
                        } elseif ($temps_ecoule < 86400) {
                            echo floor($temps_ecoule / 3600) . ' h';
                        } else {
                            echo floor($temps_ecoule / 86400) . ' j';
                        }
                        ?>
                    </div>
                </div>
                
                <!-- Actions -->
                <div style="margin-top: 15px; text-align: center;">
                    <button class="cc-btn cc-btn-secondary" 
                            onclick="viewAnonymousDetails('<?php echo esc_js($panier_info->session_id); ?>', '<?php echo md5($panier_info->session_id); ?>')"
                            style="font-size: 12px; padding: 6px 12px;">
                        👁️ Voir détails
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Modal de détails (sera créé par JavaScript) -->
        <div id="modal-<?php echo md5($panier_info->session_id); ?>" style="display: none;">
            <div class="cc-table-container">
                <table class="cc-table">
                    <thead>
                        <tr>
                            <th>Produit</th>
                            <th>Quantité</th>
                            <th>Device</th>
                            <th>Ajouté le</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items_panier as $item): 
                            $product = wc_get_product($item->product_id);
                            if ($product):
                        ?>
                            <tr>
                                <td><strong><?php echo esc_html($product->get_name()); ?></strong></td>
                                <td><?php echo intval($item->quantity); ?></td>
                                <td>
                                    <span class="cc-device-badge <?php echo $item->device_type; ?>">
                                        <?php echo self::get_device_icon($item->device_type); ?> <?php echo ucfirst($item->device_type); ?>
                                    </span>
                                </td>
                                <td style="font-size: 12px;">
                                    <?php echo date('d/m H:i', strtotime($item->date_ajout)); ?>
                                </td>
                            </tr>
                        <?php endif; endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <script>
        function viewAnonymousDetails(sessionId, modalId) {
            // Utiliser l'ID MD5 passé en paramètre
            var modalContent = jQuery('#modal-' + modalId).html();
            
            // Vérifier que le contenu existe
            if (!modalContent || modalContent.trim() === '') {
                ComportementClientAdmin.showToast('Erreur : Détails du panier non trouvés', 'error');
                console.log('Modal ID recherché: modal-' + modalId);
                console.log('Contenu trouvé:', modalContent);
                return;
            }
            
            // Créer et afficher la modal
            var modalOverlay = jQuery('<div class="cc-modal-overlay" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 10000; display: flex; align-items: center; justify-content: center;">' +
                   '<div class="cc-modal-content" style="background: white; border-radius: 12px; padding: 30px; max-width: 800px; max-height: 80vh; overflow-y: auto; position: relative; box-shadow: 0 10px 30px rgba(0,0,0,0.3);">' +
                   '<button class="cc-modal-close" style="position: absolute; top: 15px; right: 15px; background: none; border: none; font-size: 24px; cursor: pointer; color: #666; width: 30px; height: 30px; display: flex; align-items: center; justify-content: center; border-radius: 50%; transition: all 0.3s;">×</button>' +
                   '<h3 style="margin: 0 0 20px 0; color: var(--cc-primary);">📦 Détails du panier - Session ' + sessionId.substring(0, 12) + '...</h3>' +
                   '<div class="modal-body-content">' + modalContent + '</div>' +
                   '</div></div>');
            
            // Ajouter les événements de fermeture
            modalOverlay.on('click', function(e) {
                if (e.target === this || jQuery(e.target).hasClass('cc-modal-close')) {
                    modalOverlay.remove();
                }
            });
            
            // Style pour le bouton close au hover
            modalOverlay.find('.cc-modal-close').hover(
                function() { jQuery(this).css('background', '#f0f0f0'); },
                function() { jQuery(this).css('background', 'none'); }
            );
            
            // Ajouter à la page
            modalOverlay.appendTo('body');
            
            // Empêcher le scroll de la page
            jQuery('body').css('overflow', 'hidden');
            modalOverlay.on('remove', function() {
                jQuery('body').css('overflow', 'auto');
            });
        }
        
        // Fermer la modal avec la touche Escape
        jQuery(document).on('keydown', function(e) {
            if (e.keyCode === 27) { // Escape
                jQuery('.cc-modal-overlay').remove();
                jQuery('body').css('overflow', 'auto');
            }
        });
        
        function loadMoreAnonymous() {
            ComportementClientAdmin.showToast('Chargement de plus de paniers...', 'info');
            // Ici, vous pourriez implémenter le chargement AJAX
            setTimeout(function() {
                ComportementClientAdmin.showToast('Fonctionnalité à implémenter', 'warning');
            }, 1000);
        }
        </script>
        <?php
    }
    
    /**
     * Obtenir l'icône pour un type de device
     */
    private static function get_device_icon($device_type) {
        $icons = array(
            'mobile' => '📱',
            'pc' => '💻',
            'tablette' => '📱',
            'inconnu' => '❓'
        );
        
        return isset($icons[$device_type]) ? $icons[$device_type] : '❓';
    }
    
    /**
     * Déterminer le statut d'un panier
     */
    private static function determiner_statut_panier($type, $identifiant, $date_modif_panier) {
        global $wpdb;
        
        if ($type === 'connecte') {
            $commandes = wc_get_orders([
                'customer' => $identifiant,
                'limit' => 5,
                'orderby' => 'date',
                'order' => 'DESC',
                'date_created' => '>' . (time() - (7 * 24 * 60 * 60))
            ]);
            
            foreach ($commandes as $commande) {
                if (strtotime($commande->get_date_created()) > strtotime($date_modif_panier)) {
                    return 'converti';
                }
            }
        } else {
            $commandes = wc_get_orders([
                'limit' => 10,
                'orderby' => 'date',
                'order' => 'DESC',
                'date_created' => '>' . (time() - (7 * 24 * 60 * 60))
            ]);
            
            foreach ($commandes as $commande) {
                if (strtotime($commande->get_date_created()) > strtotime($date_modif_panier)) {
                    return 'potentiellement_converti';
                }
            }
        }
        
        return 'abandonné';
    }
    
    /**
     * Obtenir la couleur pour un statut
     */
    private static function get_statut_color($statut) {
        $colors = array(
            'converti' => '#28a745',
            'potentiellement_converti' => '#ffc107',
            'abandonné' => '#dc3545'
        );
        
        return isset($colors[$statut]) ? $colors[$statut] : '#6c757d';
    }
}