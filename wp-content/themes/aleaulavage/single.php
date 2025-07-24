<?php
/**
 * The Template for displaying all single posts.
 */

get_header();

if ( have_posts() ) :
	while ( have_posts() ) :
		the_post();

		get_template_part( 'content', 'single' );

		// If comments are open or we have at least one comment, load up the comment template.
		if ( comments_open() || get_comments_number() ) :
			comments_template();
		endif;
	endwhile;
endif;

wp_reset_postdata();

$count_posts = wp_count_posts();

if ( $count_posts->publish > '1' ) :
	$next_post = get_next_post();
	$prev_post = get_previous_post();
?>
<?php
// Query pour récupérer les 3 derniers articles
$args = array(
    'post_type' => 'post',
    'posts_per_page' => 3,
    'orderby' => 'date',
    'order' => 'DESC',
);

$custom_query = new WP_Query($args);

if ($custom_query->have_posts()) :
?>

    <div class="custom-section-container container mb-5">
        <h2>Découvrez d'autres articles</h2>
        <div class="custom-section-grid">

            <?php while ($custom_query->have_posts()) : $custom_query->the_post(); ?>
                <div class="custom-section-item">
                    <?php if (has_post_thumbnail()) : ?>
                        <div class="custom-section-image">
                            <?php the_post_thumbnail(); ?>
                        </div>
                    <?php endif; ?>

                    <div class="custom-section-content d-flex flex-column justify-content-between">
						<div>
							<h3><?php the_title(); ?></h3>
                        	<a href="<?php the_permalink(); ?>" class="custom-section-button">Découvrir</a>
						</div>
						<p class="custom-section-date"><?php echo get_the_date(); ?></p>
                    </div>
                </div>
            <?php endwhile; ?>

        </div>
    </div>
	<div class="container d-flex justify-content-center">
	<a class="btn bg-secondary me-2 mb-5" href=<?php echo esc_url(home_url('boutique/')); ?>>
		<span>Découvrez notre boutique</span>
	</a>
	</div>
    <?php
    wp_reset_postdata(); // Réinitialisation de la boucle
endif;
?>
<?php
endif;

get_footer();
