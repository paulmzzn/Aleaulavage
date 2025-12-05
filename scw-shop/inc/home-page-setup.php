<?php
/**
 * Home Page Setup
 * Crée une page "Configuration Accueil" éditable dans Pages
 *
 * @package SCW_Shop
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Créer la page Homepage Config lors de l'activation du thème
 */
function scw_shop_create_home_page() {
    // Vérifier si la page a déjà été créée
    $home_page_id = get_option( 'scw_home_page_id' );

    if ( $home_page_id && get_post( $home_page_id ) ) {
        return; // La page existe déjà
    }

    // Contenu par défaut avec instructions
    $content = "<!-- INSTRUCTIONS: Modifiez les valeurs ci-dessous. Ne supprimez pas les étiquettes [CHAMP] -->

=== SECTION HERO (Visiteurs & Clients) ===

[HERO_PILL]
Nouveau Catalogue 2025

[HERO_TITLE]
L'Expertise Lavage<br/>au Service des Pros

[HERO_SUBTITLE]
Plus de 5000 références en stock. Livraison J+1 partout en France.

[HERO_CTA_GUEST]
Demander un accès pro

[HERO_CTA_CLIENT]
Voir le catalogue

=== SECTION HERO REVENDEUR MODE GESTION ===

[RESELLER_GESTION_PILL]
Mode Gestion

[RESELLER_GESTION_TITLE]
Configuration Tarifaire

[RESELLER_GESTION_STAT1_LABEL]
Marge Moyenne

[RESELLER_GESTION_STAT2_LABEL]
Chiffre d'affaires

=== SECTION HERO REVENDEUR MODE ACHAT ===

[RESELLER_ACHAT_PILL]
Mode Achat Stock

[RESELLER_ACHAT_TITLE]
Réapprovisionnement

[RESELLER_ACHAT_SUBTITLE]
Commandez au meilleur prix revendeur SCW.

=== SECTION HERO REVENDEUR MODE VITRINE ===

[RESELLER_VITRINE_PILL]
Mode Vitrine

[RESELLER_VITRINE_TITLE]
Boutique Revendeur

[RESELLER_VITRINE_SUBTITLE]
Présentation client (Prix Publics uniquement)

=== MARQUES PARTENAIRES (séparées par des virgules) ===

[BRANDS]
BURKERT, MTM HYDRO, TECOMEC, SEKO, CAT PUMPS, ALBERICI

=== TITRES DES SLIDERS PRODUITS ===

[SLIDER_BESTSELLERS_TITLE]
Sélection du Moment

[SLIDER_NEWARRIVALS_TITLE]
Dernières Nouveautés

[SLIDER_PROMOS_TITLE]
Promotions en cours

[SLIDER_SEE_MORE]
Voir tous les produits

=== CARROUSEL PROMO SAISONNIER ===
Format: label | titre | description | texte bouton | url | theme (winter/summer/spring/autumn)

[PROMO_SLIDES]
Offre Saisonnière | Préparez l'Hivernage | Remises exceptionnelles sur les chaudières et hors-gel. Protégez vos installations. | Découvrir la gamme Hiver | /seasonal | winter
Offre Printemps | Préparez la Saison | Équipements haute pression pour le nettoyage de printemps. | Découvrir les offres | /seasonal?season=spring | spring

=== BARRE D'AVANTAGES ===
Format: titre | sous-titre

[FEATURES]
Livraison Express | J+1 avant 16h
Garantie 2 Ans | Pièces & MO
SAV Expert | Assistance technique dédiée
Paiement Sécurisé | LCR ou CB

=== SECTION CATÉGORIES ===

[CATEGORIES_TITLE]
Nos Univers

=== SECTION CONFIANCE ===

[TRUST_TITLE]
Pourquoi choisir SCW Shop ?

[TRUST_CARDS]
+5000 Références | Le plus grand stock de pièces détachées en France. | box
Expédition Rapide | 98% des commandes expédiées le jour même. | bolt
Partenaire Agréé | Distributeur officiel des plus grandes marques. | medal";

    // Créer la page
    $page_id = wp_insert_post( array(
        'post_title'   => 'Configuration Accueil',
        'post_content' => $content,
        'post_status'  => 'private', // Private pour ne pas apparaître en front
        'post_type'    => 'page',
        'post_author'  => 1,
    ) );

    if ( $page_id && ! is_wp_error( $page_id ) ) {
        update_option( 'scw_home_page_id', $page_id );
    }
}
add_action( 'after_switch_theme', 'scw_shop_create_home_page' );

