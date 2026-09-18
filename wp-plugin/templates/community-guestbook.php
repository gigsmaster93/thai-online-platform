<?php
if (!defined('ABSPATH')) exit;
$current_page=TOP_Community::page();$q=new WP_Query(['post_type'=>'thai_guestbook','post_status'=>'publish','posts_per_page'=>15,'paged'=>$current_page,'meta_key'=>'_thai_live_review','meta_value'=>1,'orderby'=>'date','order'=>'DESC']);
get_header(); ?>
<div class="page width clearfix"><div class="content clearfix" style="width:100%"><div class="content-view thai-guestbook">
<div><a href="/">Главная</a> &raquo; Гостевая книга <a href="#sign" style="float:right">Добавить запись</a></div><hr>
<h1 style="text-align:center">Отзывы о сервисе Thai-Online</h1>
<p style="text-align:center">Здесь можно ознакомиться с отзывами о работе сервиса Thai-Online и поделиться своими впечатлениями о качестве обслуживания.<br>Обратите внимание, что в разделе запрещена реклама и не поддерживаются встроенные ссылки во избежание спама.<br>Также, Вы можете прочитать и оставить отзывы о работе сервиса Thai-Online на альтернативных ресурсах.</p>
<p style="text-align:center"><select class="thai-review-resource" style="padding:10px;font-size:16px"><option value="">-- Выбрать ресурс --</option><option value="https://otzovik.com/reviews/turagentstvo_thai-online_tailand_dzhomten/">Отзывы на Otzovik Com</option><option value="https://www.tripadvisor.ru/Attraction_Review-g293919-d24110779-Reviews-Thai_Online_Tours_And_Travel_Pattaya-Pattaya_Chonburi_Province.html">Отзывы на Tripadvisor Ru</option><option value="https://yandex.ru/maps/org/thai_online/169163505515/reviews/">Отзывы на Яндекс Карты</option><option value="https://g.page/r/CRTzqxNw3GJnEBM/review">Отзывы на Google Maps</option></select></p><hr>
<?php TOP_Community::pagination($q->found_posts,15,$current_page,'/gb/{page}'); ?>
<div id="allEntries">
<?php while($q->have_posts()){$q->the_post();$tpl=get_post_meta(get_the_ID(),'_thai_review_template',true);
if($tpl)echo str_replace(['THAI_REVIEW_BODY','THAI_REVIEW_AUTHOR'],[wp_kses_post(get_the_content()),esc_html(get_the_title())],$tpl);
else echo '<article class="cBlock1"><b>'.esc_html(get_the_title()).'</b><div class="cMessage">'.wp_kses_post(wpautop(get_the_content())).'</div></article>';
}wp_reset_postdata(); ?>
</div>
<?php TOP_Community::pagination($q->found_posts,15,$current_page,'/gb/{page}'); ?>
<div id="sign">
<?php if(isset($_GET['submitted']))echo '<p role="status">Спасибо! Отзыв отправлен на проверку и появится после одобрения.</p>'; ?>
<h2>Добавить отзыв</h2>
<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
<?php wp_nonce_field('thai_review'); ?><input type="hidden" name="action" value="thai_review">
<p hidden><input name="website" tabindex="-1" autocomplete="off" aria-label="Website"></p>
<p><label>Ваше имя<br><input name="name" maxlength="100" required></label></p>
<p><label>Отзыв<br><textarea name="message" rows="6" minlength="5" maxlength="10000" required style="width:95%"></textarea></label></p>
<button type="submit">Отправить</button></form>
</div></div></div></div>
<?php get_footer(); ?>