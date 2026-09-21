<?php
if (!defined('ABSPATH')) exit;
$service=get_query_var('top_forum_service');
$param=static function($key,$default=''){return isset($_GET[$key])&&is_scalar($_GET[$key])?sanitize_text_field(wp_unslash($_GET[$key])):$default;};
$page=max(1,(int)get_query_var('top_page'),(int)$param('pg','1'));
$per=20;$total=0;$rows=[];$q='';$author='';$members=[];$groups=[];$group='';$sort='name';
$url=TOP_Forum_Services::url($service);
if($service==='recent'){
 $args=TOP_Forum_Services::comment_args();$total=(int)get_comments(array_merge($args,['count'=>true]));
 $rows=get_comments(array_merge($args,['number'=>$per,'offset'=>($page-1)*$per]));
}elseif($service==='members'){
 $members=get_option('thai_forum_public_members',[]);$groups=array_values(array_unique(array_column($members,'group')));sort($groups);
 $q=mb_substr($param('user'),0,100);$group=$param('group');$sort=$param('sort','name');$per=10;
 $members=array_values(array_filter($members,static function($m)use($q,$group){return ($q===''||mb_stripos($m['name'],$q)!==false)&&($group===''||$m['group']===$group);}));
 if($sort==='posts')usort($members,fn($a,$b)=>($b['posts']<=>$a['posts'])?:strcmp($a['name'],$b['name']));
 elseif($sort==='date')usort($members,fn($a,$b)=>strtotime($b['date'])<=>strtotime($a['date']));
 $total=count($members);$rows=array_slice($members,($page-1)*$per,$per);$url=add_query_arg(['user'=>$q,'group'=>$group,'sort'=>$sort],$url);
}elseif($service==='search'){
 global $wpdb;$q=mb_substr($param('q'),0,200);$author=mb_substr($param('author'),0,100);
 if($q!==''||$author!==''){
  $where="p.post_type='thai_forum_topic' AND p.post_status='publish'";
  if($q!==''){$like='%'.$wpdb->esc_like($q).'%';$where.=$wpdb->prepare(' AND (p.post_title LIKE %s OR p.post_content LIKE %s OR c.comment_content LIKE %s)',$like,$like,$like);}
  if($author!=='')$where.=$wpdb->prepare(' AND c.comment_author=%s',$author);
  $ids=$wpdb->get_col("SELECT DISTINCT p.ID FROM {$wpdb->posts} p LEFT JOIN {$wpdb->comments} c ON c.comment_post_ID=p.ID AND c.comment_approved='1' WHERE $where");
  $query=new WP_Query(['post_type'=>'thai_forum_topic','post_status'=>'publish','post__in'=>$ids?:[0],'posts_per_page'=>$per,'paged'=>$page,'meta_key'=>'_thai_forum_updated','orderby'=>'meta_value_num','order'=>'DESC']);$rows=$query->posts;$total=(int)$query->found_posts;
 }
 $url=add_query_arg(['q'=>$q,'author'=>$author],$url);
}
if($service!=='rules'&&$page>max(1,(int)ceil($total/$per))){global $wp_query;$wp_query->set_404();status_header(404);include TOP_PLUGIN_DIR.'templates/not-found.php';return;}
status_header(200);get_header();
?>
<style>
.thai-forum-services{max-width:100%;box-sizing:border-box;overflow-wrap:anywhere}.thai-forum-services .service-panel{padding:16px;background:#fff;border:1px solid #ddd;border-radius:8px}.thai-forum-services form{display:flex;flex-wrap:wrap;gap:10px;align-items:end;margin:18px 0}.thai-forum-services label{display:flex;flex-direction:column;gap:4px}.thai-forum-services input,.thai-forum-services select{max-width:100%;box-sizing:border-box}.thai-forum-services .service-table-wrap{max-width:100%;overflow-x:auto}.thai-forum-services table{width:100%;border-collapse:collapse}.thai-forum-services td,.thai-forum-services th{padding:10px;border-bottom:1px solid #ddd;text-align:left}.thai-forum-services .member-avatar{width:40px;height:40px;object-fit:contain}.thai-forum-services .service-item{padding:14px 0;border-bottom:1px solid #ddd}.thai-forum-services .page-numbers{display:inline-block;padding:6px 10px}.thai-forum-services h1{font-size:25px}.thai-forum-services .service-item p{margin:8px 0}@media(max-width:600px){.thai-forum-services .service-panel{padding:10px}.thai-forum-services .members-table{min-width:650px}.thai-forum-services input{width:100%}}
</style>
<div class="page width clearfix thai-forum thai-forum-services"><div class="forumContent">
<?php TOP_Forum_Services::navigation(); ?>
<div class="service-panel"><p><a href="<?php echo esc_url(home_url('/forum')); ?>">Форум</a> » <?php echo esc_html(TOP_Forum_Services::labels()[$service]); ?></p>
<h1><?php echo esc_html(TOP_Forum_Services::labels()[$service]); ?></h1>
<?php if($service==='rules'): ?>
<div class="forum-rules-body"><?php echo wp_kses_post(file_get_contents(TOP_PLUGIN_DIR.'data/forum-rules.html')); ?></div>
<?php elseif($service==='members'): ?>
<form action="<?php echo esc_url(TOP_Forum_Services::url('members')); ?>" method="get"><label>Имя участника<input type="search" name="user" value="<?php echo esc_attr($q); ?>" maxlength="100"></label><label>Группа<select name="group"><option value="">Все группы</option><?php foreach($groups as $g)echo '<option value="'.esc_attr($g).'" '.selected($group,$g,false).'>'.esc_html($g).'</option>'; ?></select></label><label>Сортировка<select name="sort"><?php foreach(['name'=>'По имени','posts'=>'По сообщениям','date'=>'По дате регистрации'] as $value=>$label)echo '<option value="'.esc_attr($value).'" '.selected($sort,$value,false).'>'.esc_html($label).'</option>'; ?></select></label><button type="submit">Найти</button></form>
<p class="service-count">Найдено участников: <b><?php echo (int)$total; ?></b></p>
<div class="service-table-wrap"><table class="members-table"><thead><tr><th>Никнейм</th><th>Аватар</th><th>Ранг</th><th>Группа</th><th>Дата регистрации</th><th>Сообщения</th></tr></thead><tbody>
<?php foreach($rows as $m): ?><tr><td><?php echo esc_html($m['name']); ?></td><td><?php if($m['avatar']){ $avatar=preg_replace('~^https?://thai-online\.org~','',$m['avatar']);if(str_starts_with($avatar,'/')&&is_file(ABSPATH.ltrim($avatar,'/')))$avatar=home_url($avatar);else $avatar=$m['avatar'];echo '<img class="member-avatar" loading="lazy" src="'.esc_url($avatar).'" alt="">';} ?></td><td><?php echo esc_html($m['rank']); ?></td><td><?php echo esc_html($m['group']); ?></td><td><?php echo esc_html($m['date']); ?></td><td><?php echo (int)$m['posts']; ?></td></tr><?php endforeach; ?>
</tbody></table></div><?php if(!$rows)echo '<p>Участники не найдены.</p>'; ?>
<?php TOP_Forum_Services::pages($total,$per,$page,$url); ?>
<?php elseif($service==='search'): ?>
<form action="<?php echo esc_url(TOP_Forum_Services::url('search')); ?>" method="get"><label>Поиск по темам и сообщениям<input type="search" name="q" value="<?php echo esc_attr($q); ?>" maxlength="200"></label><label>Автор<input type="text" name="author" value="<?php echo esc_attr($author); ?>" maxlength="100"></label><button type="submit">Найти</button></form>
<?php if($q!==''||$author!==''): ?><p class="service-count">Найдено тем: <b><?php echo (int)$total; ?></b></p><?php foreach($rows as $topic): $link=home_url('/forum/'.(int)get_post_meta($topic->ID,'_thai_forum_section',true).'-'.(int)get_post_meta($topic->ID,'_ucoz_forum_id',true).'-1'); ?><article class="service-item"><a href="<?php echo esc_url($link); ?>"><?php echo esc_html($topic->post_title); ?></a></article><?php endforeach; if(!$rows)echo '<p>По вашему запросу ничего не найдено.</p>'; TOP_Forum_Services::pages($total,$per,$page,$url); endif; ?>
<?php elseif($service==='recent'): ?>
<p class="service-count">Опубликовано сообщений: <b><?php echo (int)$total; ?></b></p>
<?php foreach($rows as $c): ?><article class="service-item"><a href="<?php echo esc_url(TOP_Forum_Services::comment_url($c)); ?>"><?php echo esc_html(get_the_title($c->comment_post_ID)); ?></a><p><?php echo esc_html($c->comment_author.' · '.get_comment_date('d.m.Y H:i',$c)); ?></p><p><?php echo esc_html(wp_trim_words(wp_strip_all_tags($c->comment_content),45)); ?></p></article><?php endforeach; TOP_Forum_Services::pages($total,$per,$page,$url); ?>
<?php endif; ?>
</div></div></div>
<?php get_footer(); ?>