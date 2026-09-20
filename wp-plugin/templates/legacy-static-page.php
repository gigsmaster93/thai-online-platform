<?php
if (!defined('ABSPATH')) exit;

$legacy_id = (int) get_query_var('top_id');
$posts = get_posts([
    'post_type'      => 'page',
    'meta_key'       => '_ucoz_site_id',
    'meta_value'     => $legacy_id,
    'numberposts'    => 1,
    'post_status'    => 'publish',
]);

if (!$posts) {
    global $wp_query;
    $wp_query->set_404();
    status_header(404);
    nocache_headers();
    $fallback = get_404_template();
    if ($fallback) {
        include $fallback;
    } else {
        TOP_Core::render_header(TOP_Core::lang('not_found'));
        TOP_Core::render_footer();
    }
    return;
}

$page = $posts[0];
$title_map = [
    2  => 'О Нас - Сервис поиска экскурсий в Таиланде',
    19 => 'Экскурсии по странам мира - Сервис поиска экскурсий в Таиланде',
    92 => 'Услуги по переезду в Таиланд. Паттайя, Хуахин и Пхукет - Сервис поиска экскурсий в Таиланде',
];
if (isset($title_map[$legacy_id])) {
    $legacy_title = $title_map[$legacy_id];
    add_filter('pre_get_document_title', static function () use ($legacy_title) {
        return $legacy_title;
    }, 99);
}

status_header(200);
get_header();

echo '<style id="thai-legacy-static-parity">';
echo '.thai-legacy-static-page.page.width{width:100%!important;margin-left:0!important;margin-right:0!important}';
echo '@media(min-width:835px){body.thai-module-legacy_static_page header.header{height:190px!important}}';
if ($legacy_id === 2) {
    echo '.thai-legacy-about .content{margin-left:auto!important;margin-right:auto!important;float:none!important;margin-top:20px!important}';
}
echo '</style>';

$html = (string) $page->post_content;
if ($legacy_id === 2 && str_starts_with($html, '|')) {
    $html = substr($html, 1);
}
$home = untrailingslashit(home_url('/'));
$html = str_replace(
    [
        'href="https://thai-online.org',
        "href='https://thai-online.org",
        'src="https://thai-online.org',
        "src='https://thai-online.org",
        'href="http://thai-online.org',
        "href='http://thai-online.org",
        'src="http://thai-online.org',
        "src='http://thai-online.org",
    ],
    [
        'href="' . $home,
        "href='" . $home,
        'src="' . $home,
        "src='" . $home,
        'href="' . $home,
        "href='" . $home,
        'src="' . $home,
        "src='" . $home,
    ],
    $html
);
?>
<div class="page width clearfix thai-legacy-static-page <?php echo $legacy_id === 2 ? 'thai-legacy-about' : ($legacy_id === 92 ? 'thai-legacy-immigration' : 'thai-legacy-other-countries'); ?>">
  <div class="content clearfix">
    <div class="content-view">
      <?php echo $html; ?>
    </div>
  </div>
  <?php if (in_array($legacy_id, [19, 92], true)): ?>
  <aside id="side">
    <div class="block">
      <div class="block-header">Популярное</div>
      <div class="block-body">
        <ul class="sidebar-popular">
          <?php foreach ([511, 510, 509, 508, 507] as $ucoz_id):
              $items = get_posts([
                  'post_type'      => 'thai_excursion',
                  'meta_key'       => '_ucoz_shop_id',
                  'meta_value'     => $ucoz_id,
                  'numberposts'    => 1,
                  'post_status'    => 'publish',
              ]);
              if (!$items) continue;
              $item = $items[0];
              $price = function_exists('thai_excursion_price') ? thai_excursion_price($item->ID) : (float) get_post_meta($item->ID, '_thai_price', true);
              $image = function_exists('thai_excursion_card_image') ? thai_excursion_card_image($item->ID, $ucoz_id) : '';
              $url = function_exists('thai_excursion_url') ? thai_excursion_url($item->ID) : get_permalink($item->ID);
              $sidebar_title = str_replace(
                  ['“', '”'],
                  '"',
                  html_entity_decode(get_the_title($item), ENT_QUOTES | ENT_HTML5, 'UTF-8')
              );
          ?>
          <li>
            <a href="<?php echo esc_url($url); ?>" class="clearfix">
              <img src="<?php echo esc_url($image); ?>" alt="<?php echo esc_attr($sidebar_title); ?>" class="gphoto" id="inf2-gphoto-<?php echo esc_attr($ucoz_id); ?>">
              <div>
                <span><?php echo esc_html($sidebar_title); ?></span>
                <div><span class="inf2-good-<?php echo esc_attr($ucoz_id); ?>-price"><?php echo esc_html(number_format((float) $price, 2, '.', '')); ?>฿</span></div>
              </div>
            </a>
          </li>
          <?php endforeach; ?>
        </ul>
      </div>
    </div>
  </aside>
  <?php endif; ?>
</div>
<?php get_footer(); ?>