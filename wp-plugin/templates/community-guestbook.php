<?php
if (!defined('ABSPATH')) exit;
$current_page=TOP_Community::page();$q=new WP_Query(['post_type'=>'thai_guestbook','post_status'=>'publish','posts_per_page'=>15,'paged'=>$current_page,'meta_key'=>'_thai_live_review','meta_value'=>1,'orderby'=>'date','order'=>'DESC']);
get_header(); ?>
<div class="page width clearfix"><div class="content clearfix" style="width:100%"><div class="content-view thai-guestbook">
<table class="thai-gb-breadcrumb" border="0" cellpadding="0" cellspacing="0" width="100%"><tr><td width="80%"><a href="/">Главная</a> &raquo; Гостевая книга</td><td align="right" style="white-space:nowrap">[ <a href="#sign">Добавить запись</a> ]</td></tr></table><hr>
<h1 style="text-align:center">Отзывы о сервисе Thai-Online</h1>
<p style="text-align:center">Здесь можно ознакомиться с отзывами о работе сервиса Thai-Online и поделиться своими впечатлениями о качестве обслуживания.<br>Обратите внимание, что в разделе запрещена реклама и не поддерживаются встроенные ссылки во избежание спама.<br>Также, Вы можете прочитать и оставить отзывы о работе сервиса Thai-Online на альтернативных ресурсах.</p>
<p style="text-align:center"><select class="thai-review-resource" style="padding:10px;font-size:16px"><option value="">-- Выбрать ресурс --</option><option value="https://otzovik.com/reviews/turagentstvo_thai-online_tailand_dzhomten/">Отзывы на Otzovik Com</option><option value="https://www.tripadvisor.ru/Attraction_Review-g293919-d24110779-Reviews-Thai_Online_Tours_And_Travel_Pattaya-Pattaya_Chonburi_Province.html">Отзывы на Tripadvisor Ru</option><option value="https://yandex.ru/maps/org/thai_online/169163505515/reviews/">Отзывы на Яндекс Карты</option><option value="https://g.page/r/CRTzqxNw3GJnEBM/review">Отзывы на Google Maps</option></select></p><hr>
<div class="csTop" style="padding-bottom:7px"><div id="pagesBlock1" style="float:right"><span class="pages-label">Страницы: </span><?php TOP_Community::legacy_pages($q->found_posts,15,$current_page,'/gb/{page}'); ?></div><div id="numEntries">Показано <b><?php echo ($current_page-1)*15+1; ?></b>-<b><?php echo min($current_page*15,$q->found_posts); ?></b> из <b><?php echo (int)$q->found_posts; ?></b> сообщений</div></div>
<div id="allEntries">
<?php while($q->have_posts()){$q->the_post();$tpl=get_post_meta(get_the_ID(),'_thai_review_template',true);
if($tpl)echo str_replace(['THAI_REVIEW_BODY','THAI_REVIEW_AUTHOR'],[wp_kses_post(get_the_content()),esc_html(get_the_title())],$tpl);
else echo '<article class="cBlock1"><b>'.esc_html(get_the_title()).'</b><div class="cMessage">'.wp_kses_post(wpautop(get_the_content())).'</div></article>';
}wp_reset_postdata(); ?>
<div class="clr" style="background:0;padding:0;border:0;box-shadow:none"></div>
</div>
<div id="newEntryB"></div>
<?php
$total=(int)$q->found_posts;$pages=max(1,(int)ceil($total/15));
echo '<div id="pagesBlock2" align="center">';
for($p=1;$p<=$pages;$p++){
    $start=($p-1)*15+1;$end=min($p*15,$total);$label=$start.'-'.$end;
    if($p===$current_page)echo '<b class="swchItemA1"><span>'.esc_html($label).'</span></b> ';
    else echo '<a class="swchItem1" href="'.esc_url($p===1?'/gb/':'/gb/'.$p).'"><span>'.esc_html($label).'</span></a> ';
}
echo '</div><br>';
$smile_base=plugins_url('assets/smiles/',TOP_PLUGIN_FILE);
$smiles=['>('=>'angry',':D'=>'biggrin','B)'=>'cool',":'("=>'cry','<_<'=>'dry','^_^'=>'happy',':('=>'sad',':)'=>'smile',':o'=>'surprised',':p'=>'tongue','%)'=>'wacko',';)'=>'wink'];
?>
<a id="sign" name="sign"></a>
<?php if(isset($_GET['submitted']))echo '<p class="thai-gb-submit-status" role="status">Спасибо! Отзыв отправлен на проверку и появится после одобрения.</p>'; ?>
<form method="post" id="acform" class="gb-add" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
<?php wp_nonce_field('thai_review'); ?><input type="hidden" name="action" value="thai_review"><p hidden><input name="website" tabindex="-1" autocomplete="off" aria-label="Website"></p>
<table border="0" width="100%" cellspacing="1" cellpadding="2" class="commTable"><tbody>
<tr><td class="commTd2" colspan="2"><div class="commError" id="eMessage" align="center"></div></td></tr>
<tr><td class="commTd1" width="15%" nowrap>Имя *:</td><td class="commTd2"><input class="commFl" id="gbF7" type="text" name="name" size="30" maxlength="100" required></td></tr>
<tr><td class="commTd1">Email *:</td><td class="commTd2"><input class="commFl" id="gbF1" type="email" name="email" size="30" maxlength="190" required></td></tr>
<tr><td class="commTd2" colspan="2"><div style="padding-bottom:2px"></div><table border="0" cellpadding="0" cellspacing="0" width="100%"><tbody><tr><td valign="top"><textarea id="message" class="commFl" rows="10" cols="40" name="message" minlength="5" maxlength="10000" required></textarea></td><td class="thai-gb-smiles-cell" width="5%" valign="top" align="center" style="padding-left:3px"><div class="smiles smiles-grid">
<?php foreach($smiles as $code=>$file): ?><a href="#" class="sml1" data-code="<?php echo esc_attr($code); ?>"><img alt="" src="<?php echo esc_url($smile_base.$file.'.gif'); ?>" title="<?php echo esc_attr($file); ?>"></a><?php endforeach; ?><div id="allSmiles">Все смайлы</div>
</div></td></tr></tbody></table></td></tr>
<tr><td class="commTd1">Проверка:</td><td class="commTd2"><div class="thai-gb-review-note">Отзыв появится после проверки модератором.</div></td></tr>
<tr><td class="commTd2" colspan="2" align="center"><input class="commSbmFl" type="submit" id="gbsbm" value="Добавить комментарий"></td></tr>
</tbody></table></form>
</div></div></div></div>
<?php get_footer(); ?>