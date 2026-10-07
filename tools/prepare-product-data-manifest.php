<?php
/**
 * Read-only manifest builder. Run with tools/product-data-readonly.php under PHP, never against an unguarded WP bootstrap.
 */
pdg_need(isset($GLOBALS['close_sql']), 'Pre-bootstrap read-only guard required');
$root=$args[0]??'/home/thaionline/tmp-parity/release-close-20261007';
$root=realpath($root);pdg_need($root && !str_starts_with($root,realpath(ABSPATH).'/') && (fileperms($root)&0077)===0,'Private artifact directory required');
$sources=json_decode(file_get_contents($root.'/sources.json'),true,512,JSON_THROW_ON_ERROR);
$store=new PdgWordPressStore($GLOBALS['wpdb']);$snapshot=$store->snapshot([670,674,996,965]);
$m=['schema'=>1,'scope'=>'reviewed_product_data_158_170_491_510','authorization'=>'EXPLICIT_MASTER_CONFIRMATION_REQUIRED','created_at'=>gmdate('c'),'site_url'=>get_option('siteurl'),'production_commit'=>trim(file_get_contents('/home/thaionline/autodeploy/last-deployed-commit')),'review_tool_sha256'=>hash_file('sha256',__DIR__.'/product-data-guard.php'),'accounts_sha256'=>$store->accounts(),'database_tables'=>$store->allTables(),'production_files'=>[],'catalog'=>['path'=>$root.'/sources.json','sha256'=>hash_file('sha256',$root.'/sources.json')],'items'=>[]];
foreach(pdg_live_paths() as $p)$m['production_files'][$p]=hash_file('sha256',$p);
foreach([158=>[670,'post_content'],170=>[674,'post_status'],491=>[996,'post_status'],510=>[965,'post_content']] as $legacy=>[$id,$field]){
    $s=$snapshot['items'][$id];$row=$s['row'];pdg_need($s['legacy_id']===$legacy&&$row['post_type']==='thai_excursion'&&$row['post_status']==='publish','Live identity/status drift');
    $source=$sources['sources'][$legacy];$item=['legacy_id'=>$legacy,'wp_id'=>$id,'field'=>$field,'authoritative_url'=>'https://thai-online.org/shop/'.$legacy.'/desc/'.$row['post_name'],'source'=>$source];
    if($field==='post_content'){
        $before=$row['post_content'];$after=pdg_proposed($legacy,$before,file_get_contents($source['path']));
        foreach(['before'=>$before,'after'=>$after] as $kind=>$html){$p=$root.'/product'.$legacy.'-'.$kind.'.html';pdg_need(!is_file($p),'Content artifact already exists; use a new evidence directory');file_put_contents($p,$html);chmod($p,0600);$item[$kind]=['path'=>$p,'sha256'=>hash_file('sha256',$p),'bytes'=>strlen($html)];}
    }else{$item['before_value']='publish';$item['after_value']='draft';}
    $m['items'][]=$item;
}
$m['snapshot_sha256']=pdg_hash($snapshot);$projected=pdg_project($snapshot,$m);$m['projected_snapshot_sha256']=pdg_hash($projected);
pdg_need(pdg_hash(pdg_project($projected,$m,true))===$m['snapshot_sha256'],'Term-count baseline prevents exact rollback');
$m['preserved']=['metadata and prices','titles/slugs','dates','comments','term relationships and hierarchy','WP accounts','registration','forum data'];
$m['term_count_strategy']='Only publish-status counts for unchanged thai_excursion_cat relationships; matches verified core policy. Native post/term cache invalidation after commit; no save/status hooks, revisions, notifications or date edits.';
foreach(['before-snapshot'=>$snapshot,'projected-snapshot'=>$projected] as $name=>$data){$p=$root.'/'.$name.'.json';pdg_need(!is_file($p),'Snapshot artifact already exists');file_put_contents($p,json_encode($data,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR));chmod($p,0600);}
$p=$root.'/product-data-manifest.json';pdg_need(!is_file($p),'Manifest already exists; never overwrite a reviewed bundle');file_put_contents($p,json_encode($m,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR));chmod($p,0600);
$out=pdg_run($m,hash_file('sha256',$p),'dry-run',$root.'/product-data-receipt.json',$store);$out['sql']=$GLOBALS['close_sql'];
file_put_contents($root.'/build-dry-run.json',json_encode($out,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));chmod($root.'/build-dry-run.json',0600);
echo json_encode($out,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES)."\n";
