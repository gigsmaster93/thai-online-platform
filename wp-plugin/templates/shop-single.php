<?php
if (!defined('ABSPATH')) exit;

$ucoz_id = (int) get_query_var('top_id');

$posts = get_posts([
    'post_type'      => 'thai_excursion',
    'post_status'    => 'publish',
    'meta_key'       => '_ucoz_shop_id',
    'meta_value'     => $ucoz_id,
    'posts_per_page' => 1,
]);

if (!$posts) {
    status_header(404);
    get_header();
    echo '<div class="page width clearfix"><h1>Материал не найден</h1></div>';
    get_footer();
    return;
}

$p = $posts[0];

status_header(200);
get_header();

$price = function_exists('thai_excursion_price')
    ? thai_excursion_price($p->ID)
    : (float) get_post_meta($p->ID, '_thai_price', true);

$price_text = number_format((float) $price, 2, '.', '') . '฿';

$image = '';

$main_override = get_post_meta($p->ID, '_thai_main_image', true);

if ($main_override) {
    $image = $main_override;
} elseif (function_exists('thai_excursion_card_image')) {
    $image = thai_excursion_card_image($p->ID, $ucoz_id);
}

$content = (string) $p->post_content;
$has_legacy_product = strpos($content, 'id="main-product-page"') !== false;

$variants_raw = (string) get_post_meta($p->ID, '_thai_price_variants', true);
$variants = [];

foreach (explode('#', $variants_raw) as $variant) {
    $parts = explode('&', $variant);

    if (
        isset($parts[0], $parts[1]) &&
        trim($parts[0]) !== '' &&
        is_numeric($parts[1])
    ) {
        $variants[] = [
            'label' => trim($parts[0]),
            'price' => (float) $parts[1],
        ];
    }
}

$quick_raw = (string) get_post_meta($p->ID, '_thai_quick_facts', true);
$quick = [];

foreach (explode('#', $quick_raw) as $fact) {
    $parts = explode('&', $fact);

    if (!empty($parts[0]) && isset($parts[1])) {
        $quick[] = [
            'label' => trim($parts[0]),
            'value' => trim($parts[1]),
            'icon'  => $parts[2] ?? '',
        ];
    }
}

$recommended_ids = array_values(array_filter(array_map(
    'absint',
    preg_split('/[^0-9]+/', (string) get_post_meta($p->ID, '_thai_recommended_ids', true))
)));

if (!$recommended_ids && $ucoz_id === 511) {
    $recommended_ids = [96, 103, 406];
}

function thai_render_legacy_order_block($p, $ucoz_id, $price, $price_text, $variants) {
    $product_url = home_url('/shop/' . $ucoz_id . '/desc/' . $p->post_name);
    ?>
    <div class="rightbl thai-legacy-order-block" style="user-select:none;">
      <div class="right" id="calculatey">
        <div class="innerBlockY">
          <h2 style="text-align:center;text-shadow:1px 1px 2px silver;">Узнать стоимость</h2>

          <?php if ($variants): ?>
            <div class="col-md-6 col-sm-6 col-xs-6 tourVarS">
              <div class="form-group">
                <label>Вариант тура</label>
                <select class="numbers-row tourVarScnt" id="thai-tour-variant">
                  <?php foreach ($variants as $variant): ?>
                    <option value="<?php echo esc_attr($variant['price']); ?>">
                      <?php echo esc_html($variant['label'] . ' — ' . number_format($variant['price'], 2, '.', '') . '฿'); ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
          <?php else: ?>
            <div class="col-md-6 col-sm-6 col-xs-6 tourVarS">
              <div class="form-group">
                <label>Вариант тура</label>
                <select class="numbers-row tourVarScnt" id="thai-tour-variant">
                  <option value="<?php echo esc_attr($price); ?>">Основная программа</option>
                </select>
              </div>
            </div>
          <?php endif; ?>

          <div class="clr"></div>

          <div id="total">
            Всего: <span><?php echo esc_html($price_text); ?></span><br>
            <div><i class="fa fa-info-circle" aria-hidden="true"></i> Предложена самая низкая цена!</div>
            <a href="#contacts" id="foundCheaper">Нашли дешевле?</a>
          </div>

          <div class="clr"></div>

          <div class="button-container" data-product-title="<?php echo esc_attr($p->post_title); ?>" data-product-url="<?php echo esc_url($product_url); ?>">
            <a
              id="id-<?php echo esc_attr($ucoz_id); ?>-buynow"
              class="basket now thai-book-now"
              href="<?php echo esc_url('https://wa.me/66838383539?text=' . rawurlencode('Хочу забронировать: ' . $p->post_title . ' (' . $product_url . ')')); ?>"
              target="_blank"
              rel="noopener"
            >Забронировать сейчас!</a>

            <a href="" target="_blank" rel="noopener" class="altorder" style="display:none;">
              <span>Забронировать сейчас!</span>
            </a>

            <div class="type-select">
              <select id="select-options">
                <option value="">Заказать через</option>
                <option value="telegram">Telegram</option>
                <option value="whatsapp">WhatsApp</option>
                <option value="viber">Viber</option>
                <option value="line">Line</option>
                <option value="phone">По телефону</option>
              </select>
            </div>
          </div>
        </div>
      </div>
    </div>
    <?php
}

