<?php
// ========================================
// SYSTÈME DE GALERIE V2 - TEST PARALLÈLE
// ========================================

// Charger le nouveau système de galerie V2 (en test)
add_action('wp_enqueue_scripts', function() {
    if (is_product()) {
        // CSS du nouveau système V2
        wp_enqueue_style(
            'gallery-v2',
            get_template_directory_uri() . '/css/gallery-v2.css',
            array(),
            '1.0.0'
        );
        
        // JavaScript du nouveau système V2
        wp_enqueue_script(
            'gallery-v2',
            get_template_directory_uri() . '/js/gallery-v2.js',
            array(),
            '1.0.0',
            true
        );
    }
}, 5); // Priorité 5 pour charger après l'ancien système

// ========================================
// SYSTÈME EXISTANT PRÉSERVÉ
// ========================================

// Laisser ELECX fonctionner normalement mais garder accès au prix original

/**
 * Debug / Dev features helpers
 * - Use THEME_DEV_FEATURES_ENABLED constant or WP_ENV=development or option 'theme_dev_features_enabled'
 * - Use my_theme_debug_log() instead of error_log for safer logging in production
 */
if (!function_exists('is_dev_features_enabled')) {
    function is_dev_features_enabled() {
        if (defined('THEME_DEV_FEATURES_ENABLED') && THEME_DEV_FEATURES_ENABLED) {
            return true;
        }
        if (defined('WP_ENV') && WP_ENV === 'development') {
            return true;
        }
        if (function_exists('get_option') && get_option('theme_dev_features_enabled', false)) {
            return true;
        }
        return false;
    }
}

if (!function_exists('my_theme_debug_log')) {
    function my_theme_debug_log($message) {
        // Only log when WP_DEBUG is enabled and current user is admin (safe default)
        if (!defined('WP_DEBUG') || !WP_DEBUG) {
            return;
        }

        // Allow logging for CLI contexts
        if (defined('WP_CLI') && WP_CLI) {
            if (is_array($message) || is_object($message)) {
                error_log(print_r($message, true));
            } else {
                error_log($message);
            }
            return;
        }

        if (!function_exists('current_user_can') || !current_user_can('administrator')) {
            return;
        }

        if (is_array($message) || is_object($message)) {
            error_log(print_r($message, true));
        } else {
            error_log($message);
        }
    }
}
// pour notre logique JavaScript personnalisée