// Créer la page maintenant si elle n'existe pas (pour les thèmes déjà actifs)
add_action( 'init', function() {
    $home_page_id = get_option( 'scw_home_page_id' );
    
    // Vérifier si la page existe vraiment (pas seulement l'option)
    if ( ! $home_page_id || ! get_post( $home_page_id ) || get_post_status( $home_page_id ) === 'trash' ) {
        // Supprimer l'ancienne option si elle pointe vers une page inexistante
        delete_option( 'scw_home_page_id' );
        scw_shop_create_home_page();
    }
} );

/**
 * Parser le contenu de la page Homepage Config
 */
function scw_shop_parse_homepage_content() {
    $home_page_id = get_option( 'scw_home_page_id' );

    if ( ! $home_page_id ) {
        return scw_shop_get_default_homepage_data();
    }

    $page = get_post( $home_page_id );
    if ( ! $page ) {
        return scw_shop_get_default_homepage_data();
    }

    // Nettoyer le contenu des balises Gutenberg et HTML
    $content = $page->post_content;
    // Supprimer les commentaires Gutenberg <!-- wp:xxx -->
    $content = preg_replace( '/<!--\s*\/?wp:[^>]*-->/s', '', $content );
    // Supprimer les balises HTML sauf <br>
    $content = preg_replace( '/<(?!br\s*\/?)[^>]+>/i', '', $content );
    // Nettoyer les entités HTML
    $content = html_entity_decode( $content, ENT_QUOTES, 'UTF-8' );
    
    $data = array();

    // Hero Guest/Client
    $data['hero_pill'] = scw_shop_extract_homepage_field( $content, 'HERO_PILL' );
    $data['hero_title'] = scw_shop_extract_homepage_field( $content, 'HERO_TITLE' );
    $data['hero_subtitle'] = scw_shop_extract_homepage_field( $content, 'HERO_SUBTITLE' );
    $data['hero_cta_guest'] = scw_shop_extract_homepage_field( $content, 'HERO_CTA_GUEST' );
    $data['hero_cta_client'] = scw_shop_extract_homepage_field( $content, 'HERO_CTA_CLIENT' );

    // Hero Reseller Gestion
    $data['reseller_gestion_pill'] = scw_shop_extract_homepage_field( $content, 'RESELLER_GESTION_PILL' );
    $data['reseller_gestion_title'] = scw_shop_extract_homepage_field( $content, 'RESELLER_GESTION_TITLE' );
    $data['reseller_gestion_stat1_label'] = scw_shop_extract_homepage_field( $content, 'RESELLER_GESTION_STAT1_LABEL' );
    $data['reseller_gestion_stat2_label'] = scw_shop_extract_homepage_field( $content, 'RESELLER_GESTION_STAT2_LABEL' );

    // Hero Reseller Achat
    $data['reseller_achat_pill'] = scw_shop_extract_homepage_field( $content, 'RESELLER_ACHAT_PILL' );
    $data['reseller_achat_title'] = scw_shop_extract_homepage_field( $content, 'RESELLER_ACHAT_TITLE' );
    $data['reseller_achat_subtitle'] = scw_shop_extract_homepage_field( $content, 'RESELLER_ACHAT_SUBTITLE' );

    // Hero Reseller Vitrine
    $data['reseller_vitrine_pill'] = scw_shop_extract_homepage_field( $content, 'RESELLER_VITRINE_PILL' );
    $data['reseller_vitrine_title'] = scw_shop_extract_homepage_field( $content, 'RESELLER_VITRINE_TITLE' );
    $data['reseller_vitrine_subtitle'] = scw_shop_extract_homepage_field( $content, 'RESELLER_VITRINE_SUBTITLE' );

    // Brands
    $brands_raw = scw_shop_extract_homepage_field( $content, 'BRANDS' );
    $data['brands'] = array_filter( array_map( 'trim', explode( ',', $brands_raw ) ) );

    // Slider titles
    $data['slider_bestsellers_title'] = scw_shop_extract_homepage_field( $content, 'SLIDER_BESTSELLERS_TITLE' );
    $data['slider_newarrivals_title'] = scw_shop_extract_homepage_field( $content, 'SLIDER_NEWARRIVALS_TITLE' );
    $data['slider_promos_title'] = scw_shop_extract_homepage_field( $content, 'SLIDER_PROMOS_TITLE' );
    $data['slider_see_more'] = scw_shop_extract_homepage_field( $content, 'SLIDER_SEE_MORE' );

    // Promo slides
    $data['promo_slides'] = scw_shop_parse_promo_slides( scw_shop_extract_homepage_field( $content, 'PROMO_SLIDES' ) );

    // Features
    $data['features'] = scw_shop_parse_features( scw_shop_extract_homepage_field( $content, 'FEATURES' ) );

    // Categories
    $data['categories_title'] = scw_shop_extract_homepage_field( $content, 'CATEGORIES_TITLE' );

    // Trust
    $data['trust_title'] = scw_shop_extract_homepage_field( $content, 'TRUST_TITLE' );
    $data['trust_cards'] = scw_shop_parse_trust_cards( scw_shop_extract_homepage_field( $content, 'TRUST_CARDS' ) );

    return $data;
}

