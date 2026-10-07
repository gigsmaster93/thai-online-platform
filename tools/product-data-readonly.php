<?php
/** SQL guard is installed before WordPress; only these two review operations are accepted. */
$operation=$argv[1]??'review';if(!in_array($operation,['build','review'],true))throw new RuntimeException('Read-only entry rejects apply/rollback');
$root=$argv[2]??'/home/thaionline/tmp-parity/release-close-20261007';
define('DISABLE_WP_CRON',true);
require_once '/home/thaionline/public_html/wp-includes/plugin.php';
$GLOBALS['close_sql']=[];
add_filter('query',function($sql){$verb=strtoupper(strtok(ltrim($sql)," \t\r\n"));if(!in_array($verb,['SELECT','SHOW','DESCRIBE','EXPLAIN','SET'],true))throw new RuntimeException('READ-ONLY SQL guard refused '.$verb);$GLOBALS['close_sql'][$verb]=($GLOBALS['close_sql'][$verb]??0)+1;return $sql;},PHP_INT_MAX);
add_filter('pre_http_request',fn()=>new WP_Error('product_data_readonly','HTTP disabled in WP review'),PHP_INT_MAX);
add_filter('pre_wp_mail',fn()=>false,PHP_INT_MAX);
require '/home/thaionline/public_html/wp-load.php';
if(!defined('WP_CLI'))define('WP_CLI',true);
if(!class_exists('WP_CLI')){class WP_CLI{public static function error($m){throw new RuntimeException($m);}}}
define('THAI_PRODUCT_DATA_LIBRARY_ONLY',true);require __DIR__.'/product-data-guard.php';
$args=[$root];
if($operation==='build'){require __DIR__.'/prepare-product-data-manifest.php';}
else{
 $p=$root.'/product-data-manifest.json';$raw=file_get_contents($p);$out=pdg_run(json_decode($raw,true,512,JSON_THROW_ON_ERROR),hash('sha256',$raw),'dry-run',$root.'/product-data-receipt.json',new PdgWordPressStore($GLOBALS['wpdb']));
 $out['sql']=$GLOBALS['close_sql'];echo json_encode($out,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES)."\n";
}