// Zoom multi-niveaux pour galerie produit
add_action('wp_enqueue_scripts', function() {
    if (is_product()) {
        // CSS améliorations zoom
        wp_enqueue_style(
            'zoom-enhancements',
            get_stylesheet_directory_uri() . '/css/zoom-enhancements.css',
            array(),
            filemtime(get_stylesheet_directory() . '/css/zoom-enhancements.css')
        );
        
        // Script zoom multi-niveaux
        wp_enqueue_script(
            'single-product-zoom',
            get_stylesheet_directory_uri() . '/js/single-product-zoom.js',
            array(),
            filemtime(get_stylesheet_directory() . '/js/single-product-zoom.js'),
            true
        );
    }
    // Swatch couleurs variations
    wp_enqueue_script(
        'variation-color-swatches',
        get_stylesheet_directory_uri() . '/js/variation-color-swatches.js',
        array(),
        filemtime(get_stylesheet_directory() . '/js/variation-color-swatches.js'),
        true
    );
    
    // Chargement des icônes Lucide pour la section skills de la homepage
    if (is_front_page()) {
        wp_enqueue_script(
            'lucide-icons',
            'https://unpkg.com/lucide@latest/dist/umd/lucide.js',
            array(),
            null,
            false // Charger dans le head pour que les icônes soient disponibles immédiatement
        );
        
        // Script d'initialisation des icônes
        wp_add_inline_script('lucide-icons', '
            document.addEventListener("DOMContentLoaded", function() {
                if (typeof lucide !== "undefined") {
                    lucide.createIcons();
                }
            });
        ');
        
        // Script pour faire fonctionner les boutons produits homepage comme single product
        wp_add_inline_script('jquery', '
            jQuery(document).ready(function($) {
                // Attendre que WooCommerce soit chargé
                $(document).on("wc_fragments_loaded wc_fragments_refreshed", function() {
                    initHomepageCartButtons();
                });
                
                // Initialiser immédiatement aussi
                setTimeout(initHomepageCartButtons, 1000);
                
                function initHomepageCartButtons() {
                    // Cibler tous les boutons "Ajouter au panier" de la section produits homepage
                    $(".wp-block-woocommerce-product-collection .wc-block-components-product-button button").each(function() {
                        var $btn = $(this);
                        
                        // Si ce n\'est pas déjà fait, modifier le comportement
                        if (!$btn.data("homepage-cart-initialized")) {
                            $btn.data("homepage-cart-initialized", true);
                            
                            // Intercepter le clic pour faire un vrai ajout AJAX
                            $btn.on("click", function(e) {
                                e.preventDefault();
                                e.stopPropagation();
                                
                                var $product = $btn.closest(".wc-block-product");
                                var productId = $btn.data("product_id") || $product.find("[data-product-id]").data("product-id");
                                
                                if (!productId) {
                                    // Essayer de récupérer l\'ID depuis l\'URL du bouton
                                    var href = $btn.attr("href") || $product.find("a").first().attr("href");
                                    if (href) {
                                        var match = href.match(/product_id=(\d+)|\/product\/[^\/]+\/(\d+)/);
                                        if (match) {
                                            productId = match[1] || match[2];
                                        }
                                    }
                                }
                                
                                if (productId) {
                                    // Déclencher le loading
                                    $(document.body).trigger("adding_to_cart", [$btn, {}]);
                                    $("#offcanvas-cart").addClass("loading");
                                    
                                    // Faire l\'ajout AJAX réel
                                    $.ajax({
                                        url: wc_add_to_cart_params.wc_ajax_url.toString().replace("%%endpoint%%", "add_to_cart"),
                                        method: "POST",
                                        data: {
                                            product_id: productId,
                                            quantity: 1
                                        },
                                        success: function(response) {
                                            if (response.fragments) {
                                                // Mettre à jour les fragments
                                                $.each(response.fragments, function(key, value) {
                                                    $(key).replaceWith(value);
                                                });
                                                
                                                // Déclencher les events de succès
                                                $(document.body).trigger("added_to_cart", [response.fragments, response.cart_hash, $btn]);
                                                $(document.body).trigger("wc_fragments_refreshed");
                                            }
                                            
                                            // Le produit est ajouté au panier, l\'utilisateur peut ouvrir le mini cart manuellement
                                            
                                            // Mettre à jour le header
                                            if (typeof updateCartTotalAndProgress === "function") {
                                                updateCartTotalAndProgress();
                                            }
                                        },
                                        error: function() {
                                            //console.log("Erreur lors de l\'ajout au panier");
                                        },
                                        complete: function() {
                                            $("#offcanvas-cart").removeClass("loading");
                                        }
                                    });
                                } else {
                                    //console.log("Product ID not found");
                                }
                            });
                        }
                    });
                }
            });
        ');
    }
});

/**
 * Include Theme Customizer.
 *
 * @since v1.0
 */
$theme_customizer = __DIR__ . '/inc/customizer.php';
if ( is_readable( $theme_customizer ) ) {
	require_once $theme_customizer;
}

// Ajout d'une section bandeau promo dans le Customizer
add_action('customize_register', function($wp_customize) {
    $wp_customize->add_section('promo_banner_section', [
        'title' => __('Bandeau promotion', 'aleaulavage'),
        'priority' => 10,
    ]);
    $wp_customize->add_setting('promo_banner_message', [
        'default' => '',
        'sanitize_callback' => 'wp_kses_post',
        'transport' => 'refresh',
    ]);
    $wp_customize->add_control('promo_banner_message', [
        'label' => __('Message du bandeau', 'aleaulavage'),
        'section' => 'promo_banner_section',
        'type' => 'textarea',
    ]);
});

// WooCommerce
require get_template_directory() . '/woocommerce/woocommerce-functions.php';
// WooCommerce END

/**
 * General Theme Settings.
 *
 * @since v1.0
 */
if ( ! function_exists( 'daz_setup_theme' ) ) {
	function daz_setup_theme() {
		// Make theme available for translation: Translations can be filed in the /languages/ directory.
		load_theme_textdomain( 'daz', __DIR__ . '/languages' );

		/**
		 * Set the content width based on the theme's design and stylesheet.
		 *
		 * @since v1.0
		 */
		global $content_width;
		if ( ! isset( $content_width ) ) {
			$content_width = 800;
		}

		// Theme Support.
		add_theme_support( 'title-tag' );
		add_theme_support( 'automatic-feed-links' );
		add_theme_support( 'post-thumbnails' );
		add_theme_support(
			'html5',
			array(
				'search-form',
				'comment-form',
				'comment-list',
				'gallery',
				'caption',
				'script',
				'style',
				'navigation-widgets',
			)
		);

		// Add support for Block Styles.
		add_theme_support( 'wp-block-styles' );
			// Add support for full and wide alignment.
		add_theme_support( 'align-wide' );
		// Add support for editor styles.
		add_theme_support( 'editor-styles' );
		// Enqueue editor styles.
		add_editor_style( 'style-editor.css' );

		// Default Attachment Display Settings.
		update_option( 'image_default_align', 'none' );
		update_option( 'image_default_link_type', 'none' );
		update_option( 'image_default_size', 'large' );

		// Custom CSS-Styles of Wordpress Gallery.
		add_filter( 'use_default_gallery_style', '__return_false' );
	}
	add_action( 'after_setup_theme', 'daz_setup_theme' );

	// Disable Block Directory: https://github.com/WordPress/gutenberg/blob/trunk/docs/reference-guides/filters/editor-filters.md#block-directory
	remove_action( 'enqueue_block_editor_assets', 'wp_enqueue_editor_block_directory_assets' );
	remove_action( 'enqueue_block_editor_assets', 'gutenberg_enqueue_block_editor_assets_block_directory' );
}


/**
 * Fire the wp_body_open action.
 *
 * Added for backwards compatibility to support pre 5.2.0 WordPress versions.
 *
 * @since v2.2
 */
if ( ! function_exists( 'wp_body_open' ) ) {
	function wp_body_open() {
		/**
		 * Triggered after the opening <body> tag.
		 *
		 * @since v2.2
		 */
		do_action( 'wp_body_open' );
	}
}


/**
 * Add new User fields to Userprofile.
 *
 * @since v1.0
 */
if ( ! function_exists( 'daz_add_user_fields' ) ) {
	function daz_add_user_fields( $fields ) {
		// Add new fields.
		$fields['facebook_profile'] = 'Facebook URL';
		$fields['twitter_profile']  = 'Twitter URL';
		$fields['linkedin_profile'] = 'LinkedIn URL';
		$fields['xing_profile']     = 'Xing URL';
		$fields['github_profile']   = 'GitHub URL';

		return $fields;
	}
	add_filter( 'user_contactmethods', 'daz_add_user_fields' ); // get_user_meta( $user->ID, 'facebook_profile', true );
}


/**
 * Test if a page is a blog page.
 * if ( is_blog() ) { ... }
 *
 * @since v1.0
 */
function is_blog() {
	global $post;
	$posttype = get_post_type( $post );

	return ( ( is_archive() || is_author() || is_category() || is_home() || is_single() || ( is_tag() && ( 'post' === $posttype ) ) ) ? true : false );
}


/**
 * Disable comments for Media (Image-Post, Jetpack-Carousel, etc.)
 *
 * @since v1.0
 */
function daz_filter_media_comment_status( $open, $post_id = null ) {
	$media_post = get_post( $post_id );
	if ( 'attachment' === $media_post->post_type ) {
		return false;
	}
	return $open;
}
add_filter( 'comments_open', 'daz_filter_media_comment_status', 10, 2 );


/**
 * Style Edit buttons as badges: https://getbootstrap.com/docs/5.0/components/badge
 *
 * @since v1.0
 */
function daz_custom_edit_post_link( $output ) {
	return str_replace( 'class="post-edit-link"', 'class="post-edit-link badge badge-secondary"', $output );
}
add_filter( 'edit_post_link', 'daz_custom_edit_post_link' );

function daz_custom_edit_comment_link( $output ) {
	return str_replace( 'class="comment-edit-link"', 'class="comment-edit-link badge badge-secondary"', $output );
}
add_filter( 'edit_comment_link', 'daz_custom_edit_comment_link' );


/**
 * Responsive oEmbed filter: https://getbootstrap.com/docs/5.0/helpers/ratio
 *
 * @since v1.0
 */
function daz_oembed_filter( $html ) {
	return '<div class="ratio ratio-16x9">' . $html . '</div>';
}
add_filter( 'embed_oembed_html', 'daz_oembed_filter', 10, 4 );


if ( ! function_exists( 'daz_content_nav' ) ) {
	/**
	 * Display a navigation to next/previous pages when applicable.
	 *
	 * @since v1.0
	 */
	function daz_content_nav( $nav_id ) {
		global $wp_query;

		if ( $wp_query->max_num_pages > 1 ) {
	?>
			<div id="<?php echo esc_attr( $nav_id ); ?>" class="d-flex mb-4 justify-content-between">
				<div><?php next_posts_link( '<span aria-hidden="true">&larr;</span> ' . esc_html__( 'Older posts', 'daz' ) ); ?></div>
				<div><?php previous_posts_link( esc_html__( 'Newer posts', 'daz' ) . ' <span aria-hidden="true">&rarr;</span>' ); ?></div>
			</div><!-- /.d-flex -->
	<?php
		} else {
			echo '<div class="clearfix"></div>';
		}
	}

	// Add Class.
	function posts_link_attributes() {
		return 'class="btn btn-secondary btn-lg"';
	}
	add_filter( 'next_posts_link_attributes', 'posts_link_attributes' );
	add_filter( 'previous_posts_link_attributes', 'posts_link_attributes' );
}


/**
 * Init Widget areas in Sidebar.
 *
 * @since v1.0
 */
function daz_widgets_init() {
	// Area 1.
	register_sidebar(
		array(
			'name'          => 'Footer Contact',
			'id'            => 'footer_contact_area',
			'before_widget' => '',
			'after_widget'  => '',
			'before_title'  => '<h3 class="widget-title">',
			'after_title'   => '</h3>',
		)
	);

	// Area 2.
	register_sidebar(
		array(
			'name'          => 'Footer Social',
			'id'            => 'footer_social_area',
			'before_widget' => '',
			'after_widget'  => '',
			'before_title'  => '<h3 class="widget-title">',
			'after_title'   => '</h3>',
		)
	);

	// Area 3.
	register_sidebar(
		array(
			'name'          => 'Footer Schedule',
			'id'            => 'footer_schedule_area',
			'before_widget' => '',
			'after_widget'  => '',
			'before_title'  => '<h3 class="widget-title">',
			'after_title'   => '</h3>',
		)
	);
}
add_action( 'widgets_init', 'daz_widgets_init' );


if ( ! function_exists( 'daz_article_posted_on' ) ) {
	/**
	 * "Theme posted on" pattern.
	 *
	 * @since v1.0
	 */
	function daz_article_posted_on() {
		printf(
			wp_kses_post( __( '<span class="sep">Posted on </span><a href="%1$s" title="%2$s" rel="bookmark"><time class="entry-date" datetime="%3$s">%4$s</time></a>', 'daz' ) ),
			esc_url( get_the_permalink() ),
			esc_attr( get_the_date() . ' - ' . get_the_time() ),
			esc_attr( get_the_date( 'c' ) ),
			esc_html( get_the_date() . ' - ' . get_the_time() ),
			esc_url( get_author_posts_url( (int) get_the_author_meta( 'ID' ) ) ),
			sprintf( esc_attr__( 'View all posts by %s', 'daz' ), get_the_author() ),
			get_the_author()
		);
	}
}


/**
 * Template for Password protected post form.
 *
 * @since v1.0
 */
function daz_password_form() {
	global $post;
	$label = 'pwbox-' . ( empty( $post->ID ) ? rand() : $post->ID );

	$output = '<div class="row">';
		$output .= '<form action="' . esc_url( site_url( 'wp-login.php?action=postpass', 'login_post' ) ) . '" method="post">';
		$output .= '<h4 class="col-md-12 alert alert-warning">' . esc_html__( 'This content is password protected. To view it please enter your password below.', 'daz' ) . '</h4>';
			$output .= '<div class="col-md-6">';
				$output .= '<div class="input-group">';
					$output .= '<input type="password" name="post_password" id="' . esc_attr( $label ) . '" placeholder="' . esc_attr__( 'Password', 'daz' ) . '" class="form-control" />';
					$output .= '<div class="input-group-append"><input type="submit" name="submit" class="btn btn-primary" value="' . esc_attr__( 'Submit', 'daz' ) . '" /></div>';
				$output .= '</div><!-- /.input-group -->';
			$output .= '</div><!-- /.col -->';
		$output .= '</form>';
	$output .= '</div><!-- /.row -->';
	return $output;
}
add_filter( 'the_password_form', 'daz_password_form' );


if ( ! function_exists( 'daz_comment' ) ) {
	/**
	 * Style Reply link.
	 *
	 * @since v1.0
	 */
	function daz_replace_reply_link_class( $class ) {
		return str_replace( "class='comment-reply-link", "class='comment-reply-link btn btn-outline-secondary", $class );
	}
	add_filter( 'comment_reply_link', 'daz_replace_reply_link_class' );

	/**
	 * Template for comments and pingbacks:
	 * add function to comments.php ... wp_list_comments( array( 'callback' => 'daz_comment' ) );
	 *
	 * @since v1.0
	 */
	function daz_comment( $comment, $args, $depth ) {
		$GLOBALS['comment'] = $comment;
		switch ( $comment->comment_type ) :
			case 'pingback':
			case 'trackback':
	?>
		<li class="post pingback">
			<p><?php esc_html_e( 'Pingback:', 'daz' ); ?> <?php comment_author_link(); ?><?php edit_comment_link( esc_html__( 'Edit', 'daz' ), '<span class="edit-link">', '</span>' ); ?></p>
	<?php
				break;
			default:
	?>
		<li <?php comment_class(); ?> id="li-comment-<?php comment_ID(); ?>">
			<article id="comment-<?php comment_ID(); ?>" class="comment">
				<footer class="comment-meta">
					<div class="comment-author vcard">
						<?php
							$avatar_size = ( '0' !== $comment->comment_parent ? 68 : 136 );
							echo get_avatar( $comment, $avatar_size );

							/* translators: 1: comment author, 2: date and time */
							printf(
								wp_kses_post( __( '%1$s, %2$s', 'daz' ) ),
								sprintf( '<span class="fn">%s</span>', get_comment_author_link() ),
								sprintf( '<a href="%1$s"><time datetime="%2$s">%3$s</time></a>',
									esc_url( get_comment_link( $comment->comment_ID ) ),
									get_comment_time( 'c' ),
									/* translators: 1: date, 2: time */
									sprintf( esc_html__( '%1$s ago', 'daz' ), human_time_diff( (int) get_comment_time( 'U' ), current_time( 'timestamp' ) ) )
								)
							);

							edit_comment_link( esc_html__( 'Edit', 'daz' ), '<span class="edit-link">', '</span>' );
						?>
					</div><!-- .comment-author .vcard -->

					<?php if ( '0' === $comment->comment_approved ) { ?>
						<em class="comment-awaiting-moderation"><?php esc_html_e( 'Your comment is awaiting moderation.', 'daz' ); ?></em>
						<br />
					<?php } ?>
				</footer>

				<div class="comment-content"><?php comment_text(); ?></div>

				<div class="reply">
					<?php
						comment_reply_link(
							array_merge(
								$args,
								array(
									'reply_text' => esc_html__( 'Reply', 'daz' ) . ' <span>&darr;</span>',
									'depth'      => $depth,
									'max_depth'  => $args['max_depth'],
								)
							)
						);
					?>
				</div><!-- /.reply -->
			</article><!-- /#comment-## -->
		<?php
				break;
		endswitch;
	}

	/**
	 * Custom Comment form.
	 *
	 * @since v1.0
	 * @since v1.1: Added 'submit_button' and 'submit_field'
	 * @since v2.0.2: Added '$consent' and 'cookies'
	 */
	function daz_custom_commentform( $args = array(), $post_id = null ) {
		if ( null === $post_id ) {
			$post_id = get_the_ID();
		}

		$commenter     = wp_get_current_commenter();
		$user          = wp_get_current_user();
		$user_identity = $user->exists() ? $user->display_name : '';

		$args = wp_parse_args( $args );

		$req      = get_option( 'require_name_email' );
		$aria_req = ( $req ? " aria-required='true' required" : '' );
		$consent  = ( empty( $commenter['comment_author_email'] ) ? '' : ' checked="checked"' );
		$fields   = array(
			'author'  => '<div class="form-floating mb-3">
							<input type="text" id="author" name="author" class="form-control" value="' . esc_attr( $commenter['comment_author'] ) . '" placeholder="' . esc_html__( 'Name', 'daz' ) . ( $req ? '*' : '' ) . '"' . $aria_req . ' />
							<label for="author">' . esc_html__( 'Name', 'daz' ) . ( $req ? '*' : '' ) . '</label>
						</div>',
			'email'   => '<div class="form-floating mb-3">
							<input type="email" id="email" name="email" class="form-control" value="' . esc_attr( $commenter['comment_author_email'] ) . '" placeholder="' . esc_html__( 'Email', 'daz' ) . ( $req ? '*' : '' ) . '"' . $aria_req . ' />
							<label for="email">' . esc_html__( 'Email', 'daz' ) . ( $req ? '*' : '' ) . '</label>
						</div>',
			'url'     => '',
			'cookies' => '<p class="form-check mb-3 comment-form-cookies-consent">
							<input id="wp-comment-cookies-consent" name="wp-comment-cookies-consent" class="form-check-input" type="checkbox" value="yes"' . $consent . ' />
							<label class="form-check-label" for="wp-comment-cookies-consent">' . esc_html__( 'Save my name, email, and website in this browser for the next time I comment.', 'daz' ) . '</label>
						</p>',
		);

		$defaults = array(
			'fields'               => apply_filters( 'comment_form_default_fields', $fields ),
			'comment_field'        => '<div class="form-floating mb-3">
											<textarea id="comment" name="comment" class="form-control" aria-required="true" required placeholder="' . esc_attr__( 'Comment', 'daz' ) . ( $req ? '*' : '' ) . '"></textarea>
											<label for="comment">' . esc_html__( 'Comment', 'daz' ) . '</label>
										</div>',
			/** This filter is documented in wp-includes/link-template.php */
			'must_log_in'          => '<p class="must-log-in">' . sprintf( wp_kses_post( __( 'You must be <a href="%s">logged in</a> to post a comment.', 'daz' ) ), wp_login_url( apply_filters( 'the_permalink', get_the_permalink( get_the_ID() ) ) ) ) . '</p>',
			/** This filter is documented in wp-includes/link-template.php */
			'logged_in_as'         => '<p class="logged-in-as">' . sprintf( wp_kses_post( __( 'Logged in as <a href="%1$s">%2$s</a>. <a href="%3$s" title="Log out of this account">Log out?</a>', 'daz' ) ), get_edit_user_link(), $user->display_name, wp_logout_url( apply_filters( 'the_permalink', get_the_permalink( get_the_ID() ) ) ) ) . '</p>',
			'comment_notes_before' => '<p class="small comment-notes">' . esc_html__( 'Your Email address will not be published.', 'daz' ) . '</p>',
			'comment_notes_after'  => '',
			'id_form'              => 'commentform',
			'id_submit'            => 'submit',
			'class_submit'         => 'btn btn-primary',
			'name_submit'          => 'submit',
			'title_reply'          => '',
			'title_reply_to'       => esc_html__( 'Leave a Reply to %s', 'daz' ),
			'cancel_reply_link'    => esc_html__( 'Cancel reply', 'daz' ),
			'label_submit'         => esc_html__( 'Post Comment', 'daz' ),
			'submit_button'        => '<input type="submit" id="%2$s" name="%1$s" class="%3$s" value="%4$s" />',
			'submit_field'         => '<div class="form-submit">%1$s %2$s</div>',
			'format'               => 'html5',
		);

		return $defaults;
	}
	add_filter( 'comment_form_defaults', 'daz_custom_commentform' );
}


/**
 * Nav menus.
 *
 * @since v1.0
 */
// Register Bootstrap 5 Nav Walker
if (!function_exists('register_navwalker')) :
	function register_navwalker() {
	  require_once('inc/class-bootstrap-5-navwalker.php');
	  // Register Menus
	  register_nav_menu('main-menu', 'Main menu');
	  register_nav_menu('footer-menu', 'Footer menu');
	  register_nav_menu('footer-legal', 'Footer legal');
	}
  endif;
  add_action('after_setup_theme', 'register_navwalker');
  // Register Bootstrap 5 Nav Walker END


/**
 * Loading All CSS Stylesheets and Javascript Files.
 *
 * @since v1.0
 */
function daz_scripts_loader() {
	$theme_version = wp_get_theme()->get( 'Version' );

	// 1. Styles.
	wp_enqueue_style( 'style', get_theme_file_uri( 'style.css' ), array(), $theme_version, 'all' );
	wp_enqueue_style( 'main', get_theme_file_uri( 'assets/dist/main.css' ), array(), $theme_version, 'all' ); // main.scss: Compiled Framework source + custom styles.
	wp_enqueue_style('fontawesome', get_theme_file_uri('assets/fontawesome/css/all.min.css'), array(), $theme_version, 'all');
	if ( is_rtl() ) {
		wp_enqueue_style( 'rtl', get_theme_file_uri( 'assets/dist/rtl.css' ), array(), $theme_version, 'all' );
	}

	// 2. Scripts.
	wp_enqueue_script( 'mainjs', get_theme_file_uri( 'assets/dist/main.bundle.js' ), array(), $theme_version, true );

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'daz_scripts_loader' );

// Désactiver l'affichage des miniatures des sous-catégories
add_filter('woocommerce_subcategory_thumbnail', '__return_false');

// Supprimer l'action qui ajoute la miniature avant le titre de la sous-catégorie
remove_action('woocommerce_before_subcategory_title', 'woocommerce_subcategory_thumbnail', 10);

// Place la sous-cat en bas 
remove_action( 'woocommerce_archive_description', 'woocommerce_taxonomy_archive_description', 10 );
add_action( 'woocommerce_after_shop_loop', 'woocommerce_taxonomy_archive_description', 5 );

// Place les sous cat en bulle dans les cat
add_action( 'woocommerce_before_shop_loop', 'display_subcategories_bubbles', 5 );

function get_subcategories($category_id) {
    // Get all product categories
    $all_categories = get_terms(array(
        'taxonomy' => 'product_cat',
        'hide_empty' => true,
    ));
    
    // Get the current category's children
    $subcategories = array();
    foreach ($all_categories as $cat) {
        if ($cat->parent === $category_id) {
            $subcategories[] = $cat;
        }
    }
    
    return $subcategories;
}

function display_subcategories_bubbles() {
    try {
        if (!is_product_category()) {
            return;
        }

        $subcategories = get_terms(array(
            'taxonomy'     => 'product_cat',
            'hide_empty'   => false,
            'parent'       => get_queried_object_id()
        ));

        if (is_wp_error($subcategories) || empty($subcategories)) {
            return;
        }

        $button_styles = 'position: absolute; top: 50%; transform: translateY(-50%); z-index: 10; ' .
                        'width: 40px; height: 40px; border-radius: 50%; border: none; ' .
                        'background-color: #fff; box-shadow: 0 2px 5px rgba(0,0,0,0.2); ' .
                        'cursor: pointer; display: flex; align-items: center; justify-content: center; ' .
                        'transition: all 0.3s ease;';

        echo '<div class="subcategory-slider-container" style="position: relative; width: 100%; padding: 10px 0; margin: 0 0 20px; overflow: hidden;">';
        
        // On va d'abord créer le contenu pour vérifier sa largeur
        ob_start();
        echo '<div class="subcategory-bubbles-wrapper"><div class="subcategory-bubbles">';
        
        foreach ($subcategories as $subcategory) {
            $link = get_term_link($subcategory);
            if (!is_wp_error($link)) {
                echo '<a href="' . esc_url($link) . '" class="subcategory-bubble">';
                echo '<span>' . esc_html($subcategory->name) . '</span>';
                echo '</a>';
            }
        }
        
        echo '</div></div>';
        $content = ob_get_clean();
        
        // Les boutons et gradients seront ajoutés conditionnellement via JavaScript
        echo '<div class="navigation-elements" style="display: none;">';
        echo '<button class="prev-button" style="' . $button_styles . 'left: 0;"><i class="fas fa-chevron-left"></i></button>';
        echo '<button class="next-button" style="' . $button_styles . 'right: 0;"><i class="fas fa-chevron-right"></i></button>';
        echo '<div class="gradient-left"></div>';
        echo '<div class="gradient-right"></div>';
        echo '</div>';
        
        // Afficher le contenu
        echo $content;
        echo '</div>';

        echo '<style>
            .subcategory-slider-container {
                position: relative;
                overflow: hidden !important;
                touch-action: pan-x; /* Améliore le défilement tactile */
            }
            .subcategory-bubbles-wrapper {
                margin: 0 auto;
                overflow: hidden !important;
                padding: 0 10px;
                transition: margin 0.3s ease;
            }
            .subcategory-bubbles-wrapper.with-navigation {
                margin: 0 45px; /* Réduit sur mobile */
            }
            .subcategory-bubbles {
                display: flex;
                transition: transform 0.3s ease;
                width: max-content;
                gap: 8px; /* Réduit sur mobile */
                padding-right: 45px;
            }
            .subcategory-bubble {
                flex: 0 0 auto;
                padding: 6px 12px; /* Plus compact sur mobile */
                text-decoration: none;
                background: #2A3E6A;
                color: white !important;
                border-radius: 20px;
                white-space: nowrap;
                position: relative;
                z-index: 1;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                transition: all 0.3s ease;
                font-size: 14px; /* Plus petit sur mobile */
                min-width: 40px;
                text-align: center;
            }
            /* ...existing hover styles... */

            /* Styles spécifiques mobile */
            @media (max-width: 768px) {
                .subcategory-bubbles-wrapper.with-navigation {
                    margin: 0 35px;
                }
                .subcategory-bubble {
                    padding: 5px 10px;
                    font-size: 13px;
                }
                .prev-button, .next-button {
                    width: 32px !important;
                    height: 32px !important;
                }
                .gradient-left, .gradient-right {
                    width: 40px;
                }
            }

            /* Support du défilement tactile */
            .subcategory-bubbles.dragging {
                transition: none;
                cursor: grabbing;
            }
            
            /* ...existing gradient and button styles... */
        </style>';

        echo '<script>
            document.addEventListener("DOMContentLoaded", function() {
                const container = document.querySelector(".subcategory-slider-container");
                const slider = container.querySelector(".subcategory-bubbles");
                const wrapper = container.querySelector(".subcategory-bubbles-wrapper");
                const navigation = container.querySelector(".navigation-elements");
                
                let isDragging = false;
                let startX;
                let scrollLeft;
                let index = 0;

                // Gestion du défilement tactile
                function handleTouchStart(e) {
                    isDragging = true;
                    slider.classList.add("dragging");
                    startX = e.type === "mousedown" ? e.pageX : e.touches[0].pageX;
                    scrollLeft = slider.style.transform ? 
                        parseInt(slider.style.transform.match(/-?\d+/)[0]) : 0;
                }

                function handleTouchMove(e) {
                    if (!isDragging) return;
                    e.preventDefault();
                    const x = e.type === "mousemove" ? e.pageX : e.touches[0].pageX;
                    const walk = (startX - x);
                    
                    // Limite le défilement
                    const maxScroll = slider.scrollWidth - wrapper.offsetWidth + 60;
                    let newScroll = scrollLeft + walk;
                    newScroll = Math.max(0, Math.min(newScroll, maxScroll));
                    
                    slider.style.transform = `translateX(-${newScroll}px)`;
                }

                function handleTouchEnd() {
                    isDragging = false;
                    slider.classList.remove("dragging");
                }

                // Ajout des événements tactiles
                slider.addEventListener("touchstart", handleTouchStart);
                slider.addEventListener("touchmove", handleTouchMove);
                slider.addEventListener("touchend", handleTouchEnd);

                // Support également de la souris pour le défilement
                slider.addEventListener("mousedown", handleTouchStart);
                window.addEventListener("mousemove", handleTouchMove);
                window.addEventListener("mouseup", handleTouchEnd);

                // Fonction de vérification du dépassement mise à jour
                function checkOverflow() {
                    const needsNavigation = slider.scrollWidth > wrapper.offsetWidth;
                    
                    if (needsNavigation) {
                        navigation.style.display = "block";
                        wrapper.classList.add("with-navigation");
                    } else {
                        navigation.style.display = "none";
                        wrapper.classList.remove("with-navigation");
                        slider.style.transform = "translateX(0)";
                    }
                }

                // Fonction de déplacement mise à jour pour mobile
                function moveSlider(direction) {
                    if (!slider || !wrapper) return;
                    
                    const totalWidth = slider.scrollWidth;
                    const visibleWidth = wrapper.offsetWidth;
                    const maxScroll = totalWidth - visibleWidth + 60;
                    const slideWidth = window.innerWidth <= 768 ? 
                        slider.children[0].offsetWidth + 8 : // Gap plus petit sur mobile
                        slider.children[0].offsetWidth + 10;

                    index = Math.max(0, Math.min(
                        index + direction,
                        Math.ceil((maxScroll + slideWidth) / slideWidth)
                    ));

                    let movement = Math.min(index * slideWidth, maxScroll);
                    
                    if (movement >= maxScroll - slideWidth) {
                        movement = maxScroll;
                    }

                    slider.style.transform = `translateX(-${movement}px)`;
                }

                // Event listeners existants
                checkOverflow();
                window.addEventListener("resize", checkOverflow);
                document.querySelector(".prev-button")?.addEventListener("click", () => moveSlider(-1));
                document.querySelector(".next-button")?.addEventListener("click", () => moveSlider(1));
            });
        </script>';
    } catch (Exception $e) {
    my_theme_debug_log("Error in display_subcategories_bubbles: " . $e->getMessage());
    }
}

function enable_ajax_add_to_cart() {
    // Ajoute le script WooCommerce pour l'AJAX
    wp_enqueue_script('wc-add-to-cart', plugins_url('/assets/js/frontend/add-to-cart.min.js', WC_PLUGIN_FILE), array('jquery'), WC_VERSION, true);

    // Assure que les fragments de panier sont activés
    add_filter('woocommerce_add_to_cart_fragments', 'update_cart_fragments');
}
add_action('wp_enqueue_scripts', 'enable_ajax_add_to_cart');

function update_cart_fragments($fragments) {
    ob_start();
    woocommerce_mini_cart();
    $fragments['div.widget_shopping_cart_content'] = ob_get_clean();
    return $fragments;
}

function custom_update_order_via_post() {
    register_rest_route('wp/v2', '/update_order', array(
        'methods' => 'POST', // Vérifie que tu utilises la bonne méthode HTTP
        'callback' => 'handle_update_order_post', // Fonction de gestion
        // Restreindre l'accès aux utilisateurs ayant la capacité de gérer les commandes
        'permission_callback' => function() {
            return current_user_can('manage_woocommerce') || current_user_can('edit_shop_orders');
        },
    ));
}

add_action('rest_api_init', 'custom_update_order_via_post');

function handle_update_order_post(WP_REST_Request $request) {
    $order_id = $request->get_param('order_id');
    $new_status = $request->get_param('status');
    $new_total = $request->get_param('total');
    
    $order = wc_get_order($order_id);
    
    if ($order) {
        $order->set_status($new_status);
        $order->set_total($new_total);
        $order->save();
        
        return new WP_REST_Response('Commande mise à jour', 200);
    } else {
        return new WP_REST_Response('Commande non trouvée', 404);
    }
}

function enqueue_category_styles() {
    wp_enqueue_style('category-custom-styles', get_stylesheet_directory_uri() . '/assets/main.css');
}
add_action('wp_enqueue_scripts', 'enqueue_category_styles');

// Supprimer l'affichage par défaut du prix
remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_price', 10);

// Ajouter notre fonction personnalisée à la place
add_action('woocommerce_single_product_summary', 'custom_template_single_price', 10);

function custom_template_single_price() {
    global $product;

    if ( ! $product instanceof WC_Product ) return;

    // Produit variable
    if ( $product->is_type( 'variable' ) ) {
        $variations = $product->get_available_variations();

        if ( ! empty( $variations ) ) {
            // Récupérer tous les prix
            $prices = array_map(function($variation) {
                return floatval($variation['display_price']);
            }, $variations);

            $unique_prices = array_unique($prices);
            sort($unique_prices);

            // Afficher la plage de prix initiale
            $min_price = min($unique_prices);
            $max_price = max($unique_prices);
            
            echo '<div class="price-container">';
            
            if ( count($unique_prices) === 1 ) {
                // Un seul prix
                echo '<p class="price variable-price">' . wc_price( $unique_prices[0] ) . '</p>';
            } else {
                // Plage de prix
                echo '<p class="price variable-price">' . wc_price( $min_price ) . ' – ' . wc_price( $max_price ) . '</p>';
            }
            
            // Conteneur pour le prix de la variante sélectionnée (initialement caché)
            echo '<div class="woocommerce-variation-price" style="display: none;"></div>';
            
            echo '</div>';
        }
        return;
    }

    // Produit simple : prix normal
    if ( $product->get_price() > 0 ) {
        echo '<p class="price">' . $product->get_price_html() . '</p>';
    }
}

add_action('wp_footer', function () {
    if (is_product()) : ?>
        <script>
        jQuery(document).ready(function($) {
            
            // Cibler les éléments prix et UGS
            var priceElements = $('.price');
            var skuElement = $('.product-sku span');
            
            
            // Stocker les valeurs originales
            var originalPrices = [];
            priceElements.each(function(index) {
                originalPrices[index] = $(this).html();
            });
            
            var originalSku = skuElement.html();
            
            // Fonction pour mettre à jour prix et UGS
            function updateProductInfo(variation) {
                //console.log('🔄 Mise à jour - Variation:', variation);
                
                // NE PAS mettre à jour le prix automatiquement - on gère ça ailleurs
                // Seulement mettre à jour l'UGS
                if (variation.sku && skuElement.length) {
                    //console.log('🏷️ Nouvel UGS:', variation.sku);
                    skuElement.html(variation.sku);
                } else {
                    //console.log('⚠️ Pas d\'UGS dans la variation');
                }
            }
            
            // Fonction pour restaurer les valeurs originales
            function resetProductInfo() {
                //console.log('🔄 Reset - restauration des valeurs originales');
                
                // Restaurer les prix
                $('.price').each(function(index) {
                    if (originalPrices[index]) {
                        $(this).html(originalPrices[index]);
                    }
                });
                
                // Restaurer l'UGS
                if (skuElement.length && originalSku) {
                    skuElement.html(originalSku);
                }
            }
            
            // Variable pour stocker le prix original
            var originalPriceHTML = $('.purchase-header .price').html();
            
            // Événement found_variation - simple et direct
            $(document).on('found_variation', function (event, variation) {
                //console.log('🔔 Événement found_variation déclenché');
                //console.log('📊 Variation reçue:', variation);
                updateProductInfo(variation);
                
                // Délai pour vérifier si toutes les variations sont sélectionnées
                setTimeout(function() {
                    if (isAllVariationsSelected() && variation && variation.price_html) {
                        //console.log('✅ VARIATION COMPLÈTE - Mise à jour du prix:', variation.price_html);
                        updateVariantPrice(variation);
                    } else {
                        //console.log('❌ Sélection incomplète - garder la plage de prix');
                        restoreVariableProductPriceRange();
                    }
                }, 100);
            });
            
            // Événement reset_data - déclenché quand on réinitialise les sélections
            $(document).on('reset_data', function () {
                //console.log('🔄 Reset des données de variation');
                resetProductInfo();
                restoreVariableProductPriceRange();
            });
            
            function updateVariantPrice(variation) {
                const $priceElement = $('.purchase-header .price');
                if ($priceElement.length && variation.price_html) {
                    //console.log('💰 Mise à jour du prix de la variation:', variation.price_html);
                    
                    // Masquer TOUS les éléments de la plage de prix
                    $priceElement.find('.woocommerce-Price-amount').hide();
                    $priceElement.find('[aria-hidden="true"]').hide();
                    $priceElement.find('.screen-reader-text').hide();
                    
                    // Supprimer l'ancien prix de variation s'il existe
                    $priceElement.find('.single-variation-price').remove();
                    
                    // Ajouter le nouveau prix de variation
                    $priceElement.append('<span class="single-variation-price">' + variation.price_html + '</span>');
                }
            }
            
            function restoreVariableProductPriceRange() {
                const $priceElement = $('.purchase-header .price');
                if ($priceElement.length) {
                    //console.log('🔄 Restauration de la plage de prix');
                    
                    // Supprimer le prix de variation
                    $priceElement.find('.single-variation-price').remove();
                    
                    // Réafficher TOUS les éléments de la plage de prix
                    $priceElement.find('.woocommerce-Price-amount').show();
                    $priceElement.find('[aria-hidden="true"]').show();
                    $priceElement.find('.screen-reader-text').show();
                }
            }
            
            function isAllVariationsSelected() {
                const $form = $('.variations_form');
                if (!$form.length) return false;
                
                const $selects = $form.find('.variations select');
                let allSelected = true;
                let selections = {};
                
                $selects.each(function() {
                    const value = $(this).val();
                    const name = $(this).attr('name');
                    selections[name] = value;
                    
                    if (!value || value === '') {
                        allSelected = false;
                        return false;
                    }
                });
                
                //console.log('🔍 Vérification sélection complète:', allSelected, 'Sélections:', selections);
                return allSelected;
            }
            
            // Écouter les changements sur les sélecteurs de variation pour restaurer la plage de prix si sélection incomplète
            $(document).on('change', '.variations select', function() {
                var selectedValue = $(this).val();
                //console.log('📝 Select modifié:', selectedValue);
                
                // Délai pour laisser WooCommerce traiter la sélection
                setTimeout(function() {
                    checkIfAllVariationsSelected();
                }, 100);
            });
            
            // Écouter les clics sur les boutons d'attribut personnalisés 
            $(document).on('click', '.attribute-button, .color-swatch', function() {
                //console.log('🎯 Attribut cliqué');
                
                setTimeout(function() {
                    checkIfAllVariationsSelected();
                }, 150);
            });
            
            function checkIfAllVariationsSelected() {
                const $form = $('.variations_form');
                if (!$form.length) return;
                
                const $selects = $form.find('.variations select');
                let allSelected = true;
                
                $selects.each(function() {
                    if (!$(this).val() || $(this).val() === '') {
                        allSelected = false;
                        return false;
                    }
                });
                
                //console.log('🔍 Toutes les variations sélectionnées?', allSelected);
                
                // Si toutes les variations ne sont pas sélectionnées, restaurer la plage de prix
                if (!allSelected) {
                    //console.log('⚠️ Sélection incomplète - restauration de la plage de prix');
                    restoreVariableProductPriceRange();
                }
            }
            
            // Alternative : écouter directement les changements des selects cachés
            $(document).on('change', '.variations select[style*="display: none"]', function() {
                var selectedValue = $(this).val();
                //console.log('📝 Select caché modifié:', selectedValue);
                
                setTimeout(function() {
                    var form = $('.variations_form');
                    var variations = form.data('product_variations');
                    
                    if (variations && selectedValue) {
                        // Chercher la variation correspondante
                        for (var i = 0; i < variations.length; i++) {
                            var variation = variations[i];
                            
                            // Vérifier si cette variation correspond à la sélection
                            for (var attr in variation.attributes) {
                                if (variation.attributes[attr] === selectedValue) {
                                    //console.log('🎯 Variation manuelle trouvée');
                                    updateProductInfo(variation);
                                    return;
                                }
                            }
                        }
                    }
                    
                    // Si pas de correspondance et valeur vide, reset
                    if (!selectedValue) {
                        resetProductInfo();
                    }
                }, 200);
            });
        });
        </script>
    <?php endif;
});

add_action( 'woocommerce_single_product_summary', 'display_sku_on_product_page', 25 );
function display_sku_on_product_page() {
    global $product;
    if ( $product->get_sku() ) {
        echo '<div class="sku_wrapper"><strong>' . __( 'SKU:', 'woocommerce' ) . '</strong> ' . esc_html( $product->get_sku() ) . '</div>';
    }
}

// Action AJAX pour récupérer SEULEMENT le total (pas le compteur)
add_action('wp_ajax_get_cart_total_only', 'get_cart_total_only');
add_action('wp_ajax_nopriv_get_cart_total_only', 'get_cart_total_only');

function get_cart_total_only() {
    if (function_exists('WC')) {
        $cart = WC()->cart;
        $total_ttc = $cart->get_total('edit');
        $total_ht = $cart->get_subtotal(); // Prix HT sans les frais et taxes
        
        wp_send_json_success(array(
            'total' => wc_price($total_ttc), // Pour l'affichage (TTC)
            'total_ht' => $total_ht // Pour le calcul livraison gratuite (HT, valeur numérique)
        ));
    }
    wp_send_json_error();
}

add_action('wp_enqueue_scripts', 'enqueue_ajax_cart_scripts');
function enqueue_ajax_cart_scripts() {
    if (is_cart()) {
        // Debug : Ajouter les logs
    my_theme_debug_log('AJAX Cart: Scripts chargés sur la page panier');
        
        wp_localize_script('jquery', 'ajax_cart_params', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('ajax_cart_nonce'),
            'debug' => true
        ));
    }
}

// Handler AJAX pour la mise à jour du panier
// Désactivé - conflit avec la nouvelle fonction
// add_action('wp_ajax_update_cart_ajax', 'ajax_update_cart_handler');
// add_action('wp_ajax_nopriv_update_cart_ajax', 'ajax_update_cart_handler');

function ajax_update_cart_handler() {
    // Debug
    my_theme_debug_log('AJAX Cart: Handler appelé avec POST: ' . print_r($_POST, true));
    
    // Vérification de sécurité
    if (!isset($_POST['security']) || !wp_verify_nonce($_POST['security'], 'ajax_cart_nonce')) {
    my_theme_debug_log('AJAX Cart: Erreur de sécurité - Nonce invalide');
        wp_send_json_error('Erreur de sécurité');
        return;
    }

    if (!isset($_POST['cart_key']) || !isset($_POST['quantity'])) {
    my_theme_debug_log('AJAX Cart: Données manquantes dans POST');
        wp_send_json_error('Données manquantes');
        return;
    }

    $cart_key = sanitize_text_field($_POST['cart_key']);
    $quantity = intval($_POST['quantity']);
    
    my_theme_debug_log("AJAX Cart: Mise à jour - Cart Key: $cart_key, Quantity: $quantity");
    
    if (empty($cart_key)) {
    my_theme_debug_log('AJAX Cart: Clé du panier vide');
        wp_send_json_error('Clé du panier manquante');
        return;
    }

    // Vérifier que le panier existe
    if (!WC()->cart) {
    my_theme_debug_log('AJAX Cart: Panier WooCommerce non disponible');
        wp_send_json_error('Panier non disponible');
        return;
    }

    try {
        // Mettre à jour la quantité dans le panier
        if ($quantity <= 0) {
            $removed = WC()->cart->remove_cart_item($cart_key);
            my_theme_debug_log("AJAX Cart: Suppression - Résultat: " . ($removed ? 'succès' : 'échec'));
            if (!$removed) {
                wp_send_json_error('Impossible de supprimer l\'article');
                return;
            }
        } else {
            $updated = WC()->cart->set_quantity($cart_key, $quantity, true);
            my_theme_debug_log("AJAX Cart: Mise à jour quantité - Résultat: " . ($updated ? 'succès' : 'échec'));
            if (!$updated) {
                wp_send_json_error('Impossible de mettre à jour la quantité');
                return;
            }
        }

        // Recalculer les totaux
        WC()->cart->calculate_totals();
    my_theme_debug_log('AJAX Cart: Totaux recalculés');

        // Préparer la réponse
        $response_data = array(
            'cart_key' => $cart_key,
            'quantity' => $quantity,
            'cart_contents_count' => WC()->cart->get_cart_contents_count()
        );

        // Ajouter les totaux mis à jour
        $response_data['cart_totals'] = array(
            'subtotal' => wc_price(WC()->cart->subtotal),
            'total' => wc_price(WC()->cart->total),
            'subtotal_raw' => WC()->cart->subtotal,
            'total_raw' => WC()->cart->total
        );

        // Calculer la progression pour la livraison offerte
        $target = 550;
        $subtotal = WC()->cart->subtotal;
        $percent = $subtotal > 0 ? min(100, ($subtotal / $target) * 100) : 0;
        $remaining = $target - $subtotal;

        $response_data['progress'] = $percent;
        if ($remaining > 0) {
            $response_data['shipping_message'] = __('Plus que ', 'woocommerce') . wc_price($remaining) . ' ' . __('pour profiter de la livraison offerte', 'woocommerce');
        } else {
            $response_data['shipping_message'] = __('Livraison offerte !', 'woocommerce');
        }

        // Si on a mis à jour un article, calculer le nouveau sous-total pour cette ligne
        if ($quantity > 0) {
            $cart_item = WC()->cart->get_cart_item($cart_key);
            if ($cart_item) {
                $product = $cart_item['data'];
                $response_data['item_subtotal'] = WC()->cart->get_product_subtotal($product, $quantity);
            }
        }

        // Message de succès
        if ($quantity <= 0) {
            $response_data['message'] = 'Article supprimé du panier';
        } else {
            $response_data['message'] = 'Panier mis à jour';
        }

    my_theme_debug_log('AJAX Cart: Succès - Réponse: ' . print_r($response_data, true));
        wp_send_json_success($response_data);

    } catch (Exception $e) {
    my_theme_debug_log('AJAX Cart: Exception - ' . $e->getMessage());
        wp_send_json_error('Erreur: ' . $e->getMessage());
    }
}

// Optimisation : Désactiver les redirections automatiques du panier
add_filter('woocommerce_cart_redirect_after_error', '__return_false');

// Ajouter un shortcode pour le panier AJAX (optionnel)
add_shortcode('ajax_cart', 'ajax_cart_shortcode');
function ajax_cart_shortcode($atts) {
    if (!WC()->cart) {
        return '<p>Panier non disponible</p>';
    }
    
    ob_start();
    include 'cart.php'; // Votre fichier de template
    return ob_get_clean();
}

// Hook pour nettoyer le cache si nécessaire
add_action('woocommerce_cart_updated', 'clear_cart_cache');
function clear_cart_cache() {
    if (function_exists('wp_cache_flush')) {
        wp_cache_flush();
    }
}

// Fonction utilitaire pour obtenir les fragments du panier
function get_cart_fragments() {
    ob_start();
    ?>
    <span class="cart-count"><?php echo WC()->cart->get_cart_contents_count(); ?></span>
    <?php
    $cart_count = ob_get_clean();

    ob_start();
    ?>
    <span class="cart-total"><?php echo WC()->cart->get_cart_total(); ?></span>
    <?php
    $cart_total = ob_get_clean();

    return array(
        '.cart-count' => $cart_count,
        '.cart-total' => $cart_total
    );
}

// Amélioration : Gestion des erreurs de stock
add_filter('woocommerce_cart_item_quantity', function($quantity, $cart_item_key, $cart_item) {
    $product = $cart_item['data'];
    
    if (!$product->has_enough_stock($quantity)) {
        return $product->get_stock_quantity();
    }
    
    return $quantity;
}, 10, 3);

// Messages personnalisés
add_filter('woocommerce_cart_item_removed_title', function($title, $cart_item) {
    return sprintf(__('%s supprimé du panier', 'woocommerce'), $title);
}, 10, 2);

// Optimisation des performances
add_action('init', function() {
    if (is_cart() && !is_admin()) {
        // Précharger les données du panier
        WC()->cart->get_cart();
    }
});

// AJAX handler pour la mise à jour du panier
add_action('wp_ajax_update_cart_ajax', 'handle_update_cart_ajax');
add_action('wp_ajax_nopriv_update_cart_ajax', 'handle_update_cart_ajax');

function handle_update_cart_ajax() {
    try {
        // Vérification de sécurité
        if (!wp_verify_nonce($_POST['security'], 'ajax_cart_nonce')) {
            wp_send_json_error('Erreur de sécurité');
            return;
        }

        $cart_key = sanitize_text_field($_POST['cart_key']);
        $quantity = intval($_POST['quantity']);

        // Mise à jour de la quantité
        if ($quantity === 0) {
            WC()->cart->remove_cart_item($cart_key);
        } else {
            WC()->cart->set_quantity($cart_key, $quantity);
        }

        // Calculer les totaux
        WC()->cart->calculate_totals();

        // Récupérer les totaux mis à jour avec debug
        $subtotal = WC()->cart->get_subtotal(); // Montant HT
        $subtotal_incl_tax = WC()->cart->get_subtotal() + WC()->cart->get_subtotal_tax(); // HT + TVA sur produits
        $tax_total = WC()->cart->get_taxes_total(); // Total de toutes les taxes
        
    my_theme_debug_log("DEBUG: subtotal HT = " . $subtotal);
    my_theme_debug_log("DEBUG: subtotal + tax = " . $subtotal_incl_tax);
    my_theme_debug_log("DEBUG: tax_total = " . $tax_total);
        
        // Calculer le total TTC avec frais de livraison
        $cart_total_without_shipping = $subtotal + $tax_total;
        $cart_total_with_shipping = $cart_total_without_shipping + 19;
    my_theme_debug_log("DEBUG: cart_total_with_shipping = " . $cart_total_with_shipping);
        
        // Calculer la progression pour la livraison gratuite basée sur le prix HT
        $target = 550;
        $progress = $subtotal > 0 ? min(100, ($subtotal / $target) * 100) : 0;
    my_theme_debug_log("DEBUG: progress based on HT = " . $progress);
        
        // Logique de livraison basée sur le prix HT
        if ($subtotal >= $target) {
            $shipping_message = 'Livraison offerte !';
            $shipping_display = 'Offerte';
            $total_numeric = $cart_total_without_shipping;
            my_theme_debug_log("DEBUG: Free shipping - shipping_display = " . $shipping_display);
        } else {
            $remaining = max(0, $target - $subtotal);
            $shipping_message = sprintf('Plus que %s HT pour profiter de la livraison offerte', wc_price($remaining));
            $shipping_display = wc_price(19);
            $total_numeric = $cart_total_with_shipping;
            my_theme_debug_log("DEBUG: Paid shipping - shipping_display = " . $shipping_display);
        }
        
        // Récupérer le sous-total de l'item
        $item_subtotal = '';
        if ($quantity > 0) {
            $cart_item = WC()->cart->get_cart_item($cart_key);
            if ($cart_item) {
                $item_subtotal = WC()->cart->get_product_subtotal($cart_item['data'], $quantity);
            }
        }

    my_theme_debug_log("DEBUG: Before JSON response");
        
        // Ajout des prix unitaires pour chaque item du panier
        $items = array();
        foreach (WC()->cart->get_cart() as $key => $item) {
            $items[$key] = array(
                'unit_price_html' => WC()->cart->get_product_price($item['data'])
            );
        }

        $response_data = array(
            'cart_totals' => array(
                'subtotal' => wc_price($subtotal), // Montant HT uniquement
                'subtotal_display' => wc_price($subtotal),
                'subtotal_raw' => $subtotal,
                'total' => wc_price($total_numeric),
                'total_raw' => $total_numeric
            ),
            'progress' => $progress,
            'shipping_message' => $shipping_message,
            'item_subtotal' => $item_subtotal,
            'items' => $items,
            'message' => 'Panier mis à jour'
        );
        
        error_log("DEBUG: Basic response ready");
        
        // Ajouter les données de taxe
        $response_data['cart_totals']['tax'] = wc_price($tax_total);
        $response_data['cart_totals']['tax_display'] = wc_price($tax_total);
        $response_data['cart_totals']['tax_raw'] = $tax_total;
        error_log("DEBUG: Tax data added");
        
        // Ajouter les données de livraison
        $response_data['cart_totals']['shipping'] = $shipping_display;
        $response_data['cart_totals']['shipping_display'] = $shipping_display;
        error_log("DEBUG: Shipping data added - value: " . $shipping_display);
        
        // Ajouter debug info
        $response_data['debug_info'] = array(
            'cart_total_with_shipping' => $cart_total_with_shipping,
            'target' => $target,
            'free_shipping' => $cart_total_with_shipping >= $target,
            'shipping_display' => $shipping_display
        );
        error_log("DEBUG: Debug info added");
        
        error_log("DEBUG: Sending JSON response");
        wp_send_json_success($response_data);
        
    } catch (Exception $e) {
        error_log('AJAX Error: ' . $e->getMessage());
        wp_send_json_error('Erreur lors de la mise à jour: ' . $e->getMessage());
    }
}


function enqueue_home_custom_styles() {
    // Charger seulement sur la page d'accueil
    if (is_front_page() || is_page_template('page-home.php')) {
        wp_enqueue_style(
            'home-custom-styles', 
            get_template_directory_uri() . '/css/home-custom.css', 
            array(), 
            filemtime(get_template_directory() . '/css/home-custom.css') // Version basée sur la date de modification
        );
    }
}
add_action('wp_enqueue_scripts', 'enqueue_home_custom_styles');

// Permettre la recherche par UGS (SKU) dans WordPress
function search_by_sku( $search, $wp_query ) {
    if ( ! is_admin() && $wp_query->is_main_query() && $wp_query->is_search() ) {
        global $wpdb;
        $search_term = $wp_query->get( 's' );

        if ( ! empty( $search_term ) ) {
            // Détecter le format "UGS : 010069" ou "SKU: 010069"
            if ( preg_match('/^(UGS|SKU)\s*:?\s*(\w+)/i', $search_term, $matches) ) {
                $sku_value = $matches[2];
            } else {
                $sku_value = $search_term;
            }

            // Rechercher dans les meta_value des UGS
            $sku_posts = $wpdb->get_col( $wpdb->prepare( "
                SELECT DISTINCT post_id 
                FROM {$wpdb->postmeta} 
                WHERE meta_key = '_sku' 
                AND meta_value LIKE %s
            ", '%' . $wpdb->esc_like( $sku_value ) . '%' ) );

            if ( ! empty( $sku_posts ) ) {
                $search_ids = implode( ',', array_map( 'absint', $sku_posts ) );
                $search .= " OR {$wpdb->posts}.ID IN ({$search_ids})";
            }
        }
    }
    return $search;
}
add_filter( 'posts_search', 'search_by_sku', 10, 2 );

// Alternative plus complète pour WooCommerce spécifiquement
function woocommerce_search_by_sku( $query ) {
    if ( ! is_admin() && $query->is_main_query() && $query->is_search() ) {
        $search_term = $query->get( 's' );
        
        if ( ! empty( $search_term ) ) {
            global $wpdb;
            
            // Chercher les produits par UGS
            $product_ids = $wpdb->get_col( $wpdb->prepare( "
                SELECT DISTINCT post_id 
                FROM {$wpdb->postmeta} 
                WHERE meta_key IN ('_sku') 
                AND meta_value LIKE %s
            ", '%' . $wpdb->esc_like( $search_term ) . '%' ) );
            
            if ( ! empty( $product_ids ) ) {
                // Récupérer les IDs existants dans la requête
                $post__in = $query->get( 'post__in' );
                if ( empty( $post__in ) ) {
                    $post__in = array();
                }
                
                // Fusionner avec les IDs trouvés par UGS
                $post__in = array_merge( $post__in, $product_ids );
                $post__in = array_unique( $post__in );
                
                $query->set( 'post__in', $post__in );
            }
        }
    }
}
add_action( 'pre_get_posts', 'woocommerce_search_by_sku' );

/**
 * Charge un CSS personnalisé pour la page produit WooCommerce.
 */
function montheme_enqueue_single_product_style() {
    if ( is_product() ) {
        wp_enqueue_style(
            'custom-single-product-style',
            get_stylesheet_directory_uri() . '/css/single-product-style.css',
            array(),
            '1.0.0'
        );
    }
}
add_action( 'wp_enqueue_scripts', 'montheme_enqueue_single_product_style' );

add_filter( 'woocommerce_enqueue_styles', 'child_dequeue_woocommerce_general' );
function child_dequeue_woocommerce_general( $enqueue_styles ) {
    unset( $enqueue_styles['woocommerce-general'] );
    return $enqueue_styles;
}

/**
 * 2) Enfile votre CSS perso sur la page produit
 * 3) Ajoute une surcharge inline pour le prix
 */
function child_enqueue_single_product_style() {
    if ( is_product() ) {
        wp_enqueue_style(
            'custom-single-product-style',
            get_stylesheet_directory_uri() . '/css/single-product-style.css',
            array(),
            '1.0.0'
        );
        $override_css = "
            .woocommerce:where(body:not(.woocommerce-uses-block-theme)) div.product p.price,
            .woocommerce:where(body:not(.woocommerce-uses-block-theme)) div.product span.price {
                color: #0E2141 !important;
            }
        ";
        wp_add_inline_style( 'custom-single-product-style', $override_css );
    }
}
add_action( 'wp_enqueue_scripts', 'child_enqueue_single_product_style', 20 );

add_action( 'after_setup_theme', 'childtheme_woocommerce_gallery_support' );
function childtheme_woocommerce_gallery_support() {
    add_theme_support( 'wc-product-gallery-zoom' );
    add_theme_support( 'wc-product-gallery-lightbox' );
    add_theme_support( 'wc-product-gallery-slider' );
}

// ===============================================
// SYSTÈME DE WISHLIST UTILISATEUR
// ===============================================

// Fonction pour ajouter un produit aux favoris
function add_to_wishlist() {
    // Vérifier le nonce pour la sécurité
    if (!wp_verify_nonce($_POST['nonce'], 'wishlist_nonce')) {
        wp_send_json_error(['message' => 'Erreur de sécurité']);
        return;
    }
    
    if (!is_user_logged_in()) {
        wp_send_json_error(['message' => 'Vous devez être connecté pour ajouter aux favoris']);
        return;
    }
    
    $product_id = intval($_POST['product_id']);
    $user_id = get_current_user_id();
    
    if (!$product_id) {
        wp_send_json_error(['message' => 'ID produit invalide']);
        return;
    }
    
    // Récupérer la wishlist actuelle de l'utilisateur
    $wishlist = get_user_meta($user_id, 'user_wishlist', true);
    if (!is_array($wishlist)) {
        $wishlist = array();
    }
    
    // Ajouter le produit s'il n'est pas déjà présent
    if (!in_array($product_id, $wishlist)) {
        $wishlist[] = $product_id;
        update_user_meta($user_id, 'user_wishlist', $wishlist);
        wp_send_json_success(['action' => 'added', 'message' => 'Produit ajouté aux favoris']);
    } else {
        wp_send_json_error(['message' => 'Produit déjà dans les favoris']);
    }
}
add_action('wp_ajax_add_to_wishlist', 'add_to_wishlist');
add_action('wp_ajax_nopriv_add_to_wishlist', 'add_to_wishlist');

// Fonction pour retirer un produit des favoris
function remove_from_wishlist() {
    // Vérifier le nonce pour la sécurité
    if (!wp_verify_nonce($_POST['nonce'], 'wishlist_nonce')) {
        wp_send_json_error(['message' => 'Erreur de sécurité']);
        return;
    }
    
    if (!is_user_logged_in()) {
        wp_send_json_error(['message' => 'Vous devez être connecté']);
        return;
    }
    
    $product_id = intval($_POST['product_id']);
    $user_id = get_current_user_id();
    
    if (!$product_id) {
        wp_send_json_error(['message' => 'ID produit invalide']);
        return;
    }
    
    // Récupérer la wishlist actuelle de l'utilisateur
    $wishlist = get_user_meta($user_id, 'user_wishlist', true);
    if (!is_array($wishlist)) {
        $wishlist = array();
    }
    
    // Retirer le produit
    $key = array_search($product_id, $wishlist);
    if ($key !== false) {
        unset($wishlist[$key]);
        $wishlist = array_values($wishlist); // Réindexer le tableau
        update_user_meta($user_id, 'user_wishlist', $wishlist);
        wp_send_json_success(['action' => 'removed', 'message' => 'Produit retiré des favoris']);
    } else {
        wp_send_json_error(['message' => 'Produit non trouvé dans les favoris']);
    }
}
add_action('wp_ajax_remove_from_wishlist', 'remove_from_wishlist');
add_action('wp_ajax_nopriv_remove_from_wishlist', 'remove_from_wishlist');

// Fonction pour vérifier si un produit est dans les favoris
function check_wishlist_status() {
    // Vérifier le nonce pour la sécurité
    if (!wp_verify_nonce($_POST['nonce'], 'wishlist_nonce')) {
        wp_send_json_error(['message' => 'Erreur de sécurité']);
        return;
    }
    
    if (!is_user_logged_in()) {
        wp_send_json_success(['in_wishlist' => false]);
        return;
    }
    
    $product_id = intval($_POST['product_id']);
    $user_id = get_current_user_id();
    
    $wishlist = get_user_meta($user_id, 'user_wishlist', true);
    if (!is_array($wishlist)) {
        $wishlist = array();
    }
    
    $in_wishlist = in_array($product_id, $wishlist);
    
    wp_send_json_success(['in_wishlist' => $in_wishlist]);
}
add_action('wp_ajax_check_wishlist_status', 'check_wishlist_status');
add_action('wp_ajax_nopriv_check_wishlist_status', 'check_wishlist_status');

// Fonction helper pour vérifier si un produit est en favoris (utilisation dans les templates)
function is_product_in_wishlist($product_id) {
    if (!is_user_logged_in()) {
        return false;
    }
    
    $user_id = get_current_user_id();
    $wishlist = get_user_meta($user_id, 'user_wishlist', true);
    
    if (!is_array($wishlist)) {
        return false;
    }
    
    return in_array($product_id, $wishlist);
}

// Ajouter les scripts et variables JavaScript nécessaires
function enqueue_wishlist_scripts() {
    wp_localize_script('variation-color-swatches', 'wishlist_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('wishlist_nonce'),
        'is_logged_in' => is_user_logged_in(),
        'product_id' => is_singular('product') ? get_the_ID() : null
    ));
}
add_action('wp_enqueue_scripts', 'enqueue_wishlist_scripts');

// Ajouter un shortcode pour afficher la page de wishlist
function display_user_wishlist() {
    if (!is_user_logged_in()) {
        return '<p>Vous devez être connecté pour voir vos favoris.</p>';
    }
    
    $user_id = get_current_user_id();
    $wishlist = get_user_meta($user_id, 'user_wishlist', true);
    
    if (!is_array($wishlist) || empty($wishlist)) {
        return '<p>Votre liste de favoris est vide.</p>';
    }
    
    $output = '<div class="user-wishlist"><h3>Mes favoris</h3><div class="wishlist-products">';
    
    foreach ($wishlist as $product_id) {
        $product = wc_get_product($product_id);
        if ($product) {
            $output .= '<div class="wishlist-item">';
            $output .= '<a href="' . get_permalink($product_id) . '">' . $product->get_image('thumbnail') . '</a>';
            $output .= '<div class="product-info">';
            $output .= '<h4><a href="' . get_permalink($product_id) . '">' . $product->get_name() . '</a></h4>';
            $output .= '<span class="price">' . $product->get_price_html() . '</span>';
            $output .= '<button class="remove-from-wishlist" data-product-id="' . $product_id . '">Retirer</button>';
            $output .= '</div></div>';
        }
    }
    
    $output .= '</div></div>';
    
    return $output;
}
add_shortcode('user_wishlist', 'display_user_wishlist');

// Fonction AJAX pour la connexion dans la modal
function ajax_login() {
    // Vérifier le nonce pour la sécurité
    if (!wp_verify_nonce($_POST['security'], 'wishlist_nonce')) {
        wp_send_json_error(['message' => 'Erreur de sécurité']);
        return;
    }
    
    $username = sanitize_text_field($_POST['username']);
    $password = $_POST['password'];
    
    if (empty($username) || empty($password)) {
        wp_send_json_error(['message' => 'Veuillez remplir tous les champs.']);
        return;
    }
    
    // Tenter la connexion
    $credentials = array(
        'user_login'    => $username,
        'user_password' => $password,
        'remember'      => true
    );
    
    $user = wp_signon($credentials, false);
    
    if (is_wp_error($user)) {
        // Connexion échouée
        $error_message = 'Identifiants incorrects.';
        
        // Messages d'erreur plus spécifiques selon le type d'erreur
        if ($user->get_error_code() === 'invalid_username') {
            $error_message = 'Nom d\'utilisateur ou email invalide.';
        } elseif ($user->get_error_code() === 'incorrect_password') {
            $error_message = 'Mot de passe incorrect.';
        } elseif ($user->get_error_code() === 'empty_username') {
            $error_message = 'Veuillez saisir votre nom d\'utilisateur.';
        } elseif ($user->get_error_code() === 'empty_password') {
            $error_message = 'Veuillez saisir votre mot de passe.';
        }
        
        wp_send_json_error(['message' => $error_message]);
    } else {
        // Connexion réussie
        wp_set_current_user($user->ID);
        wp_set_auth_cookie($user->ID, true);
        
        wp_send_json_success([
            'message' => 'Connexion réussie !',
            'user_id' => $user->ID,
            'user_name' => $user->display_name
        ]);
    }
}
add_action('wp_ajax_nopriv_ajax_login', 'ajax_login'); // Pour les utilisateurs non connectés
add_action('wp_ajax_ajax_login', 'ajax_login'); // Pour les utilisateurs connectés (au cas où)

// Charger les menus d'analyse clients (ancien système temporairement)
require_once get_stylesheet_directory() . '/includes/admin-comportement.php';

// Charger le nouveau système de comportement client v2.0 en parallèle
// NOTE: Ne pas activer automatiquement en production. Pour activer la V2,
// vous pouvez définir une constante dans wp-config.php:
//   define('COMPORTEMENT_V2_ENABLED', true);
// Ou activer depuis la base via l'option 'comportement_v2_enabled' (boolean).
$comportement_v2_file = get_stylesheet_directory() . '/includes/comportement-v2.php';
if (file_exists($comportement_v2_file)) {
    $v2_enabled = false;

    // 1) Constante explicite (préférée pour déploiements)
    if (defined('COMPORTEMENT_V2_ENABLED') && COMPORTEMENT_V2_ENABLED) {
        $v2_enabled = true;
    }

    // 2) Environnement de développement (optionnel) - permissif si WP_ENV=development
    if (!$v2_enabled && defined('WP_ENV') && WP_ENV === 'development') {
        $v2_enabled = true;
    }

    // 3) Option dans la base de données (peut être activée depuis un écran admin custom)
    if (!$v2_enabled && get_option('comportement_v2_enabled', false)) {
        $v2_enabled = true;
    }

    // Force activation of comportement v2 system
    if (!$v2_enabled) {
        update_option('comportement_v2_enabled', true);
        $v2_enabled = true;
    }

    if ($v2_enabled) {
        require_once $comportement_v2_file;
    } else {
        // Pour faciliter le debug local lorsque WP_DEBUG est activé et l'utilisateur est admin,
        // afficher un petit commentaire HTML dans le footer pour indiquer que la V2 est désactivée.
        if (defined('WP_DEBUG') && WP_DEBUG && current_user_can('administrator')) {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-info"><p>'; 
                echo 'Comportement v2 est présent mais non activé. Pour l\'activer: define("COMPORTEMENT_V2_ENABLED", true) dans <code>wp-config.php</code> ou set_option("comportement_v2_enabled", true).';
                echo '</p></div>';
            });
        }
    }
}
// Système de recherche intelligent type Amazon (désactivé en production par défaut)
if (is_dev_features_enabled()) {
    add_filter('posts_search', 'amazon_style_search', 20, 2);
    add_filter('posts_orderby', 'amazon_style_search_orderby', 20, 2);
} else {
    // En production, on évite d'enregistrer ces filtres coûteux
    my_theme_debug_log('Amazon-style search disabled (dev features not enabled)');
}

function amazon_style_search($search, $wp_query) {
    if (is_admin() || !$wp_query->is_main_query() || !$wp_query->is_search()) return $search;

    global $wpdb;
    $search_term = trim($wp_query->get('s'));
    if (empty($search_term) || strlen($search_term) < 2) return $search;

    // Nettoyer et normaliser le terme de recherche
    $search_term = amazon_normalize_search_term($search_term);
    $search_results = amazon_multi_level_search($search_term);
    
    if (!empty($search_results)) {
        // Stocker les IDs et scores pour l'ordre
        $wp_query->set('amazon_search_results', $search_results);
        
        $ids = array_keys($search_results);
        $search = " AND {$wpdb->posts}.ID IN (" . implode(',', array_map('absint', $ids)) . ")";
    }
    
    return $search;
}

function amazon_normalize_search_term($term) {
    // Normalisation des termes comme Amazon
    $term = mb_strtolower($term, 'UTF-8');
    
    // Appliquer les synonymes et corrections communes
    $term = amazon_apply_synonyms($term);
    
    $term = preg_replace('/[^\w\sàáâãäåæçèéêëìíîïðñòóôõöøùúûüýþÿ]/u', ' ', $term);
    $term = preg_replace('/\s+/', ' ', $term);
    return trim($term);
}

function amazon_apply_synonyms($term) {
    // Dictionnaire de synonymes et corrections pour électroménager/plomberie
    $synonyms = [
        // Corrections orthographiques communes
        'robine' => 'robinet',
        'robinnett' => 'robinet',
        'electrovane' => 'électrovanne',
        'electro-vane' => 'électrovanne',
        'electro vane' => 'électrovanne',
        'electrovannes' => 'électrovanne',
        'vannes' => 'vanne',
        'vanes' => 'vanne',
        'tuyau' => 'tuyau tube',
        'tube' => 'tuyau tube',
        'raccord' => 'raccord connecteur',
        'connecteur' => 'raccord connecteur',
        'embout' => 'embout buse',
        'buse' => 'embout buse',
        'lance' => 'pistolet lance',
        'pistolet' => 'pistolet lance',
        'nettoyeur' => 'nettoyeur haute pression',
        'karcher' => 'nettoyeur haute pression kärcher',
        'karcher' => 'nettoyeur haute pression kärcher',
        'mousse' => 'canon mousse',
        'savon' => 'mousse savon détergent',
        'detergent' => 'mousse savon détergent',
        'roue' => 'roulette roue',
        'roulette' => 'roulette roue',
        'protection' => 'protection sécurité',
        'securite' => 'protection sécurité',
        'sécurité' => 'protection sécurité',
        'tecomec' => 'tecomec protection',
        'intégré' => 'intégré intégrale',
        'integre' => 'intégré intégrale',
        'integrale' => 'intégré intégrale',
        'intégrale' => 'intégré intégrale'
    ];
    
    // Appliquer les synonymes
    foreach ($synonyms as $search => $replace) {
        if (strpos($term, $search) !== false) {
            $term = str_replace($search, $replace, $term);
        }
    }
    
    return $term;
}

function amazon_multi_level_search($search_term) {
    // Sécurité: éviter l'exécution de cette recherche lourde en production
    if (!is_dev_features_enabled()) {
        return [];
    }
    // Cache des résultats pour 5 minutes
    $cache_key = 'amazon_search_' . md5($search_term);
    $cached_results = get_transient($cache_key);
    
    if ($cached_results !== false) {
        return $cached_results;
    }
    
    $results = [];
    $search_words = preg_split('/\s+/', $search_term);
    $search_words = array_filter($search_words, function($w) { return strlen($w) >= 2; });
    
    if (empty($search_words)) return $results;
    
    // Récupérer tous les produits avec métadonnées optimisé
    $products = get_posts([
        'post_type' => 'product',
        'posts_per_page' => 500,
        'post_status' => 'publish',
        'fields' => 'ids',
        'suppress_filters' => true,
        'orderby' => 'date',
        'order' => 'DESC', // Privilégier les produits récents d'abord
        'meta_query' => [
            [
                'key' => '_stock_status',
                'value' => 'instock',
                'compare' => '='
            ]
        ]
    ]);
    
    // Ajouter aussi quelques produits en rupture mais populaires
    $out_of_stock = get_posts([
        'post_type' => 'product',
        'posts_per_page' => 100,
        'post_status' => 'publish',
        'fields' => 'ids',
        'suppress_filters' => true,
        'meta_query' => [
            [
                'key' => '_featured',
                'value' => 'yes',
                'compare' => '='
            ]
        ]
    ]);
    
    $products = array_merge($products, $out_of_stock);
    $products = array_unique($products);
    
    foreach ($products as $product_id) {
        $score = amazon_calculate_product_score($product_id, $search_term, $search_words);
        
        // Seuil de pertinence adaptatif selon la longueur du terme
        $min_score = strlen($search_term) <= 3 ? 50 : 30;
        
        if ($score >= $min_score) {
            $results[$product_id] = $score;
        }
    }
    
    // Trier par score décroissant
    arsort($results);
    
    // Limiter les résultats aux plus pertinents
    $final_results = array_slice($results, 0, 50, true);
    
    // Mettre en cache pendant 5 minutes
    set_transient($cache_key, $final_results, 300);
    
    return $final_results;
}

function amazon_calculate_product_score($product_id, $search_term, $search_words) {
    $score = 0;
    $product = wc_get_product($product_id);
    if (!$product) return 0;
    
    // Données du produit
    $title = mb_strtolower($product->get_name(), 'UTF-8');
    $description = mb_strtolower($product->get_short_description(), 'UTF-8');
    $sku = mb_strtolower($product->get_sku(), 'UTF-8');
    $categories = amazon_get_product_categories($product_id);
    
    // 1. CORRESPONDANCE EXACTE (Score max: 100)
    if (strpos($title, $search_term) !== false) {
        $score += 100;
        // Bonus si en début de titre
        if (strpos($title, $search_term) === 0) $score += 50;
    }
    
    // 2. CORRESPONDANCE SKU (Score: 90)
    if (!empty($sku) && strpos($sku, str_replace(' ', '', $search_term)) !== false) {
        $score += 90;
    }
    
    // 3. CORRESPONDANCE MOTS COMPLETS (Score: 20-60 par mot)
    $word_matches = 0;
    $total_words = count($search_words);
    
    foreach ($search_words as $word) {
        if (strlen($word) < 3) continue;
        
        $word_score = 0;
        
        // Mot complet dans le titre
        if (preg_match('/\b' . preg_quote($word, '/') . '\b/', $title)) {
            $word_score += 60;
        }
        // Mot complet dans la description
        elseif (preg_match('/\b' . preg_quote($word, '/') . '\b/', $description)) {
            $word_score += 30;
        }
        // Mot dans les catégories
        elseif (preg_match('/\b' . preg_quote($word, '/') . '\b/', $categories)) {
            $word_score += 20;
        }
        // Correspondance partielle stricte
        elseif (amazon_partial_match($word, $title)) {
            $word_score += 25;
        }
        
        if ($word_score > 0) {
            $word_matches++;
            $score += $word_score;
        }
    }
    
    // 4. BONUS COVERAGE: % de mots trouvés
    $coverage = $word_matches / $total_words;
    if ($coverage >= 0.8) $score += 40;      // 80%+ des mots
    elseif ($coverage >= 0.6) $score += 25;  // 60%+ des mots
    elseif ($coverage >= 0.4) $score += 10;  // 40%+ des mots
    
    // 5. CORRESPONDANCE FUZZY INTELLIGENTE (Score: 5-15)
    if ($score < 50) { // Seulement si pas déjà très pertinent
        $fuzzy_score = amazon_fuzzy_match_score($search_words, $title);
        $score += $fuzzy_score;
    }
    
    // 6. BONUS POPULARITÉ (Stock, ventes, etc.)
    if ($product->is_in_stock()) $score += 5;
    if ($product->is_featured()) $score += 10;
    
    return min(200, $score); // Plafonner le score
}

function amazon_partial_match($word, $text) {
    if (strlen($word) < 4) return false;
    
    // Cherche le mot comme sous-chaîne avec tolérance
    $pattern = '';
    for ($i = 0; $i < strlen($word); $i++) {
        $pattern .= preg_quote($word[$i], '/');
        if ($i < strlen($word) - 1) $pattern .= '.{0,1}'; // Max 1 caractère entre
    }
    
    return preg_match('/' . $pattern . '/u', $text);
}

function amazon_fuzzy_match_score($search_words, $title) {
    $score = 0;
    $title_words = preg_split('/\s+/', $title);
    
    foreach ($search_words as $search_word) {
        if (strlen($search_word) < 4) continue;
        
        foreach ($title_words as $title_word) {
            if (strlen($title_word) < 3) continue;
            
            $distance = levenshtein($search_word, $title_word);
            $max_distance = amazon_calculate_max_distance($search_word);
            
            if ($distance <= $max_distance) {
                // Score inversement proportionnel à la distance
                $fuzzy_score = max(0, 15 - ($distance * 5));
                $score += $fuzzy_score;
                break; // Une correspondance par mot de recherche
            }
        }
    }
    
    return min(30, $score); // Maximum 30 points pour fuzzy
}

function amazon_calculate_max_distance($word) {
    $length = strlen($word);
    if ($length <= 4) return 1;
    if ($length <= 6) return 2;
    return 2; // Maximum 2 fautes même pour mots longs
}

function amazon_get_product_categories($product_id) {
    $terms = get_the_terms($product_id, 'product_cat');
    if (!$terms || is_wp_error($terms)) return '';
    
    $categories = array_map(function($term) {
        return mb_strtolower($term->name, 'UTF-8');
    }, $terms);
    
    return implode(' ', $categories);
}

function amazon_style_search_orderby($orderby, $wp_query) {
    if (is_admin() || !$wp_query->is_main_query() || !$wp_query->is_search()) return $orderby;
    
    $results = $wp_query->get('amazon_search_results');
    if (!empty($results)) {
        global $wpdb;
        $ids_order = array();
        $position = 0;
        
        foreach ($results as $id => $score) {
            $ids_order[] = "WHEN {$id} THEN {$position}";
            $position++;
        }
        
        if (!empty($ids_order)) {
            $orderby = "CASE {$wpdb->posts}.ID " . implode(' ', $ids_order) . " END ASC";
        }
    }
    
    return $orderby;
}

// Redirection automatique vers la fiche produit si la recherche est un SKU/UGS
add_action('template_redirect', function() {
    if (!is_search() || is_admin()) return;
    $search_term = get_query_var('s');
    if (empty($search_term)) return;

    // Extraire le SKU/UGS si préfixe
    if (preg_match('/^(UGS|SKU)\s*:?-?\s*(\w+)/i', $search_term, $matches)) {
        $sku_value = $matches[2];
    } else {
        $sku_value = $search_term;
    }

    // Rechercher le produit par SKU
    global $wpdb;
    $product_id = $wpdb->get_var($wpdb->prepare(
        "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_sku' AND meta_value = %s LIMIT 1",
        $sku_value
    ));
    if ($product_id) {
        $url = get_permalink($product_id);
        if ($url) {
            wp_redirect($url);
            exit;
        }
    }
});


add_action('wp_head', function() {
    if (!is_product()) return;

    global $product;
    if (!$product || !is_a($product, 'WC_Product')) {
        $product = wc_get_product(get_the_ID());
        if (!$product) return;
    }

    $data = [
        "@context" => "https://schema.org/",
        "@type" => "Product",
        "name" => get_the_title(),
        "image" => wp_get_attachment_url($product->get_image_id()),
        "description" => wp_strip_all_tags($product->get_short_description() ?: get_the_excerpt() ?: get_the_content()),
        "sku" => $product->get_sku(),
        "brand" => [
            "@type" => "Brand",
            "name" => get_post_meta($product->get_id(), 'brand', true) ?: get_bloginfo('name')
        ],
        "offers" => [
            "@type" => "Offer",
            "url" => get_permalink(),
            "priceCurrency" => get_woocommerce_currency(),
            "price" => $product->get_price(),
            "availability" => $product->is_in_stock() ? "https://schema.org/InStock" : "https://schema.org/OutOfStock",
            "itemCondition" => "https://schema.org/NewCondition",
            "priceValidUntil" => date('Y-m-d', strtotime('+6 months'))
        ]
    ];

    echo "\n<script type='application/ld+json'>" . wp_json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . "</script>\n";
}, 5);

/**
 * =====================================================
 * OPTIMISATION PERFORMANCE SCRIPTS - PHASE 3
 * =====================================================
 * Améliore le temps de chargement avec scripts différés/asynchrones
 * First Paint rapide et rendu HTML prioritaire
 */

// Inclure le système d'optimisation des scripts
require_once get_template_directory() . '/script-optimization.php';

// Personnalisations spécifiques au thème Aleaulavage
add_action('init', function() {
    // Scripts du thème à traiter en defer
    force_script_defer('lucide-icons');
    force_script_defer('variation-color-swatches');
    force_script_defer('single-product-zoom');
    
    // Ajouter des scripts critiques spécifiques
    add_filter('script_optimizer_critical_scripts', function($scripts) {
        $scripts[] = 'custom-header';
        $scripts[] = 'homepage-cart';
        return $scripts;
    });
});

// Debug pour développement (supprimer en production)
if (WP_DEBUG && current_user_can('administrator')) {
    add_action('wp_footer', function() {
        if (isset($_GET['debug_perf'])) {
            echo '<div style="position:fixed;top:0;right:0;background:#000;color:#fff;padding:10px;z-index:999999;font-size:12px;">';
            echo 'Scripts optimisés: ✅<br>';
            echo 'First Paint: ' . (microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']) . 's';
            echo '</div>';
        }
    });
}

/**
 * =====================================================
 * SYSTÈME DE BULLES PROMO - ADVANCED DYNAMIC PRICING
 * =====================================================
 */

// Hook pour les blocks WooCommerce (page d'accueil) - DESACTIVE
// add_filter('render_block', 'add_promo_bubble_to_blocks', 999, 2);

// Approche JavaScript pour ajouter les bulles
add_action('wp_footer', 'add_promo_bubbles_javascript');

function add_promo_bubbles_javascript() {
    if (!is_front_page() && !is_shop() && !is_product_category() && !is_product_tag()) {
        return;
    }
    
    // Récupérer les données de promotion pour tous les produits visibles
    $product_promo_data = [];
    
    // Chercher tous les produits sur la page
    global $wpdb;
    $featured_products = $wpdb->get_results("
        SELECT p.ID 
        FROM {$wpdb->posts} p 
        INNER JOIN {$wpdb->term_relationships} tr ON p.ID = tr.object_id 
        INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id 
        INNER JOIN {$wpdb->terms} t ON tt.term_id = t.term_id 
        WHERE p.post_type = 'product' 
        AND p.post_status = 'publish' 
        AND t.slug = 'featured'
        LIMIT 10
    ");
    
    foreach ($featured_products as $product_row) {
        $product = wc_get_product($product_row->ID);
        if ($product) {
            $promo_data = calculate_product_promotion($product);
            if ($promo_data['has_promo']) {
                $product_promo_data[$product_row->ID] = [
                    'has_promo' => $promo_data['has_promo'],
                    'discount_percent' => $promo_data['discount_percent'],
                    'is_quantity_based' => $promo_data['is_quantity_based'],
                    'regular_price' => floatval($product->get_regular_price()),
                    'lowest_price' => $promo_data['lowest_price'],
                    'type' => $promo_data['type']
                ];
            }
        }
    }
    
    if (empty($product_promo_data)) {
        return;
    }
    ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        //console.log('🚀 PROMO SCRIPT LOADED ON:', window.location.pathname);
        
        const promoData = <?php echo json_encode($product_promo_data); ?>;
        //console.log('📊 Promo data:', promoData);
        //console.log('📊 Number of products with promos:', Object.keys(promoData).length);
        
        
        // Chercher tous les produits sur la page (blocs ET cards classiques)
        const blockProducts = document.querySelectorAll('[data-wp-context*="productId"]');
        const classicProducts = document.querySelectorAll('.product.type-product');
        const allProducts = [...blockProducts, ...classicProducts];
        //console.log('Found products:', {blocks: blockProducts.length, classic: classicProducts.length, total: allProducts.length});
        
        allProducts.forEach(function(item) {
            let productId = null;
            
            // Extraire l'ID du produit selon le type
            if (item.hasAttribute('data-wp-context')) {
                // Produit bloc (homepage)
                const contextData = item.getAttribute('data-wp-context');
                const match = contextData.match(/"productId":(\d+)/);
                if (match) productId = match[1];
            } else if (item.classList.contains('post-')) {
                // Produit classique (catégories) - extraire de la classe post-XXXX
                const classes = item.className.split(' ');
                for (let cls of classes) {
                    if (cls.startsWith('post-')) {
                        productId = cls.replace('post-', '');
                        break;
                    }
                }
            }
            
            if (productId && promoData[productId]) {
                //console.log('Promo data for product', productId, ':', promoData[productId]);
                const data = promoData[productId];
                const bubbleText = data.is_quantity_based ? 
                    'jusqu\'à -' + data.discount_percent + '%' : 
                    '-' + data.discount_percent + '%';
                
                // Créer la bulle de pourcentage
                const bubble = document.createElement('span');
                bubble.className = data.is_quantity_based ? 'promo-bubble promo-bubble-quantity' : 'promo-bubble';
                bubble.style.cssText = 'position: absolute; top: 15px; left: 15px; background-color: #5899E2; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; z-index: 10; box-shadow: 0 3px 6px rgba(0,0,0,0.3); line-height: 1.1; text-align: center;';
                
                if (data.is_quantity_based) {
                    bubble.innerHTML = bubbleText.replace('-', '-<br>');
                    bubble.style.cssText += 'width: 70px; height: 70px; font-size: 11.39px;';
                } else {
                    bubble.textContent = bubbleText;
                    bubble.style.cssText += 'width: 50px; height: 50px; font-size: 12px;';
                }
                
                // Chercher le conteneur d'image selon le type
                let imageContainer = item.querySelector('.wc-block-components-product-image') || 
                                   item.querySelector('a[href*="/produit/"]');
                
                if (imageContainer) {
                    imageContainer.style.position = 'relative';
                    imageContainer.appendChild(bubble);
                    //console.log('Bubble added for product ' + productId);
                    
                    // Ajouter le badge "Promo" s'il n'existe pas déjà
                    let existingBadge = item.querySelector('.wc-block-components-product-sale-badge');
                    if (!existingBadge) {
                        const promoBadge = document.createElement('div');
                        promoBadge.className = 'wc-block-components-product-sale-badge alignright wc-block-components-product-sale-badge--align-right';
                        promoBadge.style.cssText = 'position: absolute; top: 10px; right: 10px; z-index: 10; background: #fff; padding: 4px 8px; border-radius: 3px; font-size: 11px; font-weight: bold;';
                        promoBadge.innerHTML = '<span class="wc-block-components-product-sale-badge__text" aria-hidden="true">Promo</span><span class="screen-reader-text">Produit en promotion</span>';
                        imageContainer.appendChild(promoBadge);
                        //console.log('Promo badge added for product ' + productId);
                    }
                }
                
                // Modifier l'affichage du prix
                let priceContainer = item.querySelector('.wc-block-components-product-price') || 
                                   item.querySelector('.price');
                
                if (priceContainer && data.has_promo) {
                    //console.log('Updating price for product', productId);
                    
                    const regularPriceFormatted = data.regular_price.toFixed(2).replace('.', ',') + '\u00A0€';
                    const lowestPriceFormatted = data.lowest_price.toFixed(2).replace('.', ',') + '\u00A0€';
                    
                    let newPriceHtml;
                    if (data.is_quantity_based) {
                        newPriceHtml = '<del style="color:#999;text-decoration:line-through;">' + regularPriceFormatted + '</del> ' +
                                      '<span style="color:#5899E2;font-weight:bold;">À partir de ' + lowestPriceFormatted + '</span>';
                    } else {
                        newPriceHtml = '<del style="color:#999;text-decoration:line-through;">' + regularPriceFormatted + '</del> ' +
                                      '<span style="color:#5899E2;font-weight:bold;">' + lowestPriceFormatted + '</span>';
                    }
                    
                    // Appliquer le nouveau HTML selon la structure
                    if (priceContainer.classList.contains('wc-block-components-product-price')) {
                        // Structure bloc (homepage)
                        priceContainer.innerHTML = '<div class="wc-block-components-product-price wc-block-grid__product-price">' +
                                                  '<span class="woocommerce-Price-amount amount promo-price-vertical">' + 
                                                  newPriceHtml + '</span></div>';
                    } else {
                        // Structure classique (catégories)
                        priceContainer.innerHTML = newPriceHtml;
                    }
                    
                    //console.log('Price updated for product', productId);
                }
            }
        });
    });
    </script>
    <?php
}


// Modifier l'affichage des prix sur les pages produit
add_filter('woocommerce_get_price_html', 'modify_single_product_price_display', 10, 2);

function modify_single_product_price_display($price_html, $product) {
    // Sur les pages produit individuelles ET les pages d'archives (catégories, boutique)
    if (!is_product() && !is_shop() && !is_product_category() && !is_product_tag()) {
        return $price_html;
    }
    
    $promo_data = calculate_product_promotion($product);
    
    // Si il y a une promotion, modifier l'affichage
    if ($promo_data['has_promo']) {
        $regular_price = floatval($product->get_regular_price());
        $lowest_price = $promo_data['lowest_price'];
        
        // Format simple pour les prix
        $regular_price_simple = number_format($regular_price, 2, ',', '') . '&nbsp;€';
        $lowest_price_simple = number_format($lowest_price, 2, ',', '') . '&nbsp;€';
        
        // Pour les promotions quantity-based, afficher "À partir de..."
        if ($promo_data['is_quantity_based']) {
            $price_html = '<span class="price">' .
                         '<del style="color:#999;text-decoration:line-through;">' . $regular_price_simple . '</del> ' .
                         '<span style="color:#5899E2;font-weight:bold;">À partir de ' . $lowest_price_simple . '</span>' .
                         '</span>';
        } else {
            // Pour les promotions fixes : format standard avec prix barré
            $price_html = '<span class="price">' .
                         '<del style="color:#999;text-decoration:line-through;">' . $regular_price_simple . '</del> ' .
                         '<span style="color:#5899E2;font-weight:bold;">' . $lowest_price_simple . '</span>' .
                         '</span>';
        }
    }
    
    // Pour les promotions WooCommerce standard aussi
    elseif ($product->is_on_sale() && $product->get_sale_price()) {
        $regular_price = floatval($product->get_regular_price());
        $sale_price = floatval($product->get_sale_price());
        
        if ($sale_price < $regular_price) {
            $regular_price_simple = number_format($regular_price, 2, ',', '') . '&nbsp;€';
            $sale_price_simple = number_format($sale_price, 2, ',', '') . '&nbsp;€';
            
            $price_html = '<span class="price">' .
                         '<del style="color:#999;text-decoration:line-through;">' . $regular_price_simple . '</del> ' .
                         '<span style="color:#5899E2;font-weight:bold;">' . $sale_price_simple . '</span>' .
                         '</span>';
        }
    }
    
    return $price_html;
}

// JavaScript pour les pages produit individuelles
add_action('wp_footer', 'add_promo_bubble_single_product_js');

// Ajouter le CSS et JS pour la gestion du stock
add_action('wp_footer', 'add_stock_management_styles_and_scripts');

function add_stock_management_styles_and_scripts() {
    if (!is_product()) {
        return;
    }
    ?>
    <style>
    /* Styles pour les boutons en rupture de stock uniquement */
    .single_add_to_cart_button.disabled-out-of-stock,
    .out-of-stock-disabled {
        opacity: 0.5 !important;
        cursor: not-allowed !important;
        pointer-events: none !important;
        filter: grayscale(1) !important;
    }
    
    .purchase-qty.disabled-out-of-stock input,
    .purchase-qty.disabled-out-of-stock button {
        opacity: 0.5 !important;
        cursor: not-allowed !important;
        pointer-events: none !important;
        filter: grayscale(1) !important;
    }
    
    /* Préserver la largeur complète du conteneur de quantité */
    .purchase-qty.disabled-out-of-stock {
        width: 100% !important;
        margin-bottom: 16px !important;
    }
    
    .purchase-qty.disabled-out-of-stock .quantity {
        display: inline-flex !important;
        width: 100% !important;
    }
    
    /* Wrapper pour permettre le tooltip sur le bouton */
    .single_add_to_cart_button.disabled-out-of-stock {
        pointer-events: auto !important;
    }
    
    /* Tooltip personnalisé */
    .tooltip-out-of-stock {
        position: relative;
        display: inline-block;
    }
    
    .tooltip-out-of-stock .tooltip-text {
        visibility: hidden;
        width: 140px;
        background-color: #333;
        color: #fff;
        text-align: center;
        border-radius: 6px;
        padding: 5px 10px;
        position: absolute;
        z-index: 1000;
        bottom: 125%;
        left: 50%;
        margin-left: -70px;
        opacity: 0;
        transition: opacity 0.3s;
        font-size: 12px;
    }
    
    .tooltip-out-of-stock .tooltip-text::after {
        content: "";
        position: absolute;
        top: 100%;
        left: 50%;
        margin-left: -5px;
        border-width: 5px;
        border-style: solid;
        border-color: #333 transparent transparent transparent;
    }
    
    .tooltip-out-of-stock:hover .tooltip-text {
        visibility: visible;
        opacity: 1;
    }
    </style>
    
    <script>
    jQuery(document).ready(function($) {
        
        // Fonction pour vérifier le stock d'une variante
        function checkVariationStock(variation) {
            const quantityWrapper = $('.quantity-wrapper, .purchase-qty');
            const addToCartBtn = $('.single_add_to_cart_button');
            const quantityInput = quantityWrapper.find('input[type="number"]');
            
            let isOutOfStock = false;
            let maxStock = null;
            
            if (variation) {
                // Vérifier le stock de la variante
                if (variation.is_in_stock === false) {
                    isOutOfStock = true;
                } else if (variation.max_qty !== null && variation.max_qty <= 0) {
                    isOutOfStock = true;
                } else if (variation.availability_html && variation.availability_html.includes('rupture')) {
                    isOutOfStock = true;
                }
                
                maxStock = variation.max_qty;
                
                //console.log('Stock variante:', {
                    in_stock: variation.is_in_stock,
                    max_qty: variation.max_qty,
                    out_of_stock: isOutOfStock
                });
            }
            
            // Appliquer ou retirer les styles de rupture de stock UNIQUEMENT sur le bouton
            if (isOutOfStock) {
                addToCartBtn.addClass('out-of-stock-disabled tooltip-out-of-stock');
                addToCartBtn.prop('disabled', true);
                
                // Ajouter le tooltip uniquement sur le bouton
                if (!addToCartBtn.find('.tooltip-text').length) {
                    addToCartBtn.append('<span class="tooltip-text">Plus de stock disponible</span>');
                }
            } else {
                addToCartBtn.removeClass('out-of-stock-disabled tooltip-out-of-stock');
                addToCartBtn.prop('disabled', false);
                
                // Retirer le tooltip
                addToCartBtn.find('.tooltip-text').remove();
                
                // Mettre à jour la quantité max si nécessaire
                if (maxStock && maxStock > 0) {
                    quantityInput.attr('max', maxStock);
                }
            }
        }
        
        // Écouter les changements de variante
        $(document).on('found_variation', function(event, variation) {
            //console.log('🔄 Variante trouvée, vérification du stock');
            checkVariationStock(variation);
        });
        
        // Écouter la réinitialisation des variantes
        $(document).on('reset_data', function() {
            //console.log('🔄 Reset des variantes');
            checkVariationStock(null);
        });

        // Surveiller les changements dans le champ de quantité pour valider la quantité max
        $(document).on('input change', 'input[name="quantity"]', function() {
            const $input = $(this);
            const currentValue = parseInt($input.val()) || 1;
            const maxValue = parseInt($input.attr('max')) || 999999;
            
            //console.log('📊 Vérification quantité:', {current: currentValue, max: maxValue});
            
            if (currentValue > maxValue) {
                $input.val(maxValue);
                showQuantityLimitMessage(maxValue);
            }
        });

        // Empêcher la soumission du formulaire avec la touche Entrée sur le champ quantité
        $(document).on('keypress', 'input[name="quantity"]', function(e) {
            if (e.key === 'Enter' || e.keyCode === 13) {
                e.preventDefault();
                
                // Valider la quantité
                const $input = $(this);
                const currentValue = parseInt($input.val()) || 1;
                const maxValue = parseInt($input.attr('max')) || 999999;
                
                if (currentValue > maxValue) {
                    $input.val(maxValue);
                    showQuantityLimitMessage(maxValue);
                }
                
                // Faire perdre le focus au champ
                $input.blur();
                
                return false;
            }
        });
        
        function showQuantityLimitMessage(maxQuantity) {
            // Supprimer les anciens messages
            $('.quantity-limit-message').remove();
            
            // Créer le message
            const message = $('<div class="quantity-limit-message woocommerce-message" style="margin: 10px 0; padding: 12px; background: #f8e7c2; color: #2a3e6a; border: 1px solid #e2c48a; border-radius: 4px; font-size: 14px;">' +
                'Quantité limitée à ' + maxQuantity + ' unité(s) maximum en stock.' +
                '</div>');
            
            // Ajouter après le champ de quantité
            $('.purchase-qty').after(message);
            
            // Supprimer automatiquement après 5 secondes
            setTimeout(function() {
                message.fadeOut(300, function() {
                    $(this).remove();
                });
            }, 5000);
        }
        
        // Vérification initiale pour les produits simples
        const initialDisabled = $('.disabled-out-of-stock');
        if (initialDisabled.length) {
            //console.log('📦 Produit simple en rupture de stock détecté');
            initialDisabled.each(function() {
                $(this).addClass('tooltip-out-of-stock');
                if (!$(this).find('.tooltip-text').length) {
                    $(this).append('<span class="tooltip-text">Plus de stock disponible</span>');
                }
            });
        }
    });
    </script>
    <?php
}

