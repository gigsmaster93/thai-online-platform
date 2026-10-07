<?php
/** Hermetic tests: PdgMemoryStore only. Never bootstraps WordPress or connects to a database. */
define('ABSPATH','/home/thaionline/public_html/');
define('WP_CLI',true);define('ARRAY_A','ARRAY_A');define('THAI_PRODUCT_DATA_LIBRARY_ONLY',true);
function get_option($key){return $GLOBALS['test_options'][$key]??null;}
function get_object_taxonomies($type,$kind){return ['thai_excursion_cat'=>(object)['object_type'=>['thai_excursion'],'update_count_callback'=>$GLOBALS['test_callback']??'']];}
function apply_filters($tag,$value,$object=null){return $GLOBALS['test_statuses']??$value;}
function wp_next_scheduled($hook,$args){return $GLOBALS['test_future']??false;}
function add_filter($tag,$fn,$priority=10){$GLOBALS['test_filters'][$tag][$priority]=$fn;}
function remove_filter($tag,$fn,$priority=10){unset($GLOBALS['test_filters'][$tag][$priority]);}
function clean_post_cache($id){$GLOBALS['test_cache_ops'][]=['post',$id];if(!empty($GLOBALS['test_cache_write']))($GLOBALS['test_filters']['query'][PHP_INT_MAX])('UPDATE wp_options SET option_value=1');}
function clean_term_cache($ids,$tax,$wide){$GLOBALS['test_cache_ops'][]=['terms',$ids,$tax,$wide];}
function wp_cache_delete($key,$group){$GLOBALS['test_cache_ops'][]=['delete',$key,$group];}
function _count_posts_cache_key($type,$perm=''){return 'posts-'.$type;}
class WP_Error { public function __construct(...$args){} }
final class PdgCacheDb { public string $users='wp_users',$last_error='';public function get_results($sql,$output){check($sql==='SELECT ID FROM wp_users ORDER BY ID','Unexpected cache SQL');return [['ID'=>'1'],['ID'=>'2']];} }
require dirname(__DIR__).'/tools/product-data-guard.php';
final class PdgMemoryStore implements PdgStore {
    public array $state; public string $account; public array $events=[]; public int $writes=0;
    public bool $lock=true,$begin_fail=false,$commit_fail=false,$cache_fail=false; public int $fail_write=0;
    public $on_begin=null,$on_write=null,$on_commit=null; public array $engine_override=[]; private $save=null;
    public function __construct($s,$a){$this->state=$s;$this->account=$a;}
    public function accounts():string{return $this->account;}
    public function snapshot(array $ids):array{return $this->state;}
    public function tables():array{return ['wp_posts','wp_postmeta','wp_comments','wp_term_relationships','wp_term_taxonomy','wp_users','wp_usermeta','wp_options'];}
    public function allTables():array{$tables=array_merge($this->tables(),['wp_links','wp_commentmeta','wp_terms','wp_termmeta']);sort($tables,SORT_STRING);return $tables;}
    public function engines():array{return array_replace(array_fill_keys($this->tables(),'InnoDB'),$this->engine_override);}
    public function acquire():bool{$this->events[]='LOCK';return $this->lock;}
    public function release():void{$this->events[]='RELEASE';}
    public function begin(array $ids):void{$this->save=[$this->state,$this->account];$this->events[]='BEGIN';if($this->on_begin)($this->on_begin)($this);if($this->begin_fail)throw new RuntimeException('Injected begin failure');}
    private function write():void{$this->writes++;if($this->fail_write===$this->writes)throw new RuntimeException('Injected write failure');}
    public function post(int $id,string $field,string $value):void{$this->write();$this->state['items'][$id]['row'][$field]=$value;if($field==='post_status')foreach($this->state['members'] as &$r)if((int)$r['object_id']===$id)$r['post_status']=$value;unset($r);if($this->on_write)($this->on_write)($this);}
    public function term(int $id,string $value):void{$this->write();foreach($this->state['terms'] as &$r)if((int)$r['term_taxonomy_id']===$id)$r['count']=$value;unset($r);}
    public function commit():void{if($this->commit_fail)throw new RuntimeException('Injected commit failure');$this->events[]='COMMIT';$this->save=null;if($this->on_commit)($this->on_commit)($this);}
    public function rollback():void{$this->events[]='ROLLBACK';if($this->save){[$this->state,$this->account]=$this->save;$this->save=null;}}
    public function caches(array $ids,array $terms):void{if($this->cache_fail)throw new RuntimeException('Injected cache failure');$this->events[]=['CACHES',$ids,array_column($terms,'term_taxonomy_id')];}
}
$root=realpath($argv[1]??'/home/thaionline/tmp-parity/release-close-20261007');
$base=json_decode(file_get_contents($root.'/product-data-manifest.json'),true,512,JSON_THROW_ON_ERROR);
$base['database_tables']=(new PdgMemoryStore([],''))->allTables();
$base['review_tool_sha256']=hash_file('sha256',dirname(__DIR__).'/tools/product-data-guard.php');
$state=json_decode(file_get_contents($root.'/before-snapshot.json'),true,512,JSON_THROW_ON_ERROR);
$case_root=$root.'/tests-'.gmdate('YmdHis');mkdir($case_root,0700);
$results=[];$index=0;
function check($ok,$why){if(!$ok)throw new RuntimeException($why);}
function refuse($fn,$contains,$store,$writes=0){try{$fn();throw new RuntimeException('Expected refusal');}catch(Throwable $e){check(str_contains($e->getMessage(),$contains),'Unexpected refusal: '.$e->getMessage());}check($store->writes===$writes,'Unexpected write attempt');}
function sha($m){return pdg_hash($m);}
function arm($m,$store,$dir){
    putenv('THAI_PRODUCT_DATA_MASTER_REVIEW='.sha($m));putenv('THAI_PRODUCT_DATA_CONFIRM=APPLY_PRODUCT_DATA:'.sha($m));
    $p=$dir.'/offline-backup.sql';$body="-- MySQL dump: OFFLINE FIXTURE ONLY\n";foreach($store->allTables() as $t)$body.='CREATE TABLE `'.$t."` (ID bigint);\n";
    $body.='-- Dump completed on '.gmdate('c')."\n";
    file_put_contents($p,$body);chmod($p,0600);touch($p,max(time(),strtotime($m['created_at'])));
    putenv('THAI_PRODUCT_DATA_BACKUP='.$p);putenv('THAI_PRODUCT_DATA_BACKUP_SHA256='.hash_file('sha256',$p));
}
function apply_case($m,$s,$r,$d){arm($m,$s,$d);return pdg_run($m,sha($m),'apply',$r,$s);}
function arm_rollback($m,$r){putenv('THAI_PRODUCT_DATA_MASTER_REVIEW='.sha($m));putenv('THAI_PRODUCT_DATA_CONFIRM=ROLLBACK_PRODUCT_DATA:'.hash_file('sha256',$r));}
function test_case($name,$fn){
    global $base,$state,$case_root,$index,$results;
    $index++;$dir=$case_root.'/'.sprintf('%02d',$index);mkdir($dir,0700);
    foreach(['THAI_PRODUCT_DATA_MASTER_REVIEW','THAI_PRODUCT_DATA_CONFIRM','THAI_PRODUCT_DATA_BACKUP','THAI_PRODUCT_DATA_BACKUP_SHA256'] as $k)putenv($k);
    $GLOBALS['test_options']=['siteurl'=>'https://new.thai-online.org','users_can_register'=>'0','default_role'=>'subscriber'];$GLOBALS['test_callback']='';unset($GLOBALS['test_statuses'],$GLOBALS['_wp_suspend_cache_invalidation'],$GLOBALS['test_future']);$GLOBALS['test_filters']=[];$GLOBALS['test_cache_ops']=[];$GLOBALS['test_cache_write']=false;
    $m=$base;$s=new PdgMemoryStore($state,$m['accounts_sha256']);$receipt=$dir.'/receipt.json';
    try{$fn($m,$s,$receipt,$dir);$results[]=['case'=>$name,'pass'=>true];}
    catch(Throwable $e){$results[]=['case'=>$name,'pass'=>false,'error'=>$e->getMessage()];}
}
test_case('default dry-run has no mutations',function($m,$s,$r){$out=pdg_run($m,sha($m),'dry-run',$r,$s);check($out['production_mutations']===0&&$s->writes===0&&!file_exists($r),'Dry-run side effect');});
test_case('apply requires MASTER review',fn($m,$s,$r)=>refuse(fn()=>pdg_run($m,sha($m),'apply',$r,$s),'MASTER review',$s));
test_case('apply requires exact confirmation',function($m,$s,$r){putenv('THAI_PRODUCT_DATA_MASTER_REVIEW='.sha($m));refuse(fn()=>pdg_run($m,sha($m),'apply',$r,$s),'apply confirmation',$s);});
test_case('apply requires full backup',function($m,$s,$r){putenv('THAI_PRODUCT_DATA_MASTER_REVIEW='.sha($m));putenv('THAI_PRODUCT_DATA_CONFIRM=APPLY_PRODUCT_DATA:'.sha($m));refuse(fn()=>pdg_run($m,sha($m),'apply',$r,$s),'backup required',$s);});
test_case('backup hash mismatch refused',function($m,$s,$r,$d){arm($m,$s,$d);putenv('THAI_PRODUCT_DATA_BACKUP_SHA256='.str_repeat('0',64));refuse(fn()=>pdg_run($m,sha($m),'apply',$r,$s),'Backup hash',$s);});
test_case('backup must cover tables',function($m,$s,$r,$d){arm($m,$s,$d);$p=getenv('THAI_PRODUCT_DATA_BACKUP');file_put_contents($p,'-- MySQL dump incomplete');putenv('THAI_PRODUCT_DATA_BACKUP_SHA256='.hash_file('sha256',$p));refuse(fn()=>pdg_run($m,sha($m),'apply',$r,$s),'coverage incomplete',$s);});
test_case('backup must be fresh',function($m,$s,$r,$d){arm($m,$s,$d);touch(getenv('THAI_PRODUCT_DATA_BACKUP'),time()-90000);refuse(fn()=>pdg_run($m,sha($m),'apply',$r,$s),'Backup is stale',$s);});
test_case('backup before manifest refused',function($m,$s,$r,$d){arm($m,$s,$d);touch(getenv('THAI_PRODUCT_DATA_BACKUP'),strtotime($m['created_at'])-1);refuse(fn()=>pdg_run($m,sha($m),'apply',$r,$s),'precedes manifest',$s);});
test_case('backup public permission refused',function($m,$s,$r,$d){arm($m,$s,$d);chmod(getenv('THAI_PRODUCT_DATA_BACKUP'),0644);clearstatcache();refuse(fn()=>pdg_run($m,sha($m),'apply',$r,$s),'must be private',$s);});
test_case('transactional engine required',function($m,$s,$r){$s->engine_override['wp_posts']='MyISAM';refuse(fn()=>pdg_run($m,sha($m),'dry-run',$r,$s),'Transactional tables',$s);});
foreach(['expired'=>gmdate('c',time()-90000),'future'=>gmdate('c',time()+3600)] as $label=>$date)test_case($label.' manifest refused',function($m,$s,$r)use($date){$m['created_at']=$date;refuse(fn()=>pdg_run($m,sha($m),'dry-run',$r,$s),'expired or future',$s);});
test_case('duplicate product refused',function($m,$s,$r){$m['items'][1]=$m['items'][0];refuse(fn()=>pdg_run($m,sha($m),'dry-run',$r,$s),'duplicate product',$s);});
test_case('wrong product ID refused',function($m,$s,$r){$m['items'][0]['wp_id']=671;refuse(fn()=>pdg_run($m,sha($m),'dry-run',$r,$s),'Identity/field',$s);});
test_case('wrong write field refused',function($m,$s,$r){$m['items'][0]['field']='post_title';refuse(fn()=>pdg_run($m,sha($m),'dry-run',$r,$s),'Identity/field',$s);});
test_case('unsafe status transition refused',function($m,$s,$r){$m['items'][1]['after_value']='trash';refuse(fn()=>pdg_run($m,sha($m),'dry-run',$r,$s),'deactivation supported',$s);});
test_case('source hash drift refused',function($m,$s,$r){$m['items'][0]['source']['sha256']=str_repeat('0',64);refuse(fn()=>pdg_run($m,sha($m),'dry-run',$r,$s),'Artifact hash',$s);});
test_case('unverified source transport refused',function($m,$s,$r){$m['items'][0]['source']['tls_verified']=false;refuse(fn()=>pdg_run($m,sha($m),'dry-run',$r,$s),'transport not verified',$s);});
test_case('content outside description refused',function($m,$s,$r,$d){$p=$d.'/bad-content.html';file_put_contents($p,pdg_artifact($m['items'][0]['after']).'<p>outside edit</p>');$m['items'][0]['after']=['path'=>$p,'sha256'=>hash_file('sha256',$p)];refuse(fn()=>pdg_run($m,sha($m),'dry-run',$r,$s),'authoritative content/scope',$s);});
test_case('executable proposed markup refused',function($m,$s,$r,$d){$p=$d.'/bad-content.html';file_put_contents($p,pdg_artifact($m['items'][0]['after']).'<script>alert(1)</script>');$m['items'][0]['after']=['path'=>$p,'sha256'=>hash_file('sha256',$p)];refuse(fn()=>pdg_run($m,sha($m),'dry-run',$r,$s),'authoritative content/scope',$s);});
test_case('catalog index forgery refused',function($m,$s,$r,$d){$data=json_decode(pdg_artifact($m['catalog']),true);$data['old_product_paths'][170]='/shop/170/desc/fake';$p=$d.'/catalog.json';file_put_contents($p,json_encode($data));$m['catalog']=['path'=>$p,'sha256'=>hash_file('sha256',$p)];refuse(fn()=>pdg_run($m,sha($m),'dry-run',$r,$s),'index differs',$s);});
test_case('live code guard coverage required',function($m,$s,$r){array_pop($m['production_files']);refuse(fn()=>pdg_run($m,sha($m),'dry-run',$r,$s),'Incomplete live/core',$s);});
test_case('importer code drift refused',function($m,$s,$r){$m['review_tool_sha256']=str_repeat('0',64);refuse(fn()=>pdg_run($m,sha($m),'dry-run',$r,$s),'importer code drift',$s);});
test_case('account drift refused',function($m,$s,$r){$s->account='changed';refuse(fn()=>pdg_run($m,sha($m),'dry-run',$r,$s),'Account fingerprint',$s);});
test_case('registration stays OFF',function($m,$s,$r){$GLOBALS['test_options']['users_can_register']='1';refuse(fn()=>pdg_run($m,sha($m),'dry-run',$r,$s),'Registration/default-role',$s);});
test_case('default role drift refused',function($m,$s,$r){$GLOBALS['test_options']['default_role']='administrator';refuse(fn()=>pdg_run($m,sha($m),'dry-run',$r,$s),'Registration/default-role',$s);});
test_case('custom count callback refused',function($m,$s,$r){$GLOBALS['test_callback']='custom';refuse(fn()=>pdg_run($m,sha($m),'dry-run',$r,$s),'count policy drift',$s);});
test_case('changed count statuses refused',function($m,$s,$r){$GLOBALS['test_statuses']=['publish','draft'];refuse(fn()=>pdg_run($m,sha($m),'dry-run',$r,$s),'count policy drift',$s);});
foreach(['row','meta_sha256','comments_sha256','relationships_sha256'] as $field)test_case($field.' drift refused',function($m,$s,$r)use($field){if($field==='row')$s->state['items'][670]['row']['post_title'].='changed';else $s->state['items'][670][$field]='changed';refuse(fn()=>pdg_run($m,sha($m),'dry-run',$r,$s),'state drift',$s);});
test_case('peer status drift refused',function($m,$s,$r){$s->state['members'][0]['post_status']='draft';refuse(fn()=>pdg_run($m,sha($m),'dry-run',$r,$s),'state drift',$s);});
test_case('term count drift refused',function($m,$s,$r){$s->state['terms'][0]['count']='999';refuse(fn()=>pdg_run($m,sha($m),'dry-run',$r,$s),'state drift',$s);});
test_case('projection drift refused',function($m,$s,$r){$m['projected_snapshot_sha256']=str_repeat('0',64);refuse(fn()=>pdg_run($m,sha($m),'dry-run',$r,$s),'Projection/rollback',$s);});
test_case('concurrent lock refused',function($m,$s,$r,$d){arm($m,$s,$d);$s->lock=false;refuse(fn()=>pdg_run($m,sha($m),'apply',$r,$s),'Concurrent product operation',$s);});
test_case('concurrent row edit rechecked under lock',function($m,$s,$r,$d){arm($m,$s,$d);$s->on_begin=fn($s)=>$s->state['items'][670]['row']['post_title']='changed';refuse(fn()=>pdg_run($m,sha($m),'apply',$r,$s),'state drift',$s);check(in_array('ROLLBACK',$s->events,true),'Transaction not rolled back');});
test_case('begin failure releases transaction and lock',function($m,$s,$r,$d){arm($m,$s,$d);$s->begin_fail=true;refuse(fn()=>pdg_run($m,sha($m),'apply',$r,$s),'Injected begin',$s);check(in_array('ROLLBACK',$s->events,true)&&end($s->events)==='RELEASE','Cleanup missing');});
test_case('mid-batch failure restores exact original',function($m,$s,$r,$d){arm($m,$s,$d);$initial=pdg_hash($s->state);$s->fail_write=3;refuse(fn()=>pdg_run($m,sha($m),'apply',$r,$s),'Injected write',$s,3);check(pdg_hash($s->state)===$initial,'Partial batch retained');check(json_decode(file_get_contents($r),true)['state']==='ABORTED','Abort receipt missing');});
test_case('unexpected metadata mutation rolls back',function($m,$s,$r,$d){arm($m,$s,$d);$initial=pdg_hash($s->state);$s->on_write=fn($s)=>$s->state['items'][670]['meta_sha256']='corrupt';refuse(fn()=>pdg_run($m,sha($m),'apply',$r,$s),'state drift',$s,5);check(pdg_hash($s->state)===$initial,'Metadata corruption retained');});
test_case('commit failure restores exact original',function($m,$s,$r,$d){arm($m,$s,$d);$initial=pdg_hash($s->state);$s->commit_fail=true;refuse(fn()=>pdg_run($m,sha($m),'apply',$r,$s),'Injected commit',$s,5);check(pdg_hash($s->state)===$initial,'Failed commit retained');});
test_case('apply updates four fields and one term count',function($m,$s,$r,$d){$out=apply_case($m,$s,$r,$d);check($out['state']==='COMMITTED'&&$s->writes===5&&pdg_hash($s->state)===$m['projected_snapshot_sha256'],'Wrong apply state');check(json_decode(file_get_contents($r),true)['cache_state']==='CLEARED','Caches not journaled');check((fileperms($r)&0077)===0,'Receipt public');});
test_case('reapply refused',function($m,$s,$r,$d){apply_case($m,$s,$r,$d);refuse(fn()=>pdg_run($m,sha($m),'apply',$r,$s),'state drift',$s,5);});
test_case('rollback restores exact original including counts',function($m,$s,$r,$d){apply_case($m,$s,$r,$d);arm_rollback($m,$r);$out=pdg_run($m,sha($m),'rollback',$r,$s);check($out['state']==='ROLLED_BACK'&&$s->writes===10&&pdg_hash($s->state)===$m['snapshot_sha256'],'Rollback differs');});
test_case('rollback after later edit refused',function($m,$s,$r,$d){apply_case($m,$s,$r,$d);$s->state['items'][965]['row']['post_title'].='new edit';arm_rollback($m,$r);refuse(fn()=>pdg_run($m,sha($m),'rollback',$r,$s),'state drift',$s,5);});
test_case('rollback receipt confirmation must be exact',function($m,$s,$r,$d){apply_case($m,$s,$r,$d);putenv('THAI_PRODUCT_DATA_CONFIRM=ROLLBACK_PRODUCT_DATA:wrong');refuse(fn()=>pdg_run($m,sha($m),'rollback',$r,$s),'rollback confirmation',$s,5);});
test_case('rollback wrong manifest receipt refused',function($m,$s,$r,$d){apply_case($m,$s,$r,$d);$j=json_decode(file_get_contents($r),true);$j['manifest_sha256']='wrong';pdg_journal($r,$j);arm_rollback($m,$r);refuse(fn()=>pdg_run($m,sha($m),'rollback',$r,$s),'Receipt/manifest',$s,5);});
test_case('PREPARED receipt after commit can recover',function($m,$s,$r,$d){apply_case($m,$s,$r,$d);$j=json_decode(file_get_contents($r),true);$j['state']='PREPARED';pdg_journal($r,$j);check(pdg_run($m,sha($m),'verify-receipt',$r,$s)['database_state']==='APPLIED','Commit recovery not detected');arm_rollback($m,$r);pdg_run($m,sha($m),'rollback',$r,$s);check(pdg_hash($s->state)===$m['snapshot_sha256'],'Recovery rollback differs');});
test_case('PREPARED before commit cannot roll back original',function($m,$s,$r,$d){$j=['state'=>'PREPARED','manifest_sha256'=>sha($m),'before_snapshot_sha256'=>$m['snapshot_sha256'],'after_snapshot_sha256'=>$m['projected_snapshot_sha256']];pdg_journal($r,$j,true);check(pdg_run($m,sha($m),'verify-receipt',$r,$s)['database_state']==='ORIGINAL','Original state not detected');arm_rollback($m,$r);refuse(fn()=>pdg_run($m,sha($m),'rollback',$r,$s),'state drift',$s);});
test_case('cache failure leaves recoverable committed receipt',function($m,$s,$r,$d){arm($m,$s,$d);$s->cache_fail=true;refuse(fn()=>pdg_run($m,sha($m),'apply',$r,$s),'DATABASE COMMITTED',$s,5);check(pdg_run($m,sha($m),'verify-receipt',$r,$s)['database_state']==='APPLIED','Commit hidden');$s->cache_fail=false;arm_rollback($m,$r);pdg_run($m,sha($m),'rollback',$r,$s);check(pdg_hash($s->state)===$m['snapshot_sha256'],'Cache failure recovery rollback differs');});
test_case('existing receipt blocks write attempt',function($m,$s,$r,$d){arm($m,$s,$d);file_put_contents($r,'existing');chmod($r,0600);refuse(fn()=>pdg_run($m,sha($m),'apply',$r,$s),'Receipt exists',$s);});
test_case('public receipt directory refused',function($m,$s,$r,$d){arm($m,$s,$d);chmod($d,0755);clearstatcache();refuse(fn()=>pdg_run($m,sha($m),'apply',$r,$s),'directory must be private',$s);chmod($d,0700);});
test_case('unknown mode refused',fn($m,$s,$r)=>refuse(fn()=>pdg_run($m,sha($m),'deploy',$r,$s),'Unknown mode',$s));
test_case('backup completion marker required',function($m,$s,$r,$d){arm($m,$s,$d);$p=getenv('THAI_PRODUCT_DATA_BACKUP');$body=file_get_contents($p);$body=preg_replace('/-- Dump completed.*$/m','',$body);file_put_contents($p,$body);putenv('THAI_PRODUCT_DATA_BACKUP_SHA256='.hash_file('sha256',$p));refuse(fn()=>pdg_run($m,sha($m),'apply',$r,$s),'completion/table coverage',$s);});
test_case('full backup must include untouched tables',function($m,$s,$r,$d){arm($m,$s,$d);$p=getenv('THAI_PRODUCT_DATA_BACKUP');$body=preg_replace('/CREATE TABLE `wp_termmeta`.*$/m','',file_get_contents($p));file_put_contents($p,$body);putenv('THAI_PRODUCT_DATA_BACKUP_SHA256='.hash_file('sha256',$p));refuse(fn()=>pdg_run($m,sha($m),'apply',$r,$s),'completion/table coverage',$s);});
test_case('database schema table drift refused',function($m,$s,$r){array_pop($m['database_tables']);refuse(fn()=>pdg_run($m,sha($m),'dry-run',$r,$s),'table coverage drift',$s);});
test_case('rollback cache failure is recorded as pending',function($m,$s,$r,$d){apply_case($m,$s,$r,$d);$s->cache_fail=true;arm_rollback($m,$r);refuse(fn()=>pdg_run($m,sha($m),'rollback',$r,$s),'DATABASE COMMITTED',$s,10);$out=pdg_run($m,sha($m),'verify-receipt',$r,$s);check($out['database_state']==='ORIGINAL'&&$out['cache_state']==='PENDING','Rollback cache failure hidden');});
test_case('expired applied manifest still permits guarded rollback',function($m,$s,$r){$m['created_at']=gmdate('c',time()-90000);$s->state=pdg_project($s->state,$m);$j=['state'=>'COMMITTED','manifest_sha256'=>sha($m),'before_snapshot_sha256'=>$m['snapshot_sha256'],'after_snapshot_sha256'=>$m['projected_snapshot_sha256'],'cache_state'=>'CLEARED'];pdg_journal($r,$j,true);arm_rollback($m,$r);pdg_run($m,sha($m),'rollback',$r,$s);check(pdg_hash($s->state)===$m['snapshot_sha256'],'Expired-receipt rollback differs');});
test_case('suspended cache invalidation blocks apply',function($m,$s,$r){$GLOBALS['_wp_suspend_cache_invalidation']=true;refuse(fn()=>pdg_run($m,sha($m),'dry-run',$r,$s),'invalidation is suspended',$s);});
test_case('scheduled publish event blocks apply',function($m,$s,$r){$GLOBALS['test_future']=time()+3600;refuse(fn()=>pdg_run($m,sha($m),'dry-run',$r,$s),'scheduled publish event',$s);});
test_case('WordPress adapter clears post and publication count caches',function($m,$s,$r){$db=new PdgWordPressStore(new PdgCacheDb());$db->caches([670,674,996,965],$s->state['terms']);foreach([670,674,996,965] as $id)check(in_array(['post',$id],$GLOBALS['test_cache_ops'],true),'Missing post cache');foreach(['posts-thai_excursion','posts-thai_excursion_readable_1','posts-thai_excursion_readable_2'] as $key)check(in_array(['delete',$key,'counts'],$GLOBALS['test_cache_ops'],true),'Missing count cache');});
test_case('WordPress adapter clears modified/date caches and preserves hierarchy',function($m,$s,$r){$db=new PdgWordPressStore(new PdgCacheDb());$db->caches([674,996],$s->state['terms']);foreach(['server','gmt','blog'] as $tz)foreach(['lastpostmodified','lastpostdate'] as $key)foreach(['',':thai_excursion'] as $suffix)check(in_array(['delete',$key.':'.$tz.$suffix,'timeinfo'],$GLOBALS['test_cache_ops'],true),'Missing timestamp cache');$ops=array_values(array_filter($GLOBALS['test_cache_ops'],fn($o)=>$o[0]==='terms'));check(count($ops)===1&&$ops[0][3]===false,'Hierarchy options would be rewritten');});
test_case('WordPress adapter blocks cache-hook SQL writes',function($m,$s,$r){$GLOBALS['test_cache_write']=true;$db=new PdgWordPressStore(new PdgCacheDb());try{$db->caches([670],[]);throw new RuntimeException('Expected cache write rejection');}catch(Throwable $e){check(str_contains($e->getMessage(),'attempted a DB write'),'Wrong cache rejection');}check(empty($GLOBALS['test_filters']['query'])&&empty($GLOBALS['test_filters']['pre_wp_mail'])&&empty($GLOBALS['test_filters']['pre_http_request']),'Temporary filters leaked');});
$fail=array_values(array_filter($results,fn($r)=>!$r['pass']));$out=['tests'=>count($results),'pass'=>count($results)-count($fail),'failed'=>$fail,'cases'=>$results,'store'=>'PdgMemoryStore only; no WordPress bootstrap/DB connection','production_mutations'=>0,'real_mail'=>0];
file_put_contents($case_root.'/results.json',json_encode($out,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));chmod($case_root.'/results.json',0600);
echo json_encode(['tests'=>$out['tests'],'pass'=>$out['pass'],'failed'=>$fail,'results'=>$case_root.'/results.json','production_mutations'=>0],JSON_PRETTY_PRINT)."\n";
exit($fail?1:0);
