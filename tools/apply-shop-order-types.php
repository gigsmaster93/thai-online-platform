<?php
// wp eval-file tools/apply-shop-order-types.php INPUT_JSON BACKUP_DIRECTORY
if(!defined('WP_CLI')||!WP_CLI)exit;
$rows=json_decode(file_get_contents($args[0]),true,512,JSON_THROW_ON_ERROR);$backup=[];$updates=[];
foreach($rows as $r){if(in_array((int)$r['id'],[510,511],true))continue;$ids=get_posts(['post_type'=>'thai_excursion','post_status'=>'publish','numberposts'=>1,'fields'=>'ids','meta_key'=>'_ucoz_shop_id','meta_value'=>$r['id']]);if(!$ids)WP_CLI::error('Published product missing: '.$r['id']);$id=$ids[0];$backup[$id]=['_thai_order_type'=>get_post_meta($id,'_thai_order_type',true),'_thai_person_labels'=>get_post_meta($id,'_thai_person_labels',true)];$updates[$id]=$r;}
$dir=rtrim($args[1],'/');if(!is_dir($dir))wp_mkdir_p($dir);$path=$dir.'/order-types-'.gmdate('Ymd-His').'.json';if(file_put_contents($path,wp_json_encode($backup,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT))===false)WP_CLI::error('Could not save backup');
foreach($updates as $id=>$r){update_post_meta($id,'_thai_order_type',$r['type']);$labels=array_values(array_filter(array_map('trim',$r['labels']),'strlen'));update_post_meta($id,'_thai_person_labels',$labels);}
WP_CLI::success('Order types/quantity labels updated: '.count($updates).'; backup '.$path);