function add_promo_bubble_single_product_js() {
    if (!is_product()) {
        return;
    }
    
    global $product;
    if (!$product) {
        return;
    }
    
    $promo_data = calculate_product_promotion($product);
    
    if (!$promo_data['has_promo']) {
        return;
    }
    
    $product_id = $product->get_id();
    ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        
        const promoData = {
            discount_percent: <?php echo $promo_data['discount_percent']; ?>,
            is_quantity_based: <?php echo $promo_data['is_quantity_based'] ? 'true' : 'false'; ?>
        };
        
        // Créer la bulle
        const bubble = document.createElement('span');
        bubble.className = promoData.is_quantity_based ? 'promo-bubble-single promo-bubble-quantity' : 'promo-bubble-single';
        
        // Forcer les styles inline pour s'assurer de l'affichage sur mobile
        bubble.style.cssText = 'position: absolute !important; background-color: #5899E2 !important; color: white !important; border-radius: 50% !important; display: flex !important; align-items: center !important; justify-content: center !important; font-weight: bold !important; z-index: 9999 !important; box-shadow: 0 3px 6px rgba(0,0,0,0.3) !important; line-height: 1.1 !important; text-align: center !important; visibility: visible !important; opacity: 1 !important;';
        
        if (promoData.is_quantity_based) {
            // Pour les bulles "jusqu'à", utiliser HTML avec saut de ligne
            bubble.innerHTML = 'jusqu\'à<br>-' + promoData.discount_percent + '%';
            bubble.style.cssText += 'top: 15px !important; left: 15px !important; width: 70px !important; height: 70px !important; font-size: 11.39px !important; padding: 2px !important;';
        } else {
            // Pour les bulles simples, texte normal
            bubble.textContent = '-' + promoData.discount_percent + '%';
            bubble.style.cssText += 'top: 15px !important; left: 15px !important; width: 60px !important; height: 60px !important; font-size: 13.92px !important;';
        }
        
        // Chercher le conteneur d'image principal du produit
        const imageContainers = [
            '.woocommerce-product-gallery__wrapper',
            '.woocommerce-product-gallery',
            '.product-gallery',
            '.single-product-main-image',
            '.wp-post-image'
        ];
        
        let imageContainer = null;
        for (let selector of imageContainers) {
            imageContainer = document.querySelector(selector);
            if (imageContainer) {
                break;
            }
        }
        
        // Essayer d'abord .product-gallery mais sans casser le slide
        let productGallery = document.querySelector('.product-gallery');
        if (productGallery) {
            // Sauvegarder la position originale
            const originalPosition = getComputedStyle(productGallery).position;
            if (originalPosition === 'static') {
                productGallery.style.position = 'relative';
            }
            productGallery.appendChild(bubble);
        } else if (imageContainer) {
            imageContainer.style.position = 'relative';
            imageContainer.appendChild(bubble);
        } else {
            //console.log('No container found for single product');
        }
    });
    </script>
    <?php
}

