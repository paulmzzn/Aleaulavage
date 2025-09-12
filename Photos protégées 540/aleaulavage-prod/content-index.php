<?php
/**
 * The template for displaying content in the index.php template.
 */
?>

<article id="post-<?php the_ID(); ?>" class="custom-section-item">
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
</article><!-- /#post-<?php the_ID(); ?> -->