/**
 * Extraire un champ du contenu
 */
function scw_shop_extract_homepage_field( $content, $field_name ) {
    // Pattern pour capturer le contenu entre [FIELD] et le prochain [ ou === ou fin
    $pattern = '/\[' . $field_name . '\]\s*(.*?)(?=\[|===|$)/s';
    preg_match( $pattern, $content, $match );
    return isset( $match[1] ) ? trim( $match[1] ) : '';
}

/**
 * Parser les slides promo
 */
function scw_shop_parse_promo_slides( $text ) {
    $lines = explode( "\n", trim( $text ) );
    $slides = array();

    foreach ( $lines as $line ) {
        $line = trim( $line );
        if ( empty( $line ) ) {
            continue;
        }

        $parts = explode( '|', $line );
        if ( count( $parts ) >= 5 ) {
            $slides[] = array(
                'label'       => trim( $parts[0] ),
                'title'       => trim( $parts[1] ),
                'description' => trim( $parts[2] ),
                'button_text' => trim( $parts[3] ),
                'url'         => trim( $parts[4] ),
                'theme'       => isset( $parts[5] ) ? trim( $parts[5] ) : 'winter',
            );
        }
    }

    return $slides;
}

/**
 * Parser les features
 */
function scw_shop_parse_features( $text ) {
    $lines = explode( "\n", trim( $text ) );
    $features = array();
    $icons = array( 'truck', 'shield', 'wrench', 'card' );

    foreach ( $lines as $index => $line ) {
        $line = trim( $line );
        if ( empty( $line ) ) {
            continue;
        }

        $parts = explode( '|', $line );
        if ( count( $parts ) === 2 ) {
            $features[] = array(
                'title'    => trim( $parts[0] ),
                'subtitle' => trim( $parts[1] ),
                'icon'     => isset( $icons[ count( $features ) ] ) ? $icons[ count( $features ) ] : 'check',
            );
        }
    }

    return $features;
}