function thai_render_legacy_reviews($p) {
    ?>
    <div class="thai-legacy-reviews">
      <h2 id="feedback" style="text-align:center;text-shadow:1px 1px 2px silver;">
        Отзывы о <?php echo esc_html($p->post_title); ?>
      </h2>
      <p>
        Прочитать реальные отзывы по направлению <?php echo esc_html($p->post_title); ?>
        и поделиться своими впечатлениями о качестве обслуживания.<br>
        Обратите внимание, что в разделе запрещена реклама и не поддерживаются встроенные ссылки во избежание спама.
        Все отзывы проходят премодерацию — реклама, секс и политика удаляются.<br>
        Также, Вы можете прочитать и оставить отзывы о работе сервиса Thai-Online на других ресурсах.
      </p>
      <p style="text-align:center;">
        <select class="thai-review-resource" style="padding:10px;font-size:16px;">
          <option value="">-- Выбрать ресурс --</option>
          <option value="https://otzovik.com/reviews/turagentstvo_thai-online_tailand_dzhomten/">Отзывы на Otzovik Com</option>
          <option value="https://www.tripadvisor.ru/Attraction_Review-g293919-d24110779-Reviews-Thai_Online_Tours_And_Travel_Pattaya-Pattaya_Chonburi_Province.html">Отзывы на Tripadvisor Ru</option>
          <option value="https://yandex.ru/maps/org/thai_online/169163505515/reviews/">Отзывы на Яндекс Карты</option>
          <option value="https://g.page/r/CRTzqxNw3GJnEBM/review">Отзывы на Google Maps</option>
          <option value="https://thai-online.reformal.ru/">Отзывы на Reformal Ru</option>
        </select>
      </p>
      <hr>
      <table border="0" cellpadding="0" cellspacing="0" width="100%">
        <tr><td width="60%" height="25">Отзывов: <b>0</b></td><td align="right" height="25"></td></tr>
      </table>
    </div>
    <?php
}

function thai_render_recommendations_by_ucoz_ids($ucoz_ids) {
    if (!$ucoz_ids) {
        return;
    }

    $post_ids = [];

    foreach ($ucoz_ids as $id) {
        $found = get_posts([
            'post_type'      => 'thai_excursion',
            'post_status'    => 'publish',
            'meta_key'       => '_ucoz_shop_id',
            'meta_value'     => $id,
            'posts_per_page' => 1,
            'fields'         => 'ids',
        ]);

        if ($found) {
            $post_ids[] = (int) $found[0];
        }
    }

    if (!$post_ids) {
        return;
    }
    ?>
    <div id="recommended_products">
      <div id="recommended_products_title" style="text-align:center;font-weight:bold;text-shadow:1px 1px 2px silver;">Рекомендуем!</div><br>
      <div class="goods-list with-clear">
        <?php
        foreach ($post_ids as $post_id) {
            if (function_exists('thai_render_excursion_card')) {
                thai_render_excursion_card($post_id, 'recommended_products');
            }
        }
        ?>
      </div>
    </div>
    <?php
}
?>

<div class="page width clearfix thai-product-page">
<div class="content clearfix" style="width:100%">
<div class="content-view">

    <div class="thai-breadcrumbs">
        <a href="<?php echo esc_url(home_url('/')); ?>">Главная</a>
        »
        <a href="<?php echo esc_url(home_url('/shop/all')); ?>">Экскурсии и места</a>
    </div>

    <div class="thai-product-heading clearfix">
        <h1><?php echo esc_html($p->post_title); ?></h1>

        <div class="product-page-price">
            <span class="shop-itempage-price">
                от
                <span class="id-good-<?php echo esc_attr($ucoz_id); ?>-price"><?php echo esc_html($price_text); ?></span>
            </span>
        </div>

        <div class="priceupperbutton">
            <a class="printBtn" href="#calculatey">Оформить / Рассчитать заказ ↓</a>
        </div>
    </div>

    <?php if ($has_legacy_product): ?>

        <?php echo $content; ?>

        <?php thai_render_legacy_order_block($p, $ucoz_id, $price, $price_text, $variants); ?>

        <?php thai_render_legacy_reviews($p); ?>

        <?php thai_render_recommendations_by_ucoz_ids($recommended_ids); ?>

    <?php else: ?>

        <div id="main-product-page" class="thai-generated-product">

            <?php if ($image): ?>
                <div class="shop-itempage-images">
                    <a href="<?php echo esc_url($image); ?>">
                        <img src="<?php echo esc_url($image); ?>" alt="<?php echo esc_attr($p->post_title); ?>">
                    </a>
                </div>
            <?php endif; ?>

            <?php if ($quick): ?>
                <div class="thai-quick-facts">
                    <?php foreach ($quick as $fact): ?>
                        <div class="thai-quick-fact">
                            <strong><?php echo esc_html($fact['label']); ?></strong>
                            <span><?php echo esc_html($fact['value']); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="thai-product-description">
                <?php echo apply_filters('the_content', $content); ?>
            </div>

        </div>

        <?php thai_render_legacy_order_block($p, $ucoz_id, $price, $price_text, $variants); ?>

        <?php thai_render_legacy_reviews($p); ?>

        <?php
        if ($recommended_ids) {
            thai_render_recommendations_by_ucoz_ids($recommended_ids);
        } else {
            $recommended = new WP_Query([
                'post_type'      => 'thai_excursion',
                'post_status'    => 'publish',
                'posts_per_page' => 3,
                'post__not_in'   => [$p->ID],
                'orderby'        => 'rand',
            ]);

            if ($recommended->have_posts()): ?>
                <div id="recommended_products">
                    <div id="recommended_products_title">Рекомендуем!</div>
                    <div class="goods-list with-clear">
                        <?php
                        while ($recommended->have_posts()) {
                            $recommended->the_post();

                            if (function_exists('thai_render_excursion_card')) {
                                thai_render_excursion_card(get_the_ID(), 'recommended_products');
                            }
                        }

                        wp_reset_postdata();
                        ?>
                    </div>
                </div>
            <?php endif;
        }
        ?>

    <?php endif; ?>

</div>
</div>
</div>

<?php get_footer(); ?>
