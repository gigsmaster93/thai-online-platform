<?php
if (!defined('ABSPATH')) exit;
$section=(int)get_query_var('top_section');$topic_id=(int)get_query_var('top_id');$current_page=TOP_Community::page();$sections=get_option('thai_forum_sections',[]);
$forum_q=$topic_id?'':sanitize_text_field(wp_unslash($_GET['forum_q']??''));$forum_by=max(1,min(4,(int)($_GET['forum_by']??1)));
$args=['post_type'=>'thai_forum_topic','post_status'=>'publish','posts_per_page'=>50,'paged'=>$current_page,'orderby'=>'meta_value_num','meta_key'=>'_thai_forum_updated','order'=>'DESC'];
if($topic_id){$args['meta_key']='_ucoz_forum_id';$args['meta_value']=$topic_id;$args['posts_per_page']=1;$args['paged']=1;}
elseif($section){
    $args['meta_query']=[['key'=>'_thai_forum_section','value'=>$section]];
    if($forum_q!==''){
        if($forum_by===2)$args['meta_query'][]=['key'=>'_thai_forum_description','value'=>$forum_q,'compare'=>'LIKE'];
        elseif($forum_by===3)$args['meta_query'][]=['key'=>'_thai_forum_author','value'=>$forum_q,'compare'=>'LIKE'];
        elseif($forum_by===4){
            global $wpdb;$like='%'.$wpdb->esc_like($forum_q).'%';
            $reply_posts=$wpdb->get_col($wpdb->prepare("SELECT DISTINCT comment_post_ID FROM {$wpdb->comments} WHERE comment_approved='1' AND comment_author LIKE %s",$like));
            $args['post__in']=$reply_posts?array_map('intval',$reply_posts):[0];
        }else $args['s']=$forum_q;
    }
}
$q=($topic_id||$section)?new WP_Query($args):null;if($topic_id&&!$q->have_posts())status_header(404);
$topic_post=($topic_id&&$q&&$q->posts)?$q->posts[0]:null;
$forum_total_pages=$topic_post?max(1,(int)ceil(get_comments_number($topic_post->ID)/20)):($q?max(1,(int)ceil($q->found_posts/50)):1);
$forum_page_url=$topic_id?'/forum/'.$section.'-'.$topic_id.'-{page}':'/forum/'.$section.'-0-{page}';
$filter_suffix=(!$topic_id&&$forum_q!=='')?'?'.http_build_query(['forum_by'=>$forum_by,'forum_q'=>$forum_q]):'';
$forum_switches=static function($pages,$page,$pattern,$suffix=''){
    echo '<ul class="switches switchesTbl forum-pages"><li class="pagesInfo">Страница <span class="curPage">'.(int)$page.'</span> из <span class="numPages">'.(int)$pages.'</span></li>';
    $nums=$pages<=7?range(1,$pages):array_unique([1,max(1,$page-1),$page,min($pages,$page+1),$pages]);sort($nums);$prev=0;
    foreach($nums as $n){if($prev&&$n>$prev+1)echo '<li class="switch"><span>…</span></li>';$href=str_replace('{page}',(string)$n,$pattern).$suffix;echo $n===$page?'<li class="switchActive">'.$n.'</li>':'<li class="switch"><a class="switchDigit" href="'.esc_url($href).'">'.$n.'</a></li>';$prev=$n;}
    if($page<$pages){$href=str_replace('{page}',(string)($page+1),$pattern).$suffix;echo '<li class="switch switch-next"><a class="switchNext" href="'.esc_url($href).'" title="Следующая"><span>»</span></a></li>';}
    echo '</ul>';
};
$crumbs=[];if($section){$sid=$section;$seen=[];while($sid&&isset($sections[$sid])&&!isset($seen[$sid])){$seen[$sid]=1;$crumbs[]=['id'=>$sid,'name'=>$sections[$sid]['name']];$sid=(int)($sections[$sid]['parent']??0);}$crumbs=array_reverse($crumbs);}
get_header(); ?>
<div class="page width clearfix thai-forum forumContent thai-forum-home">
<table class="thai-forum-nav" border="0" cellpadding="0" height="30" cellspacing="0" width="100%"><tr><td align="right">[ <a href="/forum">Разделы форума</a> · <a href="/contact">Связаться с администрацией</a> ]</td></tr></table><br>
<div class="ad-forum"><p style="text-align:center"><a href="https://affiliate.klook.com/redirect?aid=28346&amp;aff_adid=1164008&amp;k_site=https%3A%2F%2Fwww.klook.com%2F"><img src="/images/klook-on-good-page-horiz.png" alt="Pattaya excursions" title="Plan your holidays right now" style="width:60%"></a></p></div><br>
<?php if($section&&$topic_id): ?>
<table class="ThrTopButtonsTbl" border="0" width="100%" cellspacing="0" cellpadding="0"><tr class="ThrTopButtonsRow1"><td width="50%" class="ThrTopButtonsCl11"><?php $forum_switches($forum_total_pages,$current_page,$forum_page_url); ?></td><td align="right" class="frmBtns ThrTopButtonsCl12"><div><a href="#forum-form"><img alt="" style="margin:0;padding:0;border:0" title="Ответить" src="/img/forum_buttons/t_reply.png"></a> <a href="<?php echo esc_url('/forum/'.$section.'#forum-form'); ?>"><img alt="" style="margin:0;padding:0;border:0" title="Новая тема" src="/img/forum_buttons/t_new.png"></a></div></td></tr></table>
<table class="ThrForumBarTbl" border="0" width="100%" cellspacing="0" cellpadding="0"><tr class="ThrForumBarRow1"><td class="forumNamesBar ThrForumBarCl11 breadcrumbs" style="padding-top:3px;padding-bottom:5px"><a class="forumBar breadcrumb-item" href="/forum/">Форум</a><?php foreach($crumbs as $crumb)echo ' <span class="breadcrumb-sep">&raquo;</span> <a class="forumBar breadcrumb-item" href="/forum/'.(int)$crumb['id'].'">'.esc_html($crumb['name']).'</a>'; if($topic_post){$desc=get_post_meta($topic_post->ID,'_thai_forum_description',true);echo ' <span class="breadcrumb-sep">&raquo;</span> <a class="forumBarA" href="'.esc_url($forum_page_url?str_replace('{page}',(string)$current_page,$forum_page_url):'#').'">'.esc_html($topic_post->post_title).'</a>';if($desc)echo ' <span class="thDescr">('.esc_html($desc).')</span>';} ?></td></tr></table>
<?php elseif($section): ?>
<table class="FrmTopButtonsTbl" border="0" width="100%" cellspacing="0" cellpadding="0"><tr class="FrmTopButtonsRow1"><td class="FrmTopButtonsCl11" width="50%"><?php $forum_switches($forum_total_pages,$current_page,$forum_page_url,$filter_suffix); ?></td><td align="right" class="frmBtns FrmTopButtonsCl12"><div><a href="#forum-form"><img alt="" style="margin:0;padding:0;border:0" title="Новая тема" src="/img/forum_buttons/t_new.png"></a></div></td></tr></table>
<table class="FrmForumBarTbl" border="0" width="100%" cellspacing="0" cellpadding="0"><tr class="FrmForumBarRow1"><td class="forumNamesBar FrmForumBarCl11 breadcrumbs" style="padding-top:3px;padding-bottom:5px"><a class="forumBar breadcrumb-item" href="/forum/">Форум</a><?php foreach($crumbs as $i=>$crumb){$last=$i===array_key_last($crumbs);echo ' <span class="breadcrumb-sep">&raquo;</span> <a class="'.($last?'forumBarA breadcrumb-curr':'forumBar breadcrumb-item').'" href="/forum/'.(int)$crumb['id'].'">'.esc_html($crumb['name']).'</a>';} ?></td></tr></table>
<div id="forum_filter"><span id="filter_by">Фильтр по:</span><form method="get" action="<?php echo esc_url('/forum/'.$section); ?>"><select id="forum_filter_select" name="forum_by"><option value="1"<?php selected($forum_by,1); ?>>Названию темы</option><option value="2"<?php selected($forum_by,2); ?>>Описанию</option><option value="3"<?php selected($forum_by,3); ?>>Автору темы</option><option value="4"<?php selected($forum_by,4); ?>>Автору ответа</option></select><input type="text" id="filter_word" name="forum_q" value="<?php echo esc_attr($forum_q); ?>"><input type="submit" value="Ok"></form></div><div class="clear2"></div>
<?php endif; ?>
<?php if(!$topic_id): ?>
<?php
// Aggregate only published topics; moderation drafts do not affect public counters.
global $wpdb;
$stats=[];$latest=[];
$public_topics=$wpdb->get_results("SELECT p.ID,p.post_title,p.comment_count,m.meta_value section,COALESCE(u.meta_value,UNIX_TIMESTAMP(p.post_date)) updated FROM {$wpdb->posts} p JOIN {$wpdb->postmeta} m ON m.post_id=p.ID AND m.meta_key='_thai_forum_section' LEFT JOIN {$wpdb->postmeta} u ON u.post_id=p.ID AND u.meta_key='_thai_forum_updated' WHERE p.post_type='thai_forum_topic' AND p.post_status='publish'");
foreach($public_topics as $t){$sid=(int)$t->section;$seen=[];while($sid&&!isset($seen[$sid])){$seen[$sid]=true;if(!isset($stats[$sid]))$stats[$sid]=(object)['topics'=>0,'replies'=>0];$stats[$sid]->topics++;$stats[$sid]->replies+=(int)$t->comment_count;if(!isset($latest[$sid])||(int)$t->updated>(int)$latest[$sid]->updated)$latest[$sid]=$t;$sid=(int)($sections[$sid]['parent']??0);}}
$section_order=[7, 6, 38, 34, 29, 9, 10, 33, 11, 25, 14, 61, 17, 50, 40, 18, 52, 53, 54, 55, 56, 57, 58, 59, 20, 26, 27];
uksort($sections,static function($a,$b)use($section_order){$ia=array_search((int)$a,$section_order,true);$ib=array_search((int)$b,$section_order,true);return ($ia===false?999:$ia)<=>($ib===false?999:$ib);});
$groups=$section?[$section]:array_unique(array_merge([3,37,4,5,13,51,1],array_keys(array_filter($sections,static function($s){return !(int)$s['parent'];}))));
foreach($groups as $group){
 if(!isset($sections[$group]))continue;
 $children=array_filter($sections,static function($s)use($group){return (int)$s['parent']===$group;});if(!$children)continue;
 echo '<div class="gDivLeft"><div class="gDivRight"><table class="gTable forum-section-table" cellspacing="1" cellpadding="0"><tr><td class="gTableTop" colspan="5"><img src="/img/th_ico.png" alt="" style="vertical-align:middle"> <a class="catLink forum-title" href="/forum/'.(int)$group.'">'.esc_html($sections[$group]['name']).'</a></td></tr><tr><td width="5%" class="gTableSubTop forum-stat-head">&nbsp;</td><td class="gTableSubTop">Форум</td><td width="8%" class="gTableSubTop forum-stat-head" align="center">Темы</td><td width="8%" class="gTableSubTop forum-stat-head" align="center">Ответы</td><td width="30%" class="gTableSubTop forum-stat-head">Обновления</td></tr>';
 foreach($children as $sid=>$s){
  echo '<tr><td class="forumIcoTd"><img src="/img/forum_ico_no_new.png" alt="" style="max-width:60px;max-height:60px"></td><td class="forumNameTd"><a class="forum" href="/forum/'.(int)$sid.'">'.esc_html($s['name']).'</a>';
  if(!filter_var($s['description'],FILTER_VALIDATE_URL))echo '<div class="forumDescr">'.wp_kses_post($s['description']).'</div>';
  $sub=[];foreach($sections as $cid=>$child)if((int)$child['parent']===(int)$sid)$sub[]='<a href="/forum/'.(int)$cid.'">'.esc_html($child['name']).'</a>';
  if($sub)echo '<div class="subforumDescr">Подфорумы: '.implode(' | ',$sub).'</div>';
  echo '</td><td class="forumThreadTd forum-stat" align="center">'.(int)($stats[$sid]->topics??0).'</td><td class="forum-stat">'.(int)($stats[$sid]->replies??0).'</td><td class="forumLastPostTd forum-stat-update">';
  if(isset($latest[$sid])){$t=$latest[$sid];$tid=(int)get_post_meta($t->ID,'_ucoz_forum_id',true);$comments=get_comments(['post_id'=>$t->ID,'status'=>'approve','number'=>1,'orderby'=>'comment_date','order'=>'DESC']);$last=$comments[0]??null;$url='/forum/'.(int)$t->section.'-'.$tid.'-'.max(1,(int)ceil($t->comment_count/20));echo '<a class="forumLastPostLink" href="'.esc_url($url).'">'.esc_html($last?get_comment_date('d.m.Y H:i',$last):wp_date('d.m.Y H:i',(int)$t->updated)).'</a><br>Тема: <a class="forumLastPostLink" href="'.esc_url($url).'">'.esc_html(mb_strimwidth($t->post_title,0,45,'…')).'</a><br>Сообщение от: '.esc_html($last?$last->comment_author:get_post_meta($t->ID,'_thai_forum_author',true));}
  echo '</td></tr>';
 }
 echo '</table></div></div><br>';
}
?>
<?php if($q){ ?>
<div class="gDivLeft"><div class="gDivRight"><table class="gTable forum-topics-table" width="100%" cellspacing="1" cellpadding="0"><tr><td class="gTableTop" colspan="6"><?php echo esc_html($sections[$section]['name']??'Темы'); ?></td></tr><tr><td class="gTableSubTop thai-topic-extra" width="8%"></td><td class="gTableSubTop">Тема</td><td class="gTableSubTop thai-topic-extra" width="7%">Ответы</td><td class="gTableSubTop thai-topic-extra" width="6%">Просмотры</td><td class="gTableSubTop thai-topic-extra" width="14%">Автор темы</td><td class="gTableSubTop thai-topic-extra" width="21%">Обновления</td></tr>
<?php while($q->have_posts()){$q->the_post();$pid=get_the_ID();$tid=(int)get_post_meta($pid,'_ucoz_forum_id',true);$url='/forum/'.$section.'-'.$tid.'-1';$total=(int)get_comments_number($pid);$replies=get_the_content()?$total:max(0,$total-1);$last=get_comments(['post_id'=>$pid,'status'=>'approve','number'=>1,'orderby'=>'comment_date','order'=>'DESC']);$last=$last[0]??null;$views=get_post_meta($pid,'_thai_forum_views',true); ?>
<tr><td class="threadIcoTd thai-topic-extra" align="center"><img src="/.s/img/fr/ic/1/f_norm_nonew.gif" alt=""></td><td class="threadNametd"><a class="threadLink" href="<?php echo esc_url($url); ?>"><?php the_title(); ?></a><div class="threadDescr"><?php echo esc_html(get_post_meta($pid,'_thai_forum_description',true)); ?></div></td><td class="threadPostTd thai-topic-extra" align="center"><?php echo $replies; ?></td><td class="threadViewTd thai-topic-extra" align="center"><?php echo $views!==''?(int)$views:'—'; ?></td><td class="threadAuthTd thai-topic-extra" align="center"><?php echo esc_html(get_post_meta($pid,'_thai_forum_author',true)); ?></td><td class="threadLastPostTd thai-topic-extra"><a href="<?php echo esc_url('/forum/'.$section.'-'.$tid.'-'.max(1,(int)ceil($total/20))); ?>"><?php echo esc_html($last?get_comment_date('d.m.Y H:i',$last):get_the_date('d.m.Y H:i')); ?></a><?php if($last)echo '<br>Сообщение от: '.esc_html($last->comment_author); ?></td></tr>
<?php } ?></table></div></div>
<?php TOP_Community::pagination($q->found_posts,50,$current_page,'/forum/'.$section.'-0-{page}');wp_reset_postdata();} ?>
<?php else: while($q->have_posts()){$q->the_post();$post_id=get_the_ID(); ?>
<div class="gDivLeft"><div class="gDivRight"><table class="gTable threadpage-posts-table" width="100%" cellspacing="1" cellpadding="0"><tr><td class="gTableTop"><span class="forum-title"><?php the_title(); ?></span></td></tr>
<?php if(get_the_content()&&$current_page===1)echo '<tr><td class="thai-forum-post-wrap"><div class="ucoz-forum-post">'.wp_kses_post(wpautop(get_the_content())).'</div></td></tr>'; $comments=get_comments(['post_id'=>$post_id,'status'=>'approve','orderby'=>'comment_date','order'=>'ASC','number'=>20,'offset'=>($current_page-1)*20]);$number=($current_page-1)*20;
foreach($comments as $c){$number++;$legacy=(int)get_comment_meta($c->comment_ID,'_ucoz_forum_post',true);$anchor=$legacy?'post'.$legacy:'comment'.$c->comment_ID;$avatar=get_comment_meta($c->comment_ID,'_thai_forum_avatar',true);$rank=get_comment_meta($c->comment_ID,'_thai_forum_rank',true);$attachments=get_comment_meta($c->comment_ID,'_thai_forum_attachments',true); ?>
<tr id="<?php echo esc_attr($anchor); ?>"><td class="thai-forum-post-wrap"><table class="postTable" width="100%" cellspacing="1" cellpadding="2"><tr><td class="postTdTop" width="23%" align="center"><span class="postUser"><?php echo esc_html($c->comment_author); ?></span></td><td class="postTdTop">Дата: <?php echo esc_html(get_comment_date('d.m.Y H:i',$c)); ?> | Сообщение # <a href="#<?php echo esc_attr($anchor); ?>"><?php echo $number; ?></a></td></tr><tr><td class="postTdInfo" valign="top"><?php if($avatar)echo '<img class="userAvatar" src="'.esc_url($avatar).'" alt="'.esc_attr($c->comment_author).'">';if($rank)echo '<div class="postRankName">'.esc_html($rank).'</div>'; ?></td><td class="posttdMessage" valign="top"><span class="ucoz-forum-post"><?php echo wp_kses_post($c->comment_content); ?></span><?php echo wp_kses_post($attachments); ?></td></tr><tr><td class="postBottom"></td><td class="postBottom"><a href="#forum-form">Ответить</a> <a href="#land-full" style="float:right">Вверх</a></td></tr></table></td></tr>
<?php } ?></table></div></div>
<?php TOP_Community::pagination(get_comments_number($post_id),20,$current_page,'/forum/'.$section.'-'.$topic_id.'-{page}');}wp_reset_postdata();endif; ?>
<?php if($section)TOP_Community::forum_form($section,$topic_id); ?>
</div>
<?php get_footer(); ?>