// Fonction utilitaire pour récupérer l'ID produit depuis le contexte du block
function get_product_id_from_block_context($block) {
    // Essayer différentes méthodes pour obtenir l'ID du produit
    if (isset($block['attrs']['context']['productId'])) {
        return $block['attrs']['context']['productId'];
    }
    
    if (isset($GLOBALS['wc_product_collection_current_product'])) {
        return $GLOBALS['wc_product_collection_current_product']->get_id();
    }
    
    // Essayer de l'extraire du contexte wp-context
    global $wp_query;
    if (isset($wp_query->post->ID)) {
        return $wp_query->post->ID;
    }
    
    return null;
}

// Fonction principale pour calculer les promotions
function calculate_product_promotion($product) {
    $product_id = $product->get_id();
    $regular_price = floatval($product->get_regular_price());
    $sale_price = $product->get_sale_price();
    
    $result = [
        'has_promo' => false,
        'discount_percent' => 0,
        'lowest_price' => $regular_price,
        'is_quantity_based' => false,
        'type' => 'none'
    ];
    
    if (empty($regular_price)) {
        return $result;
    }
    
    $lowest_price = null;
    
    // 1. Vérifier les promotions WooCommerce standard
    if ($product->is_on_sale() && !empty($sale_price)) {
        $sale_price_float = floatval($sale_price);
        if ($sale_price_float < $regular_price) {
            $lowest_price = $sale_price_float;
            $result['type'] = 'woocommerce';
        }
    }
    
    // 2. Vérifier les promotions Advanced Dynamic Pricing
    if (function_exists('adp_functions')) {
        $adp_price = get_adp_lowest_price($product_id);
        if ($adp_price && $adp_price < $regular_price) {
            // Si ADP donne un prix plus bas, l'utiliser
            if (!$lowest_price || $adp_price < $lowest_price) {
                $lowest_price = $adp_price;
                $result['type'] = 'adp';
                $result['is_quantity_based'] = is_quantity_based_discount($product_id);
            }
        }
    }
    
    // 3. Calculer le résultat final
    if ($lowest_price && $lowest_price < $regular_price) {
        $result['has_promo'] = true;
        $result['lowest_price'] = $lowest_price;
        $result['discount_percent'] = round((($regular_price - $lowest_price) / $regular_price) * 100);
    }
    
    return $result;
}

