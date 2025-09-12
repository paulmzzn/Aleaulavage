<?php
// admin-comportement.php

// Créer les tables nécessaires à l'activation du thème
add_action('after_setup_theme', 'creer_tables_comportement_clients');

function creer_tables_comportement_clients() {
    global $wpdb;
    
    $charset_collate = $wpdb->get_charset_collate();
    
    // Table pour les paniers anonymes
    $table_paniers = $wpdb->prefix . 'paniers_anonymes';
    $sql_paniers = "CREATE TABLE $table_paniers (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        session_id varchar(100) NOT NULL,
        product_id bigint(20) NOT NULL,
        quantity int(11) NOT NULL,
        date_ajout datetime DEFAULT CURRENT_TIMESTAMP,
        date_modif datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY session_id (session_id),
        KEY product_id (product_id)
    ) $charset_collate;";
    
    // Table pour les recherches anonymes
    $table_recherches = $wpdb->prefix . 'recherches_anonymes';
    $sql_recherches = "CREATE TABLE $table_recherches (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        session_id varchar(100) NOT NULL,
        user_id bigint(20) DEFAULT NULL,
        terme_recherche varchar(255) NOT NULL,
        date_recherche datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY session_id (session_id),
        KEY user_id (user_id),
        KEY date_recherche (date_recherche)
    ) $charset_collate;";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql_paniers);
    dbDelta($sql_recherches);
}

// Fonction pour obtenir ou créer un ID de session unique
function obtenir_session_id() {
    if (!isset($_COOKIE['aleaulavage_session_id'])) {
        $session_id = 'visiteur_' . uniqid();
        setcookie('aleaulavage_session_id', $session_id, time() + (30 * 24 * 60 * 60), '/'); // 30 jours
    } else {
        $session_id = sanitize_text_field($_COOKIE['aleaulavage_session_id']);
    }
    return $session_id;
}

// Hook pour tracker les ajouts au panier
add_action('woocommerce_add_to_cart', 'tracker_ajout_panier', 10, 6);

function tracker_ajout_panier($cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data) {
    global $wpdb;
    
    $session_id = obtenir_session_id();
    $user_id = get_current_user_id();
    
    if ($user_id) {
        // Utilisateur connecté - stocker dans user_meta
        $panier_actuel = get_user_meta($user_id, '_historique_panier', true);
        if (!is_array($panier_actuel)) {
            $panier_actuel = array();
        }
        
        // Chercher si le produit existe déjà
        $produit_trouve = false;
        foreach ($panier_actuel as $key => $item) {
            if ($item['product_id'] == $product_id) {
                $panier_actuel[$key]['quantity'] = $quantity;
                $panier_actuel[$key]['date_modif'] = current_time('mysql');
                $produit_trouve = true;
                break;
            }
        }
        
        if (!$produit_trouve) {
            $panier_actuel[] = array(
                'product_id' => $product_id,
                'quantity' => $quantity,
                'date_ajout' => current_time('mysql'),
                'date_modif' => current_time('mysql')
            );
        }
        
        update_user_meta($user_id, '_historique_panier', $panier_actuel);
    } else {
        // Visiteur anonyme - stocker en base de données
        $table_paniers = $wpdb->prefix . 'paniers_anonymes';
        
        // Vérifier si le produit existe déjà pour cette session
        $existing = $wpdb->get_row($wpdb->prepare(
            "SELECT id FROM $table_paniers WHERE session_id = %s AND product_id = %d",
            $session_id, $product_id
        ));
        
        if ($existing) {
            // Mettre à jour la quantité
            $wpdb->update(
                $table_paniers,
                array('quantity' => $quantity, 'date_modif' => current_time('mysql')),
                array('id' => $existing->id)
            );
        } else {
            // Insérer nouveau produit
            $wpdb->insert(
                $table_paniers,
                array(
                    'session_id' => $session_id,
                    'product_id' => $product_id,
                    'quantity' => $quantity
                )
            );
        }
    }
}

// Hook pour tracker les recherches
add_action('wp', 'tracker_recherches');

function tracker_recherches() {
    if (is_search() && !empty(get_search_query())) {
        global $wpdb;
        
        $session_id = obtenir_session_id();
        $user_id = get_current_user_id();
        $terme = get_search_query();
        
        // Stocker dans la table des recherches
        $table_recherches = $wpdb->prefix . 'recherches_anonymes';
        $wpdb->insert(
            $table_recherches,
            array(
                'session_id' => $session_id,
                'user_id' => $user_id ? $user_id : null,
                'terme_recherche' => $terme
            )
        );
        
        if ($user_id) {
            // Aussi stocker dans user_meta pour les utilisateurs connectés
            $recherches_user = get_user_meta($user_id, 'recherche_logs', true);
            if (!is_array($recherches_user)) {
                $recherches_user = array();
            }
            
            $recherches_user[] = array(
                'q' => $terme,
                'date' => current_time('mysql')
            );
            
            // Garder seulement les 20 dernières recherches
            if (count($recherches_user) > 20) {
                $recherches_user = array_slice($recherches_user, -20);
            }
            
            update_user_meta($user_id, 'recherche_logs', $recherches_user);
        }
    }
}

// Hook pour transférer le panier anonyme lors de la connexion
add_action('wp_login', 'transferer_panier_anonyme_vers_connecte', 10, 2);

