<?php
$id=get_query_var('top_id');
$posts=get_posts(['post_type'=>'page','meta_key'=>'_ucoz_site_id','meta_value'=>$id,'numberposts'=>1]);
if(!$posts){ TOP_Core::render_header(TOP_Core::lang('not_found')); TOP_Core::render_footer(); return; }
$p=$posts[0];
setup_postdata($p);
TOP_Core::render_header($p->post_title);
echo '<article class="top-page-content">'.apply_filters('the_content',$p->post_content).'</article>';
wp_reset_postdata();
TOP_Core::render_footer();
