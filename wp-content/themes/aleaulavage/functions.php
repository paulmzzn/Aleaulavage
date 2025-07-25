<?php
// Laisser ELECX fonctionner normalement mais garder accès au prix original
// pour notre logique JavaScript personnalisée

// Ajout du zoom qui suit la souris sur la page produit
add_action('wp_enqueue_scripts', function() {
    if (is_product()) {
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
    // Sync variation price with header price
    wp_enqueue_script(
        'variation-price-sync',
        get_stylesheet_directory_uri() . '/js/variation-price-sync.js',
        array(),
        filemtime(get_stylesheet_directory() . '/js/variation-price-sync.js'),
        true
    );
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
        error_log("Error in display_subcategories_bubbles: " . $e->getMessage());
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
        'permission_callback' => '__return_true', // Si tu n'as pas de permission spécifique
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

        // Récupérer tous les prix
        $prices = array_map(function($variation) {
            return floatval($variation['display_price']);
        }, $variations);

        $unique_prices = array_unique($prices);

        // ✅ S’il y a un seul prix et qu’il est supérieur à 0
        if ( count($unique_prices) === 1 && $unique_prices[0] > 0 ) {
            echo '<p class="price">' . wc_price( $unique_prices[0] ) . '</p>';
        }

        // ❌ Sinon → ne rien afficher → JS dynamique ou aucun prix
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
            const priceBox = $('.woocommerce-variation-price');
            if (priceBox.length) {
                priceBox.hide();
                $(document).on('found_variation', function (event, variation) {
                    if (variation && variation.price_html) {
                        priceBox.html(variation.price_html).show();
                    }
                });
                $(document).on('reset_data', function () {
                    priceBox.html('').hide();
                });
            }
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
        wp_send_json_success(array(
            'total' => wc_price($cart->get_total('edit'))
        ));
    }
    wp_send_json_error();
}

add_action('wp_enqueue_scripts', 'enqueue_ajax_cart_scripts');
function enqueue_ajax_cart_scripts() {
    if (is_cart()) {
        // Debug : Ajouter les logs
        error_log('AJAX Cart: Scripts chargés sur la page panier');
        
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
    error_log('AJAX Cart: Handler appelé avec POST: ' . print_r($_POST, true));
    
    // Vérification de sécurité
    if (!isset($_POST['security']) || !wp_verify_nonce($_POST['security'], 'ajax_cart_nonce')) {
        error_log('AJAX Cart: Erreur de sécurité - Nonce invalide');
        wp_send_json_error('Erreur de sécurité');
        return;
    }

    if (!isset($_POST['cart_key']) || !isset($_POST['quantity'])) {
        error_log('AJAX Cart: Données manquantes dans POST');
        wp_send_json_error('Données manquantes');
        return;
    }

    $cart_key = sanitize_text_field($_POST['cart_key']);
    $quantity = intval($_POST['quantity']);
    
    error_log("AJAX Cart: Mise à jour - Cart Key: $cart_key, Quantity: $quantity");
    
    if (empty($cart_key)) {
        error_log('AJAX Cart: Clé du panier vide');
        wp_send_json_error('Clé du panier manquante');
        return;
    }

    // Vérifier que le panier existe
    if (!WC()->cart) {
        error_log('AJAX Cart: Panier WooCommerce non disponible');
        wp_send_json_error('Panier non disponible');
        return;
    }

    try {
        // Mettre à jour la quantité dans le panier
        if ($quantity <= 0) {
            $removed = WC()->cart->remove_cart_item($cart_key);
            error_log("AJAX Cart: Suppression - Résultat: " . ($removed ? 'succès' : 'échec'));
            if (!$removed) {
                wp_send_json_error('Impossible de supprimer l\'article');
                return;
            }
        } else {
            $updated = WC()->cart->set_quantity($cart_key, $quantity, true);
            error_log("AJAX Cart: Mise à jour quantité - Résultat: " . ($updated ? 'succès' : 'échec'));
            if (!$updated) {
                wp_send_json_error('Impossible de mettre à jour la quantité');
                return;
            }
        }

        // Recalculer les totaux
        WC()->cart->calculate_totals();
        error_log('AJAX Cart: Totaux recalculés');

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

        error_log('AJAX Cart: Succès - Réponse: ' . print_r($response_data, true));
        wp_send_json_success($response_data);

    } catch (Exception $e) {
        error_log('AJAX Cart: Exception - ' . $e->getMessage());
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
        
        error_log("DEBUG: subtotal HT = " . $subtotal);
        error_log("DEBUG: subtotal + tax = " . $subtotal_incl_tax);
        error_log("DEBUG: tax_total = " . $tax_total);
        
        // Calculer le total TTC avec frais de livraison
        $cart_total_without_shipping = $subtotal + $tax_total;
        $cart_total_with_shipping = $cart_total_without_shipping + 19;
        error_log("DEBUG: cart_total_with_shipping = " . $cart_total_with_shipping);
        
        // Calculer la progression pour la livraison gratuite
        $target = 550;
        $progress = $cart_total_with_shipping > 0 ? min(100, ($cart_total_with_shipping / $target) * 100) : 0;
        error_log("DEBUG: progress = " . $progress);
        
        // Logique de livraison
        if ($cart_total_with_shipping >= $target) {
            $shipping_message = 'Livraison offerte !';
            $shipping_display = 'Offerte';
            $total_numeric = $cart_total_without_shipping;
            error_log("DEBUG: Free shipping - shipping_display = " . $shipping_display);
        } else {
            $remaining = max(0, $target - $cart_total_with_shipping);
            $shipping_message = sprintf('Plus que %s pour profiter de la livraison offerte', wc_price($remaining));
            $shipping_display = wc_price(19);
            $total_numeric = $cart_total_with_shipping;
            error_log("DEBUG: Paid shipping - shipping_display = " . $shipping_display);
        }
        
        // Récupérer le sous-total de l'item
        $item_subtotal = '';
        if ($quantity > 0) {
            $cart_item = WC()->cart->get_cart_item($cart_key);
            if ($cart_item) {
                $item_subtotal = WC()->cart->get_product_subtotal($cart_item['data'], $quantity);
            }
        }

        error_log("DEBUG: Before JSON response");
        
        // Test simple d'abord
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
            // Rechercher dans les meta_value des UGS
            $sku_posts = $wpdb->get_col( $wpdb->prepare( "
                SELECT DISTINCT post_id 
                FROM {$wpdb->postmeta} 
                WHERE meta_key = '_sku' 
                AND meta_value LIKE %s
            ", '%' . $wpdb->esc_like( $search_term ) . '%' ) );
            
            if ( ! empty( $sku_posts ) ) {
                // Modifier la requête pour inclure les posts trouvés par UGS
                $search_ids = implode( ',', array_map( 'absint', $sku_posts ) );
                
                // Ajouter les IDs des produits trouvés par UGS à la recherche
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

// Charger les menus d’analyse clients
require_once get_stylesheet_directory() . '/includes/admin-comportement.php';