function transferer_panier_anonyme_vers_connecte($user_login, $user) {
    global $wpdb;
    
    // Récupérer l'ID de session du cookie
    if (!isset($_COOKIE['aleaulavage_session_id'])) {
        return;
    }
    
    $session_id = sanitize_text_field($_COOKIE['aleaulavage_session_id']);
    $user_id = $user->ID;
    
    // Vérifier s'il y a un panier anonyme pour cette session
    $table_paniers = $wpdb->prefix . 'paniers_anonymes';
    $panier_anonyme = $wpdb->get_results($wpdb->prepare("
        SELECT product_id, quantity, date_ajout, date_modif 
        FROM $table_paniers 
        WHERE session_id = %s
    ", $session_id));
    
    if ($panier_anonyme) {
        // Récupérer le panier existant de l'utilisateur connecté
        $panier_user = get_user_meta($user_id, '_historique_panier', true);
        if (!is_array($panier_user)) {
            $panier_user = array();
        }
        
        // Fusionner les paniers
        foreach ($panier_anonyme as $item_anonyme) {
            $produit_trouve = false;
            
            // Vérifier si le produit existe déjà dans le panier utilisateur
            foreach ($panier_user as $key => $item_user) {
                if ($item_user['product_id'] == $item_anonyme->product_id) {
                    // Mettre à jour la quantité (prendre la plus récente)
                    if (strtotime($item_anonyme->date_modif) > strtotime($item_user['date_modif'])) {
                        $panier_user[$key]['quantity'] = $item_anonyme->quantity;
                        $panier_user[$key]['date_modif'] = $item_anonyme->date_modif;
                    }
                    $produit_trouve = true;
                    break;
                }
            }
            
            // Si le produit n'existe pas, l'ajouter
            if (!$produit_trouve) {
                $panier_user[] = array(
                    'product_id' => $item_anonyme->product_id,
                    'quantity' => $item_anonyme->quantity,
                    'date_ajout' => $item_anonyme->date_ajout,
                    'date_modif' => $item_anonyme->date_modif
                );
            }
        }
        
        // Sauvegarder le panier fusionné
        update_user_meta($user_id, '_historique_panier', $panier_user);
        
        // Supprimer le panier anonyme
        $wpdb->delete($table_paniers, array('session_id' => $session_id));
        
        // Mettre à jour les recherches anonymes pour les associer à l'utilisateur
        $table_recherches = $wpdb->prefix . 'recherches_anonymes';
        $wpdb->update(
            $table_recherches,
            array('user_id' => $user_id),
            array('session_id' => $session_id, 'user_id' => null)
        );
    }
}

// Nettoyage automatique des anciennes données (7 jours)
add_action('wp_scheduled_delete', 'nettoyer_anciennes_donnees_comportement');

function nettoyer_anciennes_donnees_comportement() {
    global $wpdb;
    
    $date_limite = date('Y-m-d H:i:s', strtotime('-7 days'));
    
    // Nettoyer les paniers anonymes
    $table_paniers = $wpdb->prefix . 'paniers_anonymes';
    $wpdb->query($wpdb->prepare(
        "DELETE FROM $table_paniers WHERE date_modif < %s",
        $date_limite
    ));
    
    // Nettoyer les recherches anonymes
    $table_recherches = $wpdb->prefix . 'recherches_anonymes';
    $wpdb->query($wpdb->prepare(
        "DELETE FROM $table_recherches WHERE date_recherche < %s",
        $date_limite
    ));
}

// Ajout des menus et sous-menus
add_action('admin_menu', 'ajouter_menu_comportement_utilisateur');

function ajouter_menu_comportement_utilisateur() {
    add_menu_page(
        'Comportement Clients',
        'Comportement Clients',
        'manage_woocommerce',
        'comportement-clients',
        'afficher_dashboard_comportement_clients',
        'dashicons-visibility',
        58
    );

    add_submenu_page(
        'comportement-clients',
        'Paniers clients',
        'Paniers',
        'manage_woocommerce',
        'comportement-clients-paniers',
        'afficher_page_paniers_clients'
    );

    add_submenu_page(
        'comportement-clients',
        'Recherches clients',
        'Recherches',
        'manage_woocommerce',
        'comportement-clients-recherches',
        'afficher_page_recherches_clients'
    );

    add_submenu_page(
        'comportement-clients',
        'Historique complet',
        'Historique complet',
        'manage_woocommerce',
        'comportement-clients-historique',
        'afficher_page_historique_complet'
    );
}

// Fonction pour déterminer le statut d'un panier
function determiner_statut_panier($type, $identifiant, $date_modif_panier) {
    global $wpdb;
    
    // Pour les utilisateurs connectés, vérifier les commandes
    if ($type === 'connecte') {
        // Rechercher les commandes récentes pour cet utilisateur
        $commandes = wc_get_orders([
            'customer' => $identifiant,
            'status' => ['wc-completed', 'wc-processing', 'wc-on-hold'],
            'date_created' => '>=' . (strtotime($date_modif_panier) - 3600), // 1h de marge
            'limit' => 5
        ]);
        
        if (!empty($commandes)) {
            return 'achete';
        }
    }
    
    // Considérer comme abandonné si plus de 24h
    $timestamp_modif = strtotime($date_modif_panier);
    $maintenant = current_time('timestamp');
    $diff_heures = ($maintenant - $timestamp_modif) / 3600;
    
    if ($diff_heures > 24) {
        return 'abandonne';
    }
    
    return 'en_cours';
}

// Fonction de dashboard principal
function afficher_dashboard_comportement_clients() {
    global $wpdb;
    
    echo '<div class="wrap">';
    echo '<h1>🧭 Dashboard - Comportement Clients</h1>';
    echo '<p class="description">Vue d\'ensemble de l\'activité de vos clients et visiteurs.</p>';
    
    echo '<style>
        .dashboard-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 20px; }
        .dashboard-widget { background: #fff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); overflow: hidden; }
        .widget-header { background: #2A3E6A; color: white; padding: 15px; font-size: 18px; font-weight: bold; display: flex; align-items: center; justify-content: space-between; }
        .widget-content { padding: 20px; max-height: 600px; overflow-y: auto; }
        .widget-item { padding: 10px 0; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center; }
        .widget-item:last-child { border-bottom: none; }
        .widget-stats { background: #f8f9fa; padding: 10px; border-radius: 4px; margin-bottom: 15px; text-align: center; }
        .stat-number { font-size: 24px; font-weight: bold; color: #2A3E6A; }
        .stat-label { font-size: 12px; color: #666; }
        .widget-footer { background: #f1f1f1; padding: 10px 15px; text-align: center; }
        .widget-footer a { text-decoration: none; font-weight: 500; }
        .no-data { color: #999; font-style: italic; text-align: center; padding: 20px; }
        .paniers-widget { border-left: 4px solid #28a745; }
        .recherches-widget { border-left: 4px solid #ffc107; }
        .historique-widget { border-left: 4px solid #17a2b8; }
        
        /* Styles pour les cartes d\'activité */
        .activity-section { margin-bottom: 25px; }
        .activity-cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px; margin-bottom: 15px; }
        .activity-card { 
            background: #f8f9fa; 
            border-radius: 6px; 
            padding: 12px; 
            text-align: center; 
            border: 1px solid #e9ecef;
            transition: all 0.2s ease;
        }
        .activity-card:hover { 
            background: #e9ecef; 
            transform: translateY(-1px);
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        }
        .card-number { 
            font-size: 20px; 
            font-weight: bold; 
            color: #2A3E6A; 
            margin-bottom: 4px; 
        }
        .card-label { 
            font-size: 11px; 
            color: #666; 
            text-transform: uppercase; 
            letter-spacing: 0.5px; 
            margin-bottom: 4px;
        }
        .card-user { 
            font-size: 14px; 
            font-weight: 600; 
            color: #333; 
            margin-bottom: 4px; 
        }
        .card-term { 
            font-size: 13px; 
            font-weight: 600; 
            color: #495057; 
            margin-bottom: 4px; 
            font-style: italic;
        }
        .card-detail { 
            font-size: 11px; 
            color: #666; 
        }
        .activity-footer { 
            padding-top: 10px; 
            border-top: 1px solid #e9ecef; 
            text-align: center; 
        }
        .activity-footer a { 
            text-decoration: none; 
            font-size: 12px; 
            font-weight: 500; 
            color: #0073aa;
        }
        .activity-footer a:hover { 
            text-decoration: underline; 
        }
        
        /* Styles pour les cartes d activite utilisateur */
        .user-activity-card {
            text-align: left !important;
            padding: 10px;
        }
        .card-user-name {
            font-weight: bold;
            font-size: 12px;
            color: #1d2327;
            margin-bottom: 6px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 4px;
        }
        .card-last-cart, .card-last-search {
            font-size: 11px;
            color: #2271b1;
            margin: 3px 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .card-last-cart {
            color: #00a32a;
        }
        .card-last-search {
            color: #dba617;
        }
        .card-date {
            font-size: 10px;
            color: #787c82;
            margin-top: 2px;
        }
        
        /* Styles pour les cartes de paniers récents */
        .panier-recent-card-link {
            display: block;
            text-decoration: none;
            color: inherit;
            margin-bottom: 12px;
        }
        .panier-recent-card-link:hover {
            text-decoration: none;
            color: inherit;
        }
        .panier-recent-card {
            background: #fff;
            border: 1px solid #e1e5e9;
            border-radius: 6px;
            overflow: hidden;
            transition: all 0.2s ease;
            cursor: pointer;
        }
        .panier-recent-card-link:hover .panier-recent-card {
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            border-color: #c3c4c7;
            transform: translateY(-1px);
        }
        .panier-recent-card .panier-header {
            background: #f6f7f7;
            padding: 10px 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #e1e5e9;
        }
        .client-info {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .client-name {
            font-weight: 600;
            font-size: 13px;
            color: #1d2327;
        }
        .client-badge {
            font-size: 10px;
            padding: 2px 6px;
            border-radius: 10px;
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 0.3px;
        }
        .client-badge.connecte {
            background: #d1eddd;
            color: #00753a;
        }
        .client-badge.anonyme {
            background: #fef7e0;
            color: #8a6914;
        }
        
        /* Styles pour les badges de statut panier */
        .statut-badge {
            font-size: 9px;
            padding: 2px 6px;
            border-radius: 8px;
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 0.3px;
            margin-left: 8px;
        }
        .statut-badge.achete {
            background: #d1eddd;
            color: #00753a;
        }
        .statut-badge.abandonne {
            background: #f2dede;
            color: #a94442;
        }
        .statut-badge.en_cours {
            background: #d9edf7;
            color: #31708f;
        }
        .panier-date {
            font-size: 11px;
            color: #646970;
            font-weight: 500;
        }
        .panier-produits {
            padding: 12px;
        }
        .produit-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 4px 6px;
            margin-bottom: 4px;
            border-radius: 4px;
            transition: background-color 0.2s ease;
        }
        .produit-item:last-child {
            margin-bottom: 0;
        }
        .panier-recent-card-link:hover .produit-item {
            background: #f8f9fa;
        }
        .produit-nom {
            font-size: 12px;
            color: #1d2327;
            flex: 1;
            margin-right: 8px;
        }
        .produit-qty {
            font-size: 11px;
            color: #646970;
            font-weight: 600;
            background: #f0f0f1;
            padding: 2px 5px;
            border-radius: 3px;
        }
        .produits-more {
            font-size: 11px;
            color: #646970;
            font-style: italic;
            text-align: center;
            margin-top: 8px;
            padding-top: 8px;
            border-top: 1px solid #f0f0f1;
        }
        
        /* Styles pour la popup */
        .panier-popup-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .panier-popup {
            background: #fff;
            border-radius: 8px;
            width: 90%;
            max-width: 600px;
            max-height: 80vh;
            overflow: hidden;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            animation: popupSlideIn 0.3s ease;
        }
        @keyframes popupSlideIn {
            from { transform: scale(0.9); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }
        .panier-popup-header {
            background: #2A3E6A;
            color: white;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .panier-popup-header h3 {
            margin: 0;
            font-size: 18px;
            color: white;
        }
        .panier-popup-close {
            background: none;
            border: none;
            color: white;
            font-size: 24px;
            cursor: pointer;
            padding: 0;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: background-color 0.2s;
        }
        .panier-popup-close:hover {
            background: rgba(255, 255, 255, 0.2);
        }
        .panier-popup-content {
            padding: 20px;
            max-height: 50vh;
            overflow-y: auto;
        }
        .popup-client-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
        }
        .popup-client-name {
            font-size: 18px;
            font-weight: 600;
            color: #1d2327;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .popup-client-details {
            font-size: 12px;
            color: #646970;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
        }
        .popup-produits {
            background: #fff;
        }
        .popup-produits h4 {
            margin: 0 0 15px 0;
            font-size: 16px;
            color: #2A3E6A;
            border-bottom: 2px solid #f1f1f1;
            padding-bottom: 8px;
        }
        .popup-produit-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px;
            border: 1px solid #e1e5e9;
            border-radius: 6px;
            margin-bottom: 8px;
            transition: all 0.2s ease;
        }
        .popup-produit-item:hover {
            background: #f8f9fa;
            border-color: #c3c4c7;
        }
        .popup-produit-info {
            flex: 1;
        }
        .popup-produit-nom {
            font-size: 14px;
            font-weight: 600;
            color: #1d2327;
            margin-bottom: 4px;
        }
        .popup-produit-link {
            font-size: 11px;
            color: #2271b1;
            text-decoration: none;
        }
        .popup-produit-link:hover {
            text-decoration: underline;
        }
        .popup-produit-qty {
            font-size: 14px;
            font-weight: 600;
            color: #646970;
            background: #f0f0f1;
            padding: 6px 12px;
            border-radius: 20px;
        }
        .panier-popup-footer {
            background: #f6f7f7;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-top: 1px solid #e1e5e9;
        }
        .panier-popup-footer .btn {
            padding: 8px 16px;
            border-radius: 4px;
            text-decoration: none;
            font-weight: 500;
            border: none;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        .panier-popup-footer .btn-secondary {
            background: #6c757d;
            color: white;
        }
        .panier-popup-footer .btn-secondary:hover {
            background: #5a6268;
        }
        .panier-popup-footer .btn-primary {
            background: #2A3E6A;
            color: white;
        }
        .panier-popup-footer .btn-primary:hover {
            background: #1e2d4a;
            text-decoration: none;
        }
        
        /* Styles pour les recherches cliquables */
        .recherche-item-clickable {
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .recherche-item-clickable:hover {
            background: #f0f6fc;
            transform: translateX(2px);
        }
        
        /* Styles spécifiques à la popup de recherche */
        .popup-recherche-info {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
        }
        .popup-recherche-terme {
            font-size: 20px;
            font-weight: 600;
            color: #856404;
            margin-bottom: 8px;
        }
        .popup-recherche-stats {
            font-size: 12px;
            color: #856404;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
        }
        .popup-recherches-list h4 {
            margin: 0 0 15px 0;
            font-size: 16px;
            color: #2A3E6A;
            border-bottom: 2px solid #f1f1f1;
            padding-bottom: 8px;
        }
        .popup-recherche-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px;
            border: 1px solid #e1e5e9;
            border-radius: 6px;
            margin-bottom: 8px;
            transition: all 0.2s ease;
        }
        .popup-recherche-item:hover {
            background: #f8f9fa;
            border-color: #c3c4c7;
        }
        .popup-recherche-user-info {
            flex: 1;
        }
        .popup-recherche-user-name {
            font-size: 14px;
            font-weight: 600;
            color: #1d2327;
            margin-bottom: 4px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .popup-recherche-details {
            font-size: 11px;
            color: #646970;
        }
        .popup-recherche-date {
            font-size: 12px;
            color: #646970;
            font-weight: 500;
        }
    </style>';
    
    echo '<div class="dashboard-grid">';
    
    // Widget Paniers (haut gauche)
    echo '<div class="dashboard-widget paniers-widget">';
    echo '<div class="widget-header">';
    echo '<span>🛒 Paniers Récents</span>';
    echo '</div>';
    echo '<div class="widget-content">';
    
    // Collecter tous les paniers avec dates pour tri
    $tous_paniers = [];
    
    // Paniers des utilisateurs connectés
    $users = get_users(['role__in' => ['customer', 'subscriber']]);
    foreach ($users as $user) {
        $panier = get_user_meta($user->ID, '_historique_panier', true);
        if ($panier && is_array($panier)) {
            // Trouver la date la plus récente du panier
            $derniere_modif = '';
            foreach ($panier as $item) {
                if (isset($item['date_modif']) && ($derniere_modif == '' || $item['date_modif'] > $derniere_modif)) {
                    $derniere_modif = $item['date_modif'];
                }
            }
            
            $tous_paniers[] = [
                'type' => 'connecte',
                'nom_client' => $user->display_name,
                'identifiant' => $user->ID,
                'email' => $user->user_email,
                'produits' => $panier,
                'date_modif' => $derniere_modif ?: current_time('mysql')
            ];
        }
    }
    
    // Paniers anonymes
    $table_paniers = $wpdb->prefix . 'paniers_anonymes';
    $sessions_paniers = $wpdb->get_results("
        SELECT session_id, MAX(date_modif) as derniere_modif
        FROM $table_paniers 
        GROUP BY session_id 
        ORDER BY derniere_modif DESC
        LIMIT 10
    ");
    
    foreach ($sessions_paniers as $session) {
        $produits_session = $wpdb->get_results($wpdb->prepare("
            SELECT product_id, quantity, date_ajout, date_modif 
            FROM $table_paniers 
            WHERE session_id = %s 
            ORDER BY date_modif DESC
        ", $session->session_id));
        
        if ($produits_session) {
            $produits_formates = [];
            foreach ($produits_session as $item) {
                $produits_formates[] = [
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'date_ajout' => $item->date_ajout,
                    'date_modif' => $item->date_modif
                ];
            }
            
            $tous_paniers[] = [
                'type' => 'anonyme',
                'nom_client' => $session->session_id,
                'identifiant' => $session->session_id,
                'email' => '',
                'produits' => $produits_formates,
                'date_modif' => $session->derniere_modif
            ];
        }
    }
    
    // Trier par date de modification décroissante
    usort($tous_paniers, function($a, $b) {
        return strtotime($b['date_modif']) - strtotime($a['date_modif']);
    });
    
    // Afficher les 5 paniers les plus récents
    $paniers_affiches = array_slice($tous_paniers, 0, 5);
    
    if (!empty($paniers_affiches)) {
        foreach ($paniers_affiches as $index => $panier_data) {
            // Préparer les données JSON pour la popup
            $panier_json = htmlspecialchars(json_encode($panier_data), ENT_QUOTES, 'UTF-8');
            
            echo '<div class="panier-recent-card-link" onclick="openPanierPopup(' . $panier_json . ')">';
            echo '<div class="panier-recent-card">';
            
            // En-tête du panier
            echo '<div class="panier-header">';
            echo '<div class="client-info">';
            echo '<span class="client-name">' . esc_html($panier_data['nom_client']) . '</span>';
            if ($panier_data['type'] == 'connecte') {
                echo '<span class="client-badge connecte">Connecté</span>';
            } else {
                echo '<span class="client-badge anonyme">Anonyme</span>';
            }
            
            // Ajouter le statut du panier
            $statut = determiner_statut_panier($panier_data['type'], $panier_data['identifiant'], $panier_data['date_modif']);
            $statut_labels = [
                'achete' => 'Acheté',
                'abandonne' => 'Abandonné', 
                'en_cours' => 'En cours'
            ];
            echo '<span class="statut-badge ' . $statut . '">' . $statut_labels[$statut] . '</span>';
            echo '</div>';
            echo '<div class="panier-date">' . date('d/m/Y H:i', strtotime($panier_data['date_modif'])) . '</div>';
            echo '</div>';
            
            // Produits du panier (maximum 4)
            echo '<div class="panier-produits">';
            $produits_affiches = array_slice($panier_data['produits'], 0, 4);
            $total_produits = count($panier_data['produits']);
            
            foreach ($produits_affiches as $item) {
                $product = wc_get_product($item['product_id']);
                if ($product) {
                    echo '<div class="produit-item">';
                    echo '<span class="produit-nom">' . esc_html($product->get_name()) . '</span>';
                    echo '<span class="produit-qty">×' . intval($item['quantity']) . '</span>';
                    echo '</div>';
                }
            }
            
            // Afficher le nombre total si plus de 4 produits
            if ($total_produits > 4) {
                $articles_supplementaires = $total_produits - 4;
                echo '<div class="produits-more">+ ' . $articles_supplementaires . ' article' . ($articles_supplementaires > 1 ? 's' : '') . ' supplémentaire' . ($articles_supplementaires > 1 ? 's' : '') . '</div>';
            }
            
            echo '</div>';
            echo '</div>';
            echo '</div>';
        }
    } else {
        echo '<div class="no-data">Aucun panier récent trouvé</div>';
    }
    
    echo '</div>';
    echo '<div class="widget-footer">';
    echo '<a href="' . admin_url('admin.php?page=comportement-clients-paniers') . '">Voir tous les paniers →</a>';
    echo '</div>';
    echo '</div>';
    
    // Widget Activité Récente (haut droite - élargi)
    echo '<div class="dashboard-widget historique-widget" style="grid-row: span 2;">';
    echo '<div class="widget-header">';
    echo '<span>📊 Activité Récente - Vue d\'ensemble</span>';
    echo '</div>';
    echo '<div class="widget-content">';
    
    // Section Historique dans l'activité
    echo '<div class="activity-section">';
    echo '<h4 style="margin: 20px 0 15px 0; color: #17a2b8; display: flex; align-items: center;"><span style="margin-right: 8px;">📈</span> Historique Complet</h4>';
    echo '<div class="activity-cards">';
    
    // 10 derniers utilisateurs avec leurs dernières activités
    $activites_users = [];
    $table_recherches = $wpdb->prefix . 'recherches_anonymes';
    
    // Récupérer les 10 derniers utilisateurs ayant une activité
    foreach ($users as $user) {
        $panier = get_user_meta($user->ID, '_historique_panier', true);
        $recherches = get_user_meta($user->ID, 'recherche_logs', true);
        
        $derniere_activite = null;
        $dernier_panier = null;
        $derniere_recherche = null;
        
        // Trouver le dernier élément du panier
        if ($panier && is_array($panier)) {
            $dernier_panier = end($panier);
        }
        
        // Trouver la dernière recherche (priorité à la base de données)
        $derniere_recherche = null;
        
        // Recherche depuis la base de données (priorité)
        $recherche_db = $wpdb->get_row($wpdb->prepare("
            SELECT terme_recherche, date_recherche
            FROM $table_recherches 
            WHERE user_id = %d 
            ORDER BY date_recherche DESC 
            LIMIT 1
        ", $user->ID));
        
        if ($recherche_db) {
            $derniere_recherche = [
                'terme' => $recherche_db->terme_recherche,
                'date' => $recherche_db->date_recherche
            ];
        } elseif ($recherches && is_array($recherches)) {
            // Fallback vers user_meta si pas de données en DB
            $recherche_meta = end($recherches);
            $derniere_recherche = $recherche_meta;
        }
        
        // Déterminer l'activité la plus récente
        $date_panier = $dernier_panier ? strtotime($dernier_panier['date_ajout']) : 0;
        $date_recherche = $derniere_recherche ? strtotime($derniere_recherche['date']) : 0;
        
        if ($date_panier > 0 || $date_recherche > 0) {
            $derniere_activite = max($date_panier, $date_recherche);
            $activites_users[] = [
                'user' => $user,
                'derniere_activite' => $derniere_activite,
                'dernier_panier' => $dernier_panier,
                'derniere_recherche' => $derniere_recherche
            ];
        }
    }
    
    // Ajouter les utilisateurs anonymes depuis la base de données
    $table_paniers = $wpdb->prefix . 'paniers_anonymes';
    $table_recherches = $wpdb->prefix . 'recherches_anonymes';
    
    // Derniers paniers anonymes
    $paniers_anonymes = $wpdb->get_results("
        SELECT DISTINCT session_id, MAX(date_modif) as derniere_activite
        FROM $table_paniers 
        WHERE date_modif >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        GROUP BY session_id 
        ORDER BY derniere_activite DESC 
        LIMIT 5
    ");
    
    foreach ($paniers_anonymes as $panier_anonyme) {
        $derniere_activite_panier = strtotime($panier_anonyme->derniere_activite);
        $dernier_produit = $wpdb->get_row($wpdb->prepare("
            SELECT * FROM $table_paniers 
            WHERE session_id = %s 
            ORDER BY date_modif DESC 
            LIMIT 1
        ", $panier_anonyme->session_id));
        
        // Récupérer la dernière recherche pour cette session
        $derniere_recherche_anonyme = $wpdb->get_row($wpdb->prepare("
            SELECT terme_recherche, date_recherche
            FROM $table_recherches 
            WHERE session_id = %s 
            ORDER BY date_recherche DESC 
            LIMIT 1
        ", $panier_anonyme->session_id));
        
        $derniere_activite_recherche = $derniere_recherche_anonyme ? strtotime($derniere_recherche_anonyme->date_recherche) : 0;
        $derniere_activite = max($derniere_activite_panier, $derniere_activite_recherche);
        
        $activites_users[] = [
            'user' => (object)['display_name' => 'Session: ' . $panier_anonyme->session_id, 'ID' => null],
            'derniere_activite' => $derniere_activite,
            'dernier_panier' => $dernier_produit ? [
                'product_id' => $dernier_produit->product_id,
                'quantity' => $dernier_produit->quantity,
                'date_ajout' => $dernier_produit->date_modif
            ] : null,
            'derniere_recherche' => $derniere_recherche_anonyme ? [
                'terme' => $derniere_recherche_anonyme->terme_recherche,
                'date' => $derniere_recherche_anonyme->date_recherche
            ] : null
        ];
    }
    
    // Ajouter les sessions qui ont seulement des recherches (sans paniers)
    $recherches_seules = $wpdb->get_results("
        SELECT DISTINCT r.session_id, MAX(r.date_recherche) as derniere_recherche
        FROM $table_recherches r
        LEFT JOIN $table_paniers p ON r.session_id = p.session_id
        WHERE p.session_id IS NULL 
        AND r.date_recherche >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        AND r.user_id IS NULL
        GROUP BY r.session_id 
        ORDER BY derniere_recherche DESC 
        LIMIT 5
    ");
    
    foreach ($recherches_seules as $recherche_seule) {
        $derniere_activite = strtotime($recherche_seule->derniere_recherche);
        $derniere_recherche_data = $wpdb->get_row($wpdb->prepare("
            SELECT terme_recherche, date_recherche
            FROM $table_recherches 
            WHERE session_id = %s 
            ORDER BY date_recherche DESC 
            LIMIT 1
        ", $recherche_seule->session_id));
        
        $activites_users[] = [
            'user' => (object)['display_name' => 'Session: ' . $recherche_seule->session_id, 'ID' => null],
            'derniere_activite' => $derniere_activite,
            'dernier_panier' => null,
            'derniere_recherche' => $derniere_recherche_data ? [
                'terme' => $derniere_recherche_data->terme_recherche,
                'date' => $derniere_recherche_data->date_recherche
            ] : null
        ];
    }
    
    // Trier par activité la plus récente et prendre les 10 premiers
    usort($activites_users, function($a, $b) {
        return $b['derniere_activite'] - $a['derniere_activite'];
    });
    
    $activites_users = array_slice($activites_users, 0, 10);
    
    if (!empty($activites_users)) {
        foreach ($activites_users as $activite) {
            $user = $activite['user'];
            $dernier_panier = $activite['dernier_panier'];
            $derniere_recherche = $activite['derniere_recherche'];
            
            echo '<div class="activity-card user-activity-card">';
            
            // Nom de l'utilisateur
            echo '<div class="card-user-name">' . esc_html($user->display_name) . '</div>';
            
            // Dernière activité panier
            if ($dernier_panier) {
                $product = wc_get_product($dernier_panier['product_id']);
                $product_name = $product ? $product->get_name() : 'Produit supprimé';
                echo '<div class="card-last-cart">🛒 ' . esc_html($product_name) . ' (x' . $dernier_panier['quantity'] . ')</div>';
                echo '<div class="card-date">' . date('d/m H:i', strtotime($dernier_panier['date_ajout'])) . '</div>';
            }
            
            // Dernière recherche
            if ($derniere_recherche) {
                $terme_recherche = isset($derniere_recherche['terme']) ? $derniere_recherche['terme'] : '';
                if (empty($terme_recherche) && isset($derniere_recherche['terme_recherche'])) {
                    $terme_recherche = $derniere_recherche['terme_recherche'];
                }
                echo '<div class="card-last-search">🔍 "' . esc_html($terme_recherche) . '"</div>';
                echo '<div class="card-date">' . date('d/m H:i', strtotime($derniere_recherche['date'])) . '</div>';
            }
            
            if (!$dernier_panier && !$derniere_recherche) {
                echo '<div class="card-detail">Aucune activité récente</div>';
            }
            
            echo '</div>';
        }
    } else {
        echo '<div class="activity-card">';
        echo '<div class="card-detail" style="text-align: center; color: #999;">Aucune activité récente</div>';
        echo '</div>';
    }
    
    echo '</div>';
    echo '<div class="activity-footer">';
    echo '<a href="' . admin_url('admin.php?page=comportement-clients-historique') . '">→ Explorer l\'historique complet</a>';
    echo '</div>';
    echo '</div>';
    
    echo '</div>';
    echo '</div>';
    
    // Widget Recherches (bas gauche)
    echo '<div class="dashboard-widget recherches-widget">';
    echo '<div class="widget-header">';
    echo '<span>🔍 Recherches Populaires</span>';
    echo '</div>';
    echo '<div class="widget-content">';
    
    // Variables nécessaires pour le widget recherches
    $table_recherches = $wpdb->prefix . 'recherches_anonymes';
    $recherches_populaires = $wpdb->get_results("
        SELECT terme_recherche, COUNT(*) as nb_recherches 
        FROM $table_recherches 
        WHERE date_recherche >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        GROUP BY terme_recherche 
        ORDER BY nb_recherches DESC 
        LIMIT 5
    ");
    
    echo '<div class="widget-stats">';
    echo '<div class="stat-number">' . count($recherches_populaires) . '</div>';
    echo '<div class="stat-label">Termes différents (7j)</div>';
    echo '</div>';
    
    if ($recherches_populaires) {
        foreach ($recherches_populaires as $recherche) {
            // Récupérer toutes les recherches pour ce terme
            $recherches_detail = $wpdb->get_results($wpdb->prepare("
                SELECT r.*, u.display_name, u.user_email
                FROM $table_recherches r
                LEFT JOIN {$wpdb->users} u ON r.user_id = u.ID
                WHERE r.terme_recherche = %s 
                AND r.date_recherche >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                ORDER BY r.date_recherche DESC
            ", $recherche->terme_recherche));
            
            $recherche_json = htmlspecialchars(json_encode([
                'terme' => $recherche->terme_recherche,
                'nb_total' => intval($recherche->nb_recherches),
                'recherches' => $recherches_detail
            ]), ENT_QUOTES, 'UTF-8');
            
            echo '<div class="widget-item recherche-item-clickable" onclick="openRecherchePopup(' . $recherche_json . ')">';
            echo '<span>' . esc_html($recherche->terme_recherche) . '</span>';
            echo '<span>' . intval($recherche->nb_recherches) . ' fois</span>';
            echo '</div>';
        }
    } else {
        echo '<div class="no-data">Aucune recherche récente</div>';
    }
    
    echo '</div>';
    echo '<div class="widget-footer">';
    echo '<a href="' . admin_url('admin.php?page=comportement-clients-recherches') . '">Voir toutes les recherches →</a>';
    echo '</div>';
    echo '</div>';
    
    echo '</div>'; // Fin dashboard-grid
    
    // HTML de la popup
    echo '<div id="panierPopup" class="panier-popup-overlay" style="display: none;">';
    echo '<div class="panier-popup">';
    echo '<div class="panier-popup-header">';
    echo '<h3 id="popupTitle">Détails du panier</h3>';
    echo '<button class="panier-popup-close" onclick="closePanierPopup()">&times;</button>';
    echo '</div>';
    echo '<div class="panier-popup-content">';
    echo '<div class="popup-client-info" id="popupClientInfo"></div>';
    echo '<div class="popup-produits" id="popupProduits"></div>';
    echo '</div>';
    echo '<div class="panier-popup-footer">';
    echo '<button class="btn btn-secondary" onclick="closePanierPopup()">Fermer</button>';
    echo '<a id="popupViewDetails" href="#" class="btn btn-primary">Voir page complète</a>';
    echo '</div>';
    echo '</div>';
    echo '</div>';
    
    // HTML de la popup pour les recherches
    echo '<div id="recherchePopup" class="panier-popup-overlay" style="display: none;">';
    echo '<div class="panier-popup">';
    echo '<div class="panier-popup-header">';
    echo '<h3 id="recherchePopupTitle">Détails de la recherche</h3>';
    echo '<button class="panier-popup-close" onclick="closeRecherchePopup()">&times;</button>';
    echo '</div>';
    echo '<div class="panier-popup-content">';
    echo '<div class="popup-recherche-info" id="recherchePopupInfo"></div>';
    echo '<div class="popup-recherches-list" id="recherchePopupList"></div>';
    echo '</div>';
    echo '<div class="panier-popup-footer">';
    echo '<button class="btn btn-secondary" onclick="closeRecherchePopup()">Fermer</button>';
    echo '<a id="recherchePopupViewDetails" href="#" class="btn btn-primary">Voir page complète</a>';
    echo '</div>';
    echo '</div>';
    echo '</div>';
    
    // JavaScript pour la popup
    echo '<script>
    function openPanierPopup(panierData) {
        // Mettre à jour le titre
        document.getElementById("popupTitle").textContent = "Détails du panier - " + panierData.nom_client;
        
        // Mettre à jour les informations client
        const clientInfo = document.getElementById("popupClientInfo");
        let badgeClass = panierData.type === "connecte" ? "connecte" : "anonyme";
        let badgeText = panierData.type === "connecte" ? "Connecté" : "Anonyme";
        
        clientInfo.innerHTML = `
            <div class="popup-client-name">
                ${panierData.nom_client}
                <span class="client-badge ${badgeClass}">${badgeText}</span>
            </div>
            <div class="popup-client-details">
                <div><strong>Type:</strong> ${panierData.type === "connecte" ? "Utilisateur connecté" : "Visiteur anonyme"}</div>
                <div><strong>Dernière modification:</strong> ${formatDate(panierData.date_modif)}</div>
                ${panierData.email ? `<div><strong>Email:</strong> ${panierData.email}</div>` : ""}
                <div><strong>ID:</strong> ${panierData.identifiant}</div>
            </div>
        `;
        
        // Mettre à jour la liste des produits
        const produitsContainer = document.getElementById("popupProduits");
        let produitsHTML = `<h4>🛒 Produits dans le panier (${panierData.produits.length})</h4>`;
        
        // Créer une promesse pour chaque produit pour récupérer le nom
        Promise.all(panierData.produits.map(item => getProductName(item.product_id)))
            .then(productNames => {
                panierData.produits.forEach(function(item, index) {
                    let productEditUrl = "' . admin_url('post.php?post=') . '" + item.product_id + "&action=edit";
                    let productName = productNames[index] || "Produit ID: " + item.product_id;
                    produitsHTML += `
                        <div class="popup-produit-item">
                            <div class="popup-produit-info">
                                <div class="popup-produit-nom">${productName}</div>
                                <a href="${productEditUrl}" target="_blank" class="popup-produit-link">
                                    → Modifier le produit
                                </a>
                            </div>
                            <div class="popup-produit-qty">×${item.quantity}</div>
                        </div>
                    `;
                });
                produitsContainer.innerHTML = produitsHTML;
            });
        
        // Mettre à jour le lien "Voir page complète"
        let detailUrl;
        if (panierData.type === "connecte") {
            detailUrl = "' . admin_url('admin.php?page=comportement-clients-paniers') . '#user-" + panierData.identifiant;
        } else {
            detailUrl = "' . admin_url('admin.php?page=comportement-clients-paniers') . '#session-" + encodeURIComponent(panierData.identifiant);
        }
        document.getElementById("popupViewDetails").href = detailUrl;
        
        // Afficher la popup
        document.getElementById("panierPopup").style.display = "flex";
        document.body.style.overflow = "hidden";
    }
    
    function closePanierPopup() {
        document.getElementById("panierPopup").style.display = "none";
        document.body.style.overflow = "auto";
    }
    
    function formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString("fr-FR") + " " + date.toLocaleTimeString("fr-FR", {hour: "2-digit", minute: "2-digit"});
    }
    
    async function getProductName(productId) {
        try {
            const response = await fetch("' . admin_url('admin-ajax.php') . '", {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded",
                },
                body: "action=get_product_name&product_id=" + productId
            });
            const data = await response.json();
            return data.success ? data.data : "Produit ID: " + productId;
        } catch (error) {
            return "Produit ID: " + productId;
        }
    }
    
    function openRecherchePopup(rechercheData) {
        // Mettre à jour le titre
        document.getElementById("recherchePopupTitle").textContent = "Recherches pour \"" + rechercheData.terme + "\"";
        
        // Mettre à jour les informations de recherche
        const rechercheInfo = document.getElementById("recherchePopupInfo");
        rechercheInfo.innerHTML = `
            <div class="popup-recherche-terme">"${rechercheData.terme}"</div>
            <div class="popup-recherche-stats">
                <div><strong>Total recherches:</strong> ${rechercheData.nb_total}</div>
                <div><strong>Période:</strong> 7 derniers jours</div>
                <div><strong>Utilisateurs uniques:</strong> ${getUniqueUsers(rechercheData.recherches)}</div>
                <div><strong>Dernière recherche:</strong> ${formatDate(rechercheData.recherches[0].date_recherche)}</div>
            </div>
        `;
        
        // Mettre à jour la liste des recherches
        const recherchesList = document.getElementById("recherchePopupList");
        let recherchesHTML = `<h4>🔍 Historique des recherches (${rechercheData.recherches.length})</h4>`;
        
        rechercheData.recherches.forEach(function(recherche) {
            let userName, badgeClass, badgeText;
            
            if (recherche.user_id && recherche.display_name) {
                userName = recherche.display_name;
                badgeClass = "connecte";
                badgeText = "Connecté";
            } else {
                userName = recherche.session_id;
                badgeClass = "anonyme";
                badgeText = "Anonyme";
            }
            
            recherchesHTML += `
                <div class="popup-recherche-item">
                    <div class="popup-recherche-user-info">
                        <div class="popup-recherche-user-name">
                            ${userName}
                            <span class="client-badge ${badgeClass}">${badgeText}</span>
                        </div>
                        <div class="popup-recherche-details">
                            ${recherche.user_id ? `Email: ${recherche.user_email || "N/A"}` : `Session: ${recherche.session_id}`}
                        </div>
                    </div>
                    <div class="popup-recherche-date">
                        ${formatDate(recherche.date_recherche)}
                    </div>
                </div>
            `;
        });
        
        recherchesList.innerHTML = recherchesHTML;
        
        // Mettre à jour le lien "Voir page complète"
        let detailUrl = "' . admin_url('admin.php?page=comportement-clients-recherches') . '#terme-" + encodeURIComponent(rechercheData.terme);
        document.getElementById("recherchePopupViewDetails").href = detailUrl;
        
        // Afficher la popup
        document.getElementById("recherchePopup").style.display = "flex";
        document.body.style.overflow = "hidden";
    }
    
    function closeRecherchePopup() {
        document.getElementById("recherchePopup").style.display = "none";
        document.body.style.overflow = "auto";
    }
    
    function getUniqueUsers(recherches) {
        const uniqueUsers = new Set();
        recherches.forEach(r => {
            if (r.user_id) {
                uniqueUsers.add("user_" + r.user_id);
            } else {
                uniqueUsers.add("session_" + r.session_id);
            }
        });
        return uniqueUsers.size;
    }
    
    // Fermer les popups en cliquant sur l\'overlay
    document.addEventListener("DOMContentLoaded", function() {
        // Popup paniers
        document.getElementById("panierPopup").addEventListener("click", function(e) {
            if (e.target === this) {
                closePanierPopup();
            }
        });
        
        // Popup recherches
        document.getElementById("recherchePopup").addEventListener("click", function(e) {
            if (e.target === this) {
                closeRecherchePopup();
            }
        });
        
        // Fermer avec la touche Échap
        document.addEventListener("keydown", function(e) {
            if (e.key === "Escape") {
                closePanierPopup();
                closeRecherchePopup();
            }
        });
    });
    </script>';
    
    echo '</div>'; // Fin wrap
}

// Endpoint AJAX pour récupérer le nom d'un produit
add_action('wp_ajax_get_product_name', 'ajax_get_product_name');

function ajax_get_product_name() {
    if (!current_user_can('manage_woocommerce')) {
        wp_die('Accès refusé');
    }
    
    $product_id = intval($_POST['product_id']);
    $product = wc_get_product($product_id);
    
    if ($product) {
        wp_send_json_success($product->get_name());
    } else {
        wp_send_json_error('Produit non trouvé');
    }
}

// Fonctions de rendu des pages
function afficher_page_paniers_clients() {
    global $wpdb;
    
    echo '<div class="wrap">';
    echo '<h1>🛒 Paniers des clients</h1>';
    
    // Ajouter les boutons d'export
    ajouter_boutons_export('paniers');
    
    echo '<style>
        .panier-section { margin-bottom: 30px; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .panier-header { font-size: 18px; font-weight: bold; margin-bottom: 15px; color: #2A3E6A; }
        .panier-item { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #eee; }
        .panier-meta { font-size: 12px; color: #666; margin-top: 5px; }
        .utilisateur-connecte { border-left: 4px solid #28a745; }
        .visiteur-anonyme { border-left: 4px solid #ffc107; }
    </style>';
    
    // Section 1: Utilisateurs connectés
    echo '<h2>👤 Utilisateurs Connectés</h2>';
    $users = get_users(['role__in' => ['customer', 'subscriber']]);
    $paniers_connectes = 0;
    
    foreach ($users as $user) {
        $panier = get_user_meta($user->ID, '_historique_panier', true);
        if (!$panier || !is_array($panier)) continue;
        
        $paniers_connectes++;
        echo '<div class="panier-section utilisateur-connecte">';
        echo '<div class="panier-header">' . esc_html($user->display_name) . ' (' . esc_html($user->user_email) . ')</div>';
        
        $total_produits = 0;
        $derniere_modif = '';
        
        foreach ($panier as $item) {
            $product = wc_get_product($item['product_id']);
            if ($product) {
                echo '<div class="panier-item">';
                echo '<span>' . esc_html($product->get_name()) . '</span>';
                echo '<span>Quantité: ' . intval($item['quantity']) . '</span>';
                echo '</div>';
                $total_produits += $item['quantity'];
                
                if (isset($item['date_modif']) && ($derniere_modif == '' || $item['date_modif'] > $derniere_modif)) {
                    $derniere_modif = $item['date_modif'];
                }
            }
        }
        
        // Déterminer le statut du panier
        $statut = determiner_statut_panier('connecte', $user->ID, $derniere_modif);
        $statut_labels = [
            'achete' => 'Acheté',
            'abandonne' => 'Abandonné', 
            'en_cours' => 'En cours'
        ];
        
        echo '<div class="panier-meta">';
        echo '<strong>Total produits:</strong> ' . $total_produits . ' | ';
        echo '<strong>Dernière modification:</strong> ' . ($derniere_modif ? date('d/m/Y H:i', strtotime($derniere_modif)) : 'N/A') . ' | ';
        echo '<strong>Statut:</strong> <span class="statut-badge ' . $statut . '">' . $statut_labels[$statut] . '</span>';
        echo '</div>';
        echo '</div>';
    }
    
    if ($paniers_connectes == 0) {
        echo '<p><em>Aucun panier trouvé pour les utilisateurs connectés.</em></p>';
    }
    
    // Section 2: Visiteurs anonymes
    echo '<h2>👻 Visiteurs Anonymes</h2>';
    $table_paniers = $wpdb->prefix . 'paniers_anonymes';
    
    $sessions_anonymes = $wpdb->get_results("
        SELECT session_id, COUNT(*) as nb_produits, MAX(date_modif) as derniere_modif
        FROM $table_paniers 
        GROUP BY session_id 
        ORDER BY derniere_modif DESC
    ");
    
    if ($sessions_anonymes) {
        foreach ($sessions_anonymes as $session) {
            echo '<div class="panier-section visiteur-anonyme">';
            echo '<div class="panier-header">Session: ' . esc_html($session->session_id) . '</div>';
            
            $produits_session = $wpdb->get_results($wpdb->prepare("
                SELECT product_id, quantity, date_ajout, date_modif 
                FROM $table_paniers 
                WHERE session_id = %s 
                ORDER BY date_modif DESC
            ", $session->session_id));
            
            $total_quantite = 0;
            foreach ($produits_session as $item) {
                $product = wc_get_product($item->product_id);
                if ($product) {
                    echo '<div class="panier-item">';
                    echo '<span>' . esc_html($product->get_name()) . '</span>';
                    echo '<span>Quantité: ' . intval($item->quantity) . '</span>';
                    echo '</div>';
                    $total_quantite += $item->quantity;
                }
            }
            
            // Déterminer le statut du panier anonyme
            $statut = determiner_statut_panier('anonyme', $session->session_id, $session->derniere_modif);
            $statut_labels = [
                'achete' => 'Acheté',
                'abandonne' => 'Abandonné', 
                'en_cours' => 'En cours'
            ];
            
            echo '<div class="panier-meta">';
            echo '<strong>Total produits:</strong> ' . $total_quantite . ' | ';
            echo '<strong>Dernière modification:</strong> ' . date('d/m/Y H:i', strtotime($session->derniere_modif)) . ' | ';
            echo '<strong>Statut:</strong> <span class="statut-badge ' . $statut . '">' . $statut_labels[$statut] . '</span>';
            echo '</div>';
            echo '</div>';
        }
    } else {
        echo '<p><em>Aucun panier trouvé pour les visiteurs anonymes.</em></p>';
    }
    
    echo '</div>';
}

function afficher_page_recherches_clients() {
    global $wpdb;
    
    echo '<div class="wrap">';
    echo '<h1>🔍 Recherches des clients</h1>';
    
    // Ajouter les boutons d'export
    ajouter_boutons_export('recherches');
    
    echo '<style>
        .recherche-section { margin-bottom: 30px; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .recherche-header { font-size: 18px; font-weight: bold; margin-bottom: 15px; color: #2A3E6A; }
        .recherche-item { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #eee; }
        .recherche-terme { font-weight: 500; }
        .recherche-date { font-size: 12px; color: #666; }
        .recherche-meta { font-size: 12px; color: #666; margin-top: 10px; }
        .utilisateur-connecte { border-left: 4px solid #28a745; }
        .visiteur-anonyme { border-left: 4px solid #ffc107; }
        .stats-globales { background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
    </style>';
    
    // Statistiques globales
    $table_recherches = $wpdb->prefix . 'recherches_anonymes';
    $total_recherches = $wpdb->get_var("SELECT COUNT(*) FROM $table_recherches WHERE date_recherche >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
    $recherches_populaires = $wpdb->get_results("
        SELECT terme_recherche, COUNT(*) as nb_recherches 
        FROM $table_recherches 
        WHERE date_recherche >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        GROUP BY terme_recherche 
        ORDER BY nb_recherches DESC 
        LIMIT 5
    ");
    
    echo '<div class="stats-globales">';
    echo '<h3>📊 Statistiques (7 derniers jours)</h3>';
    echo '<p><strong>Total recherches:</strong> ' . intval($total_recherches) . '</p>';
    
    if ($recherches_populaires) {
        echo '<p><strong>Top 5 recherches:</strong></p>';
        echo '<ul>';
        foreach ($recherches_populaires as $recherche) {
            echo '<li>' . esc_html($recherche->terme_recherche) . ' (' . intval($recherche->nb_recherches) . ' fois)</li>';
        }
        echo '</ul>';
    }
    echo '</div>';
    
    // Section 1: Utilisateurs connectés
    echo '<h2>👤 Utilisateurs Connectés</h2>';
    $users = get_users(['role__in' => ['customer', 'subscriber']]);
    $recherches_connectees = 0;
    
    foreach ($users as $user) {
        $recherches = get_user_meta($user->ID, 'recherche_logs', true);
        if (!$recherches || !is_array($recherches)) continue;
        
        $recherches_connectees++;
        echo '<div class="recherche-section utilisateur-connecte">';
        echo '<div class="recherche-header">' . esc_html($user->display_name) . ' (' . esc_html($user->user_email) . ')</div>';
        
        $recherches_recentes = array_slice(array_reverse($recherches), 0, 20);
        
        foreach ($recherches_recentes as $r) {
            echo '<div class="recherche-item">';
            echo '<span class="recherche-terme">' . esc_html($r['q']) . '</span>';
            echo '<span class="recherche-date">' . date('d/m/Y H:i', strtotime($r['date'])) . '</span>';
            echo '</div>';
        }
        
        echo '<div class="recherche-meta">';
        echo '<strong>Total recherches:</strong> ' . count($recherches) . ' | ';
        echo '<strong>Dernière recherche:</strong> ' . (isset($recherches_recentes[0]) ? date('d/m/Y H:i', strtotime($recherches_recentes[0]['date'])) : 'N/A');
        echo '</div>';
        echo '</div>';
    }
    
    if ($recherches_connectees == 0) {
        echo '<p><em>Aucune recherche trouvée pour les utilisateurs connectés.</em></p>';
    }
    
    // Section 2: Visiteurs anonymes
    echo '<h2>👻 Visiteurs Anonymes</h2>';
    
    $sessions_recherches = $wpdb->get_results("
        SELECT session_id, COUNT(*) as nb_recherches, MAX(date_recherche) as derniere_recherche
        FROM $table_recherches 
        WHERE user_id IS NULL
        GROUP BY session_id 
        ORDER BY derniere_recherche DESC
        LIMIT 20
    ");
    
    if ($sessions_recherches) {
        foreach ($sessions_recherches as $session) {
            echo '<div class="recherche-section visiteur-anonyme">';
            echo '<div class="recherche-header">Session: ' . esc_html($session->session_id) . '</div>';
            
            $recherches_session = $wpdb->get_results($wpdb->prepare("
                SELECT terme_recherche, date_recherche 
                FROM $table_recherches 
                WHERE session_id = %s AND user_id IS NULL 
                ORDER BY date_recherche DESC 
                LIMIT 20
            ", $session->session_id));
            
            foreach ($recherches_session as $recherche) {
                echo '<div class="recherche-item">';
                echo '<span class="recherche-terme">' . esc_html($recherche->terme_recherche) . '</span>';
                echo '<span class="recherche-date">' . date('d/m/Y H:i', strtotime($recherche->date_recherche)) . '</span>';
                echo '</div>';
            }
            
            echo '<div class="recherche-meta">';
            echo '<strong>Total recherches:</strong> ' . intval($session->nb_recherches) . ' | ';
            echo '<strong>Dernière recherche:</strong> ' . date('d/m/Y H:i', strtotime($session->derniere_recherche));
            echo '</div>';
            echo '</div>';
        }
    } else {
        echo '<p><em>Aucune recherche trouvée pour les visiteurs anonymes.</em></p>';
    }
    
    echo '</div>';
}

function afficher_page_historique_complet() {
    global $wpdb;
    
    echo '<div class="wrap">';
    echo '<h1>📊 Historique Complet - Vue Croisée</h1>';
    
    // Ajouter les boutons d'export
    ajouter_boutons_export('historique');
    
    echo '<style>
        .historique-card { margin-bottom: 30px; background: #fff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); overflow: hidden; }
        .historique-header { background: #2A3E6A; color: white; padding: 15px; font-size: 18px; font-weight: bold; }
        .historique-body { padding: 20px; }
        .historique-section { margin-bottom: 20px; }
        .section-title { font-size: 16px; font-weight: bold; color: #2A3E6A; margin-bottom: 10px; border-bottom: 2px solid #f1f1f1; padding-bottom: 5px; }
        .item-list { list-style: none; padding: 0; }
        .item-list li { padding: 5px 0; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; }
        .item-list li:last-child { border-bottom: none; }
        .meta-info { font-size: 12px; color: #666; background: #f8f9fa; padding: 10px; border-radius: 4px; margin-top: 15px; }
        .utilisateur-connecte { border-left: 4px solid #28a745; }
        .visiteur-anonyme { border-left: 4px solid #ffc107; }
        .no-data { color: #999; font-style: italic; }
        .parcours-analyse { background: #e3f2fd; padding: 10px; border-radius: 4px; margin-top: 10px; }
    </style>';
    
    // Section 1: Utilisateurs connectés
    echo '<h2>👤 Utilisateurs Connectés - Parcours Complet</h2>';
    $users = get_users(['role__in' => ['customer', 'subscriber']]);
    $historiques_connectes = 0;
    
    foreach ($users as $user) {
        $panier = get_user_meta($user->ID, '_historique_panier', true);
        $recherches = get_user_meta($user->ID, 'recherche_logs', true);
        
        if ((!$panier || !is_array($panier)) && (!$recherches || !is_array($recherches))) continue;
        
        $historiques_connectes++;
        echo '<div class="historique-card utilisateur-connecte">';
        echo '<div class="historique-header">' . esc_html($user->display_name) . ' (' . esc_html($user->user_email) . ')</div>';
        echo '<div class="historique-body">';
        
        // Recherches
        echo '<div class="historique-section">';
        echo '<div class="section-title">🔍 Dernières Recherches</div>';
        if ($recherches && is_array($recherches)) {
            echo '<ul class="item-list">';
            $recherches_recentes = array_slice(array_reverse($recherches), 0, 10);
            foreach ($recherches_recentes as $r) {
                echo '<li>';
                echo '<span>' . esc_html($r['q']) . '</span>';
                echo '<span>' . date('d/m H:i', strtotime($r['date'])) . '</span>';
                echo '</li>';
            }
            echo '</ul>';
        } else {
            echo '<p class="no-data">Aucune recherche enregistrée</p>';
        }
        echo '</div>';
        
        // Panier
        echo '<div class="historique-section">';
        echo '<div class="section-title">🛒 Contenu du Panier</div>';
        if ($panier && is_array($panier)) {
            echo '<ul class="item-list">';
            $total_produits = 0;
            foreach ($panier as $item) {
                $product = wc_get_product($item['product_id']);
                if ($product) {
                    echo '<li>';
                    echo '<span>' . esc_html($product->get_name()) . '</span>';
                    echo '<span>Qté: ' . intval($item['quantity']) . '</span>';
                    echo '</li>';
                    $total_produits += $item['quantity'];
                }
            }
            echo '</ul>';
        } else {
            echo '<p class="no-data">Panier vide</p>';
            $total_produits = 0;
        }
        echo '</div>';
        
        // Analyse du parcours
        if (($recherches && is_array($recherches)) || ($panier && is_array($panier))) {
            echo '<div class="parcours-analyse">';
            echo '<strong>📈 Analyse du parcours :</strong><br>';
            
            if ($recherches && is_array($recherches) && $panier && is_array($panier)) {
                echo '• Utilisateur actif : effectue des recherches ET ajoute des produits au panier<br>';
                echo '• Potentiel de conversion élevé';
            } elseif ($recherches && is_array($recherches)) {
                echo '• Utilisateur en exploration : recherche sans achat<br>';
                echo '• Opportunité de relance marketing';
            } elseif ($panier && is_array($panier)) {
                echo '• Acheteur direct : ajoute au panier sans recherche préalable<br>';
                echo '• Comportement d\'achat ciblé';
            }
            echo '</div>';
        }
        
        // Métadonnées
        echo '<div class="meta-info">';
        echo '<strong>Résumé :</strong> ';
        echo count($recherches ?: []) . ' recherches • ';
        echo ($total_produits ?? 0) . ' produits dans le panier • ';
        echo 'Dernière activité : ';
        
        $derniere_activite = '';
        if ($recherches && is_array($recherches) && !empty($recherches)) {
            $derniere_recherche = end($recherches)['date'];
            $derniere_activite = $derniere_recherche;
        }
        if ($panier && is_array($panier)) {
            foreach ($panier as $item) {
                if (isset($item['date_modif']) && ($derniere_activite == '' || $item['date_modif'] > $derniere_activite)) {
                    $derniere_activite = $item['date_modif'];
                }
            }
        }
        echo $derniere_activite ? date('d/m/Y H:i', strtotime($derniere_activite)) : 'N/A';
        echo '</div>';
        
        echo '</div>';
        echo '</div>';
    }
    
    if ($historiques_connectes == 0) {
        echo '<p><em>Aucun historique trouvé pour les utilisateurs connectés.</em></p>';
    }
    
    // Section 2: Visiteurs anonymes
    echo '<h2>👻 Visiteurs Anonymes - Parcours Complet</h2>';
    $table_paniers = $wpdb->prefix . 'paniers_anonymes';
    $table_recherches = $wpdb->prefix . 'recherches_anonymes';
    
    // Récupérer toutes les sessions qui ont soit des paniers soit des recherches
    $sessions_actives = $wpdb->get_results("
        SELECT DISTINCT session_id FROM (
            SELECT session_id FROM $table_paniers
            UNION 
            SELECT session_id FROM $table_recherches WHERE user_id IS NULL
        ) AS sessions
        ORDER BY session_id
        LIMIT 20
    ");
    
    if ($sessions_actives) {
        foreach ($sessions_actives as $session_data) {
            $session_id = $session_data->session_id;
            
            echo '<div class="historique-card visiteur-anonyme">';
            echo '<div class="historique-header">Session: ' . esc_html($session_id) . '</div>';
            echo '<div class="historique-body">';
            
            // Recherches de cette session
            echo '<div class="historique-section">';
            echo '<div class="section-title">🔍 Recherches Effectuées</div>';
            $recherches_session = $wpdb->get_results($wpdb->prepare("
                SELECT terme_recherche, date_recherche 
                FROM $table_recherches 
                WHERE session_id = %s AND user_id IS NULL 
                ORDER BY date_recherche DESC 
                LIMIT 10
            ", $session_id));
            
            if ($recherches_session) {
                echo '<ul class="item-list">';
                foreach ($recherches_session as $recherche) {
                    echo '<li>';
                    echo '<span>' . esc_html($recherche->terme_recherche) . '</span>';
                    echo '<span>' . date('d/m H:i', strtotime($recherche->date_recherche)) . '</span>';
                    echo '</li>';
                }
                echo '</ul>';
            } else {
                echo '<p class="no-data">Aucune recherche enregistrée</p>';
            }
            echo '</div>';
            
            // Panier de cette session
            echo '<div class="historique-section">';
            echo '<div class="section-title">🛒 Produits Ajoutés</div>';
            $produits_session = $wpdb->get_results($wpdb->prepare("
                SELECT product_id, quantity, date_modif 
                FROM $table_paniers 
                WHERE session_id = %s 
                ORDER BY date_modif DESC
            ", $session_id));
            
            if ($produits_session) {
                echo '<ul class="item-list">';
                $total_quantite = 0;
                foreach ($produits_session as $item) {
                    $product = wc_get_product($item->product_id);
                    if ($product) {
                        echo '<li>';
                        echo '<span>' . esc_html($product->get_name()) . '</span>';
                        echo '<span>Qté: ' . intval($item->quantity) . '</span>';
                        echo '</li>';
                        $total_quantite += $item->quantity;
                    }
                }
                echo '</ul>';
            } else {
                echo '<p class="no-data">Aucun produit ajouté</p>';
                $total_quantite = 0;
            }
            echo '</div>';
            
            // Analyse du parcours anonyme
            if ($recherches_session || $produits_session) {
                echo '<div class="parcours-analyse">';
                echo '<strong>📈 Analyse du parcours :</strong><br>';
                
                if ($recherches_session && $produits_session) {
                    echo '• Visiteur engagé : recherche et ajoute des produits<br>';
                    echo '• Candidat potentiel pour création de compte';
                } elseif ($recherches_session) {
                    echo '• Visiteur en exploration : recherche sans achat<br>';
                    echo '• Opportunité de retargeting publicitaire';
                } elseif ($produits_session) {
                    echo '• Acheteur potentiel : ajout direct au panier<br>';
                    echo '• Risque d\'abandon de panier élevé';
                }
                echo '</div>';
            }
            
            // Métadonnées
            echo '<div class="meta-info">';
            echo '<strong>Résumé :</strong> ';
            echo count($recherches_session ?: []) . ' recherches • ';
            echo ($total_quantite ?? 0) . ' produits ajoutés • ';
            echo 'Session active depuis : ';
            
            $premiere_activite = null;
            if ($recherches_session) {
                $premiere_activite = end($recherches_session)->date_recherche;
            }
            if ($produits_session) {
                foreach ($produits_session as $item) {
                    if ($premiere_activite === null || $item->date_modif < $premiere_activite) {
                        $premiere_activite = $item->date_modif;
                    }
                }
            }
            echo $premiere_activite ? date('d/m/Y', strtotime($premiere_activite)) : 'N/A';
            echo '</div>';
            
            echo '</div>';
            echo '</div>';
        }
    } else {
        echo '<p><em>Aucun historique trouvé pour les visiteurs anonymes.</em></p>';
    }
    
    echo '</div>';
}

// Fonctions utilitaires et avancées

// Export CSV pour les recherches
add_action('wp_ajax_export_recherches_csv', 'export_recherches_csv');

function export_recherches_csv() {
    if (!current_user_can('manage_woocommerce')) {
        wp_die('Accès refusé');
    }
    
    global $wpdb;
    $table_recherches = $wpdb->prefix . 'recherches_anonymes';
    
    // Headers pour le téléchargement CSV
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=recherches_clients_' . date('Y-m-d') . '.csv');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    // Créer le fichier CSV
    $output = fopen('php://output', 'w');
    
    // En-têtes CSV
    fputcsv($output, [
        'Type Client',
        'Identifiant',
        'Email',
        'Terme Recherché',
        'Date Recherche'
    ], ';');
    
    // Recherches des utilisateurs connectés
    $users = get_users(['role__in' => ['customer', 'subscriber']]);
    foreach ($users as $user) {
        $recherches = get_user_meta($user->ID, 'recherche_logs', true);
        if ($recherches && is_array($recherches)) {
            foreach ($recherches as $r) {
                fputcsv($output, [
                    'Utilisateur Connecté',
                    $user->ID,
                    $user->user_email,
                    $r['q'],
                    $r['date']
                ], ';');
            }
        }
    }
    
    // Recherches des visiteurs anonymes
    $recherches_anonymes = $wpdb->get_results("
        SELECT session_id, terme_recherche, date_recherche 
        FROM $table_recherches 
        WHERE user_id IS NULL 
        ORDER BY date_recherche DESC
    ");
    
    foreach ($recherches_anonymes as $recherche) {
        fputcsv($output, [
            'Visiteur Anonyme',
            $recherche->session_id,
            '',
            $recherche->terme_recherche,
            $recherche->date_recherche
        ], ';');
    }
    
    fclose($output);
    exit;
}

// Export CSV pour les paniers
add_action('wp_ajax_export_paniers_csv', 'export_paniers_csv');

function export_paniers_csv() {
    if (!current_user_can('manage_woocommerce')) {
        wp_die('Accès refusé');
    }
    
    global $wpdb;
    $table_paniers = $wpdb->prefix . 'paniers_anonymes';
    
    // Headers pour le téléchargement CSV
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=paniers_clients_' . date('Y-m-d') . '.csv');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    // Créer le fichier CSV
    $output = fopen('php://output', 'w');
    
    // En-têtes CSV
    fputcsv($output, [
        'Type Client',
        'Identifiant',
        'Email',
        'Produit',
        'Quantité',
        'Date Ajout',
        'Date Modification'
    ], ';');
    
    // Paniers des utilisateurs connectés
    $users = get_users(['role__in' => ['customer', 'subscriber']]);
    foreach ($users as $user) {
        $panier = get_user_meta($user->ID, '_historique_panier', true);
        if ($panier && is_array($panier)) {
            foreach ($panier as $item) {
                $product = wc_get_product($item['product_id']);
                if ($product) {
                    fputcsv($output, [
                        'Utilisateur Connecté',
                        $user->ID,
                        $user->user_email,
                        $product->get_name(),
                        $item['quantity'],
                        $item['date_ajout'] ?? '',
                        $item['date_modif'] ?? ''
                    ], ';');
                }
            }
        }
    }
    
    // Paniers des visiteurs anonymes
    $paniers_anonymes = $wpdb->get_results("
        SELECT session_id, product_id, quantity, date_ajout, date_modif 
        FROM $table_paniers 
        ORDER BY date_modif DESC
    ");
    
    foreach ($paniers_anonymes as $item) {
        $product = wc_get_product($item->product_id);
        if ($product) {
            fputcsv($output, [
                'Visiteur Anonyme',
                $item->session_id,
                '',
                $product->get_name(),
                $item->quantity,
                $item->date_ajout,
                $item->date_modif
            ], ';');
        }
    }
    
    fclose($output);
    exit;
}

// Fonction pour ajouter les boutons d'export dans les pages
function ajouter_boutons_export($page_type) {
    echo '<div style="margin: 20px 0; padding: 15px; background: #f1f1f1; border-radius: 5px;">';
    echo '<h3>📤 Fonctions d\'Export</h3>';
    
    if ($page_type === 'recherches') {
        echo '<a href="' . admin_url('admin-ajax.php?action=export_recherches_csv') . '" class="button button-secondary" style="margin-right: 10px;">';
        echo '<span class="dashicons dashicons-download" style="vertical-align: middle;"></span> Exporter les recherches (CSV)';
        echo '</a>';
    }
    
    if ($page_type === 'paniers') {
        echo '<a href="' . admin_url('admin-ajax.php?action=export_paniers_csv') . '" class="button button-secondary" style="margin-right: 10px;">';
        echo '<span class="dashicons dashicons-download" style="vertical-align: middle;"></span> Exporter les paniers (CSV)';
        echo '</a>';
    }
    
    if ($page_type === 'historique') {
        echo '<a href="' . admin_url('admin-ajax.php?action=export_recherches_csv') . '" class="button button-secondary" style="margin-right: 10px;">';
        echo '<span class="dashicons dashicons-download" style="vertical-align: middle;"></span> Exporter les recherches (CSV)';
        echo '</a>';
        echo '<a href="' . admin_url('admin-ajax.php?action=export_paniers_csv') . '" class="button button-secondary">';
        echo '<span class="dashicons dashicons-download" style="vertical-align: middle;"></span> Exporter les paniers (CSV)';
        echo '</a>';
    }
    
    echo '</div>';
}