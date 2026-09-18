<?php
libxml_use_internal_errors(true);
$root='/home/thaionline/tmp-parity/';
$backup=json_decode(file_get_contents($root.'community-backup-map.json'),true);
function domfile($f){$d=new DOMDocument();$s=preg_replace('~<script\b[^>]*>.*?</script>~is','',file_get_contents($f));$d->loadHTML('<?xml encoding="UTF-8">'.$s);return [$d,new DOMXPath($d)];}
function cls($name){return 'contains(concat(" ",normalize-space(@class)," ")," '.$name.' ")';}
function inner($d,$e){$s='';if($e)foreach($e->childNodes as $n)$s.=$d->saveHTML($n);return $s;}
function clean($x,$e){foreach($x->query('.//*',$e) as $n){foreach(iterator_to_array($n->attributes) as $a){if(stripos($a->name,'on')===0)$n->removeAttribute($a->name);}if($n->hasAttribute('href')){$u=$n->getAttribute('href');if(stripos($u,'javascript:')===0)$n->removeAttribute('href');else $n->setAttribute('href',preg_replace('~^https?://thai-online\.org(?=/)~','',$u));}}}
$out=fopen($root.'gallery-prepared.jsonl','w');$photos=[];$albums=[];
foreach(glob($root.'gallery-live/*.html') as $f){
 [$d,$x]=domfile($f);
 foreach($x->query('//*[starts-with(@id,"entryID")]') as $e){
  $a=$x->query('.//*['.cls('photo-title').']/a',$e)->item(0);$img=$x->query('.//img',$e)->item(0);$full=$x->query('.//a['.cls('ph-link').']',$e)->item(0);
  if(!$a||!$img||!$full)continue;$u=$a->getAttribute('href');$id=(int)substr($e->getAttribute('id'),7);if(!preg_match('~/_ph/(\d+)/~',$full->getAttribute('href'),$m))continue;
  $album=(int)$m[1];if(isset($photos[$id]))continue;$photos[$id]=true;
  $chunks=explode('/',trim($u,'/'));array_pop($chunks);array_pop($chunks);$album_url='/'.implode('/',$chunks).'/'.$album;
  if(strpos($u,'/photo/')!==0){$slugs=[];$cid=$album;while($cid&&isset($backup['albums'][$cid])){array_unshift($slugs,$backup['albums'][$cid][10]);$cid=(int)$backup['albums'][$cid][1];}$album_url='/photo/'.implode('/',$slugs).'/'.$album;}
  $albums[$album]=['id'=>$album,'url'=>$album_url,'name'=>$backup['albums'][$album][5]??str_replace('_',' ',end($chunks)),'parent'=>(int)($backup['albums'][$album][1]??0)];
  $data=['id'=>$id,'album'=>$album,'title'=>trim($a->textContent),'content'=>$backup['photos'][$id][9]??'','url'=>$u,'thumb'=>preg_replace('/\?.*$/','',$img->getAttribute('src')),'full'=>$full->getAttribute('href'),'date'=>$backup['photos'][$id][6]??time()];
  fwrite($out,json_encode($data,JSON_UNESCAPED_UNICODE)."\n");
 }
}
fclose($out);
foreach($albums as $a){$parent=$a['parent'];while($parent&&!isset($albums[$parent])&&isset($backup['albums'][$parent])){$b=$backup['albums'][$parent];$albums[$parent]=['id'=>$parent,'name'=>$b[5],'parent'=>(int)$b[1],'url'=>'/photo/'.$b[10].'/'.$parent];$parent=(int)$b[1];}}
file_put_contents($root.'albums-prepared.json',json_encode($albums,JSON_UNESCAPED_UNICODE));
$reviews=[];
foreach(array_merge([$root.'old-module-gb.html'],glob($root.'old-gb-*.html')) as $f){
 [$d,$x]=domfile($f);
 foreach($x->query('//*[starts-with(@id,"entryID")]') as $e){
  $id=(int)substr($e->getAttribute('id'),7);$msg=$x->query('.//*['.cls('cMessage').']',$e)->item(0);$author=$x->query('.//*['.cls('cTop').']//b',$e)->item(0);$date=$x->query('.//*['.cls('cDate').']',$e)->item(0);
  if(!$id||!$msg||!$author||!$date)continue;$dt=DateTime::createFromFormat('d.m.Y H:i',trim($date->textContent)) ?: DateTime::createFromFormat('Y.m.d H:i',trim($date->textContent));
  $body=inner($d,$msg);$name=trim($author->textContent);
  while($msg->firstChild)$msg->removeChild($msg->firstChild);$msg->appendChild($d->createTextNode('THAI_REVIEW_BODY'));
  $author->nodeValue='THAI_REVIEW_AUTHOR';clean($x,$e);
  foreach(iterator_to_array($x->query('.//*['.cls('report-spam-wrap').']|.//a[@title="Email"]',$e)) as $n)$n->parentNode->removeChild($n);
  $reviews[$id]=['id'=>$id,'title'=>$name,'content'=>$body,'date'=>$dt?$dt->format('Y-m-d H:i:00'):null,'template'=>$d->saveHTML($e)];
 }
}
file_put_contents($root.'reviews-prepared.json',json_encode(array_values($reviews),JSON_UNESCAPED_UNICODE));
[$d,$x]=domfile($root.'old-module-contact.html');$e=$x->query('//*['.cls('page-content-wrapper').']')->item(0);
$form=$x->query('.//form',$e)->item(0);if(!$e||!$form)throw new Exception('Contact structure missing');$form->parentNode->replaceChild($d->createTextNode('[thai_contact_form]'),$form);clean($x,$e);
file_put_contents($root.'contact-prepared.html',$d->saveHTML($e));
echo json_encode(['photos'=>count($photos),'albums'=>count($albums),'reviews'=>count($reviews)])."\n";