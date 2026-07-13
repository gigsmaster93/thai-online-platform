<?php
TOP_Core::render_header(TOP_Core::lang('publ_title'));
$q = new WP_Query(['post_type'=>'thai_article','posts_per_page'=>20,'paged'=>max(1,get_query_var('paged'))]);
echo '<section class="top-publ-list">';
while($q->have_posts()){ $q->the_post();
    $ucoz = get_post_meta(get_the_ID(), '_ucoz_publ_id', true);
    echo '<article class="top-content-card">';
    echo '<h2><a href="'.esc_url(home_url('/publ/1-'.$ucoz)).'">'.esc_html(get_the_title()).'</a></h2>';
    echo '<div class="top-content-date">'.esc_html(get_the_date('d.m.Y')).'</div>';
    echo '<div class="top-content-excerpt">'.wp_kses_post(wpautop(get_the_excerpt() ?: wp_trim_words(get_the_content(), 45))).'</div>';
    echo '</article>';
}
wp_reset_postdata();
echo '</section>';
TOP_Core::render_footer();
