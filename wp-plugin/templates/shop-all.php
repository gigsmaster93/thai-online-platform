<?php
if (!defined('ABSPATH')) exit;
status_header(200);
get_header();

$sort = isset($_GET['sort']) ? sanitize_key($_GET['sort']) : 'date';
$order = isset($_GET['order']) && strtolower($_GET['order']) === 'asc' ? 'ASC' : 'DESC';
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
if ($min !== null || $max !== null) {
    $range = [];
    if ($min !== null) $range[] = ['key'=>'_thai_price','value'=>$min,'compare'=>'>=','type'=>'NUMERIC'];
    if ($max !== null) $range[] = ['key'=>'_thai_price','value'=>$max,'compare'=>'<=','type'=>'NUMERIC'];
    $args['meta_query'] = $range;
}
$q = new WP_Query($args);
$total = (int) $q->found_posts;
?>
<div class="content clearfix thai-shop-page">
  <div class="content-view">
    <div class="topbar" style="width:100%">
      <div class="thai-breadcrumbs"><a href="<?php echo esc_url(home_url('/')); ?>">Главная</a> » <a class="current" href="<?php echo esc_url(home_url('/shop/all')); ?>">Все товары</a></div>
      <div style="text-align:center"><img id="catImgX" alt=""></div>
      <div><h1 class="catalog-header-title" style="text-align:center;margin:0">Паттайя экскурсии <?php echo esc_html(wp_date('Y')); ?></h1></div>
      <div class="clr"></div>
      <div class="shop-product-num"><b><span class="ne_cont"><?php echo esc_html($total); ?></span></b> позиций(-ии) в каталоге</div>
    </div>

    <hr class="hidempty">
    <h2 class="catshdr hidempty">Категории</h2>
    <?php if (function_exists('thai_render_category_grid')) thai_render_category_grid(false); ?>
    <hr>

    <div class="shop-sort-selector"><span class="slist">Сортировка:
      <a class="<?php echo $sort==='name' ? 'active' : ''; ?>" href="<?php echo esc_url(add_query_arg(['sort'=>'name','order'=>$sort==='name' && $order==='ASC' ? 'desc' : 'asc'], home_url('/shop/all'))); ?>">Наименование</a> ·
      <a class="<?php echo $sort==='price' ? 'active' : ''; ?>" href="<?php echo esc_url(add_query_arg(['sort'=>'price','order'=>$sort==='price' && $order==='ASC' ? 'desc' : 'asc'], home_url('/shop/all'))); ?>">Цена</a> ·
      <a class="<?php echo $sort==='date' ? 'active' : ''; ?>" href="<?php echo esc_url(add_query_arg(['sort'=>'date','order'=>$sort==='date' && $order==='DESC' ? 'asc' : 'desc'], home_url('/shop/all'))); ?>"><?php echo $sort==='date' && $order==='DESC' ? '↓ ' : ''; ?>Дата добавления</a>
    </span></div>

    <div id="slider-range"></div>
    <div class="flist"><form class="flist-item" id="flist-item-price" method="get" action="<?php echo esc_url(home_url('/shop/all')); ?>">
      <span class="flist-label" id="flist-label-price">Цена:</span>
      <input class="price_filter" name="min_price" id="price_min" type="number" min="0" value="<?php echo $min !== null ? esc_attr($min) : ''; ?>" placeholder="от">
      <input class="price_filter" name="max_price" id="price_max" type="number" min="0" value="<?php echo $max !== null ? esc_attr($max) : ''; ?>" placeholder="до">
      <input type="hidden" name="sort" value="<?php echo esc_attr($sort); ?>">
      <input type="hidden" name="order" value="<?php echo esc_attr(strtolower($order)); ?>">
      <button type="submit">Фильтровать</button>
      <a class="thai-filter-reset" href="<?php echo esc_url(home_url('/shop/all')); ?>">Сбросить</a>
    </form></div>
    <hr>

    <div id="goods_cont"><div class="goods-list with-clear">
      <?php while ($q->have_posts()): $q->the_post(); ?>
        <?php if (function_exists('thai_render_excursion_card')) thai_render_excursion_card(get_the_ID(), 'all'); ?>
      <?php endwhile; wp_reset_postdata(); ?>
    </div></div>

    <?php if ($q->max_num_pages > 1): ?>
      <div class="plist shop-page-wrap">
        <?php
        $pagination = paginate_links([
            'base'      => untrailingslashit(home_url('/shop/all')) . '/%#%',
            'format'    => '',
            'total'     => $q->max_num_pages,
            'current'   => $paged,
            'prev_text' => '«',
            'next_text' => '»',
            'add_args'  => array_filter([
                'sort'      => $sort,
                'order'     => strtolower($order),
                'min_price' => $min,
                'max_price' => $max,
            ], fn($v) => $v !== null && $v !== ''),
        ]);

        if ($pagination) {
            $pagination = str_replace(
                untrailingslashit(home_url('/shop/all')) . '/1',
                untrailingslashit(home_url('/shop/all')),
                $pagination
            );
            echo $pagination;
        }
        ?>
      </div>
    <?php endif; ?>
  </div>
</div>
<?php get_footer(); ?>
