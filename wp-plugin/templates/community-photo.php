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
<div class="thai-entry-toolbar"><span>Фото: <?php echo (int)$q->found_posts; ?> | Категорий: <?php echo count(get_terms(['taxonomy'=>'thai_photo_album','hide_empty'=>false,'childless'=>true])); ?></span><?php TOP_Community::pagination($q->found_posts,50,$current_page,($term?get_term_meta($term->term_id,'_thai_legacy_url',true):'/photo/').'?page{page}'); ?></div>
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
</div></div><aside class="thai-gallery-sidebar"><h2>Категории</h2>
<?php $children=get_terms(['taxonomy'=>'thai_photo_album','hide_empty'=>false,'parent'=>$term?$term->term_id:0]);if(!$children)$children=get_terms(['taxonomy'=>'thai_photo_album','hide_empty'=>false,'parent'=>0]);foreach($children as $child){$u=get_term_meta($child->term_id,'_thai_legacy_url',true);echo '<a href="'.esc_url($u?:'/photo/'.substr($child->slug,6)).'">'.esc_html($child->name).'</a>';} ?>
</aside></div>
<?php get_footer(); ?>