<?php
if (!defined('ABSPATH')) exit;

$print_mode = isset($_GET['printMode']) && (string) $_GET['printMode'] === '1';
add_filter('pre_get_document_title', static function () {
    return 'Прайс-лист на все экскурсии в Паттайе Таиланде в 2025-2026 году';
}, 99);

status_header(200);
get_header();

echo '<style id="thai-pricelist-parity">.thai-pricelist-page.page.width{width:100%!important;margin-left:0!important;margin-right:0!important}';
if (!$print_mode) {
    echo '@media(min-width:835px){body.thai-module-pricelist header.header{height:190px!important}.thai-pricelist-page h1{min-height:50.359375px!important}}';
}
echo '</style>';

if ($print_mode) {
    echo '<style id="thai-pricelist-print-mode">header,footer,.printBtn{display:none!important}</style>';
}

$html = (string) get_option('thai_pricelist_html', '');
if ($html === '') {
    $html = '<h1>Прайс-лист временно недоступен</h1>';
}

if ($print_mode) {
    $html = preg_replace('/<h1>/', '<div id="xlogo" style="margin:auto;"></div><h1>', $html, 1);
}
?>
<div class="page width clearfix thai-pricelist-page">
  <div class="content clearfix">
    <div class="content-view">
      <?php echo $html; ?>
    </div>
  </div>
</div>
<?php get_footer(); ?>