/**
 * Parser les trust cards
 */
function scw_shop_parse_trust_cards( $text ) {
    $lines = explode( "\n", trim( $text ) );
    $cards = array();

    foreach ( $lines as $line ) {
        $line = trim( $line );
        if ( empty( $line ) ) {
            continue;
        }

        $parts = explode( '|', $line );
        if ( count( $parts ) >= 2 ) {
            $cards[] = array(
                'title'       => trim( $parts[0] ),
                'description' => trim( $parts[1] ),
                'icon'        => isset( $parts[2] ) ? trim( $parts[2] ) : 'box',
            );
        }
    }

    return $cards;
}

/**
 * Valeurs par défaut
 */
function scw_shop_get_default_homepage_data() {
    return array(
        // Hero Guest/Client
        'hero_pill'           => 'Nouveau Catalogue 2025',
        'hero_title'          => 'L\'Expertise Lavage<br/>au Service des Pros',
        'hero_subtitle'       => 'Plus de 5000 références en stock. Livraison J+1 partout en France.',
        'hero_cta_guest'      => 'Demander un accès pro',
        'hero_cta_client'     => 'Voir le catalogue',

        // Hero Reseller Gestion
        'reseller_gestion_pill'        => 'Mode Gestion',
        'reseller_gestion_title'       => 'Configuration Tarifaire',
        'reseller_gestion_stat1_label' => 'Marge Moyenne',
        'reseller_gestion_stat2_label' => 'Chiffre d\'affaires',

        // Hero Reseller Achat
        'reseller_achat_pill'     => 'Mode Achat Stock',
        'reseller_achat_title'    => 'Réapprovisionnement',
        'reseller_achat_subtitle' => 'Commandez au meilleur prix revendeur SCW.',

        // Hero Reseller Vitrine
        'reseller_vitrine_pill'     => 'Mode Vitrine',
        'reseller_vitrine_title'    => 'Boutique Revendeur',
        'reseller_vitrine_subtitle' => 'Présentation client (Prix Publics uniquement)',

        // Brands
        'brands' => array( 'BURKERT', 'MTM HYDRO', 'TECOMEC', 'SEKO', 'CAT PUMPS', 'ALBERICI' ),

        // Sliders
        'slider_bestsellers_title' => 'Sélection du Moment',
        'slider_newarrivals_title' => 'Dernières Nouveautés',
        'slider_promos_title'      => 'Promotions en cours',
        'slider_see_more'          => 'Voir tous les produits',

        // Promo slides
        'promo_slides' => array(
            array(
                'label'       => 'Offre Saisonnière',
                'title'       => 'Préparez l\'Hivernage',
                'description' => 'Remises exceptionnelles sur les chaudières et hors-gel. Protégez vos installations.',
                'button_text' => 'Découvrir la gamme Hiver',
                'url'         => '/seasonal',
                'theme'       => 'winter',
            ),
        ),

        // Features
        'features' => array(
            array( 'title' => 'Livraison Express', 'subtitle' => 'J+1 avant 16h', 'icon' => 'truck' ),
            array( 'title' => 'Garantie 2 Ans', 'subtitle' => 'Pièces & MO', 'icon' => 'shield' ),
            array( 'title' => 'SAV Expert', 'subtitle' => 'Assistance technique dédiée', 'icon' => 'wrench' ),
            array( 'title' => 'Paiement Sécurisé', 'subtitle' => 'LCR ou CB', 'icon' => 'card' ),
        ),

        // Categories
        'categories_title' => 'Nos Univers',

        // Trust
        'trust_title' => 'Pourquoi choisir SCW Shop ?',
        'trust_cards' => array(
            array( 'title' => '+5000 Références', 'description' => 'Le plus grand stock de pièces détachées en France.', 'icon' => 'box' ),
            array( 'title' => 'Expédition Rapide', 'description' => '98% des commandes expédiées le jour même.', 'icon' => 'bolt' ),
            array( 'title' => 'Partenaire Agréé', 'description' => 'Distributeur officiel des plus grandes marques.', 'icon' => 'medal' ),
        ),
    );
}

