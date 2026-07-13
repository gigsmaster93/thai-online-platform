<?php
TOP_Core::render_header(TOP_Core::lang('guestbook_title'));
$q = new WP_Query(['post_type'=>'thai_guestbook','posts_per_page'=>20,'paged'=>max(1,get_query_var('paged'))]);
echo '<section class="top-guestbook-list">';
while($q->have_posts()){ $q->the_post();
    echo '<article class="top-review-card">';
    echo '<div class="top-review-head"><strong>'.esc_html(get_the_title()).'</strong><span>'.esc_html(get_the_date('d.m.Y')).'</span></div>';
    echo '<div class="top-review-body">'.wp_kses_post(wpautop(get_the_content())).'</div>';
    echo '</article>';
}
wp_reset_postdata();
echo '</section>';
TOP_Core::render_footer();
