<?php
/**
 * Footer Page Setup
 * Crée une page "Configuration Footer" éditable dans Pages
 *
 * @package SCW_Shop
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Créer la page Footer lors de l'activation du thème
 */
function scw_shop_create_footer_config_page() {
    // Vérifier si la page a déjà été créée
    $footer_page_id = get_option( 'scw_footer_config_page_id' );

    if ( $footer_page_id && get_post( $footer_page_id ) ) {
        return; // La page existe déjà
    }

    // Contenu par défaut avec instructions
    $content = "<!-- INSTRUCTIONS: Modifiez les valeurs ci-dessous. Ne supprimez pas les étiquettes [CHAMP] -->

[DESCRIPTION]
Leader de la distribution de pièces détachées pour stations de lavage et haute pression. Une expertise technique au service des professionnels depuis 15 ans.

[FACEBOOK]

[LINKEDIN]

[TWITTER]

[PHONE]
01 23 45 67 89

[EMAIL]
contact@scw-shop.com

[NEWSLETTER_TITLE]
Restez informé

[NEWSLETTER_DESCRIPTION]
Recevez nos offres exclusives pro et actualités techniques.

[SHOP_LINKS]
Nouveautés | /boutique?filter=nouveautes
Meilleures Ventes | /boutique?filter=meilleures-ventes
Promotions | /boutique?filter=promotions
Pièces Détachées | /boutique?cat=pieces-detachees

[SUPPORT_LINKS]
Centre d'aide | /support/aide
Documentation Technique | /support/documentation
Suivre ma commande | /compte/commandes
Demande de SAV | /support/sav

[COPYRIGHT]
© " . date( 'Y' ) . " SCW Shop. Tous droits réservés.

[LEGAL_LINKS]
Mentions Légales | /mentions-legales
CGV | /cgv
Confidentialité | /confidentialite";

    // Créer la page
    $page_id = wp_insert_post( array(
        'post_title'   => 'Footer',
        'post_content' => $content,
        'post_status'  => 'publish',
        'post_type'    => 'page',
        'post_author'  => 1,
        'page_template' => '',
    ) );

    if ( $page_id && ! is_wp_error( $page_id ) ) {
        update_option( 'scw_footer_config_page_id', $page_id );
    }
}
add_action( 'after_switch_theme', 'scw_shop_create_footer_config_page' );

/**
 * Parser le contenu de la page Footer
 */
function scw_shop_parse_footer_content() {
    $footer_page_id = get_option( 'scw_footer_config_page_id' );

    if ( ! $footer_page_id ) {
        return scw_shop_get_default_footer_data();
    }

    $page = get_post( $footer_page_id );
    if ( ! $page ) {
        return scw_shop_get_default_footer_data();
    }

    $content = $page->post_content;
    $data = array();

    // Parser chaque champ
    preg_match( '/\[DESCRIPTION\](.*?)\[/s', $content, $match );
    $data['description'] = isset( $match[1] ) ? trim( $match[1] ) : '';

    preg_match( '/\[FACEBOOK\](.*?)\[/s', $content, $match );
    $data['facebook'] = isset( $match[1] ) ? trim( $match[1] ) : '';

    preg_match( '/\[LINKEDIN\](.*?)\[/s', $content, $match );
    $data['linkedin'] = isset( $match[1] ) ? trim( $match[1] ) : '';

    preg_match( '/\[TWITTER\](.*?)\[/s', $content, $match );
    $data['twitter'] = isset( $match[1] ) ? trim( $match[1] ) : '';

    preg_match( '/\[PHONE\](.*?)\[/s', $content, $match );
    $data['phone'] = isset( $match[1] ) ? trim( $match[1] ) : '';

    preg_match( '/\[EMAIL\](.*?)\[/s', $content, $match );
    $data['email'] = isset( $match[1] ) ? trim( $match[1] ) : '';

    preg_match( '/\[NEWSLETTER_TITLE\](.*?)\[/s', $content, $match );
    $data['newsletter_title'] = isset( $match[1] ) ? trim( $match[1] ) : '';

    preg_match( '/\[NEWSLETTER_DESCRIPTION\](.*?)\[/s', $content, $match );
    $data['newsletter_description'] = isset( $match[1] ) ? trim( $match[1] ) : '';

    preg_match( '/\[COPYRIGHT\](.*?)\[/s', $content, $match );
    $data['copyright'] = isset( $match[1] ) ? trim( $match[1] ) : '';

    // Parser les liens (format: Texte | URL)
    preg_match( '/\[SHOP_LINKS\](.*?)\[/s', $content, $match );
    $data['shop_links'] = scw_shop_parse_links( isset( $match[1] ) ? $match[1] : '' );

    preg_match( '/\[SUPPORT_LINKS\](.*?)\[/s', $content, $match );
    $data['support_links'] = scw_shop_parse_links( isset( $match[1] ) ? $match[1] : '' );

    preg_match( '/\[LEGAL_LINKS\](.*?)\[/s', $content, $match );
    $data['legal_links'] = scw_shop_parse_links( isset( $match[1] ) ? $match[1] : '' );

    return $data;
}

