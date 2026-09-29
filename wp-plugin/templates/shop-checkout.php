<?php
if (!defined('ABSPATH')) exit;

$legacy_id = absint($_GET['product'] ?? 0);
$product = null;
if ($legacy_id) {
    $matches = get_posts([
        'post_type' => 'thai_excursion',
        'post_status' => 'publish',
        'meta_key' => '_ucoz_shop_id',
        'meta_value' => $legacy_id,
        'posts_per_page' => 1,
    ]);
    $product = $matches[0] ?? null;
}

$quantity = max(1, min(1000, absint($_GET['quantity'] ?? 1)));
$details = sanitize_textarea_field(wp_unslash($_GET['details'] ?? ''));
$total = sanitize_text_field(wp_unslash($_GET['total'] ?? ''));
$wishes = [];
if ($details !== '') $wishes[] = 'Выбрано: ' . mb_substr($details, 0, 1200);
if ($total !== '' && preg_match('/^[0-9]+(?:[.,][0-9]{1,2})?\s*฿?$/u', $total)) $wishes[] = 'Предварительный расчёт: ' . $total;

status_header(200);
get_header();
?>
<main class="page width clearfix thai-checkout-page" id="maincont">
  <div class="content-view">
    <div id="cont-shop-checkout">
      <h1>Оформление заказа</h1>
      <?php if (!$product): ?>
        <div class="thai-checkout-empty">
          <h3>Ваша корзина пуста</h3>
          <p>[ <a href="<?php echo esc_url(home_url('/shop/all')); ?>">Продолжить покупки</a> ]</p>
        </div>
      <?php else:
        $product_url = home_url('/shop/' . $legacy_id . '/desc/' . $product->post_name);
      ?>
        <section class="thai-checkout-product" aria-labelledby="thai-checkout-product-title">
          <h2 id="thai-checkout-product-title"><?php echo esc_html($product->post_title); ?></h2>
          <?php if ($details !== ''): ?><p><strong>Выбрано:</strong> <?php echo esc_html($details); ?></p><?php endif; ?>
          <?php if ($total !== '' && preg_match('/^[0-9]+(?:[.,][0-9]{1,2})?\s*฿?$/u', $total)): ?><p><strong>Предварительный расчёт:</strong> <?php echo esc_html($total); ?></p><?php endif; ?>
          <p><a href="<?php echo esc_url($product_url); ?>">← Вернуться к экскурсии</a></p>
        </section>
        <section class="thai-checkout-form" aria-label="Форма заказа">
          <?php TOP_Community::booking_form($product, ['quantity' => $quantity, 'wishes' => implode("\n", $wishes)]); ?>
        </section>
      <?php endif; ?>
    </div>
  </div>
</main>
<?php get_footer(); ?>