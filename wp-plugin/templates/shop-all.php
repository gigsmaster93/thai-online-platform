<?php
if (!defined('ABSPATH')) exit;
$module = (string) get_query_var('top_module');
$is_wishlist = $module === 'shop_wishlist';
$category = $module === 'shop_category' ? TOP_Shop_Categories::term() : null;
$category_data = $category ? TOP_Shop_Categories::data($category) : [];
$catalog_url = home_url($is_wishlist ? '/shop/wishlist' : ($category ? TOP_Shop_Categories::path($category) : '/shop/all'));

$sort = isset($_GET['sort']) ? sanitize_key($_GET['sort']) : ($category ? 'name' : 'date');
$order = isset($_GET['order']) ? (strtolower($_GET['order']) === 'asc' ? 'ASC' : 'DESC') : ($category ? 'ASC' : 'DESC');
$orderby = 'meta_value_num';
$meta_key = '_ucoz_shop_id';

if ($sort === 'name') {
    $orderby = 'title';
    $meta_key = '';
}

if ($sort === 'price') {
    $orderby = 'meta_value_num';
    $meta_key = '_thai_price';
}

if ($sort === 'date') {
    $orderby = 'meta_value_num';
    $meta_key = '_ucoz_shop_id';
}
$min = isset($_GET['min_price']) && $_GET['min_price'] !== '' ? (float) $_GET['min_price'] : null;
$max = isset($_GET['max_price']) && $_GET['max_price'] !== '' ? (float) $_GET['max_price'] : null;
$paged = max(
    1,
    (int) get_query_var('top_page'),
    isset($_GET['pg']) ? (int) $_GET['pg'] : 1
);

