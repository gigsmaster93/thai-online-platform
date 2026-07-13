<?php
TOP_Core::render_header(TOP_Core::lang('shop_title'));
$q = new WP_Query(['post_type'=>'thai_excursion','posts_per_page'=>24,'paged'=>max(1,get_query_var('paged'))]);
echo '<section class="top-shop-grid">';
while($q->have_posts()){ $q->the_post();
    $price=get_post_meta(get_the_ID(),'_thai_price',true);
    $ucoz=get_post_meta(get_the_ID(),'_ucoz_shop_id',true);
    echo '<article class="top-product-card">';
    echo '<h2>'.esc_html(get_the_title()).'</h2>';
    if($price) echo '<div class="top-product-price">'.esc_html(TOP_Core::lang('price_from')).' '.esc_html($price).' ฿</div>';
    echo '<div class="top-product-excerpt">'.wp_kses_post(wpautop(get_the_excerpt())).'</div>';
    echo '<a class="top-button" href="'.esc_url(home_url('/shop/'.$ucoz.'/desc/'.get_post_field('post_name',get_the_ID()))).'">'.esc_html(TOP_Core::lang('read_more')).'</a>';
    echo '</article>';
}
wp_reset_postdata();
echo '</section>';
TOP_Core::render_footer();
