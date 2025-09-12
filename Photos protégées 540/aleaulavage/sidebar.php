<?php
/**
 * Sidebar Template.
 */
if (is_active_sidebar('primary_widget_area') || is_archive() || is_single()) :
?>
    <div id="sidebar" class="col-md-4 order-md-first col-sm-12 oder-sm-last">
        <?php
        if (is_active_sidebar('primary_widget_area')) :
        ?>
        
        <?php
        endif;

        // Ajoutez le formulaire de recherche ici
        get_template_part('searchform');

        if (is_archive() || is_single()) :
        ?>
            <div class="bg-faded sidebar-nav">
                <div id="primary-two" class="widget-area">
                    <?php
                    $output = '<ul class="recentposts">';
                    $recentposts_query = new WP_Query('posts_per_page=5'); // Max. 5 posts in Sidebar!
                    $month_check = null;
                    if ($recentposts_query->have_posts()) :
                        $output .= '<li><h3>' . esc_html__('Recent Posts', 'daz') . '</h3></li>';
                        while ($recentposts_query->have_posts()) :
                            $recentposts_query->the_post();
                            $output .= '<li>';
                            // Show monthly archive and link to months.
                            $month = get_the_date('F, Y');
                            if ($month !== $month_check) :
                                $output .= '<a href="' . esc_url(get_month_link(get_the_date('Y'), get_the_date('m'))) . '" title="' . esc_attr(get_the_date('F, Y')) . '">' . esc_html($month) . '</a>';
                            endif;
                            $month_check = $month;
                            $output .= '<h4><a href="' . esc_url(get_the_permalink()) . '" title="' . sprintf(esc_attr__('Permalink to %s', 'daz'), the_title_attribute('echo=0')) . '" rel="bookmark">' . esc_html(get_the_title()) . '</a></h4>';
                            $output .= '</li>';
                        endwhile;
                    endif;
                    wp_reset_postdata();
                    $output .= '</ul>';
                    echo $output;
                    ?>
                    <br />
                    <ul class="categories">
                        <li>
                            <h3><?php esc_html_e('Categories', 'daz'); ?></h3>
                        </li>
                        <?php
                        wp_list_categories('&title_li=');
                        if (!is_author()) :
                        ?>
                            <li>&nbsp;</li>
                            <li><a href="<?php echo esc_url(get_the_permalink(get_option('page_for_posts'))); ?>" class="btn btn-outline-secondary"><?php esc_html_e('more', 'daz'); ?></a></li>
                        <?php
                        endif;
                        ?>
                    </ul>
                </div><!-- /#primary-two -->
            </div>
        <?php
        endif;
        ?>
    </div><!-- /#sidebar -->
<?php
endif;
?>
