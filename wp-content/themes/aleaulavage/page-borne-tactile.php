<?php
/**
 * Template Name: Borne Tactile
 * Description: Page d'accueil pour borne tactile de salon - Carrousel défilant par catégorie
 */

// Vérifier si l'utilisateur est connecté et a le mode borne activé
$user_id = get_current_user_id();
$mode_borne = get_user_meta($user_id, 'mode_borne_active', true);
$delai_inactivite = get_user_meta($user_id, 'borne_delai_inactivite', true) ?: 30;

if (!$user_id || $mode_borne !== '1') {
    wp_redirect(home_url());
    exit;
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title>Borne Tactile - <?php bloginfo('name'); ?></title>
    
    <!-- Swiper CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css"/>
    
    <?php wp_head(); ?>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body.borne-tactile {
            overflow: hidden;
            background: #fff;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }

        .borne-container {
            width: 100vw;
            height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Header compact */
        .borne-header {
            background: #2A3E6A;
            padding: 0.8rem 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            flex-shrink: 0;
        }

        .borne-header .logo {
            max-height: 45px;
            width: auto;
        }

        .borne-header h1 {
            color: #fff;
            font-size: 1.4rem;
            font-weight: 700;
        }

        .shop-button {
            background: #f6bb42;
            color: #2A3E6A;
            border: none;
            padding: 0.6rem 1.2rem;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .shop-button:hover {
            background: #f6a623;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(246, 187, 66, 0.3);
        }

        /* Container des carrousels - hauteur optimisée pour 9 lignes */
        .carousel-container {
            flex: 1;
            overflow-y: auto;
            overflow-x: hidden;
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
            padding: 1.2rem 0;
            background: #f8f9fa;
        }

        /* Chaque ligne de catégorie - hauteur augmentée et délimitée */
        .category-carousel {
            position: relative;
            flex-shrink: 0;
        }

        .category-header {
            padding: 0 0.5rem 0.3rem 0;
            margin-bottom: 0.3rem;
            margin-left: 1rem;
            margin-right: 1rem;
        }

        .category-title {
            color: #2A3E6A;
            font-size: 1.1rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            white-space: nowrap;
            display: inline-block;
            border-bottom: 3px solid #f6bb42;
            padding-bottom: 0.3rem;
        }

        .category-carousel-track {
            background: #fff;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            overflow: visible;
            display: flex;
            align-items: center;
            min-height: 110px;
        }

        .category-carousel-track:hover {
            border-color: #2A3E6A;
            box-shadow: 0 4px 12px rgba(42, 62, 106, 0.15);
        }

        /* Swiper personnalisé */
        .swiper {
            width: 100%;
            height: 100%;
            overflow: visible !important;
        }

        .swiper-wrapper {
            transition-timing-function: linear !important;
            align-items: center !important;
        }

        .swiper-slide {
            width: auto !important;
            display: flex;
            align-items: center;
        }

        /* Carte produit - taille ajustée */
        .product-card {
            background: #fff;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            overflow: hidden;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 180px;
            height: 86px;
            display: flex;
            box-shadow: 0 2px 6px rgba(0,0,0,0.08);
        }

        .product-card:hover {
            transform: translateY(-3px);
            border-color: #f6bb42;
            box-shadow: 0 6px 16px rgba(246, 187, 66, 0.3);
        }

        .product-image-container {
            position: relative;
            width: 86px;
            height: 86px;
            overflow: hidden;
            background: #f8f9fa;
            flex-shrink: 0;
        }

        .product-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .product-card:hover .product-image {
            transform: scale(1.08);
        }

        .product-badge {
            position: absolute;
            top: 4px;
            right: 4px;
            background: #dc3545;
            color: white;
            padding: 0.2rem 0.45rem;
            border-radius: 5px;
            font-weight: 700;
            font-size: 0.7rem;
            line-height: 1;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }

        .product-info {
            padding: 0.6rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            flex: 1;
            min-width: 0;
        }

        .product-title {
            color: #212529;
            font-size: 0.8rem;
            font-weight: 600;
            overflow: hidden;
            text-overflow: ellipsis;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            line-height: 1.3;
        }

        .product-price {
            color: #2A3E6A;
            font-size: 0.95rem;
            font-weight: 700;
            white-space: nowrap;
        }

        /* Footer compact */
        .borne-footer {
            background: #2A3E6A;
            padding: 0.6rem 1.5rem;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-shrink: 0;
        }

        .inactivity-indicator {
            display: none;
            align-items: center;
            gap: 0.8rem;
            color: #fff;
            font-size: 0.9rem;
        }

        .inactivity-indicator.active {
            display: flex;
        }

        .countdown {
            background: #f6bb42;
            color: #2A3E6A;
            padding: 0.4rem 0.8rem;
            border-radius: 6px;
            font-weight: 700;
            font-size: 1.1rem;
            min-width: 40px;
            text-align: center;
        }

        /* Scrollbar personnalisée */
        .carousel-container::-webkit-scrollbar {
            width: 8px;
        }

        .carousel-container::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        .carousel-container::-webkit-scrollbar-thumb {
            background: #2A3E6A;
            border-radius: 4px;
        }

        .carousel-container::-webkit-scrollbar-thumb:hover {
            background: #1a2e4a;
        }
    </style>
</head>
<body class="borne-tactile">
    <div class="borne-container">
        <!-- Header -->
        <div class="borne-header">
            <img src="<?php echo esc_url(get_stylesheet_directory_uri()); ?>/img/logo/logo-white.svg" alt="<?php bloginfo('name'); ?>" class="logo">
            <h1>Nos Produits</h1>
            <button class="shop-button" onclick="window.location.href='<?php echo esc_url(home_url('/boutique/')); ?>'">
                <i class="fa-solid fa-store"></i>
                <span>Voir la boutique</span>
            </button>
        </div>

        <!-- Carrousels de produits par catégorie -->
        <div class="carousel-container">
            <?php
            // Récupérer TOUTES les catégories
            $categories = get_terms(array(
                'taxonomy' => 'product_cat',
                'orderby' => 'menu_order',
                'order' => 'ASC',
                'hide_empty' => true,
                'parent' => 0,
                'exclude' => [16]
            ));

            if (!empty($categories)) :
                $carousel_index = 0;
                foreach ($categories as $category) :
                    // Récupérer TOUS les produits de cette catégorie
                    $products_args = array(
                        'post_type' => 'product',
                        'posts_per_page' => -1,
                        'tax_query' => array(
                            array(
                                'taxonomy' => 'product_cat',
                                'field' => 'term_id',
                                'terms' => $category->term_id,
                            )
                        ),
                        'post_status' => 'publish'
                    );

                    $products = new WP_Query($products_args);

                    if ($products->have_posts()) :
                        ?>
                        <div class="category-carousel">
                            <div class="category-header">
                                <h2 class="category-title"><?php echo esc_html($category->name); ?></h2>
                            </div>
                            
                            <!-- Swiper Container -->
                            <div class="category-carousel-track">
                                <div class="swiper swiper-<?php echo $carousel_index; ?>">
                                <div class="swiper-wrapper">
                                    <?php
                                    // Premier passage: stocker les produits
                                    $products_data = array();
                                    while ($products->have_posts()) : $products->the_post();
                                        global $product;

                                        $image_url = wp_get_attachment_image_url($product->get_image_id(), 'medium');
                                        if (!$image_url) {
                                            $image_url = wc_placeholder_img_src('medium');
                                        }

                                        $is_on_sale = $product->is_on_sale();
                                        $regular_price = $product->get_regular_price();
                                        $sale_price = $product->get_sale_price();

                                        $products_data[] = array(
                                            'url' => get_permalink(),
                                            'image' => $image_url,
                                            'name' => $product->get_name(),
                                            'price_html' => $product->get_price_html(),
                                            'is_on_sale' => $is_on_sale,
                                            'regular_price' => $regular_price,
                                            'sale_price' => $sale_price
                                        );
                                    endwhile;

                                    // Dupliquer les produits plusieurs fois pour une boucle fluide
                                    $duplications = 3;
                                    for ($i = 0; $i < $duplications; $i++) :
                                        foreach ($products_data as $product_data) :
                                            ?>
                                            <div class="swiper-slide">
                                                <div class="product-card" onclick="window.location.href='<?php echo esc_url($product_data['url']); ?>'">
                                                    <div class="product-image-container">
                                                        <?php if ($product_data['is_on_sale'] && $product_data['regular_price'] && $product_data['sale_price']) : 
                                                            $discount = round((($product_data['regular_price'] - $product_data['sale_price']) / $product_data['regular_price']) * 100);
                                                        ?>
                                                            <div class="product-badge">-<?php echo $discount; ?>%</div>
                                                        <?php endif; ?>
                                                        <img src="<?php echo esc_url($product_data['image']); ?>" 
                                                             alt="<?php echo esc_attr($product_data['name']); ?>" 
                                                             class="product-image">
                                                    </div>
                                                    <div class="product-info">
                                                        <h3 class="product-title"><?php echo esc_html($product_data['name']); ?></h3>
                                                        <div class="product-price"><?php echo $product_data['price_html']; ?></div>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php
                                        endforeach;
                                    endfor;
                                    ?>
                                </div>
                            </div>
                            </div>
                        </div>
                        <?php
                        wp_reset_postdata();
                        $carousel_index++;
                    endif;
                endforeach;
            endif;
            ?>
        </div>

        <!-- Footer -->
        <div class="borne-footer">
            <div class="inactivity-indicator" id="inactivity-indicator">
                <span>Retour automatique dans</span>
                <div class="countdown" id="countdown"><?php echo $delai_inactivite; ?></div>
            </div>
        </div>
    </div>

    <!-- Swiper JS -->
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    
    <script>
    (function() {
        // Initialisation des Swipers avec défilement infini
        document.addEventListener('DOMContentLoaded', function() {
            const carousels = document.querySelectorAll('.swiper');
            const speeds = [25000, 30000, 28000, 32000, 26000, 29000, 31000, 27000, 33000]; // Vitesses variables
            
            carousels.forEach(function(carousel, index) {
                new Swiper(carousel, {
                    slidesPerView: 'auto',
                    spaceBetween: 12,
                    speed: speeds[index % speeds.length],
                    loop: true,
                    loopedSlides: 50,
                    autoplay: {
                        delay: 0,
                        disableOnInteraction: false,
                        reverseDirection: index % 2 === 1 // Alternance de direction
                    },
                    freeMode: {
                        enabled: true,
                        momentum: false
                    },
                    allowTouchMove: true,
                    grabCursor: true
                });
            });
        });

        // Système d'inactivité
        let inactivityTimer;
        let countdownTimer;
        let remainingTime = <?php echo $delai_inactivite; ?>;
        const delayBeforeWarning = 10;
        const totalDelay = <?php echo $delai_inactivite; ?>;

        const indicator = document.getElementById('inactivity-indicator');
        const countdownEl = document.getElementById('countdown');
        const borneUrl = '<?php echo esc_url(get_permalink()); ?>';

        function resetInactivityTimer() {
            indicator.classList.remove('active');
            clearTimeout(inactivityTimer);
            clearInterval(countdownTimer);
            remainingTime = totalDelay;
            countdownEl.textContent = totalDelay;

            inactivityTimer = setTimeout(function() {
                showWarning();
            }, (totalDelay - delayBeforeWarning) * 1000);
        }

        function showWarning() {
            indicator.classList.add('active');
            remainingTime = delayBeforeWarning;
            countdownEl.textContent = remainingTime;

            countdownTimer = setInterval(function() {
                remainingTime--;
                countdownEl.textContent = remainingTime;

                if (remainingTime <= 0) {
                    clearInterval(countdownTimer);
                    if (window.location.href !== borneUrl) {
                        window.location.href = borneUrl;
                    } else {
                        resetInactivityTimer();
                    }
                }
            }, 1000);
        }

        const events = ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart', 'click'];
        events.forEach(function(event) {
            document.addEventListener(event, resetInactivityTimer, true);
        });

        resetInactivityTimer();
    })();
    </script>

    <?php wp_footer(); ?>
</body>
</html>