// Fonction pour obtenir le prix le plus bas avec ADP
function get_adp_lowest_price($product_id) {
    if (!function_exists('adp_functions')) {
        return null;
    }
    
    $product = wc_get_product($product_id);
    if (!$product) {
        return null;
    }
    
    // Tester différentes quantités pour trouver le prix le plus bas
    $quantities_to_test = [1, 2, 5, 10, 25, 50, 100, 150, 200, 300, 500, 1000];
    $lowest_price = null;
    
    foreach ($quantities_to_test as $qty) {
        try {
            $calculated_product = adp_functions()->calculateProduct($product, $qty, true);
            
            if ($calculated_product && method_exists($calculated_product, 'getPrice')) {
                $price = $calculated_product->getPrice();
                if ($price && (!$lowest_price || $price < $lowest_price)) {
                    $lowest_price = $price;
                }
            }
        } catch (Exception $e) {
            // Ignorer les erreurs et continuer
            continue;
        }
    }
    
    return $lowest_price;
}

// Fonction pour détecter si c'est une remise basée sur la quantité
function is_quantity_based_discount($product_id) {
    if (!function_exists('adp_functions')) {
        return false;
    }
    
    $product = wc_get_product($product_id);
    if (!$product) {
        return false;
    }
    
    // Comparer les prix pour différentes quantités
    $prices = [];
    $quantities_to_test = [1, 2, 5, 10, 50, 100, 200, 500];
    
    foreach ($quantities_to_test as $qty) {
        try {
            $calculated_product = adp_functions()->calculateProduct($product, $qty, true);
            
            if ($calculated_product && method_exists($calculated_product, 'getPrice')) {
                $price = $calculated_product->getPrice();
                if ($price) {
                    $prices[] = round($price, 2);
                }
            }
        } catch (Exception $e) {
            continue;
        }
    }
    
    // Si les prix varient, c'est basé sur la quantité
    return count(array_unique($prices)) > 1;
}

