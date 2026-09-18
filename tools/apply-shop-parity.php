<?php
// Run with: wp eval-file tools/apply-shop-parity.php PREPARED_JSON BACKUP_DIR [ID ...]
if (!defined('WP_CLI') || !WP_CLI) exit;
$input=$args[0]??'';$backup=$args[1]??'';$only=array_map('intval',array_slice($args,2));
$rows=json_decode(file_get_contents($input),true,512,JSON_THROW_ON_ERROR);
if (!$backup || !wp_mkdir_p($backup)) WP_CLI::error('Cannot create backup directory');
$before=[];
foreach($rows as $r){
 if(in_array((int)$r['ucoz_id'],[510,511],true)||($only&&!in_array((int)$r['ucoz_id'],$only,true)))continue;
 $p=get_post($r['ID']);
 if(!$p||$p->post_type!=='thai_excursion'||$p->post_status!=='publish'||(int)get_post_meta($p->ID,'_ucoz_shop_id',true)!==(int)$r['ucoz_id'])WP_CLI::error('Identity/status mismatch');
 if(strpos($r['content'],'id="main-product-page"')===false||stripos($r['content'],'<script')!==false)WP_CLI::error('Invalid prepared content');
 $before[]=['post'=>$p,'meta'=>get_post_meta($p->ID)];
}
$snapshot=$backup.'/shop-'.gmdate('Ymd-His').'.json';
if(file_exists($snapshot)||file_put_contents($snapshot,wp_json_encode($before,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT))===false)WP_CLI::error('Cannot write snapshot');
kses_remove_filters();$count=0;
foreach($rows as $r){
 if(in_array((int)$r['ucoz_id'],[510,511],true)||($only&&!in_array((int)$r['ucoz_id'],$only,true)))continue;
 $result=wp_update_post(wp_slash(['ID'=>$r['ID'],'post_content'=>$r['content']]),true);
 if(is_wp_error($result))WP_CLI::error($result->get_error_message());
 foreach($r['meta'] as $key=>$value)update_post_meta($r['ID'],$key,wp_slash($value));
 $count++;
}
kses_init_filters();WP_CLI::success('Updated '.$count.' published products; snapshot '.$snapshot);