<?php
/** MASTER-only guarded additive importer. Default dry-run; never creates WP users. */
if(!defined('ABSPATH')){fwrite(STDERR,"Use wp eval-file; default is dry-run.\n");exit(2);}
function ff_require($ok,$message){if(!$ok)throw new RuntimeException($message);}
function ff_hash($value){return hash('sha256',serialize($value));}
function ff_fingerprint($kind,$id){
    global $wpdb;
    $table=$kind==='post'?$wpdb->posts:$wpdb->comments;$key=$kind==='post'?'ID':'comment_ID';
    $meta=$kind==='post'?$wpdb->postmeta:$wpdb->commentmeta;$meta_key=$kind==='post'?'post_id':'comment_id';
    return ff_hash([$wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE $key=%d",$id),ARRAY_A),$wpdb->get_results($wpdb->prepare("SELECT * FROM $meta WHERE $meta_key=%d ORDER BY meta_id",$id),ARRAY_A)]);
}
function ff_guard_sources($m){
    foreach($m['sources'] as $file=>$hash)ff_require(is_file($file)&&hash_file('sha256',$file)===$hash,'Source changed/missing: '.basename($file));
    foreach($m['media'] as $a){$file=$a['existing']?$a['target']:$a['staged'];ff_require(is_file($file)&&hash_file('sha256',$file)===$a['sha256'],'Media changed/missing: '.$a['path']);}
}
function ff_topic($legacy){
    return get_posts(['post_type'=>'thai_forum_topic','post_status'=>'any','numberposts'=>2,'meta_key'=>'_ucoz_forum_id','meta_value'=>(string)$legacy]);
}
function ff_guard_data($m){
    global $wpdb;
    ff_require((string)get_option('users_can_register')==='0','Registration must remain OFF');
    ff_require(get_option('default_role')==='subscriber','Default role changed');
    ff_require(hash('sha256',serialize(get_option('thai_forum_public_members'))) === $m['guards']['member_option_sha256'],'Member option changed');
    $sections=get_option('thai_forum_sections',[]);
    foreach($m['topics'] as $t){ff_require(!ff_topic($t['id']),'Topic already exists: '.$t['id']);preg_match('~^/forum/(\d+)-(\d+)-1$~',$t['url'],$match);ff_require(isset($match[2])&&(int)$match[2]===$t['id']&&isset($sections[$match[1]]),'Invalid topic route/section');}
    foreach($m['posts'] as $p){
        ff_require($p['epoch']>0&&$p['content']!==''&&$p['author']!=='','Incomplete public message');
        ff_require(!(int)$wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->commentmeta} WHERE meta_key='_ucoz_forum_post' AND meta_value=%s",(string)$p['id'])),'Message already exists: '.$p['id']);
    }
    foreach($m['existing_topics'] as $legacy=>$t){
        $found=ff_topic($legacy);ff_require(count($found)===1&&(int)$found[0]->ID===$t['wp_id'],'Existing topic identity changed');$p=$found[0];
        ff_require($p->post_status===$t['status']&&$p->post_title===$t['title']&&hash('sha256',$p->post_content)===$t['content_sha256'],'Existing topic changed');
        ff_require((int)get_comments_number($p->ID)===$t['comment_count']&&(string)get_post_meta($p->ID,'_thai_forum_updated',true)===(string)$t['updated'],'Existing topic messages changed');
    }
}
function ff_apply($m,$sha,$journal){
    global $wpdb;
    ff_validate_manifest($m);
    ff_guard_sources($m);
    $backup=getenv('THAI_FORUM_BACKUP');$backup_sha=getenv('THAI_FORUM_BACKUP_SHA256');
    ff_require($backup&&is_file($backup)&&filesize($backup)>0&&hash_file('sha256',$backup)===$backup_sha,'Verified backup required');
    $age=time()-strtotime($m['created_at']);ff_require($age>=0&&$age<=86400,'Manifest older than 24h: refresh current source first');
    ff_require(trim(file_get_contents('/home/thaionline/autodeploy/last-deployed-commit'))===$m['production_commit'],'Production commit changed');
    foreach([$wpdb->posts,$wpdb->postmeta,$wpdb->comments,$wpdb->commentmeta,$wpdb->options] as $table){
        $engine=$wpdb->get_var($wpdb->prepare('SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=%s',$table));
        ff_require(strtolower((string)$engine)==='innodb','Transactional table required: '.$table);
    }
    ff_require(!file_exists($journal),'Journal already exists; review previous run');
    ff_require((int)$wpdb->get_var("SELECT GET_LOCK('thai_forum_followup',0)")===1,'Another importer holds lock');
    $created=['posts'=>[],'comments'=>[]];$copied=[];$committed=false;
    $receipt=['manifest_sha256'=>$sha,'state'=>'PREPARED','backup_sha256'=>$backup_sha,'members_before'=>get_option('thai_forum_public_members'),'existing_before'=>$m['existing_topics'],'created'=>$created,'media'=>[]];
    try{
        $wpdb->query('START TRANSACTION');
        $wpdb->get_results("SELECT option_id FROM {$wpdb->options} WHERE option_name IN ('thai_forum_public_members','users_can_register','default_role') FOR UPDATE");
        foreach($m['existing_topics'] as $t)$wpdb->get_row($wpdb->prepare("SELECT ID FROM {$wpdb->posts} WHERE ID=%d FOR UPDATE",$t['wp_id']));
        ff_guard_data($m);
        $topic_ids=[];
        foreach($m['existing_topics'] as $legacy=>$t)$topic_ids[$legacy]=$t['wp_id'];
        foreach($m['topics'] as $t){
            $first=array_values(array_filter($m['posts'],fn($p)=>$p['topic']===$t['id']))[0]??null;ff_require($first!==null,'Missing opening message');
            preg_match('~^/forum/(\d+)-~',$t['url'],$section);$local=(new DateTime('@'.$first['epoch']))->setTimezone(new DateTimeZone('Asia/Bangkok'))->format('Y-m-d H:i:s');
            $id=wp_insert_post(wp_slash(['post_type'=>'thai_forum_topic','post_status'=>'publish','post_title'=>$t['title'],'post_content'=>'','post_date'=>$local,'post_date_gmt'=>gmdate('Y-m-d H:i:s',$first['epoch'])]),true);
            ff_require(!is_wp_error($id)&&$id,'Topic insert failed');$created['posts'][]=(int)$id;$topic_ids[$t['id']]=$id;
            foreach(['_ucoz_forum_id'=>$t['id'],'_thai_forum_section'=>$section[1],'_thai_forum_author'=>$first['author'],'_thai_forum_description'=>$t['description'],'_thai_forum_views'=>$t['views'],'_thai_forum_pinned'=>$t['pinned']?1:0,'_thai_migration_batch'=>$m['batch']] as $key=>$value)update_post_meta($id,$key,wp_slash((string)$value));
        }
        $updated=[];
        foreach($m['posts'] as $p){
            ff_require(isset($topic_ids[$p['topic']]),'Unknown message topic');
            $local=(new DateTime('@'.$p['epoch']))->setTimezone(new DateTimeZone('Asia/Bangkok'))->format('Y-m-d H:i:s');
            $body=preg_replace('~https?://thai-online\.org(?=/)~','',$p['content']);
            $id=wp_insert_comment(wp_slash(['comment_post_ID'=>$topic_ids[$p['topic']],'comment_approved'=>1,'comment_type'=>'comment','comment_author'=>$p['author'],'comment_content'=>wp_kses_post($body),'comment_date'=>$local,'comment_date_gmt'=>gmdate('Y-m-d H:i:s',$p['epoch'])]));
            ff_require((bool)$id,'Comment insert failed');$created['comments'][]=(int)$id;
            $meta=['_ucoz_forum_post'=>$p['id'],'_thai_migration_batch'=>$m['batch']];
            foreach(['avatar','rank','group','posts','reputation','group_icon','attachments'] as $key)$meta['_thai_forum_'.$key]=$key==='attachments'?wp_kses_post($p[$key]):$p[$key];
            foreach($meta as $key=>$value)add_comment_meta($id,$key,wp_slash((string)$value),true);
            $updated[$p['topic']]=max($updated[$p['topic']]??0,$p['epoch']);
        }
        foreach($updated as $legacy=>$epoch){update_post_meta($topic_ids[$legacy],'_thai_forum_updated',(string)$epoch);wp_update_comment_count_now($topic_ids[$legacy]);}
        $members=get_option('thai_forum_public_members');$member_ids=array_column($members,'id');
        foreach($m['members'] as $member){ff_require(!in_array($member['id'],$member_ids),'Member already present');$members[]=$member;}
        ff_require(update_option('thai_forum_public_members',$members),'Member option update failed');
        foreach($m['media'] as $a){
            if($a['existing'])continue;
            ff_require(!file_exists($a['target']),'Media destination appeared');
            ff_require(wp_mkdir_p(dirname($a['target'])),'Media directory failed');
            $handle=fopen($a['target'],'x');ff_require($handle!==false,'Media exclusive create failed');$copied[]=$a['target'];
            $bytes=file_get_contents($a['staged']);$written=fwrite($handle,$bytes);fclose($handle);ff_require($written===strlen($bytes)&&hash_file('sha256',$a['target'])===$a['sha256'],'Media copy incomplete');
        }
        $receipt['created']=$created;$receipt['media']=$copied;$receipt['after']=['posts'=>[],'comments'=>[],'existing'=>[],'media'=>[],'members_sha256'=>ff_hash($members)];
        foreach($created['posts'] as $id)$receipt['after']['posts'][$id]=ff_fingerprint('post',$id);
        foreach($created['comments'] as $id)$receipt['after']['comments'][$id]=ff_fingerprint('comment',$id);
        foreach($m['existing_topics'] as $legacy=>$t)$receipt['after']['existing'][$t['wp_id']]=ff_fingerprint('post',$t['wp_id']);
        foreach($copied as $file)$receipt['after']['media'][$file]=hash_file('sha256',$file);
        ff_require(file_put_contents($journal,json_encode($receipt,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT),LOCK_EX)!==false,'Cannot write rollback receipt');chmod($journal,0600);
        ff_require($wpdb->query('COMMIT')!==false,'Commit failed');$committed=true;
        $receipt['state']='COMMITTED';ff_require(file_put_contents($journal,json_encode($receipt,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT),LOCK_EX)!==false,'Committed; receipt marker update failed: inspect before retry');
        foreach($topic_ids as $id)clean_post_cache($id);
        echo json_encode(['mode'=>'APPLY','topics'=>count($created['posts']),'messages'=>count($created['comments']),'public_members'=>count($m['members']),'wp_users_created'=>0])."\n";
    }catch(Throwable $e){if(!$committed){$wpdb->query('ROLLBACK');foreach($copied as $file)unlink($file);wp_cache_delete('thai_forum_public_members','options');}throw $e;}
    finally{$wpdb->query("SELECT RELEASE_LOCK('thai_forum_followup')");}
}

function ff_validate_manifest($m){
    ff_require(is_array($m)&&($m['schema']??null)===1,'Unsupported manifest schema');
    foreach(['batch','created_at','production_commit','guards','sources','topics','posts','members','existing_topics','media'] as $key)ff_require(array_key_exists($key,$m),'Manifest missing '.$key);
    ff_require(preg_match('/^[0-9a-f]{40}$/',$m['production_commit'])===1,'Invalid production commit');
    $topic_ids=[];$message_ids=[];$member_ids=[];
    foreach($m['topics'] as $t){
        $id=(int)($t['id']??0);ff_require($id>0&&!isset($topic_ids[$id]),'Duplicate/invalid topic id');$topic_ids[$id]=true;
        ff_require(preg_match('~^/forum/(\d+)-'.$id.'-1$~',(string)($t['url']??''))===1,'Invalid topic URL '.$id);
    }
    $existing_ids=array_map('intval',array_keys($m['existing_topics']));
    foreach($m['posts'] as $p){
        $id=(int)($p['id']??0);ff_require($id>0&&!isset($message_ids[$id]),'Duplicate/invalid message id');$message_ids[$id]=true;
        $topic=(int)($p['topic']??0);ff_require(isset($topic_ids[$topic])||in_array($topic,$existing_ids,true),'Message references unknown topic '.$topic);
        $source=(string)($p['source']??'');ff_require(isset($m['sources'][$source]),'Message source not pinned');
        ff_require(($p['source_sha256']??'')===$m['sources'][$source],'Message source hash mismatch');
    }
    foreach(array_keys($topic_ids) as $topic)ff_require(count(array_filter($m['posts'],fn($p)=>(int)$p['topic']===$topic))>=1,'New topic lacks opening message '.$topic);
    foreach($m['members'] as $member){$id=(int)($member['id']??0);ff_require($id>0&&!isset($member_ids[$id]),'Duplicate/invalid member id');$member_ids[$id]=true;}
    $public='/home/thaionline/public_html';$stage='/home/thaionline/tmp-parity/forum-followup-20261005/media';
    foreach($m['media'] as $a){
        $path=(string)($a['path']??'');$target=(string)($a['target']??'');$staged=(string)($a['staged']??'');
        ff_require($path!==''&&str_starts_with($path,'/'),'Invalid media path');
        ff_require($target===$public.$path,'Media target escapes public root');
        if(empty($a['existing']))ff_require(str_starts_with($staged,$stage.'/'),'Media staging path escapes staging root');
        ff_require(preg_match('/^[0-9a-f]{64}$/',(string)($a['sha256']??''))===1,'Invalid media hash');
    }
}
function ff_current_counts(){
    global $wpdb;
    $counts=$wpdb->get_row("SELECT COUNT(*) topics,COALESCE(SUM(GREATEST(COALESCE(c.messages,0)-IF(TRIM(p.post_content)='',1,0),0)),0) replies FROM {$wpdb->posts} p LEFT JOIN (SELECT comment_post_ID,COUNT(*) messages FROM {$wpdb->comments} WHERE comment_approved='1' GROUP BY comment_post_ID) c ON c.comment_post_ID=p.ID WHERE p.post_type='thai_forum_topic' AND p.post_status='publish'",ARRAY_A);
    $members=get_option('thai_forum_public_members',[]);
    return ['topics'=>(int)$counts['topics'],'replies'=>(int)$counts['replies'],'public_members'=>is_array($members)?count($members):0];
}
function ff_dry_run($m,$sha){
    ff_validate_manifest($m);ff_guard_sources($m);ff_guard_data($m);
    $deploy=trim(file_get_contents('/home/thaionline/autodeploy/last-deployed-commit'));
    ff_require($deploy===$m['production_commit'],'Production commit changed');
    $age=time()-strtotime($m['created_at']);ff_require($age>=0&&$age<=86400,'Manifest older than 24h: refresh current source first');
    $current=ff_current_counts();
    $reply_delta=count($m['posts'])-count($m['topics']);
    $projected=['topics'=>$current['topics']+count($m['topics']),'replies'=>$current['replies']+$reply_delta,'public_members'=>$current['public_members']+count($m['members'])];
    echo json_encode([
        'mode'=>'DRY_RUN','production_mutations'=>0,'ready_for_master_review'=>true,'apply_allowed'=>false,
        'manifest_sha256'=>$sha,'manifest_age_seconds'=>$age,'production_commit'=>$deploy,
        'current'=>$current,
        'delta'=>['topics'=>count($m['topics']),'messages'=>count($m['posts']),'legacy_replies'=>$reply_delta,'public_members'=>count($m['members']),'wp_users'=>0],
        'projected'=>$projected,'media'=>['total'=>count($m['media']),'new'=>count(array_filter($m['media'],fn($a)=>empty($a['existing'])))]
    ],JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT)."\n";
}
if(!defined('THAI_FORUM_FOLLOWUP_LIBRARY'))try{
    $manifest=getenv('THAI_FORUM_MANIFEST')?:'/home/thaionline/tmp-parity/forum-followup-20261005/data-manifest.json';
    ff_require(is_file($manifest),'Manifest file not found');
    $raw=file_get_contents($manifest);$m=json_decode($raw,true);ff_require(is_array($m),'Manifest JSON invalid');
    $sha=hash('sha256',$raw);
    $mode=strtoupper(getenv('THAI_FORUM_MODE')?:'DRY_RUN');
    if($mode==='DRY_RUN'){ff_dry_run($m,$sha);return;}
    ff_require($mode==='APPLY','THAI_FORUM_MODE must be DRY_RUN or APPLY');
    ff_require(getenv('THAI_FORUM_CONFIRM')==='APPLY_FORUM_FOLLOWUP_20261005','Explicit apply confirmation missing');
    $journal=getenv('THAI_FORUM_JOURNAL')?:'/home/thaionline/tmp-parity/forum-followup-20261005/apply-receipt.json';
    ff_apply($m,$sha,$journal);
}catch(Throwable $e){
    fwrite(STDERR,'REFUSED: '.$e->getMessage()."\n");exit(2);
}