// Fonction pour générer le HTML de la bulle
function generate_promo_bubble($promo_data, $context = 'homepage') {
    if (!$promo_data['has_promo']) {
        return '';
    }
    
    $discount_percent = $promo_data['discount_percent'];
    $is_quantity_based = $promo_data['is_quantity_based'];
    
    // Texte de la bulle
    if ($is_quantity_based) {
        $bubble_text = 'jusqu\'à -' . $discount_percent . '%';
    } else {
        $bubble_text = '-' . $discount_percent . '%';
    }
    
    // Classes CSS selon le contexte
    $css_class = $context === 'single' ? 'promo-bubble-single' : 'promo-bubble';
    
    return '<span class="' . $css_class . '">' . $bubble_text . '</span>';
}

// CSS pour les bulles
add_action('wp_head', function() {
    echo '<style>
        /* Bulle promo page d\'accueil */
        .promo-price-container {
            position: relative;
        }
        
        /* S\'assurer que le conteneur d\'image est positionné relativement */
        .wc-block-components-product-image {
            position: relative !important;
        }
        
        /* Style pour l affichage vertical des prix promo uniquement */
        .wc-block-components-product-price .promo-price-vertical {
            display: block !important;
            text-align: center;
        }
        
        .wc-block-components-product-price .promo-price-vertical del {
            display: block !important;
            margin-bottom: 2px;
            font-size: 0.9em;
        }
        
        .wc-block-components-product-price .promo-price-vertical span[style*="color:#5899E2"] {
            display: block !important;
            font-size: 1em;
        }
        
        /* Badge promo positionné correctement */
        .wc-block-components-product-image .wc-block-components-product-sale-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            z-index: 10;
        }
        
        .promo-bubble {
            position: absolute;
            top: 10px;
            left: 10px;
            background-color: #5899E2;
            color: white;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            font-weight: bold;
            z-index: 100;
            box-shadow: 0 3px 6px rgba(0,0,0,0.3);
            line-height: 1.1;
            text-align: center;
        }
        
        
        /* Bulle promo page produit - en haut a gauche */
        .promo-bubble-single {
            position: absolute !important;
            top: 15px !important;
            left: 15px !important;
            background-color: #5899E2 !important;
            color: white !important;
            border-radius: 50% !important;
            width: 60px !important;
            height: 60px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            font-size: 13.92px !important;
            font-weight: bold !important;
            z-index: 9999 !important;
            box-shadow: 0 3px 6px rgba(0,0,0,0.3) !important;
            line-height: 1.1 !important;
            text-align: center !important;
            visibility: visible !important;
            opacity: 1 !important;
        }
        
        /* Bulle specifique pour "jusqu a" - 2 lignes */
        .promo-bubble-single.promo-bubble-quantity {
            left: 15px !important;
            width: 70px !important;
            height: 70px !important;
            font-size: 11.39px !important;
            line-height: 1.1 !important;
            padding: 2px !important;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .promo-bubble {
                width: 45px;
                height: 45px;
                font-size: 10px;
                top: 8px;
                left: 8px;
            }
            
            .promo-bubble-single {
                width: 50px !important;
                height: 50px !important;
                font-size: 12.65px !important;
                top: 10px !important;
                left: 10px !important;
                display: flex !important;
                visibility: visible !important;
                opacity: 1 !important;
                z-index: 9999 !important;
            }
            
            .promo-bubble-single.promo-bubble-quantity {
                left: 10px !important;
                width: 60px !important;
                height: 60px !important;
                font-size: 10.12px !important;
                line-height: 1.1 !important;
                padding: 2px !important;
            }
        }
        
        @media (max-width: 480px) {
            .promo-bubble {
                width: 40px;
                height: 40px;
                font-size: 9px;
                top: 6px;
                left: 6px;
            }
            
            .promo-bubble-single {
                width: 45px !important;
                height: 45px !important;
                font-size: 11.39px !important;
                top: 8px !important;
                left: 8px !important;
                display: flex !important;
                visibility: visible !important;
                opacity: 1 !important;
                z-index: 9999 !important;
            }
            
            .promo-bubble-single.promo-bubble-quantity {
                left: 8px !important;
                width: 55px !important;
                height: 55px !important;
                font-size: 8.86px !important;
                line-height: 1.1 !important;
                padding: 1px !important;
            }
        }
    </style>';
});