/**
 * Obtenir l'icône SVG pour les features
 */
function scw_shop_get_feature_icon( $icon ) {
    $icons = array(
        'truck' => '<svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="1" y="3" width="15" height="13"></rect>
            <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon>
            <circle cx="5.5" cy="18.5" r="2.5"></circle>
            <circle cx="18.5" cy="18.5" r="2.5"></circle>
        </svg>',
        'shield' => '<svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
        </svg>',
        'wrench' => '<svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"></path>
        </svg>',
        'card' => '<svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="2" y="5" width="20" height="14" rx="2"></rect>
            <line x1="2" y1="10" x2="22" y2="10"></line>
        </svg>',
    );

    return isset( $icons[ $icon ] ) ? $icons[ $icon ] : $icons['truck'];
}

/**
 * Obtenir l'icône SVG pour les trust cards
 */
function scw_shop_get_trust_icon( $icon ) {
    $icons = array(
        'box' => '<svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
            <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
            <line x1="12" y1="22.08" x2="12" y2="12"></line>
        </svg>',
        'bolt' => '<svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"></path>
        </svg>',
        'medal' => '<svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <circle cx="12" cy="8" r="7"></circle>
            <polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"></polyline>
        </svg>',
    );

    return isset( $icons[ $icon ] ) ? $icons[ $icon ] : $icons['box'];
}

/**
 * Obtenir l'icône SVG pour les promos saisonnières
 */
function scw_shop_get_promo_icon( $theme ) {
    $icons = array(
        'winter' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <path d="M2 12h20M12 2v20M20 20L4 4m16 0L4 20" />
            <path d="M8.5 8.5l7 7M8.5 15.5l7-7" />
        </svg>',
        'summer' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <circle cx="12" cy="12" r="5"></circle>
            <line x1="12" y1="1" x2="12" y2="3"></line>
            <line x1="12" y1="21" x2="12" y2="23"></line>
            <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line>
            <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line>
            <line x1="1" y1="12" x2="3" y2="12"></line>
            <line x1="21" y1="12" x2="23" y2="12"></line>
            <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line>
            <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line>
        </svg>',
        'spring' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <path d="M12 22c4-4 8-7.5 8-12a8 8 0 1 0-16 0c0 4.5 4 8 8 12z"></path>
            <circle cx="12" cy="10" r="3"></circle>
        </svg>',
        'autumn' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <path d="M11 20A7 7 0 0 1 9.8 6.1C15.5 5 17 4.5 19 2c1 2 2 4.5 2 7a7 7 0 0 1-7 7"></path>
            <path d="M12 20v-7"></path>
            <path d="M9 17l3 3 3-3"></path>
        </svg>',
    );

    return isset( $icons[ $theme ] ) ? $icons[ $theme ] : $icons['winter'];
}

/**
 * Ajouter un lien rapide vers la page Homepage Config dans l'admin bar
 */
function scw_shop_admin_bar_home_link( $wp_admin_bar ) {
    $home_page_id = get_option( 'scw_home_page_id' );

    if ( ! $home_page_id || ! current_user_can( 'edit_pages' ) ) {
        return;
    }

    $args = array(
        'id'    => 'edit-home-page',
        'title' => '🏠 Modifier l\'Accueil',
        'href'  => admin_url( 'post.php?post=' . $home_page_id . '&action=edit' ),
        'meta'  => array(
            'class' => 'scw-edit-home',
        ),
    );

    $wp_admin_bar->add_node( $args );
}
add_action( 'admin_bar_menu', 'scw_shop_admin_bar_home_link', 99 );
