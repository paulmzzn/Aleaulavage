<?php
/**
 * The sidebar containing the main widget area
 *
 * @package Aleaulavage_V2
 */

if (!is_active_sidebar('sidebar-1')) {
    return;
}
?>

<aside id="secondary" class="widget-area" role="complementary">
    <?php dynamic_sidebar('sidebar-1'); ?>
</aside><!-- #secondary -->
