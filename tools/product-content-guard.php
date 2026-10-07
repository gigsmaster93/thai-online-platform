<?php
/** Targeted product 510 content repair. wp eval-file FILE MANIFEST [dry-run|apply|rollback] [RECEIPT]. */
if (!defined('ABSPATH') || !defined('WP_CLI') || !WP_CLI) { fwrite(STDERR,"Use wp eval-file; default is dry-run.\n"); exit(2); }
function pcg_need($ok,$message) { if (!$ok) throw new RuntimeException($message); }
function pcg_hash($value) { return hash('sha256',json_encode($value,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR)); }
function pcg_file($path,$sha,$read=true) {
    $real=realpath($path); $web=realpath(ABSPATH);
    pcg_need($real && is_file($real) && !str_starts_with($real,$web.'/'),'Artifact must be outside webroot');
    pcg_need(hash_file('sha256',$real)===$sha,'Artifact changed: '.basename($path));
    return $read ? file_get_contents($real) : $real;
}
function pcg_accounts() {
    global $wpdb;
    return pcg_hash([$wpdb->get_results("SELECT * FROM {$wpdb->users} ORDER BY ID",ARRAY_A),
        $wpdb->get_results("SELECT * FROM {$wpdb->usermeta} ORDER BY umeta_id",ARRAY_A),
        $wpdb->get_results("SELECT * FROM {$wpdb->options} WHERE option_name IN ('users_can_register','default_role') ORDER BY option_name",ARRAY_A)]);
}
function pcg_state($id) {
    global $wpdb;
    return [$wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->posts} WHERE ID=%d",$id),ARRAY_A),
        pcg_hash($wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->postmeta} WHERE post_id=%d ORDER BY meta_id",$id),ARRAY_A))];
}
function pcg_write($path,$value,$exclusive=false) {
    pcg_need(realpath(dirname($path)) && !str_starts_with(realpath(dirname($path)),realpath(ABSPATH).'/'),'Receipt must be outside webroot');
    if ($exclusive) { $f=fopen($path,'x'); pcg_need((bool)$f,'Receipt exists; inspect previous run'); fclose($f); chmod($path,0600); }
    $tmp=$path.'.tmp';
    pcg_need(file_put_contents($tmp,json_encode($value,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR))!==false,'Cannot write receipt');
    chmod($tmp,0600); pcg_need(rename($tmp,$path),'Cannot finalize receipt');
}
function pcg_validate($m) {
    pcg_need(($m['schema']??null)===1 && ($m['scope']??'')==='product_content_only','Wrong manifest schema/scope');
    pcg_need(($m['wp_id']??0)===965 && ($m['legacy_id']??0)===510,'Only reviewed product 510 is supported');
    pcg_need(($m['source']['url']??'')==='https://thai-online.org/shop/510/desc/top-bangkok-2026','Wrong authoritative source');
    pcg_need(pcg_file($m['source']['path'],$m['source']['sha256'])!=='','Source is empty');
    $before=pcg_file($m['before']['path'],$m['before']['sha256']);
    $after=pcg_file($m['after']['path'],$m['after']['sha256']);
    pcg_need($before!==$after && !str_contains($before,'id="main-product-page"'),'Unexpected repair precondition');
    foreach (['id="main-product-page"','id="dscr"','id="tabContainer"','slideout-sidebar'] as $marker) pcg_need(str_contains($after,$marker),'Missing legacy marker');
    pcg_need(!preg_match('~<script\b|\bon[a-z]+\s*=|javascript:~i',$after),'Executable content in proposal');
    pcg_need((string)get_option('siteurl')===$m['site_url'],'Wrong WordPress site');
    return [$before,$after];
}
global $wpdb;
$manifest=$args[0]??''; $mode=$args[1]??'dry-run'; $receipt=$args[2]??dirname($manifest).'/product510-receipt.json';
try {
    pcg_need(in_array($mode,['dry-run','apply','rollback'],true),'Unknown mode');
    $raw=file_get_contents($manifest); $sha=hash('sha256',$raw); $m=json_decode($raw,true,512,JSON_THROW_ON_ERROR);
    [$before,$after]=pcg_validate($m); $id=$m['wp_id']; [$row,$meta_sha]=pcg_state($id);
    pcg_need($row && $row['post_type']==='thai_excursion' && $row['post_status']==='publish','Product identity/status changed');
    pcg_need((int)get_post_meta($id,'_ucoz_shop_id',true)===510,'Legacy identity changed');
    pcg_need($meta_sha===$m['meta_sha256'] && pcg_accounts()===$m['accounts_sha256'],'Product metadata/accounts changed');
    pcg_need((string)get_option('users_can_register')==='0','Registration must remain OFF');
    $expected=$m['row_sha256']; $content=$after; $journal=null;
    if ($mode==='rollback') {
        $receipt_raw=file_get_contents($receipt); $journal=json_decode($receipt_raw,true,512,JSON_THROW_ON_ERROR);
        pcg_need(in_array($journal['state']??'', ['PREPARED','COMMITTED'],true),'Wrong receipt state');
        pcg_need(($journal['manifest_sha256']??'')===$sha,'Receipt/manifest mismatch');
        pcg_need(getenv('THAI_PRODUCT_CONFIRM')==='ROLLBACK_PRODUCT_CONTENT:'.hash('sha256',$receipt_raw),'Explicit rollback confirmation required');
        $expected=$journal['after_row_sha256']; $content=$before;
    } else {
        $age=time()-strtotime($m['created_at']); $source_age=time()-strtotime($m['source']['fetched_at']); pcg_need($age>=0 && $age<=86400 && $source_age>=0 && $source_age<=86400,'Manifest/source older than 24h; refresh source');
        pcg_need(trim(file_get_contents('/home/thaionline/autodeploy/last-deployed-commit'))===$m['production_commit'],'Production commit changed');
        pcg_need((string)shell_exec('git -C /home/thaionline/autodeploy/thai-online-platform rev-parse HEAD 2>/dev/null')===$m['production_commit']."\n",'Develop differs from reviewed production');
        pcg_need(trim((string)shell_exec('git -C /home/thaionline/autodeploy/thai-online-platform status --porcelain --untracked-files=no'))==='','Production checkout is dirty');
        foreach ($m['production_files'] as $path=>$hash) pcg_need(is_file($path) && hash_file('sha256',$path)===$hash,'Live code changed: '.basename($path));
    }
    pcg_need(pcg_hash($row)===$expected,'Product row changed; refresh/review manifest');
    if ($mode==='dry-run') {
        echo json_encode(['mode'=>'DRY_RUN','mutations'=>0,'product'=>510,'wp_id'=>$id,'field'=>'post_content','before_bytes'=>strlen($before),'after_bytes'=>strlen($after),'manifest_sha256'=>$sha,'registration'=>0,'accounts_unchanged'=>true],JSON_PRETTY_PRINT)."\n"; return;
    }
    if ($mode==='apply') {
        pcg_need(getenv('THAI_PRODUCT_CONFIRM')==='APPLY_PRODUCT_CONTENT:'.$sha,'Explicit apply confirmation required');
        $backup=getenv('THAI_PRODUCT_DB_BACKUP');$backup_sha=getenv('THAI_PRODUCT_DB_BACKUP_SHA256');
        pcg_need($backup && $backup_sha && filesize(pcg_file($backup,$backup_sha,false))>100,'Verified DB backup required');
        pcg_need(time()-filemtime($backup)>=0 && time()-filemtime($backup)<=86400,'Fresh DB backup required');
    }
    foreach ([$wpdb->posts,$wpdb->postmeta,$wpdb->users,$wpdb->usermeta,$wpdb->options] as $table) {
        pcg_need(strtolower((string)$wpdb->get_var($wpdb->prepare('SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=%s',$table)))==='innodb','Transactional tables required');
    }
    pcg_need((int)$wpdb->get_var("SELECT GET_LOCK('thai_product_content_965',0)")===1,'Concurrent product content operation');
    $committed=false;
    try {
        pcg_need($wpdb->query('START TRANSACTION')!==false,'Cannot start transaction');
        $wpdb->get_row($wpdb->prepare("SELECT ID FROM {$wpdb->posts} WHERE ID=%d FOR UPDATE",$id));
        $wpdb->get_results($wpdb->prepare("SELECT meta_id FROM {$wpdb->postmeta} WHERE post_id=%d FOR UPDATE",$id));
        $wpdb->get_results("SELECT ID FROM {$wpdb->users} FOR UPDATE");$wpdb->get_results("SELECT umeta_id FROM {$wpdb->usermeta} FOR UPDATE");
        $wpdb->get_results("SELECT option_id FROM {$wpdb->options} WHERE option_name IN ('users_can_register','default_role') FOR UPDATE");
        [$locked,$locked_meta]=pcg_state($id);
        pcg_need(pcg_hash($locked)===$expected && $locked_meta===$m['meta_sha256'] && pcg_accounts()===$m['accounts_sha256'],'Concurrent data drift');
        $projected=$locked;$projected['post_content']=$content;
        if ($mode==='apply') {
            $journal=['state'=>'PREPARED','created_at'=>gmdate('c'),'manifest_sha256'=>$sha,'before_row_sha256'=>$expected,'after_row_sha256'=>pcg_hash($projected),'backup_sha256'=>$backup_sha,'product'=>510];
            pcg_write($receipt,$journal,true);
        }
        pcg_need($wpdb->update($wpdb->posts,['post_content'=>$content],['ID'=>$id],['%s'],['%d'])===1,'Content update failed');
        [$updated,$updated_meta]=pcg_state($id);
        pcg_need(pcg_hash($updated)===pcg_hash($projected) && $updated_meta===$m['meta_sha256'] && pcg_accounts()===$m['accounts_sha256'],'Unexpected field/account mutation');
        pcg_need($wpdb->query('COMMIT')!==false,'Commit failed');$committed=true;
        $journal['state']=$mode==='apply'?'COMMITTED':'ROLLED_BACK';$journal['finished_at']=gmdate('c');pcg_write($receipt,$journal);
        clean_post_cache($id); echo $journal['state']." product 510 content only\n";
    } catch (Throwable $e) {
        if (!$committed) $wpdb->query('ROLLBACK');
        throw $e;
    } finally { $wpdb->get_var("SELECT RELEASE_LOCK('thai_product_content_965')"); }
} catch (Throwable $e) { WP_CLI::error($e->getMessage()); }
