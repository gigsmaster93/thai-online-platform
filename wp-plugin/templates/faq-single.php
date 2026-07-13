<?php
$id = get_query_var('top_id');
$posts = get_posts(['post_type'=>'thai_faq','meta_key'=>'_ucoz_faq_id','meta_value'=>$id,'numberposts'=>1]);
if(!$posts){ TOP_Core::render_header(TOP_Core::lang('not_found')); TOP_Core::render_footer(); return; }
$p=$posts[0];
TOP_Core::render_header($p->post_title);
echo '<article class="top-faq-single">'.wp_kses_post(wpautop($p->post_content)).'</article>';
TOP_Core::render_footer();
