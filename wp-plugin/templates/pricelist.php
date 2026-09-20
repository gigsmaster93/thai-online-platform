<?php
if (!defined('ABSPATH')) exit;

$print_mode = isset($_GET['printMode']) && (string) $_GET['printMode'] === '1';
add_filter('pre_get_document_title', static function () {
    return 'Прайс-лист на все экскурсии в Паттайе Таиланде в 2025-2026 году';
}, 99);

status_header(200);
get_header();

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