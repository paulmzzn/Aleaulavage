<?php
/**
 * The template for displaying the front page
 */

get_header();
?>

<main id="primary" class="site-main">

    <!-- Section: Hero / Intro (Background Image Style) -->
    <?php
    $hero_bg = get_the_post_thumbnail_url(get_the_ID(), 'full');
    if (!$hero_bg) {
        $hero_bg = get_template_directory_uri() . '/assets/images/hero-illustration.png';
    }
    ?>
    <section class="section-hero d-flex align-items-center position-relative"
        style="background-image: url('<?php echo esc_url($hero_bg); ?>'); background-size: cover; background-position: center; background-repeat: no-repeat;">
        <div class="hero-overlay"></div>
        <div class="container position-relative z-1">
            <div class="row">
                <div class="col-lg-8">
                    <h1 class="display-4 fw-bold text-white mb-4">Votre Station de Lavage <br><span
                            class="text-primary">Notre Priorité !</span></h1>
                    <p class="lead text-white mb-4" style="max-width: 600px;">Une sélection de pièces sur mesure liée
                        à notre expérience dans le lavage auto. Nos pièces sont reconnues pour leur robustesse et leur
                        fiabilité.</p>
                    <div class="d-flex gap-3">
                        <a href="<?php echo esc_url(home_url('/boutique/')); ?>"
                            class="btn btn-primary btn-lg px-4 rounded-pill">
                            Accéder à la boutique
                        </a>
                        <a href="<?php echo esc_url(home_url('/catalogue-2025/')); ?>"
                            class="btn btn-outline-light btn-lg px-4 rounded-pill">
                            Catalogue 2025
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Section: Featured Products (Coups de coeur) -->
    <section class="section-featured-products py-5 bg-light">
        <div class="container-fluid" style="padding-left: 10%; padding-right: 10%;">
            <div class="section-header mb-5 text-start">
                <!-- Badge Coups de coeur avec trait -->
                <div class="d-flex align-items-center gap-3 mb-3">
                    <span class="badge text-white px-3 py-2 rounded-pill" style="background-color: #f1bb69;">
                        <i class="fa-solid fa-heart me-1"></i> Coups de cœur
                    </span>
                    <div class="flex-grow-1"
                        style="height: 2px; background: linear-gradient(to right, #f1bb69, transparent);">
                    </div>
                </div>

                <!-- Titre principal -->
                <h2 class="modern-title mb-3">Découvrez nos produits <br><span class="text-primary">pour vos stations de
                        lavage</span></h2>
            </div>

            <!-- Grille de produits favoris -->
            <div class="woocommerce products-glass-grid">
                <?php echo do_shortcode('[products limit="10" columns="5" orderby="popularity" order="DESC"]'); ?>
            </div>
        </div>
    </section>

    <!-- WordPress Content (Managed via Editor) -->
    <?php
    if (have_posts()):
        while (have_posts()):
            the_post();
            the_content();
        endwhile;
    endif;
    ?>

</main>

<?php
get_footer();
