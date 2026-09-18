<?php
if(!defined('WP_CLI')||!WP_CLI)exit;
$rows=json_decode(file_get_contents($args[0]),true,512,JSON_THROW_ON_ERROR);$created=0;
foreach($rows as $r){
 if(in_array((int)$r['ucoz_id'],[510,511],true))continue;
 if((int)get_post_meta($r['post_id'],'_ucoz_shop_id',true)!==(int)$r['ucoz_id'])WP_CLI::error('Post mismatch');
 $key=$r['ucoz_id'].':'.$r['legacy_id'];
 $found=get_comments(['post_id'=>$r['post_id'],'meta_key'=>'_thai_legacy_comment','meta_value'=>$key,'number'=>1,'status'=>'all']);
 if($found)continue;
 $id=wp_insert_comment(['comment_post_ID'=>$r['post_id'],'comment_author'=>$r['author'],'comment_content'=>wp_kses_post($r['body']),'comment_date'=>$r['date'],'comment_date_gmt'=>get_gmt_from_date($r['date']),'comment_approved'=>1,'comment_type'=>'comment','comment_meta'=>['_thai_legacy_comment'=>$key,'_thai_legacy_template'=>wp_kses_post($r['template'])]]);
 if(!$id)WP_CLI::error('Comment insert failed');$created++;
}
WP_CLI::success('Imported '.$created.' public comments');