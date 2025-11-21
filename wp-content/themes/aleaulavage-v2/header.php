<?php
/**
 * The header for our theme
 */
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="profile" href="https://gmpg.org/xfn/11">
    <link rel="preconnect" href="<?php echo esc_url(home_url()); ?>">

    <!-- Favicons (Ideally these should be handled via Site Icon in Customizer, but keeping for now) -->
    <link rel="apple-touch-icon" sizes="180x180"
        href="<?php echo get_template_directory_uri(); ?>/assets/images/favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32"
        href="<?php echo get_template_directory_uri(); ?>/assets/images/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16"
        href="<?php echo get_template_directory_uri(); ?>/assets/images/favicon/favicon-16x16.png">
    <link rel="manifest" href="<?php echo get_template_directory_uri(); ?>/assets/images/favicon/site.webmanifest">
    <link rel="mask-icon" href="<?php echo get_template_directory_uri(); ?>/assets/images/favicon/safari-pinned-tab.svg"
        color="#5899E2">
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="theme-color" content="#ffffff">

    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
    <?php wp_body_open(); ?>

    <div id="page" class="site">
        <header id="masthead" class="site-header">
            <div class="fixed-top">
                <?php get_template_part('template-parts/header/promo-banner'); ?>

                <?php get_template_part('template-parts/header/navigation'); ?>

                <?php get_template_part('template-parts/header/category-bar'); ?>
            </div>

            <?php get_template_part('template-parts/header/mobile-menu'); ?>

            <?php get_template_part('template-parts/header/cart-offcanvas'); ?>
        </header><!-- #masthead -->