<?php
// wp eval-file tools/import-community.php DATA_DIRECTORY gallery|reviews|contact|forum
if(!defined('WP_CLI')||!WP_CLI)exit;
$root=rtrim($args[0],'/').'/';$mode=$args[1]??'';
function top_existing_ids($type,$key){global $wpdb;$rows=$wpdb->get_results($wpdb->prepare("SELECT p.ID,m.meta_value FROM {$wpdb->posts} p JOIN {$wpdb->postmeta} m ON m.post_id=p.ID WHERE p.post_type=%s AND m.meta_key=%s",$type,$key));$map=[];foreach($rows as $r)$map[$r->meta_value]=(int)$r->ID;return $map;}
wp_defer_term_counting(true);wp_defer_comment_counting(true);wp_suspend_cache_addition(true);
if($mode==='gallery'){
 $albums=json_decode(file_get_contents($root.'albums-prepared.json'),true);$terms=[];
 foreach($albums as $id=>$a){$t=get_term_by('slug','album-'.$id,'thai_photo_album');if(!$t){$res=wp_insert_term($a['name'],'thai_photo_album',['slug'=>'album-'.$id]);if(is_wp_error($res))WP_CLI::error($res->get_error_message());$terms[$id]=(int)$res['term_id'];}else $terms[$id]=(int)$t->term_id;update_term_meta($terms[$id],'_thai_legacy_url',$a['url']);}
 foreach($albums as $id=>$a)if(!empty($terms[$a['parent']]))wp_update_term($terms[$id],'thai_photo_album',['parent'=>$terms[$a['parent']]]);
 $aliases=[];
 $existing=top_existing_ids('thai_photo','_ucoz_photo_id');$f=fopen($root.'gallery-prepared.jsonl','r');$count=0;
 while(($line=fgets($f))!==false){$r=json_decode($line,true,512,JSON_THROW_ON_ERROR);if(strpos($r['url'],'/photo/')!==0)$aliases[trim($r['url'],'/')]=[$r['id'],$r['album']];if(isset($existing[$r['id']]))continue;
  $id=wp_insert_post(wp_slash(['post_type'=>'thai_photo','post_status'=>'publish','post_name'=>'photo-'.$r['id'],'post_title'=>$r['title'],'post_content'=>wp_kses_post($r['content']),'post_date'=>date('Y-m-d H:i:s',(int)$r['date']),'meta_input'=>['_ucoz_photo_id'=>$r['id'],'_thai_photo_full'=>$r['full'],'_thai_photo_thumb'=>$r['thumb'],'_thai_legacy_url'=>$r['url'],'_thai_migration_batch'=>'20260918']]),true);
  if(is_wp_error($id))WP_CLI::error($id->get_error_message());
  wp_set_object_terms($id,[$terms[$r['album']]],'thai_photo_album');
  $existing[$r['id']]=$id;$count++;if($count%1000===0)WP_CLI::line('Photos imported: '.$count);
 }fclose($f);update_option('thai_photo_aliases',$aliases,false);WP_CLI::success('Gallery imported: '.$count);
}elseif($mode==='reviews'){
 $rows=json_decode(file_get_contents($root.'reviews-prepared.json'),true);
 $existing=top_existing_ids('thai_guestbook','_ucoz_gb_id');
 foreach($rows as $r){
  $data=['post_type'=>'thai_guestbook','post_status'=>'publish','post_title'=>$r['title'],'post_content'=>wp_kses_post($r['content']),'post_date'=>$r['date'],'meta_input'=>['_ucoz_gb_id'=>$r['id'],'_thai_live_review'=>1,'_thai_review_template'=>wp_kses_post($r['template'])]];
  if(empty($data['post_date']))unset($data['post_date']);
  if(isset($existing[$r['id']]))$data['ID']=$existing[$r['id']];
  $id=wp_insert_post(wp_slash($data),true);if(is_wp_error($id))WP_CLI::error($id->get_error_message());
 }
 WP_CLI::success('Public guestbook entries synchronized: '.count($rows));
}elseif($mode==='contact'){
 $p=get_page_by_path('contact');if(!$p)WP_CLI::error('Contact page missing');
 kses_remove_filters();$r=wp_update_post(wp_slash(['ID'=>$p->ID,'post_title'=>'Контакты','post_content'=>file_get_contents($root.'contact-prepared.html')]),true);kses_init_filters();
 if(is_wp_error($r))WP_CLI::error($r->get_error_message());WP_CLI::success('Contact content restored');
}elseif($mode==='forum'){
 $sections=json_decode(file_get_contents($root.'forum-sections-prepared.json'),true);update_option('thai_forum_sections',$sections,false);
 $existing=top_existing_ids('thai_forum_topic','_ucoz_forum_id');$rows=json_decode(file_get_contents($root.'forum-prepared.json'),true);$topics=0;$messages=0;
 foreach($rows as $r){
  if(isset($existing[$r['id']])){$id=$existing[$r['id']];update_post_meta($id,'_thai_forum_updated',$r['updated']);}else{
   $id=wp_insert_post(wp_slash(['post_type'=>'thai_forum_topic','post_status'=>'publish','post_title'=>$r['title'],'post_content'=>'','post_date'=>$r['date'],'meta_input'=>['_ucoz_forum_id'=>$r['id'],'_thai_forum_section'=>$r['section'],'_thai_forum_updated'=>$r['updated'],'_thai_migration_batch'=>'20260918']]),true);
   if(is_wp_error($id))WP_CLI::error($id->get_error_message());$existing[$r['id']]=$id;$topics++;
  }
  $known=[];foreach(get_comments(['post_id'=>$id,'status'=>'all','number'=>0]) as $c)$known[get_comment_meta($c->comment_ID,'_ucoz_forum_post',true)]=true;
  foreach($r['posts'] as $c){if(isset($known[$c['id']]))continue;
   $cid=wp_insert_comment(['comment_post_ID'=>$id,'comment_author'=>$c['author'],'comment_content'=>wp_kses_post($c['content']),'comment_date'=>$c['date'],'comment_date_gmt'=>get_gmt_from_date($c['date']),'comment_approved'=>1,'comment_type'=>'comment','comment_meta'=>['_ucoz_forum_post'=>$c['id']]]);
   if(!$cid)WP_CLI::error('Forum message failed');$messages++;
  }
  if($topics&&$topics%100===0)WP_CLI::line('Forum topics: '.$topics);
 }
 WP_CLI::success('Forum topics '.$topics.', messages '.$messages);
}else WP_CLI::error('Unknown mode');
wp_suspend_cache_addition(false);wp_defer_term_counting(false);wp_defer_comment_counting(false);wp_cache_flush();