// Désactiver la description par défaut de WooCommerce sur les pages catégories
add_action('wp', function() {
    if (is_product_category()) {
        remove_action('woocommerce_archive_description', 'woocommerce_taxonomy_archive_description', 10);
    }
});

add_filter('wpseo_canonical', 'fix_shop_canonical_with_add_to_cart');
function fix_shop_canonical_with_add_to_cart($canonical) {
    if (is_shop() && isset($_GET['add-to-cart'])) {
        $canonical = get_permalink(wc_get_page_id('shop'));
    }
    return $canonical;
}

// Désactiver complètement la section archive description de WooCommerce
add_filter('woocommerce_taxonomy_archive_description_raw', '__return_empty_string', 99);

// Ajouter la description courte entre les sous-catégories et les résultats
add_action('woocommerce_before_shop_loop', 'add_category_description_before_products', 15);
function add_category_description_before_products() {
    if (is_product_category()) {
        $term = get_queried_object();
        if ( $term && ! empty( $term->description ) ) {
            $description_text = strip_tags($term->description);
            $description_short = substr($description_text, 0, 200);
            ?>
            <div class="category-description-top">
                <div class="category-description-short">
                    <?php echo esc_html($description_short); ?>
                    <?php if (strlen($description_text) > 200) : ?>
                        <span class="dots">...</span>
                        <span class="read-more" onclick="scrollToFullDescription()">Lire la suite</span>
                    <?php endif; ?>
                </div>
            </div>
            <?php
        }
    }
}

