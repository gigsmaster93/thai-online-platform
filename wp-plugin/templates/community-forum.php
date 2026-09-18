<?php
if (!defined('ABSPATH')) exit;
$section=(int)get_query_var('top_section');$topic_id=(int)get_query_var('top_id');$current_page=TOP_Community::page();$sections=get_option('thai_forum_sections',[]);
$args=['post_type'=>'thai_forum_topic','post_status'=>'publish','posts_per_page'=>30,'paged'=>$current_page,'orderby'=>'meta_value_num','meta_key'=>'_thai_forum_updated','order'=>'DESC'];
if($topic_id){$args['meta_key']='_ucoz_forum_id';$args['meta_value']=$topic_id;$args['posts_per_page']=1;$args['paged']=1;}
elseif($section){$args['meta_query']=[['key'=>'_thai_forum_section','value'=>$section]];}
$q=($topic_id||$section)?new WP_Query($args):null;if($topic_id&&!$q->have_posts())status_header(404);
get_header(); ?>
<div class="page width clearfix thai-forum forumContent">
<p><a href="/">Главная</a> &raquo; <a href="/forum">Форум</a><?php if(isset($sections[$section]))echo ' &raquo; '.esc_html($sections[$section]['name']); ?></p>
<?php if(!$topic_id): ?>
<h1><?php echo esc_html($sections[$section]['name']??'Форум'); ?></h1>
<?php
// Aggregate only published topics; moderation drafts do not affect public counters.
global $wpdb;
$stats=[];
foreach($wpdb->get_results("SELECT m.meta_value section, COUNT(*) topics, SUM(p.comment_count) replies FROM {$wpdb->posts} p JOIN {$wpdb->postmeta} m ON m.post_id=p.ID AND m.meta_key='_thai_forum_section' WHERE p.post_type='thai_forum_topic' AND p.post_status='publish' GROUP BY m.meta_value") as $row)$stats[(int)$row->section]=$row;
$groups=$section?[$section]:array_unique(array_merge([3,37,4,5,13,51,1],array_keys(array_filter($sections,static function($s){return !(int)$s['parent'];}))));
foreach($groups as $group){
 if(!isset($sections[$group]))continue;
 $children=array_filter($sections,static function($s)use($group){return (int)$s['parent']===$group;});if(!$children)continue;
 echo '<table class="gTable forum-section-table" cellspacing="1" cellpadding="0"><tr><td class="gTableTop" colspan="4"><a class="catLink forum-title" href="/forum/'.(int)$group.'">'.esc_html($sections[$group]['name']).'</a></td></tr><tr><td class="gTableSubTop forum-stat-head"></td><td class="gTableSubTop">Форум</td><td class="gTableSubTop forum-stat-head">Темы</td><td class="gTableSubTop forum-stat-head">Ответы</td></tr>';
 foreach($children as $sid=>$s){
  echo '<tr><td class="forumIcoTd"><img src="/img/th_ico.png" alt=""></td><td class="forumNameTd"><a class="forum" href="/forum/'.(int)$sid.'">'.esc_html($s['name']).'</a>';
  if(!filter_var($s['description'],FILTER_VALIDATE_URL))echo '<div class="forumDescr">'.wp_kses_post($s['description']).'</div>';
  $sub=[];foreach($sections as $cid=>$child)if((int)$child['parent']===(int)$sid)$sub[]='<a href="/forum/'.(int)$cid.'">'.esc_html($child['name']).'</a>';
  if($sub)echo '<div class="forumDescr">Подфорумы: '.implode(', ',$sub).'</div>';
  echo '</td><td class="forum-stat">'.(int)($stats[$sid]->topics??0).'</td><td class="forum-stat">'.(int)($stats[$sid]->replies??0).'</td></tr>';
 }
 echo '</table>';
}
?>
<?php if($q){echo '<table class="gTable" width="100%" cellspacing="1" cellpadding="8"><tr><th class="gTableTop">Тема</th><th class="gTableTop">Сообщений</th></tr>';while($q->have_posts()){$q->the_post();$tid=(int)get_post_meta(get_the_ID(),'_ucoz_forum_id',true);echo '<tr><td class="threadNametd"><a class="threadLink" href="/forum/'.$section.'-'.$tid.'-1">'.esc_html(get_the_title()).'</a></td><td class="threadPostTd">'.get_comments_number().'</td></tr>';}echo '</table>';TOP_Community::pagination($q->found_posts,30,$current_page,'/forum/'.$section.'-0-{page}');wp_reset_postdata();} ?>
<?php else: while($q->have_posts()){$q->the_post(); ?>
<h1><?php the_title(); ?></h1>
<?php if(get_the_content())echo '<div class="postTable posttdMessage"><b>'.esc_html(get_post_meta(get_the_ID(),'_thai_forum_author',true)).'</b>'.wp_kses_post(wpautop(get_the_content())).'</div>'; $post_id=get_the_ID();$comments=get_comments(['post_id'=>$post_id,'status'=>'approve','orderby'=>'comment_date','order'=>'ASC','number'=>20,'offset'=>($current_page-1)*20]);
foreach($comments as $c){ ?>
<table class="postTable" width="100%" cellspacing="1" cellpadding="8" id="post<?php echo (int)get_comment_meta($c->comment_ID,'_ucoz_forum_post',true); ?>"><tr><td class="postTdTop" width="23%"><?php echo esc_html($c->comment_author); ?></td><td class="postTdTop"><?php echo esc_html(get_comment_date('d.m.Y H:i',$c)); ?></td></tr><tr><td class="posttdMessage" colspan="2"><?php echo wp_kses_post($c->comment_content); ?></td></tr></table>
<?php }TOP_Community::pagination(get_comments_number($post_id),20,$current_page,'/forum/'.$section.'-'.$topic_id.'-{page}');}wp_reset_postdata();endif; ?>
<?php if($section)TOP_Community::forum_form($section,$topic_id); ?>
</div>
<?php get_footer(); ?>