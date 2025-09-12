<?php
/**
 * My Account page - refonte moderne et épurée
 */
defined('ABSPATH') || exit;
?>
<div class="account-wrapper container py-5">
  <div class="row justify-content-center">
    <aside class="col-12 col-md-4 col-lg-3 mb-4">
      <nav class="account-nav card shadow-sm">
        <ul class="list-group list-group-flush">
          <?php foreach (wc_get_account_menu_items() as $endpoint => $label) : ?>
            <li class="list-group-item <?php echo wc_get_account_endpoint_url($endpoint) === esc_url_raw( add_query_arg( array(), $wp->request ) ) ? 'active' : ''; ?>">
              <a href="<?php echo esc_url(wc_get_account_endpoint_url($endpoint)); ?>">
                <?php echo esc_html($label); ?>
              </a>
            </li>
          <?php endforeach; ?>
        </ul>
      </nav>
    </aside>
    <main class="col-12 col-md-8 col-lg-7">
      <section class="account-content card shadow-sm p-4">
        <?php do_action('woocommerce_account_content'); ?>
      </section>
    </main>
  </div>
</div>