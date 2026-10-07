<?php
/**
 * Reviewed four-product import, default dry-run.
 * wp eval-file FILE MANIFEST [dry-run|apply|rollback|verify-receipt] [RECEIPT]
 * Production execution needs separate explicit MASTER confirmation and a verified full backup.
 */
if (!defined('ABSPATH') || !defined('WP_CLI') || !WP_CLI) { fwrite(STDERR, "Use wp eval-file; default is dry-run.\n"); exit(2); }
function pdg_need($ok, $message) { if (!$ok) throw new RuntimeException($message); }
function pdg_hash($value) { return hash('sha256', json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)); }
function pdg_artifact($item) {
    $p = realpath($item['path'] ?? '');
    $web = realpath(ABSPATH);
    pdg_need($p && is_file($p) && $p !== $web && !str_starts_with($p, $web . '/'), 'Artifact must be outside webroot');
    pdg_need(hash_file('sha256', $p) === ($item['sha256'] ?? ''), 'Artifact hash changed');
    $raw = file_get_contents($p); pdg_need($raw !== false, 'Cannot read artifact'); return $raw;
}
function pdg_fresh($date) {
    $stamp = is_string($date) ? strtotime($date) : false;
    pdg_need($stamp !== false && time() - $stamp >= 0 && time() - $stamp <= 86400, 'Manifest/source expired or future-dated');
}
function pdg_node($html, $id) {
    libxml_use_internal_errors(true);
    $doc = new DOMDocument('1.0', 'UTF-8'); $doc->loadHTML('<?xml encoding="UTF-8">' . $html);
    $xpath = new DOMXPath($doc); $nodes = $xpath->query('//*[@id="' . $id . '"]');
    pdg_need($nodes->length === 1, 'Expected exactly one authoritative ' . $id); $node = $nodes->item(0);
    foreach (iterator_to_array($xpath->query('.//script|.//link', $node)) as $n) $n->parentNode->removeChild($n);
    foreach ($xpath->query('.//*', $node) as $el) foreach (iterator_to_array($el->attributes) as $a)
        if (preg_match('/^on/i', $a->name) || in_array(strtolower($a->name), ['href','src','action'], true) && preg_match('/^\s*(javascript|vbscript):/i', $a->value)) $el->removeAttribute($a->name);
    return [$doc, $node];
}
function pdg_dscr_range($html) {
    preg_match_all('~</?div\b[^>]*>~i', $html, $tags, PREG_OFFSET_CAPTURE); $depth = null; $start = null;
    foreach ($tags[0] as [$tag, $offset]) {
        if ($depth === null) { if (preg_match('~\bid=["\']dscr["\']~', $tag)) { $depth = 1; $start = $offset + strlen($tag); } continue; }
        $depth += str_starts_with($tag, '</') ? -1 : 1;
        if ($depth === 0) return [$start, $offset];
    }
    throw new RuntimeException('Cannot isolate stored description');
}
function pdg_proposed($legacy, $before, $source) {
    [$doc, $node] = pdg_node($source, $legacy === 158 ? 'dscr' : 'main-product-page');
    if ($legacy === 158) {
        $inner = ''; foreach ($node->childNodes as $child) $inner .= $doc->saveHTML($child);
        $inner = preg_replace('~https?://(?:www\.)?thai-online\.org(?=/)~', '', $inner);
        [$start,$end] = pdg_dscr_range($before); $after = substr($before,0,$start) . $inner . substr($before,$end);
    } else {
        pdg_need($legacy === 510 && !str_contains($before, 'id="main-product-page"'), 'Wrong wrapper repair precondition');
        $after = preg_replace('~https?://(?:www\.)?thai-online\.org(?=/)~', '', $doc->saveHTML($node));
        foreach (['id="main-product-page"','id="dscr"','id="tabContainer"','slideout-sidebar'] as $marker) pdg_need(str_contains($after,$marker), 'Missing legacy product marker');
    }
    pdg_need(!preg_match('~<script\b|\bon[a-z]+\s*=|(?:javascript|vbscript):|data:text/html~i',$after), 'Executable proposed content');
    return $after;
}
interface PdgStore {
    public function accounts(): string;
    public function snapshot(array $ids): array;
    public function engines(): array;
    public function tables(): array;
    public function allTables(): array;
    public function acquire(): bool;
    public function release(): void;
    public function begin(array $ids): void;
    public function post(int $id, string $field, string $value): void;
    public function term(int $id, string $value): void;
    public function commit(): void;
    public function rollback(): void;
    public function caches(array $ids, array $terms): void;
}
final class PdgWordPressStore implements PdgStore {
    private $db;
    public function __construct($db) { $this->db = $db; }
    private function rows($sql) { $r = $this->db->get_results($sql, ARRAY_A); pdg_need(is_array($r) && !$this->db->last_error, 'Database read failed'); return $r; }
    private function query($sql) { pdg_need($this->db->query($sql) !== false, 'Database operation failed'); }
    public function accounts(): string {
        $d=$this->db; return pdg_hash([$this->rows("SELECT * FROM {$d->users} ORDER BY ID"),$this->rows("SELECT * FROM {$d->usermeta} ORDER BY umeta_id"),$this->rows("SELECT * FROM {$d->options} WHERE option_name IN ('users_can_register','default_role') ORDER BY option_name")]);
    }
    public function snapshot(array $ids): array {
        $d=$this->db; sort($ids,SORT_NUMERIC); $items=[]; $tt=[];
        foreach ($ids as $id) {
            $rows=$this->rows($d->prepare("SELECT * FROM {$d->posts} WHERE ID=%d",$id)); pdg_need(count($rows)===1,'Product missing');
            $meta=$this->rows($d->prepare("SELECT * FROM {$d->postmeta} WHERE post_id=%d ORDER BY meta_id",$id));
            $legacy=array_values(array_filter($meta,fn($r)=>$r['meta_key']==='_ucoz_shop_id')); pdg_need(count($legacy)===1,'Legacy identity missing/duplicated');
            $rel=$this->rows($d->prepare("SELECT * FROM {$d->term_relationships} WHERE object_id=%d ORDER BY term_taxonomy_id",$id));
            foreach($rel as $r)$tt[(int)$r['term_taxonomy_id']]=true;
            $items[$id]=['row'=>$rows[0],'legacy_id'=>(int)$legacy[0]['meta_value'],'meta_sha256'=>pdg_hash($meta),'comments_sha256'=>pdg_hash($this->rows($d->prepare("SELECT * FROM {$d->comments} WHERE comment_post_ID=%d ORDER BY comment_ID",$id))),'relationships_sha256'=>pdg_hash($rel)];
        }
        $terms=[];$members=[];
        if($tt){$list=implode(',',array_keys($tt));$terms=$this->rows("SELECT * FROM {$d->term_taxonomy} WHERE term_taxonomy_id IN ($list) ORDER BY term_taxonomy_id");$members=$this->rows("SELECT tr.*,p.post_type,p.post_status FROM {$d->term_relationships} tr JOIN {$d->posts} p ON p.ID=tr.object_id WHERE tr.term_taxonomy_id IN ($list) ORDER BY tr.term_taxonomy_id,tr.object_id");}
        return ['items'=>$items,'terms'=>$terms,'members'=>$members];
    }
    public function tables(): array { $d=$this->db;return array_map(fn($p)=>$d->$p,['posts','postmeta','comments','term_relationships','term_taxonomy','users','usermeta','options']); }
    public function allTables(): array { $out=array_map(fn($r)=>(string)reset($r),$this->rows('SHOW TABLES'));sort($out,SORT_STRING);return $out; }
    public function engines(): array { $out=[];foreach($this->tables() as $table)$out[$table]=$this->db->get_var($this->db->prepare('SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=%s',$table));return $out; }
    public function acquire(): bool { return (int)$this->db->get_var("SELECT GET_LOCK('thai_product_data_158_170_491_510',0)")===1; }
    public function release(): void { $this->db->get_var("SELECT RELEASE_LOCK('thai_product_data_158_170_491_510')"); }
    public function begin(array $ids): void {
        $d=$this->db;$this->query('SET TRANSACTION ISOLATION LEVEL SERIALIZABLE');$this->query('START TRANSACTION');
        $ids=array_map('intval',$ids);sort($ids,SORT_NUMERIC);$list=implode(',',$ids);
        $this->rows("SELECT ID FROM {$d->posts} WHERE ID IN ($list) ORDER BY ID FOR UPDATE");
        $this->rows("SELECT meta_id FROM {$d->postmeta} WHERE post_id IN ($list) ORDER BY meta_id FOR UPDATE");
        $this->rows("SELECT comment_ID FROM {$d->comments} WHERE comment_post_ID IN ($list) ORDER BY comment_ID FOR UPDATE");
        $rel=$this->rows("SELECT * FROM {$d->term_relationships} WHERE object_id IN ($list) ORDER BY object_id,term_taxonomy_id FOR UPDATE");
        $tt=array_unique(array_column($rel,'term_taxonomy_id'));$tt=array_map('intval',$tt);
        if($tt){$tl=implode(',',$tt);$this->rows("SELECT * FROM {$d->term_taxonomy} WHERE term_taxonomy_id IN ($tl) ORDER BY term_taxonomy_id FOR UPDATE");$members=$this->rows("SELECT * FROM {$d->term_relationships} WHERE term_taxonomy_id IN ($tl) ORDER BY term_taxonomy_id,object_id FOR UPDATE");$peers=array_map('intval',array_unique(array_column($members,'object_id')));sort($peers,SORT_NUMERIC);if($peers)$this->rows("SELECT ID FROM {$d->posts} WHERE ID IN (".implode(',',$peers).") ORDER BY ID FOR UPDATE");}
        $this->rows("SELECT ID FROM {$d->users} ORDER BY ID FOR UPDATE");$this->rows("SELECT umeta_id FROM {$d->usermeta} ORDER BY umeta_id FOR UPDATE");$this->rows("SELECT option_id FROM {$d->options} WHERE option_name IN ('users_can_register','default_role') ORDER BY option_name FOR UPDATE");
    }
    public function post(int $id,string $field,string $value):void { pdg_need(in_array($field,['post_content','post_status'],true),'Unreviewed write field');pdg_need($this->db->update($this->db->posts,[$field=>$value],['ID'=>$id],['%s'],['%d'])===1,'Product update failed'); }
    public function term(int $id,string $value):void { pdg_need($this->db->update($this->db->term_taxonomy,['count'=>$value],['term_taxonomy_id'=>$id],['%d'],['%d'])===1,'Term count update failed'); }
    public function commit():void { $this->query('COMMIT'); }
    public function rollback():void { $this->query('ROLLBACK'); }
    public function caches(array $ids,array $terms):void {
        global $_wp_suspend_cache_invalidation;pdg_need(empty($_wp_suspend_cache_invalidation),'Cache invalidation is suspended');
        $mail=fn()=>false;$http=fn()=>new WP_Error('product_import_no_outbound','External requests disabled during cache cleanup');
        add_filter('pre_wp_mail',$mail,PHP_INT_MAX);add_filter('pre_http_request',$http,PHP_INT_MAX);
        $guard=function($sql){pdg_need((bool)preg_match('/^\\s*(SELECT|SHOW|DESCRIBE|EXPLAIN|SET)\\b/i',$sql),'Cache hook attempted a DB write');return $sql;};
        add_filter('query',$guard,PHP_INT_MAX);
        try {
            foreach($ids as $id)clean_post_cache($id);
            wp_cache_delete(_count_posts_cache_key('thai_excursion'),'counts');wp_cache_delete(_count_posts_cache_key('thai_excursion','readable'),'counts');
            foreach($this->rows("SELECT ID FROM {$this->db->users} ORDER BY ID") as $u)wp_cache_delete('posts-thai_excursion_readable_'.(int)$u['ID'],'counts');
            foreach(['server','gmt','blog'] as $timezone)foreach(['lastpostmodified','lastpostdate'] as $key){wp_cache_delete($key.':'.$timezone,'timeinfo');wp_cache_delete($key.':'.$timezone.':thai_excursion','timeinfo');}
            // Counts changed, relationships/hierarchy did not. Avoid rewriting hierarchy options.
            if($terms)clean_term_cache(array_map('intval',array_column($terms,'term_taxonomy_id')),'',false);
        } finally { remove_filter('query',$guard,PHP_INT_MAX);remove_filter('pre_wp_mail',$mail,PHP_INT_MAX);remove_filter('pre_http_request',$http,PHP_INT_MAX); }
    }
}
function pdg_live_paths() {
    $root='/home/thaionline/public_html/';
    $rel=['wp-content/themes/thai-online-theme/header.php','wp-content/themes/thai-online-theme/functions.php','wp-content/themes/thai-online-theme/style.css','wp-content/themes/thai-online-theme/assets/shell.js','wp-content/plugins/thai-online-platform/src/Core.php','wp-content/plugins/thai-online-platform/src/Community.php','wp-content/plugins/thai-online-platform/src/LegacyRoutes.php','wp-content/plugins/thai-online-platform/templates/shop-single.php','wp-content/plugins/thai-online-platform/templates/shop-checkout.php','wp-content/plugins/thai-online-platform/assets/community.css','wp-content/plugins/thai-online-platform/assets/community.js','wp-includes/post.php','wp-includes/taxonomy.php','wp-includes/functions.php','wp-includes/cache.php','wp-includes/version.php'];
    return array_map(fn($p)=>$root.$p,$rel);
}
function pdg_runtime($m) {
    global $_wp_suspend_cache_invalidation;pdg_need(empty($_wp_suspend_cache_invalidation),'Cache invalidation is suspended');
    foreach([670,674,996,965] as $id)pdg_need(wp_next_scheduled('publish_future_post',[$id])===false,'Reviewed product has a scheduled publish event');
    pdg_need(get_option('siteurl') === 'https://new.thai-online.org' && $m['site_url'] === 'https://new.thai-online.org','Wrong WordPress site');
    pdg_need((string)get_option('users_can_register') === '0' && get_option('default_role') === 'subscriber','Registration/default-role drift');
    pdg_need(trim(file_get_contents('/home/thaionline/autodeploy/last-deployed-commit')) === $m['production_commit'],'Deploy marker drift');
    pdg_need(trim((string)shell_exec('git -C /home/thaionline/autodeploy/thai-online-platform rev-parse HEAD 2>/dev/null')) === $m['production_commit'],'Develop drift');
    pdg_need(trim((string)shell_exec('git -C /home/thaionline/autodeploy/thai-online-platform status --porcelain --untracked-files=no')) === '','Production checkout dirty');
    pdg_need(array_keys($m['production_files'])===pdg_live_paths(),'Incomplete live/core code guards');
    pdg_need(($m['review_tool_sha256']??'')===hash_file('sha256',__FILE__),'Reviewed importer code drift');
    pdg_need(is_array($m['production_files']??null),'Missing live code fingerprints');
    foreach($m['production_files'] as $p=>$sha)pdg_need(is_file($p)&&hash_file('sha256',$p)===$sha,'Live/core code drift');
    $tax=get_object_taxonomies('thai_excursion','objects');
    pdg_need(array_keys($tax)===['thai_excursion_cat'],'Product taxonomy set drift');
    $t=$tax['thai_excursion_cat'];
    pdg_need($t->object_type===['thai_excursion'] && empty($t->update_count_callback) && apply_filters('update_post_term_count_statuses',['publish'],$t)===['publish'],'Native taxonomy count policy drift');
}
function pdg_validate($m,$fresh) {
    pdg_need(($m['schema']??null)===1&&($m['scope']??'')==='reviewed_product_data_158_170_491_510','Wrong manifest scope');
    pdg_need(($m['authorization']??'')==='EXPLICIT_MASTER_CONFIRMATION_REQUIRED','Wrong execution boundary');
    if($fresh)pdg_fresh($m['created_at']??null);
    pdg_need(is_array($m['items']??null),'Manifest items missing');
    $sources=json_decode(pdg_artifact($m['catalog']),true,512,JSON_THROW_ON_ERROR);
    if($fresh)pdg_fresh($sources['checked_at']??null);
    pdg_need(empty($sources['errors'])&&count($sources['old_catalog_pages'])===10&&$sources['old_discovered_pages']===range(2,10),'Incomplete source catalog');
    $paths=[];foreach($sources['old_catalog_pages'] as $p){pdg_need($p['status']===200&&$p['host']==='thai-online.org'&&($p['tls_verified']??false)===true,'Invalid catalog origin');pdg_artifact($p);if($fresh)pdg_fresh($p['fetched_at']);$paths[]=$p['url'];}
    $observed=[];$discovered=[];
    foreach($sources['old_catalog_pages'] as $p){
        libxml_use_internal_errors(true);$doc=new DOMDocument();$doc->loadHTML(pdg_artifact($p));$x=new DOMXPath($doc);
        foreach($x->query('//a[@href]') as $a){$href=$a->getAttribute('href');if(preg_match('~/shop/(\\d+)/desc/~',$href,$hit))$observed[(int)$hit[1]]=true;if(preg_match('~/shop/all/(\\d+)~',$href,$hit))$discovered[(int)$hit[1]]=true;}
    }
    $listed=array_map('intval',array_keys($sources['old_product_paths']));$actual=array_keys($observed);sort($listed,SORT_NUMERIC);sort($actual,SORT_NUMERIC);pdg_need($listed===$actual&&count($actual)===$sources['old_catalog_count'],'Catalog index differs from captured pages');
    $pages=array_keys($discovered);sort($pages,SORT_NUMERIC);pdg_need($pages===range(2,10),'Captured pagination coverage incomplete');
    $expected=['https://thai-online.org/shop/all'];for($p=2;$p<=10;$p++)$expected[]='https://thai-online.org/shop/all/'.$p;pdg_need($paths===$expected,'Wrong catalog page coverage');
    $allowed=[158=>[670,'post_content'],170=>[674,'post_status'],491=>[996,'post_status'],510=>[965,'post_content']];$seen=[];
    foreach($m['items'] as $item){
        $legacy=$item['legacy_id'];pdg_need(isset($allowed[$legacy])&&!isset($seen[$legacy]),'Unreviewed/duplicate product');$seen[$legacy]=true;
        pdg_need([$item['wp_id'],$item['field']]===$allowed[$legacy],'Identity/field drift');
        $s=$item['source'];pdg_need(($s['tls_verified']??false)===true,'Source transport not verified');
        pdg_need($s['url']===$item['authoritative_url']&&str_starts_with($s['url'],'https://thai-online.org/shop/'.$legacy.'/desc/'),'Wrong authoritative source URL');
        if($fresh)pdg_fresh($s['fetched_at']);$source=pdg_artifact($s);
        if($item['field']==='post_content'){
            $before=pdg_artifact($item['before']);$after=pdg_artifact($item['after']);
            pdg_need($before!==$after&&pdg_proposed($legacy,$before,$source)===$after,'Proposal differs from authoritative content/scope');
        }else{
            pdg_need($item['before_value']==='publish'&&$item['after_value']==='draft','Only reviewed deactivation supported');
            pdg_need($s['final_url']===$s['url'].'-cancel'&&str_contains($source,'Это направление сейчас недоступно!')&&str_contains($source,'id="u_social"'),'Unavailable-source evidence missing');
            pdg_need(!isset($sources['old_product_paths'][$legacy]),'Cancelled product is listed in current catalog');
        }
    }
    pdg_need(count($seen)===4,'Expected exact four-product batch');
}
function pdg_project($snapshot,$m,$reverse=false) {
    $result=$snapshot;$status=[];
    foreach($m['items'] as $i){
        $id=$i['wp_id'];$field=$i['field'];
        $value=$field==='post_content'?pdg_artifact($i[$reverse?'before':'after']):$i[$reverse?'before_value':'after_value'];
        $result['items'][$id]['row'][$field]=$value;if($field==='post_status')$status[$id]=$value;
    }
    foreach($result['members'] as &$r)if(isset($status[(int)$r['object_id']]))$r['post_status']=$status[(int)$r['object_id']];unset($r);
    foreach($result['terms'] as &$term){
        pdg_need($term['taxonomy']==='thai_excursion_cat','Unexpected associated taxonomy');
        $count=0;foreach($result['members'] as $r)if($r['term_taxonomy_id']===$term['term_taxonomy_id']&&$r['post_type']==='thai_excursion'&&$r['post_status']==='publish')$count++;
        $term['count']=(string)$count;
    }unset($term);
    return $result;
}
function pdg_state_guard($snapshot,$m,$expected) {
    pdg_need(pdg_hash($snapshot)===$expected,'Product/meta/comment/relationship/term/peer state drift');
    foreach($m['items'] as $i){
        $s=$snapshot['items'][$i['wp_id']]??null;
        $original=$expected===$m['snapshot_sha256'];$status=$original?'publish':($i['field']==='post_status'?'draft':'publish');
        pdg_need($s&&$s['row']['post_status']===$status&&$s['legacy_id']===$i['legacy_id']&&$s['row']['post_type']==='thai_excursion'&&$i['authoritative_url']==='https://thai-online.org/shop/'.$i['legacy_id'].'/desc/'.$s['row']['post_name'],'Live/source identity mismatch');
    }
}
function pdg_path($path) {
    $dir=realpath(dirname($path));$web=realpath(ABSPATH);
    pdg_need($dir&&$dir!==$web&&!str_starts_with($dir,$web.'/')&&!is_link($path),'Journal must be outside webroot, without symlink');
    pdg_need((fileperms($dir)&0077)===0,'Journal directory must be private');
    return $dir.'/'.basename($path);
}
function pdg_journal($path,$value,$new=false) {
    $path=pdg_path($path);$raw=json_encode($value,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR);
    if($new){$f=@fopen($path,'x');pdg_need((bool)$f,'Receipt exists; inspect prior run');chmod($path,0600);pdg_need(fwrite($f,$raw)===strlen($raw)&&fflush($f),'Cannot prepare journal');if(function_exists('fsync'))pdg_need(fsync($f),'Cannot sync journal');fclose($f);return;}
    $tmp=$path.'.tmp-'.bin2hex(random_bytes(8));$f=fopen($tmp,'x');pdg_need((bool)$f,'Cannot create journal temporary file');chmod($tmp,0600);pdg_need(fwrite($f,$raw)===strlen($raw)&&fflush($f),'Cannot write journal');if(function_exists('fsync'))pdg_need(fsync($f),'Cannot sync journal');fclose($f);pdg_need(rename($tmp,$path),'Cannot finalize journal');
}
function pdg_backup($m,PdgStore $store) {
    $p=getenv('THAI_PRODUCT_DATA_BACKUP');$sha=getenv('THAI_PRODUCT_DATA_BACKUP_SHA256');
    pdg_need($p&&$sha&&preg_match('/^[a-f0-9]{64}$/',$sha),'Verified full DB backup required');
    $real=realpath($p);pdg_need($real&&is_file($real)&&!str_starts_with($real,realpath(ABSPATH).'/')&&!is_link($p),'Backup must be outside webroot');
    pdg_need((fileperms($real)&0077)===0,'Backup file must be private');
    pdg_need(hash_file('sha256',$real)===$sha,'Backup hash mismatch');
    $age=time()-filemtime($real);pdg_need($age>=0&&$age<=86400&&filemtime($real)>=strtotime($m['created_at']),'Backup is stale or precedes manifest');
    $tables=array_fill_keys($store->allTables(),false);$header=false;$complete=false;$f=fopen($real,'r');
    while(($line=fgets($f))!==false){if(str_contains($line,'-- Dump completed'))$complete=true;if(str_contains($line,'MySQL dump')||str_contains($line,'MariaDB dump'))$header=true;foreach($tables as $t=>$seen)if(!$seen&&str_contains($line,'CREATE TABLE `'.$t.'`'))$tables[$t]=true;}
    fclose($f);pdg_need($header&&$complete&&!in_array(false,$tables,true),'Backup completion/table coverage incomplete');
    return ['path'=>$real,'sha256'=>$sha,'bytes'=>filesize($real)];
}
function pdg_run($m,$manifest_sha,$mode,$receipt,PdgStore $store) {
    pdg_need(in_array($mode,['dry-run','apply','rollback','verify-receipt'],true),'Unknown mode');
    $reverse=$mode==='rollback';$verify=$mode==='verify-receipt';pdg_validate($m,!$reverse&&!$verify);pdg_runtime($m);
    pdg_need($store->accounts()===$m['accounts_sha256'],'Account fingerprint drift');
    $engines=$store->engines();pdg_need(count($engines)===8,'Incomplete transactional table coverage');
    foreach($engines as $engine)pdg_need(strtolower((string)$engine)==='innodb','Transactional tables required');
    pdg_need(($m['database_tables']??null)===$store->allTables(),'Database table coverage drift');
    $ids=array_column($m['items'],'wp_id');$snapshot=$store->snapshot($ids);$expected=$m['snapshot_sha256'];$journal=null;
    if($reverse||$verify){
        $receipt=pdg_path($receipt);$raw=file_get_contents($receipt);pdg_need($raw!==false,'Receipt missing');$journal=json_decode($raw,true,512,JSON_THROW_ON_ERROR);
        pdg_need(($journal['manifest_sha256']??'')===$manifest_sha&&in_array($journal['state']??'', ['PREPARED','COMMITTED','ROLLED_BACK'],true),'Receipt/manifest/state mismatch');
        pdg_need(($journal['before_snapshot_sha256']??'')===$m['snapshot_sha256']&&($journal['after_snapshot_sha256']??'')===$m['projected_snapshot_sha256'],'Receipt snapshot mismatch');
        $actual=pdg_hash($snapshot);
        if($verify)return ['mode'=>'VERIFY_RECEIPT','receipt_state'=>$journal['state'],'cache_state'=>$journal['cache_state']??'UNKNOWN','database_state'=>$actual===$m['projected_snapshot_sha256']?'APPLIED':($actual===$m['snapshot_sha256']?'ORIGINAL':'DRIFT'),'production_mutations'=>0];
        pdg_need($journal['state']!=='ROLLED_BACK','Already rolled back');
        pdg_need(getenv('THAI_PRODUCT_DATA_CONFIRM')==='ROLLBACK_PRODUCT_DATA:'.hash('sha256',$raw),'Explicit rollback confirmation required');
        $expected=$m['projected_snapshot_sha256'];
    }
    pdg_state_guard($snapshot,$m,$expected);
    $projected=pdg_project($snapshot,$m,$reverse);
    pdg_need(pdg_hash($projected)===($reverse?$m['snapshot_sha256']:$m['projected_snapshot_sha256']),'Projection/rollback mismatch');
    pdg_need(pdg_hash(pdg_project($projected,$m,!$reverse))===$expected,'Native term-count baseline is inconsistent; rollback would not be exact');
    if($mode==='dry-run')return ['mode'=>'DRY_RUN','manifest_sha256'=>$manifest_sha,'production_mutations'=>0,'products'=>array_column($m['items'],'legacy_id'),'fields'=>array_column($m['items'],'field'),'term_count_delta'=>array_map(fn($a,$b)=>['term_taxonomy_id'=>$a['term_taxonomy_id'],'before'=>(int)$a['count'],'after'=>(int)$b['count']],$snapshot['terms'],$projected['terms']),'registration'=>0,'apply_authorized'=>false];
    pdg_need(getenv('THAI_PRODUCT_DATA_MASTER_REVIEW')===$manifest_sha,'Separate explicit MASTER review required');
    if(!$reverse)pdg_need(getenv('THAI_PRODUCT_DATA_CONFIRM')==='APPLY_PRODUCT_DATA:'.$manifest_sha,'Explicit apply confirmation required');
    $backup=$reverse?null:pdg_backup($m,$store);
    $receipt=pdg_path($receipt);pdg_need($store->acquire(),'Concurrent product operation');
    $started=false;$committed=false;$reserved=false;$operation_path=$reverse?$receipt.'.rollback.json':$receipt;
    try {
        $started=true;$store->begin($ids);$locked=$store->snapshot($ids);pdg_state_guard($locked,$m,$expected);
        pdg_need($store->accounts()===$m['accounts_sha256'],'Concurrent account drift');pdg_runtime($m);
        $operation=['state'=>'PREPARED','operation'=>$reverse?'ROLLBACK':'APPLY','created_at'=>gmdate('c'),'manifest_sha256'=>$manifest_sha,'before_snapshot_sha256'=>$m['snapshot_sha256'],'after_snapshot_sha256'=>$m['projected_snapshot_sha256'],'backup'=>$backup,'cache_state'=>'PENDING'];
        pdg_journal($operation_path,$operation,true);$reserved=true;
        foreach($m['items'] as $i)$store->post($i['wp_id'],$i['field'],$projected['items'][$i['wp_id']]['row'][$i['field']]);
        foreach($projected['terms'] as $n=>$term)if($term['count']!==$locked['terms'][$n]['count'])$store->term((int)$term['term_taxonomy_id'],$term['count']);
        pdg_state_guard($store->snapshot($ids),$m,pdg_hash($projected));pdg_need($store->accounts()===$m['accounts_sha256'],'Unexpected account mutation');
        $store->commit();$committed=true;
        $operation['state']=$reverse?'ROLLED_BACK':'COMMITTED';$operation['finished_at']=gmdate('c');pdg_journal($operation_path,$operation);
        if($reverse){$journal['state']='ROLLED_BACK';$journal['rollback_finished_at']=gmdate('c');$journal['cache_state']='PENDING';pdg_journal($receipt,$journal);}
        $store->caches($ids,$projected['terms']);$operation['cache_state']='CLEARED';pdg_journal($operation_path,$operation);
        if($reverse){$journal['cache_state']='CLEARED';pdg_journal($receipt,$journal);}
        return ['mode'=>$mode,'state'=>$operation['state'],'receipt'=>$receipt,'cache_state'=>'CLEARED','production_mutations'=>$reverse?'REVIEWED_ROLLBACK':'REVIEWED_APPLY'];
    }catch(Throwable $e){
        if($started&&!$committed){$store->rollback();if($reserved){$operation['state']='ABORTED';$operation['error']='Transaction rolled back; inspect error output';pdg_journal($operation_path,$operation);}}
        if($committed)throw new RuntimeException('DATABASE COMMITTED; receipt/cache finalization needs review. Use verify-receipt; never blindly repeat. '.$e->getMessage(),0,$e);
        throw $e;
    }finally{$store->release();}
}
if(defined('THAI_PRODUCT_DATA_LIBRARY_ONLY'))return;
try {
    $path=$args[0]??'';pdg_need($path&&is_file($path),'Manifest required');
    $raw=file_get_contents($path);$m=json_decode($raw,true,512,JSON_THROW_ON_ERROR);
    $result=pdg_run($m,hash('sha256',$raw),$args[1]??'dry-run',$args[2]??dirname($path).'/product-data-receipt.json',new PdgWordPressStore($GLOBALS['wpdb']));
    echo json_encode($result,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)."\n";
}catch(Throwable $error){WP_CLI::error($error->getMessage());}
