<?php
TOP_Core::render_header(TOP_Core::lang('faq_title'));
$q = new WP_Query(['post_type'=>'thai_faq','posts_per_page'=>100,'orderby'=>'date','order'=>'ASC']);
echo '<section class="top-faq-list">';
while($q->have_posts()){ $q->the_post();
    $ucoz = get_post_meta(get_the_ID(), '_ucoz_faq_id', true);
    echo '<article class="top-faq-item">';
    echo '<h2><a href="'.esc_url(home_url('/faq/1-'.$ucoz)).'">'.esc_html(get_the_title()).'</a></h2>';
    echo '<div class="top-faq-answer">'.wp_kses_post(wpautop(get_the_content())).'</div>';
    echo '</article>';
}
wp_reset_postdata();
echo '</section>';
TOP_Core::render_footer();
