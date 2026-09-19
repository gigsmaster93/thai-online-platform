<?php
if (!defined('ABSPATH')) exit;
$photo_id=(int)get_query_var('top_id');$album=(int)get_query_var('top_section');$current_page=TOP_Community::page();
$term=$album?get_term_by('slug','album-'.$album,'thai_photo_album'):null;
$args=['post_type'=>'thai_photo','post_status'=>'publish','posts_per_page'=>50,'paged'=>$current_page,'orderby'=>'title','order'=>'ASC'];
if($photo_id){$args['meta_key']='_ucoz_photo_id';$args['meta_value']=$photo_id;$args['posts_per_page']=1;$args['paged']=1;}
elseif($term)$args['tax_query']=[['taxonomy'=>'thai_photo_album','terms'=>$term->term_id,'include_children'=>true]];
$q=new WP_Query($args);if($photo_id&&!$q->have_posts())status_header(404);
get_header(); ?>
<div class="page width clearfix thai-gallery-page"><div class="content clearfix"><div class="content-view thai-gallery">
<?php if($term||$photo_id): ?><p><a href="/">Главная</a> &raquo; <a href="/photo">Фотогалерея</a><?php if($term)echo ' &raquo; '.esc_html($term->name); ?></p><?php endif; ?>
<?php if(!$photo_id): ?>
<?php if($term)echo '<h1>'.esc_html($term->name).'</h1>'; ?>
<table class="thai-photo-toolbar" border="0" cellpadding="0" cellspacing="0" width="100%"><tr><td width="70%">Фото: <b><?php echo (int)$q->found_posts; ?></b> | Категорий: <b><?php echo count(get_terms(['taxonomy'=>'thai_photo_album','hide_empty'=>false,'childless'=>true])); ?></b></td><td align="right" style="white-space:nowrap">Страницы: <?php TOP_Community::legacy_pages($q->found_posts,50,$current_page,($term?get_term_meta($term->term_id,'_thai_legacy_url',true):'/photo/').'?page{page}'); ?><br></td></tr></table>
<div id="allEntries"><ul id="uEntriesList" class="allEntriesTable thai-photo-grid">
<?php endif; ?>
<?php while($q->have_posts()){$q->the_post();$pid=get_the_ID();$legacy_id=(int)get_post_meta($pid,'_ucoz_photo_id',true);$full=get_post_meta($pid,'_thai_photo_full',true);$thumb=get_post_meta($pid,'_thai_photo_thumb',true);$url=get_post_meta($pid,'_thai_legacy_url',true);$title=get_the_title();
$date_display=get_post_meta($pid,'_thai_photo_date_display',true)?:get_the_date('d.m.Y');$descr=get_post_meta($pid,'_thai_photo_descr',true);$author=get_post_meta($pid,'_thai_photo_author',true)?:'Thai-Online';$views=get_post_meta($pid,'_thai_photo_views',true);$comments=get_post_meta($pid,'_thai_photo_comments',true);$rating=get_post_meta($pid,'_thai_photo_rating',true);$image_title=get_post_meta($pid,'_thai_photo_image_title',true)?:$title;$image_alt=get_post_meta($pid,'_thai_photo_image_alt',true)?:$title;$link_title=get_post_meta($pid,'_thai_photo_link_title',true);
if($photo_id){echo '<h1>'.esc_html($title).'</h1><a href="'.esc_url($full).'" target="_blank" rel="noopener"><img class="thai-photo-full" src="'.esc_url($full).'" alt="'.esc_attr($image_alt).'"></a><div>'.wp_kses_post(wpautop(get_the_content())).'</div>';}
else { ?>
<li class="phtTdMain uEntryWrap" prev="0"><div id="entryID<?php echo $legacy_id; ?>" class="entryBlock"><span class="uphoto">
<span class="photo-title"><a href="<?php echo esc_url($url); ?>"><?php echo esc_html($title); ?></a></span>
<span class="photo-block"><span class="ph-wrap">
<span class="ph-tc"><img loading="lazy" title="<?php echo esc_attr($image_title); ?>" alt="<?php echo esc_attr($image_alt); ?>" style="padding:0;border:0" src="<?php echo esc_url($thumb); ?>"></span>
<a href="<?php echo esc_url($full); ?>" data-title="<?php echo esc_attr($image_title); ?>" data-alt="<?php echo esc_attr($image_alt); ?>" class="ulightbox ph-link thai-lightbox" data-url="<?php echo esc_url($url); ?>" title="<?php echo esc_attr($link_title); ?>">
<span class="ph-tc"><span class="ph-data"><span class="ph-date"><?php echo esc_html($date_display); ?></span><span class="ph-descr"><?php echo esc_html($descr); ?></span><span class="ph-author"><?php echo esc_html($author); ?></span></span></span>
</a></span>
<span class="ph-details ph-js-details"><span class="phd-views"><?php echo esc_html($views); ?></span> <a href="<?php echo esc_url($url.'#comments'); ?>" class="phd-comments"><?php echo esc_html($comments); ?></a> <span class="phd-rating"><span id="entRating<?php echo $legacy_id; ?>"><?php echo esc_html($rating); ?></span></span></span>
</span></span></div></li>
<?php }}wp_reset_postdata();if(!$photo_id){echo '</ul></div>';TOP_Community::pagination($q->found_posts,50,$current_page,($term?get_term_meta($term->term_id,'_thai_legacy_url',true):'/photo/').'?page{page}');} ?>
</div></div><aside id="side" class="thai-gallery-sidebar"><div class="block"><div class="block-header">Категории</div><div class="block-body"><table class="catsTable" cellspacing="1" cellpadding="0" width="100%">
<?php $children=get_terms(['taxonomy'=>'thai_photo_album','hide_empty'=>false,'parent'=>$term?$term->term_id:0]);if(!$children)$children=get_terms(['taxonomy'=>'thai_photo_album','hide_empty'=>false,'parent'=>0]);foreach($children as $child){$u=get_term_meta($child->term_id,'_thai_legacy_url',true);$count=new WP_Query(['post_type'=>'thai_photo','post_status'=>'publish','posts_per_page'=>1,'fields'=>'ids','tax_query'=>[['taxonomy'=>'thai_photo_album','terms'=>$child->term_id,'include_children'=>true]]]);echo '<tr><td class="catsTd"><a class="catName" href="'.esc_url($u?:'/photo/'.substr($child->slug,6)).'">'.esc_html($child->name).'</a> <span class="catNumData">['.(int)$count->found_posts.']</span></td></tr>';} ?>
</table></div></div><div class="block"><div class="block-header">Популярное</div><div class="block-body"><ul class="sidebar-popular">
<?php foreach([511,510,509,508,507] as $legacy){$items=get_posts(['post_type'=>'thai_excursion','post_status'=>'publish','numberposts'=>1,'meta_key'=>'_ucoz_shop_id','meta_value'=>$legacy]);if(!$items)continue;$item=$items[0];$url='/shop/'.$legacy.'/desc/'.$item->post_name;$price=get_post_meta($item->ID,'_thai_price',true);$img=get_post_meta($item->ID,'_thai_card_image',true)?:'/_sh/'.(int)floor($legacy/100).'/'.$legacy.'m.webp';echo '<li><a class="clearfix" href="'.esc_url($url).'"><img class="gphoto" loading="lazy" src="'.esc_url($img).'" alt="'.esc_attr($item->post_title).'"><div><span>'.esc_html($item->post_title).'</span><div><span>'.esc_html(number_format((float)$price,2,'.','')).'฿</span></div></div></a></li>';} ?>
</ul></div></div></aside></div>
<?php get_footer(); ?>