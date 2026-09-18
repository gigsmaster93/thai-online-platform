<?php
if (!defined('ABSPATH')) exit;
$section=(int)get_query_var('top_section');$topic_id=(int)get_query_var('top_id');$page=TOP_Community::page();$sections=get_option('thai_forum_sections',[]);
$args=['post_type'=>'thai_forum_topic','post_status'=>'publish','posts_per_page'=>30,'paged'=>$page,'orderby'=>'meta_value_num','meta_key'=>'_thai_forum_updated','order'=>'DESC'];
if($topic_id){$args['meta_key']='_ucoz_forum_id';$args['meta_value']=$topic_id;$args['posts_per_page']=1;$args['paged']=1;}
elseif($section){$args['meta_query']=[['key'=>'_thai_forum_section','value'=>$section]];}
$q=($topic_id||$section)?new WP_Query($args):null;if($topic_id&&!$q->have_posts())status_header(404);
get_header(); ?>
<div class="page width clearfix thai-forum">
<p><a href="/">Главная</a> &raquo; <a href="/forum">Форум</a><?php if(isset($sections[$section]))echo ' &raquo; '.esc_html($sections[$section]['name']); ?></p>
<?php if(!$topic_id): ?>
<h1><?php echo esc_html($sections[$section]['name']??'Форум'); ?></h1>
<table class="gTable forum-section-table" width="100%" cellspacing="1" cellpadding="8">
<tr><th class="gTableTop">Раздел</th></tr>
<?php foreach($sections as $sid=>$s){if((int)$s['parent']!==$section)continue;echo '<tr><td class="forumNameTd"><a class="forum" href="/forum/'.(int)$sid.'">'.esc_html($s['name']).'</a><div class="forumDescr">'.wp_kses_post($s['description']).'</div></td></tr>';} ?>
</table>
<?php if($q){echo '<table class="gTable" width="100%" cellspacing="1" cellpadding="8"><tr><th class="gTableTop">Тема</th><th class="gTableTop">Сообщений</th></tr>';while($q->have_posts()){$q->the_post();$tid=(int)get_post_meta(get_the_ID(),'_ucoz_forum_id',true);echo '<tr><td class="threadNametd"><a class="threadLink" href="/forum/'.$section.'-'.$tid.'-1">'.esc_html(get_the_title()).'</a></td><td class="threadPostTd">'.get_comments_number().'</td></tr>';}echo '</table>';TOP_Community::pagination($q->found_posts,30,$page,'/forum/'.$section.'-0-{page}');wp_reset_postdata();} ?>
<?php else: while($q->have_posts()){$q->the_post(); ?>
<h1><?php the_title(); ?></h1>
<?php if(get_the_content())echo '<div class="postTable posttdMessage"><b>'.esc_html(get_post_meta(get_the_ID(),'_thai_forum_author',true)).'</b>'.wp_kses_post(wpautop(get_the_content())).'</div>'; $post_id=get_the_ID();$comments=get_comments(['post_id'=>$post_id,'status'=>'approve','orderby'=>'comment_date','order'=>'ASC','number'=>20,'offset'=>($page-1)*20]);
foreach($comments as $c){ ?>
<table class="postTable" width="100%" cellspacing="1" cellpadding="8" id="post<?php echo (int)get_comment_meta($c->comment_ID,'_ucoz_forum_post',true); ?>"><tr><td class="postTdTop" width="23%"><?php echo esc_html($c->comment_author); ?></td><td class="postTdTop"><?php echo esc_html(get_comment_date('d.m.Y H:i',$c)); ?></td></tr><tr><td class="posttdMessage" colspan="2"><?php echo wp_kses_post($c->comment_content); ?></td></tr></table>
<?php }TOP_Community::pagination(get_comments_number($post_id),20,$page,'/forum/'.$section.'-'.$topic_id.'-{page}');}wp_reset_postdata();endif; ?>
<?php if($section)TOP_Community::forum_form($section,$topic_id); ?>
</div>
<?php get_footer(); ?>