<?php
libxml_use_internal_errors(true);$root='/home/thaionline/tmp-parity/';
$backup=json_decode(file_get_contents($root.'community-backup-map.json'),true);$topics=[];$sections=[];
function cc($name){return 'contains(concat(" ",normalize-space(@class)," ")," '.$name.' ")';}
foreach(glob($root.'forum-live/*.html') as $f){
 $raw=preg_replace('~<script\b[^>]*>.*?</script>~is','',file_get_contents($f));$d=new DOMDocument();$d->loadHTML('<?xml encoding="UTF-8">'.$raw);$x=new DOMXPath($d);
 foreach($x->query('//a[@href]') as $a){if(preg_match('~^/forum/(\d+)$~',$a->getAttribute('href'),$m)){$sid=(int)$m[1];$name=trim($a->textContent);if(!$name)continue;$b=$backup['sections'][$sid]??[];$sections[$sid]=['name'=>$b[5]??$name,'parent'=>(int)($b[1]??0),'description'=>$b[6]??''];}}
 if(!preg_match('/^(\d+)-(\d+)-(\d+)\.html$/',basename($f),$m)||!(int)$m[2])continue;
 $section=(int)$m[1];$id=(int)$m[2];$title=$x->query('//a['.cc('forumBarA').']')->item(0);$nodes=$x->query('//*[starts-with(@id,"ucoz-forum-post-")]');
 if(!$title||!$nodes->length)continue;
 if(!isset($topics[$id]))$topics[$id]=['id'=>$id,'section'=>$section,'title'=>trim($title->textContent),'posts'=>[]];
 foreach($nodes as $e){
  $pid=(int)substr($e->getAttribute('id'),16);if(!$pid)continue;
  $table=$x->query('ancestor::table['.cc('postTable').'][1]',$e)->item(0);if(!$table)continue;
  $author=$x->query('.//a['.cc('postUser').']',$table)->item(0);
  $head=$x->query('.//td['.cc('postTdTop').']',$table);$dateText=$head->length>1?$head->item(1)->textContent:'';
  $dt=null;$number=$x->query('.//a['.cc('postNumberLink').']',$table)->item(0);
  if($number&&preg_match('~/forum/\d+-\d+-\d+-16-(\d+)~',$number->getAttribute('onclick'),$tm))$dt=(new DateTime('@'.$tm[1]))->setTimezone(new DateTimeZone('Asia/Bangkok'));
  elseif(preg_match('/(\d{2}\.\d{2}\.\d{4}),\s*(\d{2}:\d{2})/',$dateText,$dm))$dt=DateTime::createFromFormat('d.m.Y H:i',$dm[1].' '.$dm[2]);
  if(!$dt)continue;
  foreach($x->query('.//*',$e) as $n){foreach(iterator_to_array($n->attributes) as $a)if(stripos($a->name,'on')===0)$n->removeAttribute($a->name);if($n->hasAttribute('href'))$n->setAttribute('href',preg_replace('~^https?://thai-online\.org(?=/)~','',$n->getAttribute('href')));}
  $body='';foreach($e->childNodes as $n)$body.=$d->saveHTML($n);
  $topics[$id]['posts'][$pid]=['id'=>$pid,'author'=>$author?trim($author->textContent):'Гость','date'=>$dt->format('Y-m-d H:i:00'),'content'=>$body];
 }
}
$total=0;foreach($topics as $id=>&$t){if(!$t['posts']){unset($topics[$id]);continue;}$t['posts']=array_values($t['posts']);usort($t['posts'],fn($a,$b)=>strcmp($a['date'],$b['date']));$t['date']=$t['posts'][0]['date'];$t['updated']=strtotime(end($t['posts'])['date']);$total+=count($t['posts']);}unset($t);
foreach($sections as &$s){if(!isset($sections[$s['parent']]))$s['parent']=0;}unset($s);
file_put_contents($root.'forum-prepared.json',json_encode(array_values($topics),JSON_UNESCAPED_UNICODE));file_put_contents($root.'forum-sections-prepared.json',json_encode($sections,JSON_UNESCAPED_UNICODE));
echo json_encode(['sections'=>count($sections),'topics'=>count($topics),'messages'=>$total])."\n";