/**
 * Parser les liens (format: Texte | URL)
 */
function scw_shop_parse_links( $text ) {
    $lines = explode( "\n", trim( $text ) );
    $links = array();

    foreach ( $lines as $line ) {
        $line = trim( $line );
        if ( empty( $line ) ) {
            continue;
        }

        $parts = explode( '|', $line );
        if ( count( $parts ) === 2 ) {
            $links[] = array(
                'text' => trim( $parts[0] ),
                'url'  => trim( $parts[1] ),
            );
        }
    }

    return $links;
}

/**
 * Valeurs par défaut
 */
function scw_shop_get_default_footer_data() {
    return array(
        'description' => 'Leader de la distribution de pièces détachées pour stations de lavage et haute pression. Une expertise technique au service des professionnels depuis 15 ans.',
        'facebook' => '',
        'linkedin' => '',
        'twitter' => '',
        'phone' => '01 23 45 67 89',
        'email' => 'contact@scw-shop.com',
        'newsletter_title' => 'Restez informé',
        'newsletter_description' => 'Recevez nos offres exclusives pro et actualités techniques.',
        'copyright' => '© ' . date( 'Y' ) . ' SCW Shop. Tous droits réservés.',
        'shop_links' => array(
            array( 'text' => 'Nouveautés', 'url' => '/boutique?filter=nouveautes' ),
            array( 'text' => 'Meilleures Ventes', 'url' => '/boutique?filter=meilleures-ventes' ),
            array( 'text' => 'Promotions', 'url' => '/boutique?filter=promotions' ),
            array( 'text' => 'Pièces Détachées', 'url' => '/boutique?cat=pieces-detachees' ),
        ),
        'support_links' => array(
            array( 'text' => 'Centre d\'aide', 'url' => '/support/aide' ),
            array( 'text' => 'Documentation Technique', 'url' => '/support/documentation' ),
            array( 'text' => 'Suivre ma commande', 'url' => '/compte/commandes' ),
            array( 'text' => 'Demande de SAV', 'url' => '/support/sav' ),
        ),
        'legal_links' => array(
            array( 'text' => 'Mentions Légales', 'url' => '/mentions-legales' ),
            array( 'text' => 'CGV', 'url' => '/cgv' ),
            array( 'text' => 'Confidentialité', 'url' => '/confidentialite' ),
        ),
    );
}

/**
 * Ajouter un lien rapide vers la page Footer dans l'admin bar
 */
function scw_shop_admin_bar_footer_link( $wp_admin_bar ) {
    $footer_page_id = get_option( 'scw_footer_config_page_id' );

    if ( ! $footer_page_id || ! current_user_can( 'edit_pages' ) ) {
        return;
    }

    $args = array(
        'id'    => 'edit-footer-page',
        'title' => '✏️ Modifier le Footer',
        'href'  => admin_url( 'post.php?post=' . $footer_page_id . '&action=edit' ),
        'meta'  => array(
            'class' => 'scw-edit-footer',
        ),
    );

    $wp_admin_bar->add_node( $args );
}
add_action( 'admin_bar_menu', 'scw_shop_admin_bar_footer_link', 100 );