// ========================================
// SYSTÈME DE BORNE TACTILE
// ========================================

// Ajouter les champs personnalisés au profil utilisateur
add_action('show_user_profile', 'aleaulavage_add_borne_fields');
add_action('edit_user_profile', 'aleaulavage_add_borne_fields');

function aleaulavage_add_borne_fields($user) {
    ?>
    <h3>Options Borne Tactile</h3>
    <table class="form-table">
        <tr>
            <th><label for="mode_borne_active">Mode Borne Tactile</label></th>
            <td>
                <input type="checkbox" name="mode_borne_active" id="mode_borne_active" value="1" <?php checked(get_user_meta($user->ID, 'mode_borne_active', true), '1'); ?>>
                <label for="mode_borne_active">Activer le mode borne tactile pour cet utilisateur</label>
                <p class="description">Lorsque activé, cet utilisateur sera automatiquement redirigé vers la page borne tactile après inactivité.</p>
            </td>
        </tr>
        <tr>
            <th><label for="borne_delai_inactivite">Délai d'inactivité (secondes)</label></th>
            <td>
                <input type="number" name="borne_delai_inactivite" id="borne_delai_inactivite" value="<?php echo esc_attr(get_user_meta($user->ID, 'borne_delai_inactivite', true) ?: '30'); ?>" min="10" max="300" class="regular-text">
                <p class="description">Temps d'inactivité avant retour automatique à la page borne (entre 10 et 300 secondes).</p>
            </td>
        </tr>
        <tr>
            <th><label for="borne_page_id">Page de la borne</label></th>
            <td>
                <?php
                $borne_pages = get_pages(array(
                    'meta_key' => '_wp_page_template',
                    'meta_value' => 'page-borne-tactile.php'
                ));

                if (!empty($borne_pages)) {
                    $selected_page = get_user_meta($user->ID, 'borne_page_id', true);
                    ?>
                    <select name="borne_page_id" id="borne_page_id" class="regular-text">
                        <option value="">Sélectionner une page</option>
                        <?php foreach ($borne_pages as $page) : ?>
                            <option value="<?php echo esc_attr($page->ID); ?>" <?php selected($selected_page, $page->ID); ?>>
                                <?php echo esc_html($page->post_title); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <p class="description">Page de borne tactile vers laquelle rediriger automatiquement.</p>
                <?php } else { ?>
                    <p class="description">Aucune page avec le template "Borne Tactile" n'a été créée. <a href="<?php echo admin_url('post-new.php?post_type=page'); ?>">Créer une page</a></p>
                <?php } ?>
            </td>
        </tr>
    </table>
    <?php
}

// Sauvegarder les champs personnalisés
add_action('personal_options_update', 'aleaulavage_save_borne_fields');
add_action('edit_user_profile_update', 'aleaulavage_save_borne_fields');

function aleaulavage_save_borne_fields($user_id) {
    if (!current_user_can('edit_user', $user_id)) {
        return false;
    }

    update_user_meta($user_id, 'mode_borne_active', isset($_POST['mode_borne_active']) ? '1' : '0');

    if (isset($_POST['borne_delai_inactivite'])) {
        $delai = intval($_POST['borne_delai_inactivite']);
        $delai = max(10, min(300, $delai)); // Entre 10 et 300 secondes
        update_user_meta($user_id, 'borne_delai_inactivite', $delai);
    }

    if (isset($_POST['borne_page_id'])) {
        update_user_meta($user_id, 'borne_page_id', intval($_POST['borne_page_id']));
    }
}

// Ajouter le script de redirection automatique sur toutes les pages (sauf la borne elle-même)
add_action('wp_footer', 'aleaulavage_borne_auto_redirect_script');

function aleaulavage_borne_auto_redirect_script() {
    // Ne pas ajouter le script sur la page de la borne elle-même
    if (is_page_template('page-borne-tactile.php')) {
        return;
    }

    $user_id = get_current_user_id();
    if (!$user_id) {
        return;
    }

    $mode_borne = get_user_meta($user_id, 'mode_borne_active', true);
    if ($mode_borne !== '1') {
        return;
    }

    $delai_inactivite = get_user_meta($user_id, 'borne_delai_inactivite', true) ?: 30;
    $borne_page_id = get_user_meta($user_id, 'borne_page_id', true);

    if (!$borne_page_id) {
        return;
    }

    $borne_url = get_permalink($borne_page_id);
    ?>
    <script>
    (function() {
        let borneInactivityTimer;
        const borneDelay = <?php echo intval($delai_inactivite); ?> * 1000;
        const borneUrl = '<?php echo esc_js($borne_url); ?>';

        function resetBorneTimer() {
            clearTimeout(borneInactivityTimer);
            borneInactivityTimer = setTimeout(function() {
                window.location.href = borneUrl;
            }, borneDelay);
        }

        const events = ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart', 'click'];
        events.forEach(function(event) {
            document.addEventListener(event, resetBorneTimer, true);
        });

        resetBorneTimer();
    })();
    </script>
    <?php
}


