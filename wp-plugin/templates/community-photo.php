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
<?php while($q->have_posts()){$q->the_post();$full=get_post_meta(get_the_ID(),'_thai_photo_full',true);$thumb=get_post_meta(get_the_ID(),'_thai_photo_thumb',true);$url=get_post_meta(get_the_ID(),'_thai_legacy_url',true);
if($photo_id){echo '<h1>'.esc_html(get_the_title()).'</h1><a href="'.esc_url($full).'" target="_blank" rel="noopener"><img class="thai-photo-full" src="'.esc_url($full).'" alt="'.esc_attr(get_the_title()).'"></a><div>'.wp_kses_post(wpautop(get_the_content())).'</div>';}
else { ?>
<li class="phtTdMain uEntryWrap"><div class="entryBlock"><span class="uphoto">
<span class="photo-title"><a href="<?php echo esc_url($url); ?>"><?php the_title(); ?></a></span>
<span class="photo-block"><a class="thai-lightbox" href="<?php echo esc_url($full); ?>"><img loading="lazy" src="<?php echo esc_url($thumb); ?>" alt="<?php echo esc_attr(get_the_title()); ?>"></a></span>
<span class="ph-details"><a href="<?php echo esc_url($url); ?>">Подробнее</a></span>
</span></div></li>
<?php }}wp_reset_postdata();if(!$photo_id){echo '</ul></div>';TOP_Community::pagination($q->found_posts,50,$current_page,($term?get_term_meta($term->term_id,'_thai_legacy_url',true):'/photo/').'?page{page}');} ?>
</div></div><aside id="side" class="thai-gallery-sidebar"><div class="block"><div class="block-header">Категории</div><div class="block-body"><table class="catsTable" cellspacing="1" cellpadding="0" width="100%">
<?php $children=get_terms(['taxonomy'=>'thai_photo_album','hide_empty'=>false,'parent'=>$term?$term->term_id:0]);if(!$children)$children=get_terms(['taxonomy'=>'thai_photo_album','hide_empty'=>false,'parent'=>0]);foreach($children as $child){$u=get_term_meta($child->term_id,'_thai_legacy_url',true);$count=new WP_Query(['post_type'=>'thai_photo','post_status'=>'publish','posts_per_page'=>1,'fields'=>'ids','tax_query'=>[['taxonomy'=>'thai_photo_album','terms'=>$child->term_id,'include_children'=>true]]]);echo '<tr><td class="catsTd"><a class="catName" href="'.esc_url($u?:'/photo/'.substr($child->slug,6)).'">'.esc_html($child->name).'</a> <span class="catNumData">['.(int)$count->found_posts.']</span></td></tr>';} ?>
</table></div></div><div class="block"><div class="block-header">Популярное</div><div class="block-body"><ul class="sidebar-popular">
<?php foreach([511,510,509,508,507] as $legacy){$items=get_posts(['post_type'=>'thai_excursion','post_status'=>'publish','numberposts'=>1,'meta_key'=>'_ucoz_shop_id','meta_value'=>$legacy]);if(!$items)continue;$item=$items[0];$url='/shop/'.$legacy.'/desc/'.$item->post_name;$price=get_post_meta($item->ID,'_thai_price',true);$img=get_post_meta($item->ID,'_thai_card_image',true);echo '<li><a class="clearfix" href="'.esc_url($url).'"><img class="gphoto" loading="lazy" src="'.esc_url($img).'" alt="'.esc_attr($item->post_title).'"><div><span>'.esc_html($item->post_title).'</span><div><span>'.esc_html(number_format((float)$price,2,'.','')).'฿</span></div></div></a></li>';} ?>
</ul></div></div></aside></div>
<?php get_footer(); ?>