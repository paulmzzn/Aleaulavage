<?php
/**
 * Shortcode to display recent blog posts
 * Usage: [recent_posts posts="6"]
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

function aleaulavage_v2_recent_posts_shortcode($atts)
{
    $atts = shortcode_atts(array(
        'posts' => 6,
    ), $atts);

    $args = array(
        'post_type' => 'post',
        'posts_per_page' => intval($atts['posts']),
        'orderby' => 'date',
        'order' => 'DESC',
        'post_status' => 'publish'
    );

    $blog_posts = new WP_Query($args);

    ob_start();

    if ($blog_posts->have_posts()):
        while ($blog_posts->have_posts()):
            $blog_posts->the_post();
            $post_url = esc_url(get_permalink());
            $post_title = get_the_title();
            $post_date = get_the_date('d F Y');
            $post_excerpt = wp_trim_words(get_the_excerpt(), 15, '...');
            $thumbnail_url = get_the_post_thumbnail_url(get_the_ID(), 'medium');
            // Updated path to assets/images
            $default_thumbnail = get_template_directory_uri() . '/assets/images/default-blog.jpg';
            ?>
            <a href="<?php echo $post_url; ?>" class="blog-post-card">
                <?php if ($thumbnail_url): ?>
                    <img src="<?php echo esc_url($thumbnail_url); ?>" alt="<?php echo esc_attr($post_title); ?>" class="post-thumbnail"
                        loading="lazy">
                <?php else: ?>
                    <div class="post-thumbnail"
                        style="background: linear-gradient(135deg, #5899e2 0%, #4a8cd4 100%); display: flex; align-items: center; justify-content: center;">
                        <i class="fa-solid fa-newspaper text-white" style="font-size: 48px; opacity: 0.5;"></i>
                    </div>
                <?php endif; ?>

                <div class="post-content">
                    <div class="post-date">
                        <i class="fa-regular fa-calendar"></i>
                        <?php echo $post_date; ?>
                    </div>

                    <h3 class="post-title"><?php echo esc_html($post_title); ?></h3>

                    <p class="post-excerpt"><?php echo esc_html($post_excerpt); ?></p>

                    <span class="read-more">
                        Lire la suite <i class="fa-solid fa-arrow-right"></i>
                    </span>
                </div>
            </a>
            <?php
        endwhile;
        wp_reset_postdata();
    else: ?>
        <div class="text-center py-5">
            <i class="fa-solid fa-inbox text-muted mb-3" style="font-size: 48px;"></i>
            <p class="text-muted">Aucun article disponible pour le moment.</p>
        </div>
    <?php endif;

    return ob_get_clean();
}
add_shortcode('recent_posts', 'aleaulavage_v2_recent_posts_shortcode');

/**
 * Shortcode to display Wishlist Page
 * Shortcode: [aleaulavage_wishlist]
 * Displays the user's wishlist or a login prompt.
 */
function aleaulavage_v2_wishlist_shortcode()
{
    // Script is already enqueued globally in inc/scripts.php

    ob_start();
    ?>
    <div id="aleaulavage-wishlist-container" class="aleaulavage-wishlist-container">
        <?php if (is_user_logged_in()):
            $user_id = get_current_user_id();
            $wishlist = get_user_meta($user_id, 'user_wishlist', true);

            if (!is_array($wishlist) || empty($wishlist)): ?>
                <div class="wishlist-empty">
                    <i class="fa-regular fa-heart empty-icon"></i>
                    <h2 class="empty-title">Votre liste est vide</h2>
                    <p class="empty-text">
                        Vous n'avez pas encore ajouté de produits à vos favoris.<br>
                        Découvrez notre sélection et créez votre liste de souhaits !
                    </p>
                    <a href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>" class="btn btn-primary">
                        <i class="fa-solid fa-shopping-bag me-2"></i>
                        Découvrir nos produits
                    </a>
                </div>
            <?php else: ?>
                <div class="woocommerce">
                    <ul class="wishlist-grid products">
                        <?php
                        // Fetch products
                        $args = array(
                            'post_type' => 'product',
                            'post__in' => $wishlist,
                            'posts_per_page' => -1,
                            'orderby' => 'post__in' // Keep order of addition
                        );

                        // Filter by category if selected
                        if (isset($_GET['wishlist_category']) && !empty($_GET['wishlist_category'])) {
                            $cat_id = absint($_GET['wishlist_category']);
                            $args['tax_query'] = array(
                                array(
                                    'taxonomy' => 'product_cat',
                                    'field' => 'term_id',
                                    'terms' => $cat_id,
                                ),
                            );
                        }

                        $query = new WP_Query($args);

                        if ($query->have_posts()):
                            while ($query->have_posts()):
                                $query->the_post();
                                wc_get_template_part('content', 'product');
                            endwhile;
                            wp_reset_postdata();
                        else:
                            ?>
                            <div class="col-12">
                                <div class="wishlist-empty">
                                    <i class="fa-regular fa-folder-open empty-icon"></i>
                                    <h2 class="empty-title">Aucun favori dans cette catégorie</h2>
                                    <p class="empty-text">Vous n'avez pas de produits favoris correspondant à ce filtre.</p>
                                    <a href="<?php echo esc_url(remove_query_arg('wishlist_category')); ?>"
                                        class="btn btn-outline-primary">
                                        Voir tous mes favoris
                                    </a>
                                </div>
                            </div>
                        <?php endif; ?>
                    </ul>
                </div>
            <?php endif; ?>

        <?php else: ?>
            <div class="wishlist-login-required empty-state">
                <i class="fa-solid fa-heart-crack empty-icon"></i>
                <h2 class="empty-title">Connexion requise</h2>
                <p class="empty-text">
                    Connectez-vous à votre compte pour accéder à votre liste de favoris<br>
                    et retrouver tous vos produits préférés.
                </p>
                <button type="button" class="btn btn-primary"
                    onclick="document.getElementById('login-modal').style.display='flex'">
                    <i class="fa-solid fa-sign-in-alt me-2"></i>
                    Se connecter
                </button>
            </div>
        <?php endif; ?>
    </div>

    <!-- Login Modal (Always present in footer via JS or here if needed, but putting here ensures it's available on this page) -->
    <?php if (!is_user_logged_in()): ?>
        <div id="login-modal" class="login-modal" style="display: none;">
            <div class="login-modal-content">
                <button type="button" class="close-modal"
                    onclick="document.getElementById('login-modal').style.display='none'">&times;</button>

                <div class="modal-header-custom">
                    <i class="fa-solid fa-heart modal-icon"></i>
                    <h3>Connexion</h3>
                    <p>Connectez-vous pour gérer vos favoris.</p>
                </div>

                <form id="wishlist-login-form" class="login-form">
                    <div class="form-group">
                        <label for="login-username">Email ou nom d'utilisateur</label>
                        <input type="text" id="login-username" name="username" required>
                    </div>

                    <div class="form-group">
                        <label for="login-password">Mot de passe</label>
                        <input type="password" id="login-password" name="password" required>
                    </div>

                    <div id="login-error" class="login-error" style="display: none;"></div>

                    <div class="form-actions">
                        <button type="submit" id="submit-login" class="btn btn-primary w-100">Se connecter</button>
                    </div>
                    <div class="text-center mt-3">
                        <button type="button" class="btn btn-link"
                            onclick="document.getElementById('login-modal').style.display='none'">Annuler</button>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>

    <?php
    return ob_get_clean();
}
add_shortcode('aleaulavage_wishlist', 'aleaulavage_v2_wishlist_shortcode');
