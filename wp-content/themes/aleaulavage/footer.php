<?php
				// If Single or Archive (Category, Tag, Author or a Date based page).
				if ( is_single() || is_archive() ) :
			?>
					</div><!-- /.col -->
				</div><!-- /.row -->
			<?php
				endif;
			?>
		</main><!-- /#main -->
		<footer id="footer" class="pt-5 pb-3 text-light <?php if (!is_checkout()) echo 'left-search'?>">
			<div class="container">
				<img src="<?php echo esc_url(get_stylesheet_directory_uri()); ?>/img/logo/logo-white.svg" alt="logo" class="logo">
				<div class="row mt-4">
					<div class="col mt-2">
						<p><strong>Nous contacter</strong></p>
						<?php
							if ( is_active_sidebar( 'footer_contact_area' ) ) :
						?>
							<div class="col-md-12">
								<?php
									dynamic_sidebar( 'footer_contact_area' );

								?>
							</div>
						<?php
							endif;
						?>
					</div>
					<div class="col mt-2">
						<?php
							if ( has_nav_menu( 'footer-menu' ) ) : // See function register_nav_menus() in functions.php
								/*
									Loading WordPress Custom Menu (theme_location) ... remove <div> <ul> containers and show only <li> items!!!
									Menu name taken from functions.php!!! ... register_nav_menu( 'footer-menu', 'Footer Menu' );
									!!! IMPORTANT: After adding all pages to the menu, don't forget to assign this menu to the Footer menu of "Theme locations" /wp-admin/nav-menus.php (on left side) ... Otherwise the themes will not know, which menu to use!!!
								*/
								wp_nav_menu(array(
									'theme_location' => 'footer-menu',
									'container' => false,
									'menu_class' => '',
									'fallback_cb' => '__return_false',
									'items_wrap' => '<ul id="footer-menu" class="%2$s">%3$s</ul>'
								));

							endif;
						?>
					</div>
					<div class="col mt-2">
						<p><strong>Nos réseaux sociaux</strong></p>
						<?php
							if ( is_active_sidebar( 'footer_social_area' ) ) :
						?>
							<div class="col-md-12">
								<?php
									dynamic_sidebar( 'footer_social_area' );

								?>
							</div>
						<?php
							endif;
						?>
						<p class="mt-4"><strong>Horaire de contact</strong></p>
						<?php
							if ( is_active_sidebar( 'footer_schedule_area' ) ) :
						?>
							<div class="col-md-12">
								<?php
									dynamic_sidebar( 'footer_schedule_area' );

								?>
							</div>
						<?php
							endif;
						?>
					</div>
				</div>
				<div class="md-6 mt-5 row">
					<div class="col">
						<?php
							if ( has_nav_menu( 'footer-menu' ) ) : // See function register_nav_menus() in functions.php
								/*
									Loading WordPress Custom Menu (theme_location) ... remove <div> <ul> containers and show only <li> items!!!
									Menu name taken from functions.php!!! ... register_nav_menu( 'footer-menu', 'Footer Menu' );
									!!! IMPORTANT: After adding all pages to the menu, don't forget to assign this menu to the Footer menu of "Theme locations" /wp-admin/nav-menus.php (on left side) ... Otherwise the themes will not know, which menu to use!!!
								*/
								wp_nav_menu(array(
									'theme_location' => 'footer-legal',
									'container' => false,
									'menu_class' => '',
									'fallback_cb' => '__return_false',
									'items_wrap' => '<ul id="footer-legal" class="%2$s">%3$s</ul>'
								));

							endif;
						?>
					</div>
					<div class="col">
						<p class="text-md-end"><?php printf( esc_html__( '&copy; %1$s %2$s.', 'aleaulavage' ), date_i18n( 'Y' ), get_bloginfo( 'name', 'display' ) ); ?></p>
					</div>
				</div>
			</div><!-- /.container -->
		</footer><!-- /#footer -->
	</div><!-- /#wrapper -->
	<?php
		wp_footer();
	?>
</body>
</html>
