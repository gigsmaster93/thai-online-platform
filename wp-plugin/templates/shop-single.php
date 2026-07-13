<?php
$id=get_query_var('top_id');
$posts=get_posts(['post_type'=>'thai_excursion','meta_key'=>'_ucoz_shop_id','meta_value'=>$id,'numberposts'=>1]);
if(!$posts){ TOP_Core::render_header(TOP_Core::lang('not_found')); TOP_Core::render_footer(); return; }
$p=$posts[0];
TOP_Core::render_header($p->post_title);
$price=get_post_meta($p->ID,'_thai_price',true);
if($price) echo '<div class="top-product-price top-single-price">'.esc_html(TOP_Core::lang('price_from')).' '.esc_html($price).' ฿</div>';
echo '<article class="top-product-single">'.wp_kses_post(wpautop($p->post_content)).'</article>';
TOP_Core::render_footer();