$args = [
    'post_type' => 'thai_excursion',
    'post_status' => 'publish',
    'posts_per_page' => 24,
    'paged' => $paged,
    'orderby' => $orderby,
    'order' => $order,
];
if ($meta_key) $args['meta_key'] = $meta_key;
if ($is_wishlist) {
    $args['post__in'] = [0];
}
if ($min !== null || $max !== null) {
    $range = [];
    if ($min !== null) $range[] = ['key'=>'_thai_price','value'=>$min,'compare'=>'>=','type'=>'NUMERIC'];
    if ($max !== null) $range[] = ['key'=>'_thai_price','value'=>$max,'compare'=>'<=','type'=>'NUMERIC'];
    $args['meta_query'] = $range;
}
if ($category) {
    $args['tax_query'] = [['taxonomy'=>'thai_excursion_cat', 'field'=>'term_id', 'terms'=>[$category->term_id], 'include_children'=>true]];
}
$q = new WP_Query($args);
if ($paged > 1 && $paged > max(1, (int) $q->max_num_pages)) {
    global $wp_query;
    $wp_query->set_404();
    set_query_var('top_module', '');
    status_header(404);
    nocache_headers();
    include (get_404_template() ?: TOP_PLUGIN_DIR . 'templates/not-found.php');
    return;
}
status_header(200);
get_header();
$total = (int) $q->found_posts;
if ($is_wishlist) {
    echo '<style id="thai-wishlist-parity">
    body.thai-module-shop_wishlist .thai-shop-page .topbar{margin-bottom:0!important}
    body.thai-module-shop_wishlist .thai-shop-page #slider-range{position:relative;text-align:left;height:.8em;border:1px solid #eee;background:#fff;color:#333;font-family:Arial,sans-serif;font-size:1.1em;border-radius:6px;box-sizing:content-box;margin:0 auto 30px!important}
    body.thai-module-shop_wishlist .thai-shop-page .flist{display:inline!important;margin-bottom:0!important}
    body.thai-module-shop_wishlist .thai-shop-page .flist-item{display:block!important;gap:0!important;align-items:normal!important;flex-wrap:nowrap!important;justify-content:normal!important}
    body.thai-module-shop_wishlist .thai-shop-page .price_filter{padding:14px 20px!important}
    body.thai-module-shop_wishlist .thai-shop-page .thai-filter-reset{display:none!important}
    @media(min-width:601px){body.thai-module-shop_wishlist .thai-shop-page .price_filter{width:auto!important}}
    </style>';
}
?>
<div class="content clearfix thai-shop-page">
  <div class="content-view">
    <div class="topbar" style="width:100%">
      <div class="thai-breadcrumbs"><a href="<?php echo esc_url(home_url('/')); ?>">Главная</a> » <a class="current" href="<?php echo esc_url(home_url('/shop/all')); ?>"><?php echo ($category || $is_wishlist) ? 'Все экскурсии' : 'Все товары'; ?></a></div>
      <div style="text-align:center"><img id="catImgX" <?php if (!empty($category_data['image'])): ?>src="<?php echo esc_url($category_data['image']); ?>"<?php endif; ?> alt="<?php echo $category ? esc_attr($category->name) : ''; ?>"></div>
      <div><h1 class="catalog-header-title" style="text-align:center;margin:0">Паттайя экскурсии <?php echo esc_html(wp_date('Y')); ?><?php if ($is_wishlist): ?> <span style="display:none;">- </span><?php endif; ?></h1></div>
      <div class="clr"></div>
      <?php if (!empty($category_data['description'])): ?><div class="shop-cat-descr with-clear"><?php echo wp_kses_post($category_data['description']); ?></div><div class="clr"></div><?php endif; ?>
      <div class="shop-product-num"><b><span class="ne_cont"><?php echo esc_html($total); ?></span></b> позиций(-ии) в каталоге</div>
    </div>

    <?php if ($is_wishlist): ?>
    <hr class="hidempty" style="display:none">
    <h2 class="catshdr hidempty" style="display:none">Категории</h2>
    <br>
    <?php elseif ($category): TOP_Shop_Categories::render_children($category); else: ?>
    <hr class="hidempty">
    <h2 class="catshdr hidempty">Категории</h2>
    <?php if (function_exists('thai_render_category_grid')) thai_render_category_grid(false); ?>
    <?php endif; ?>
    <hr>

    <div class="shop-sort-selector"><span class="slist">Сортировка:
      <a class="<?php echo $sort==='name' ? 'active' : ''; ?>" href="<?php echo esc_url(add_query_arg(['sort'=>'name','order'=>$sort==='name' && $order==='ASC' ? 'desc' : 'asc'], $catalog_url)); ?>"><?php echo $sort==='name' && $order==='ASC' ? '↑ ' : ''; ?>Наименование</a> ·
      <a class="<?php echo $sort==='price' ? 'active' : ''; ?>" href="<?php echo esc_url(add_query_arg(['sort'=>'price','order'=>$sort==='price' && $order==='ASC' ? 'desc' : 'asc'], $catalog_url)); ?>">Цена</a> ·
      <a class="<?php echo $sort==='date' ? 'active' : ''; ?>" href="<?php echo esc_url(add_query_arg(['sort'=>'date','order'=>$sort==='date' && $order==='DESC' ? 'asc' : 'desc'], $catalog_url)); ?>"><?php echo $sort==='date' && $order==='DESC' ? '↓ ' : ''; ?>Дата добавления</a>
    </span></div>

    <div id="slider-range"<?php if ($is_wishlist): ?> class="ui-slider ui-slider-horizontal ui-widget ui-widget-content ui-corner-all"<?php endif; ?>></div>
    <div class="flist"><form class="flist-item" id="flist-item-price" method="get" action="<?php echo esc_url($catalog_url); ?>">
      <span class="flist-label" id="flist-label-price">Цена, ฿:</span>
      <input class="price_filter" name="min_price" id="price_min" type="number" min="0" value="<?php echo $min !== null ? esc_attr($min) : ''; ?>" placeholder="от">
      <input class="price_filter" name="max_price" id="price_max" type="number" min="0" value="<?php echo $max !== null ? esc_attr($max) : ''; ?>" placeholder="до">
      <input type="hidden" name="sort" value="<?php echo esc_attr($sort); ?>">
      <input type="hidden" name="order" value="<?php echo esc_attr(strtolower($order)); ?>">
      <button type="submit">Фильтровать</button>
      <?php if (!$is_wishlist): ?><a class="thai-filter-reset" href="<?php echo esc_url($catalog_url); ?>">Сбросить</a><?php endif; ?>
    </form></div>
    <hr>

    <div id="goods_cont">
      <?php if ($is_wishlist): ?>
        <div class="empty">Не найдено ни одного товара</div>
        <?php wp_reset_postdata(); ?>
      <?php else: ?>
        <div class="goods-list with-clear">
          <?php if (!$q->have_posts()): ?><p class="thai-catalog-empty">По выбранным условиям экскурсии не найдены.</p><?php endif; ?>
          <?php while ($q->have_posts()): $q->the_post(); ?>
            <?php if (function_exists('thai_render_excursion_card')) thai_render_excursion_card(get_the_ID(), 'all'); ?>
          <?php endwhile; wp_reset_postdata(); ?>
        </div>
      <?php endif; ?>
    </div>

    <?php if ($q->max_num_pages > 1):
      $page_args = array_filter([
          'sort'      => $sort,
          'order'     => strtolower($order),
          'min_price' => $min,
          'max_price' => $max,
      ], fn($v) => $v !== null && $v !== '');
      $next_page_url = '';
      if ($paged < (int) $q->max_num_pages) {
          $next_path = $category
              ? untrailingslashit($catalog_url) . ';' . ($paged + 1)
              : untrailingslashit($catalog_url) . '/' . ($paged + 1);
          $next_page_url = add_query_arg($page_args, $next_path);
      }
    ?>
      <div class="plist shop-page-wrap" data-next-url="<?php echo esc_url($next_page_url); ?>">
        <?php
        $pagination = paginate_links([
            'base'      => untrailingslashit($catalog_url) . '%_%',
            'format'    => $category ? ';%#%' : '/%#%',
            'total'     => $q->max_num_pages,
            'current'   => $paged,
            'prev_next' => false,
            'add_args'  => $page_args,
        ]);

        if ($pagination) {
            echo $pagination;
        }
        ?>
      </div>
      <script>
      (function(){
        var pager=document.querySelector('.thai-shop-page .shop-page-wrap');
        var list=document.querySelector('.thai-shop-page #goods_cont .goods-list');
        if(!pager||!list||!pager.dataset.nextUrl||!('IntersectionObserver' in window))return;
        var nextUrl=pager.dataset.nextUrl,loading=false,observer;
        function activateMedia(root){
          root.querySelectorAll('[data-src]').forEach(function(el){
            var src=el.getAttribute('data-src');
            if(!src)return;
            if(el.tagName==='IMG'){
              if(!el.getAttribute('src'))el.setAttribute('src',src);
            }else{
              el.style.backgroundImage='url("'+src.replace(/"/g,'')+'")';
            }
            el.setAttribute('data-thai-lazy-loaded','1');
          });
        }
        async function loadNext(){
          if(loading||!nextUrl)return;
          loading=true;
          pager.setAttribute('aria-busy','true');
          try{
            var response=await fetch(nextUrl,{credentials:'same-origin'});
            if(!response.ok)throw new Error('HTTP '+response.status);
            var text=await response.text();
            var doc=new DOMParser().parseFromString(text,'text/html');
            var incoming=doc.querySelectorAll('#goods_cont .goods-list > .list-item');
            incoming.forEach(function(item){
              if(item.id&&document.getElementById(item.id))return;
              var node=document.importNode(item,true);
              activateMedia(node);
              list.appendChild(node);
            });
            var remotePager=doc.querySelector('.shop-page-wrap');
            nextUrl=remotePager ? (remotePager.dataset.nextUrl||'') : '';
            pager.dataset.nextUrl=nextUrl;
            window.dispatchEvent(new Event('resize'));
            if(!nextUrl&&observer)observer.disconnect();
          }catch(err){
            console.warn('Thai Online catalog autoload failed:',err);
            if(observer)observer.disconnect();
          }finally{
            loading=false;
            pager.removeAttribute('aria-busy');
          }
        }
        observer=new IntersectionObserver(function(entries){
          entries.forEach(function(entry){if(entry.isIntersecting)loadNext();});
        },{root:null,rootMargin:'700px 0px',threshold:0.01});
        observer.observe(pager);
      })();
      </script>
    <?php endif; ?>
  </div>
</div>
<?php get_footer(); ?>