<?php
/** MASTER-only rollback for forum-followup-import.php. Default dry-run. */
if(!defined('ABSPATH')){fwrite(STDERR,"Use wp eval-file; default is dry-run.\n");exit(2);}
define('THAI_FORUM_FOLLOWUP_LIBRARY',1);
require __DIR__.'/forum-followup-import.php';

function ff_rollback_load(){
    $journal=getenv('THAI_FORUM_JOURNAL')?:'/home/thaionline/tmp-parity/forum-followup-20261005/apply-receipt.json';
    $manifest=getenv('THAI_FORUM_MANIFEST')?:'/home/thaionline/tmp-parity/forum-followup-20261005/data-manifest.json';
    ff_require(is_file($journal),'Committed receipt not found');
    ff_require(is_file($manifest),'Manifest not found');
    $r=json_decode(file_get_contents($journal),true);$raw=file_get_contents($manifest);$m=json_decode($raw,true);
    ff_require(is_array($r)&&($r['state']??'')==='COMMITTED','Receipt is not COMMITTED');
    ff_require(is_array($m),'Manifest JSON invalid');
    ff_require(hash('sha256',$raw)===($r['manifest_sha256']??''),'Manifest differs from applied receipt');
    return [$r,$m,$journal];
}
function ff_rollback_guard($r,$m){
    ff_require((string)get_option('users_can_register')==='0','Registration must remain OFF');
    ff_require(trim(file_get_contents('/home/thaionline/autodeploy/last-deployed-commit'))===$m['production_commit'],'Production commit changed; manual rollback review required');
    $backup=getenv('THAI_FORUM_BACKUP');$backup_sha=getenv('THAI_FORUM_BACKUP_SHA256');
    ff_require($backup&&is_file($backup)&&filesize($backup)>0,'Verified backup required');
    ff_require($backup_sha&&hash_file('sha256',$backup)===$backup_sha&&$backup_sha===($r['backup_sha256']??''),'Backup hash differs from apply receipt');
    foreach(($r['created']['comments']??[]) as $id)ff_require(ff_fingerprint('comment',(int)$id)===($r['after']['comments'][$id]??null),'Created comment changed: '.$id);
    foreach(($r['created']['posts']??[]) as $id)ff_require(ff_fingerprint('post',(int)$id)===($r['after']['posts'][$id]??null),'Created topic changed: '.$id);
    foreach(($r['after']['existing']??[]) as $id=>$hash)ff_require(ff_fingerprint('post',(int)$id)===$hash,'Existing topic changed after import: '.$id);
    ff_require(ff_hash(get_option('thai_forum_public_members'))===($r['after']['members_sha256']??''),'Public member directory changed after import');
    foreach(($r['after']['media']??[]) as $file=>$hash)ff_require(is_file($file)&&hash_file('sha256',$file)===$hash,'Imported media changed/missing: '.$file);
}
function ff_rollback_apply($r,$m,$journal){
    global $wpdb;
    ff_require((int)$wpdb->get_var("SELECT GET_LOCK('thai_forum_followup',0)")===1,'Another importer holds lock');
    $committed=false;
    try{
        $wpdb->query('START TRANSACTION');
        foreach(($r['created']['comments']??[]) as $id)$wpdb->get_row($wpdb->prepare("SELECT comment_ID FROM {$wpdb->comments} WHERE comment_ID=%d FOR UPDATE",(int)$id));
        foreach(($r['created']['posts']??[]) as $id)$wpdb->get_row($wpdb->prepare("SELECT ID FROM {$wpdb->posts} WHERE ID=%d FOR UPDATE",(int)$id));
        foreach(($r['after']['existing']??[]) as $id=>$hash)$wpdb->get_row($wpdb->prepare("SELECT ID FROM {$wpdb->posts} WHERE ID=%d FOR UPDATE",(int)$id));
        $wpdb->get_row($wpdb->prepare("SELECT option_id FROM {$wpdb->options} WHERE option_name=%s FOR UPDATE",'thai_forum_public_members'));
        ff_rollback_guard($r,$m);
        foreach(array_reverse($r['created']['comments']??[]) as $id)ff_require(wp_delete_comment((int)$id,true)!==false,'Failed deleting comment '.$id);
        foreach(array_reverse($r['created']['posts']??[]) as $id)ff_require(wp_delete_post((int)$id,true)!==false,'Failed deleting topic '.$id);
        ff_require(update_option('thai_forum_public_members',$r['members_before']),'Failed restoring public member directory');
        foreach(($r['existing_before']??[]) as $legacy=>$before){
            $id=(int)$before['wp_id'];
            update_post_meta($id,'_thai_forum_updated',(string)$before['updated']);
            wp_update_comment_count_now($id);
        }
        ff_require($wpdb->query('COMMIT')!==false,'Rollback commit failed');$committed=true;
        $media_removed=[];$media_failed=[];
        foreach(($r['after']['media']??[]) as $file=>$hash){
            if(is_file($file)&&hash_file('sha256',$file)===$hash&&@unlink($file))$media_removed[]=$file;
            else $media_failed[]=$file;
        }
        $rollback_receipt=$journal.'.rollback.json';
        $out=['state'=>'ROLLED_BACK','source_receipt'=>$journal,'rolled_back_at'=>gmdate('c'),'topics_removed'=>count($r['created']['posts']??[]),'comments_removed'=>count($r['created']['comments']??[]),'public_members_restored'=>count($r['members_before']??[]),'media_removed'=>$media_removed,'media_failed'=>$media_failed,'current'=>ff_current_counts()];
        ff_require(file_put_contents($rollback_receipt,json_encode($out,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT),LOCK_EX)!==false,'Rollback completed but receipt write failed');
        chmod($rollback_receipt,0600);
        echo json_encode($out,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT)."\n";
    }catch(Throwable $e){
        if(!$committed)$wpdb->query('ROLLBACK');
        throw $e;
    }finally{$wpdb->query("SELECT RELEASE_LOCK('thai_forum_followup')");}
}
try{
    [$r,$m,$journal]=ff_rollback_load();
    ff_rollback_guard($r,$m);
    $mode=strtoupper(getenv('THAI_FORUM_ROLLBACK_MODE')?:'DRY_RUN');
    if($mode==='DRY_RUN'){
        echo json_encode(['mode'=>'DRY_RUN','production_mutations'=>0,'ready_for_rollback'=>true,'created'=>$r['created'],'media'=>array_keys($r['after']['media']??[]),'current'=>ff_current_counts()],JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT)."\n";
        return;
    }
    ff_require($mode==='APPLY','THAI_FORUM_ROLLBACK_MODE must be DRY_RUN or APPLY');
    ff_require(getenv('THAI_FORUM_CONFIRM')==='ROLLBACK_FORUM_FOLLOWUP_20261005','Explicit rollback confirmation missing');
    ff_rollback_apply($r,$m,$journal);
}catch(Throwable $e){
    fwrite(STDERR,'REFUSED: '.$e->getMessage()."\n");exit(2);
}
