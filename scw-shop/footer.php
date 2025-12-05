<?php
/**
 * The footer for our theme
 *
 * @package SCW_Shop
 */

// Récupérer les données du footer depuis la page
$footer_data = scw_shop_parse_footer_content();
?>

<footer class="site-footer">
	<div class="footer-top">
		<div class="footer-container">
			<div class="footer-brand">
				<div class="footer-logo">SCW<span>SHOP</span></div>
				<p><?php echo esc_html( $footer_data['description'] ); ?></p>
				<div class="social-links">
					<?php if ( ! empty( $footer_data['facebook'] ) ) : ?>
						<a href="<?php echo esc_url( $footer_data['facebook'] ); ?>" target="_blank" rel="noopener" aria-label="Facebook">
							<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"></path></svg>
						</a>
					<?php endif; ?>
					<?php if ( ! empty( $footer_data['linkedin'] ) ) : ?>
						<a href="<?php echo esc_url( $footer_data['linkedin'] ); ?>" target="_blank" rel="noopener" aria-label="LinkedIn">
							<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"></path><rect x="2" y="9" width="4" height="12"></rect><circle cx="4" cy="4" r="2"></circle></svg>
						</a>
					<?php endif; ?>
					<?php if ( ! empty( $footer_data['twitter'] ) ) : ?>
						<a href="<?php echo esc_url( $footer_data['twitter'] ); ?>" target="_blank" rel="noopener" aria-label="Twitter">
							<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><path d="M23 3a10.9 10.9 0 0 1-3.14 1.53 4.48 4.48 0 0 0-7.86 3v1A10.66 10.66 0 0 1 3 4s-4 9 5 13a11.64 11.64 0 0 1-7 2c9 5 20 0 20-11.5a4.5 4.5 0 0 0-.08-.83A7.72 7.72 0 0 0 23 3z"></path></svg>
						</a>
					<?php endif; ?>
				</div>
			</div>

			<div class="footer-links">
				<h4>Boutique</h4>
				<ul>
					<?php foreach ( $footer_data['shop_links'] as $link ) : ?>
						<li><a href="<?php echo esc_url( $link['url'] ); ?>"><?php echo esc_html( $link['text'] ); ?></a></li>
					<?php endforeach; ?>
				</ul>
			</div>

			<div class="footer-links">
				<h4>Support</h4>
				<ul>
					<?php foreach ( $footer_data['support_links'] as $link ) : ?>
						<li><a href="<?php echo esc_url( $link['url'] ); ?>"><?php echo esc_html( $link['text'] ); ?></a></li>
					<?php endforeach; ?>
				</ul>
			</div>

			<div class="footer-newsletter">
				<h4><?php echo esc_html( $footer_data['newsletter_title'] ); ?></h4>
				<p><?php echo esc_html( $footer_data['newsletter_description'] ); ?></p>
				<div class="newsletter-form">
					<input type="email" placeholder="Votre email pro..." />
					<button>OK</button>
				</div>
				<div class="footer-contact">
					<div class="contact-item">
						<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
							<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
						</svg>
						<span><?php echo esc_html( $footer_data['phone'] ); ?></span>
					</div>
					<div class="contact-item">
						<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
							<path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
							<polyline points="22,6 12,13 2,6"></polyline>
						</svg>
						<span><?php echo esc_html( $footer_data['email'] ); ?></span>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="footer-bottom">
		<div class="footer-container">
			<p><?php echo esc_html( $footer_data['copyright'] ); ?></p>
			<div class="legal-links">
				<?php foreach ( $footer_data['legal_links'] as $link ) : ?>
					<a href="<?php echo esc_url( $link['url'] ); ?>"><?php echo esc_html( $link['text'] ); ?></a>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
</footer>

<?php
// Reseller mode switcher (floating button)
get_template_part( 'template-parts/reseller-mode-switcher' );

// Cookie consent banner
get_template_part( 'template-parts/cookie-consent' );
?>

<?php wp_footer(); ?>
</body>
</html>
