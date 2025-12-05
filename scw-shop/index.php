<?php
/**
 * The main template file
 *
 * @package SCW_Shop
 */

get_header(); ?>

<main id="main" class="site-main">
    <div class="container">
        <?php
        if ( have_posts() ) :
            while ( have_posts() ) :
                the_post();
                ?>
                <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                    <header class="entry-header">
                        <h2 class="entry-title">
                            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                        </h2>
                    </header>

                    <div class="entry-content">
                        <?php the_excerpt(); ?>
                    </div>

                    <footer class="entry-footer">
                        <a href="<?php the_permalink(); ?>" class="read-more">Lire la suite</a>
                    </footer>
                </article>
                <?php
            endwhile;

            the_posts_navigation();
        else :
            ?>
            <p>Aucun contenu trouvé.</p>
            <?php
        endif;
        ?>
    </div>
</main>

<?php get_footer(); ?>
