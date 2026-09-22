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
$main_override = (string) get_post_meta($p->ID, '_thai_main_image', true);

if ($main_override !== '') {
    $image = $main_override;
} elseif (function_exists('thai_excursion_card_image')) {
    $image = thai_excursion_card_image($p->ID, $ucoz_id);
}

$hero_image = (string) get_post_meta($p->ID, '_thai_hero_image', true);
if ($hero_image === '') {
    $hero_image = $image;
}

$content = (string) $p->post_content;
$has_legacy_product = strpos($content, 'id="main-product-page"') !== false;

$variants_raw = (string) get_post_meta($p->ID, '_thai_price_variants', true);
$variant_groups = [];
foreach (explode('%', str_replace('#%', '%', $variants_raw)) as $group_raw) {
    $group = [];
    foreach (explode('#', $group_raw) as $entry) {
        $bits = explode('&', $entry);
        if (isset($bits[1]) && is_numeric($bits[1])) $group[] = ['label'=>trim($bits[0]), 'price'=>(float)$bits[1]];
    }
    if ($group) $variant_groups[] = $group;
}
$variants = $variant_groups[0] ?? [];
$person_labels = get_post_meta($p->ID, '_thai_person_labels', true);
if (!is_array($person_labels)) $person_labels = [];
$transport_variants=[];
foreach(explode('#',(string)get_post_meta($p->ID,'_thai_transport_variants',true)) as $entry){$bits=explode('&',$entry);if(count($bits)>=2&&is_numeric($bits[1]))$transport_variants[]=['label'=>str_replace('--',"'",$bits[0]),'price'=>(float)$bits[1]];}
$show_hero_price=!empty($variants)&&!$transport_variants;
if($transport_variants){$variants=[['label'=>'Человек','price'=>0]];$person_labels=['Человек'];}
$transport_per_person=in_array($ucoz_id,[197,240,241,238,237,236,242,239,247],true);


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

$breadcrumbs = [];
$breadcrumbs_raw = (string) get_post_meta($p->ID, '_thai_breadcrumbs', true);

if ($breadcrumbs_raw !== '') {
    foreach (preg_split('/\r?\n/', $breadcrumbs_raw) as $line) {
        if (trim($line) === '') continue;
        $parts = array_map('trim', explode('|', $line, 2));
        $breadcrumbs[] = [
            'label' => $parts[0],
            'url'   => $parts[1] ?? '',
        ];
    }
}

if (!$breadcrumbs) {
    $breadcrumbs = [
        ['label' => 'Главная', 'url' => '/'],
        ['label' => 'Экскурсии и места', 'url' => '/shop/all'],
    ];
}

$is_person_pricing = false;
if (count($variants) >= 2) {
    $matched = 0;
    foreach ($variants as $variant) {
        if (preg_match('/взрос|adult|дет|реб|млад|child|инф|infant/iu', $variant['label'])) {
            $matched++;
        }
    }
    $is_person_pricing = ($matched >= 2 || (count($variant_groups) > 1 && count($variants) >= 2));
}

$is_person_pricing = $is_person_pricing || (!empty($person_labels) && !empty($variants));
$is_booking_form = get_post_meta($p->ID, '_thai_order_type', true) === 'form';
$hero_price_label = 'Взрослый';
if (!in_array($ucoz_id,[510,511],true) && $variant_groups) {
    $lowest = $variant_groups[0][0];
    foreach($variant_groups as $group) if($group[0]['price'] < $lowest['price']) $lowest=$group[0];
    $hero_price_label=$lowest['label'];
}

$render_order_block = static function ($p, $ucoz_id, $price, $price_text, $variants, $is_person_pricing) use ($variant_groups, $person_labels, $is_booking_form, $transport_variants, $transport_per_person) {
    $product_url = home_url('/shop/' . $ucoz_id . '/desc/' . $p->post_name);
    ?>
    <div class="rightbl thai-legacy-order-block" style="user-select:none;">
      <div class="right" id="calculatey">
        <div class="innerBlockY">
          <?php if($is_booking_form): ?><h2 style="text-align:center;">Заказать</h2><div id="order-form"><?php TOP_Community::booking_form($p); ?></div><?php else: ?>
          <h2 style="text-align:center;text-shadow:1px 1px 2px silver;">Узнать стоимость</h2>

          <?php if($transport_variants): ?>
            <div class="col-md-6 col-sm-6 col-xs-6 transport" style="display:block"><div class="form-group"><label for="thai-transport">Вариант транспорта</label><div class="numbers-row"><select id="thai-transport" class="qty2 counting"><option value="0">- выбрать -</option>
            <?php foreach($transport_variants as $transport): $capacity=['Седан'=>3,'Минивэн'=>6,'Минибас'=>13];$word=explode(' ',$transport['label'])[0]; ?>
              <option value="<?php echo esc_attr($transport['price']); ?>" data-capacity="<?php echo (int)($capacity[$word]??13); ?>"><?php echo esc_html($transport['label']); ?></option>
            <?php endforeach; ?></select></div></div></div><div class="clr"></div>
          <?php endif; ?>
          <?php if (count($variant_groups) > 1): ?>
            <div class="col-md-6 col-sm-6 col-xs-6 tourVarS"><div class="form-group">
              <label for="thai-price-group">Вариант тура</label>
              <select class="numbers-row tourVarScnt" id="thai-price-group">
                <?php foreach ($variant_groups as $group): ?>
                  <option data-prices="<?php echo esc_attr(wp_json_encode(array_column($group, 'price'))); ?>" value="<?php echo esc_attr($group[0]['price']); ?>"><?php echo esc_html($group[0]['label']); ?></option>
                <?php endforeach; ?>
              </select>
            </div></div><div class="clr"></div>
          <?php endif; ?>
          <?php if ($is_person_pricing): ?>
            <div class="thai-person-pricing">
              <?php foreach ($variants as $index => $variant): ?>
                <?php
                $qty_label = $person_labels[$index] ?? $variant['label'];
                if ((int) $ucoz_id === 511) {
                    $legacy_labels = ['Взрослые', 'Дети 4-11 лет', 'Дети < 4 лет'];
                    $qty_label = $legacy_labels[$index] ?? $qty_label;
                }
                ?>
                <div class="col-md-6 col-sm-6 col-xs-6 thai-qty-row">
                  <div class="form-group">
                    <label><?php echo esc_html($qty_label); ?></label>
                    <div class="numbers-row">
                      <div class="dec button_inc" role="button" tabindex="0">-</div>
                      <input
                        value="<?php echo $index === 0 ? '1' : '0'; ?>"
                        class="qty2 form-control thai-person-qty counting"
                        type="text"
                        inputmode="numeric"
                        data-price="<?php echo esc_attr($variant['price']); ?>"
                        <?php if($transport_variants)echo 'data-transport="1" data-fixed-price="'.($transport_per_person?'0':'1').'" min="1" max="13"'; ?>
                        aria-label="<?php echo esc_attr($qty_label); ?>"
                      >
                      <div class="inc button_inc" role="button" tabindex="0">+</div>
                      <span><b class="thai-line-total">0</b><span>฿</span></span>
                    </div>
                  </div>
                </div>
                <div class="clr"></div>
              <?php endforeach; ?>
            </div>
          <?php elseif ($variants && count($variant_groups) <= 1): ?>
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
            <div class="clr"></div>
          <?php endif; ?>

          <div id="total">
            Всего: <span><?php echo $is_person_pricing ? '' : esc_html($price_text); ?></span><br>
            <div><i class="fa fa-info-circle" aria-hidden="true"></i> Предложена самая низкая цена!</div>
            <a href="#foundCheaperDialog" id="foundCheaper">Нашли дешевле?</a>
            <?php if(isset($_GET['cheaper_sent'])): ?><div class="thai-cheaper-success" role="status">Спасибо! Предложение отправлено.</div><?php endif; ?>
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

            <div class="type-select visible">
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
          <?php endif; ?>
        </div>

        <br>
        <div class="addblockright" style="max-width:350px;justify-content:center;display:contents;">
          <p style="text-align:center;">
            <a href="https://affiliate.klook.com/redirect?aid=28346&aff_adid=1164008&k_site=https%3A%2F%2Fwww.klook.com%2F" rel="nofollow" target="_blank">
              <img src="/images/klook-on-good-page.png" width="100%" alt="Pattaya excursions" title="Plan you holidays right now">
            </a>
          </p>
        </div>
      </div>
    </div>
    <?php if(!$is_booking_form && method_exists('TOP_Community','found_cheaper_form')) TOP_Community::found_cheaper_form($p); ?>
    <?php
};

$render_reviews = static function ($p) {
    $comment_count = (int) get_comments_number($p->ID);
    $comments = get_comments([
        'post_id' => $p->ID,
        'status'  => 'approve',
        'order'   => 'ASC',
    ]);
    ?>
    <div class="clr"></div>
    <div class="feedBlock thai-legacy-reviews">
      <h2 id="feedback" style="text-align:center;text-shadow:1px 1px 2px silver;">
        Отзывы о <?php echo esc_html($p->post_title); ?>
      </h2>

      <p>
        Прочитать реальные отзывы по направлению <?php echo esc_html($p->post_title); ?> и поделиться своими впечатлениями о качестве обслуживания.<br>
        Обратите внимание, что в разделе запрещена реклама и не поддерживаются встроенные ссылки во избежание спама.
        Все отзывы проходят премодерацию - реклама, секс и политика удаляются.<br>
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
        <tr>
          <td width="60%" height="25">Отзывов: <b><?php echo esc_html($comment_count); ?></b></td>
          <td align="right" height="25"></td>
        </tr>
        <?php if (!$comments): ?><tr><td colspan="2" height="10"></td></tr><?php endif; ?>
      </table>

      <?php if ($comments): ?>
        <div id="allEntries">
          <?php foreach ($comments as $comment): ?>
            <?php $legacy_review = get_comment_meta($comment->comment_ID, '_thai_legacy_template', true);
            if ($legacy_review) {
                echo str_replace(['THAI_COMMENT_BODY', 'THAI_COMMENT_AUTHOR'], [wp_kses_post($comment->comment_content), esc_html($comment->comment_author)], $legacy_review);
                continue;
            } ?>
            <div class="uComment cBlock1">
              <b><?php echo esc_html($comment->comment_author); ?></b>
              <span class="cDate"><?php echo esc_html(get_comment_date('d.m.Y H:i', $comment)); ?></span>
              <div class="cMessage"><?php echo wp_kses_post(wpautop($comment->comment_content)); ?></div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <?php if ($comments): ?><div style="height:10px"></div><?php endif; ?>
      <div id="postFormContent">
        <form method="post" id="acform" class="shop-com-add thai-comment-form" action="<?php echo esc_url(site_url('/wp-comments-post.php')); ?>">
          <div class="uForm uComForm">
            <div class="uauth-small-links uauth-links-set1">
              Войдите:
              <span class="login-with local" aria-hidden="true"><i></i></span>
            </div>
            <div class="uComForm-inner">
              <span class="ucf-avatar">
                <img src="/wp-content/uploads/thai-live/noavatar.png" alt="avatar" width="50" height="50">
              </span>
              <div class="ucf-content ucf-start-content">
                <ul class="uf-form ucf-form">
                  <li><textarea class="uf-txt-input commFl js-start-txt" name="comment" required placeholder="Оставьте ваш комментарий..."></textarea></li>
                  <li><button class="uf-btn" type="submit">Отправить</button></li>
                </ul>
              </div>
            </div>
          </div>
          <input type="hidden" name="comment_post_ID" value="<?php echo esc_attr($p->ID); ?>">
          <input type="hidden" name="comment_parent" value="0">
        </form>
      </div>

    </div>

    <div class="clr"></div>

    <div class="thai-share-legacy" style="text-align:center;width:100%;">
      <div class="sharetxt" style="width:100%;">Поделиться: </div><br>
      <div class="thai-share-icons">
        <a href="https://vk.com/share.php?url=<?php echo rawurlencode(get_permalink($p)); ?>" target="_blank" rel="noopener" aria-label="VK">VK</a>
        <a href="https://connect.ok.ru/offer?url=<?php echo rawurlencode(get_permalink($p)); ?>" target="_blank" rel="noopener" aria-label="OK">OK</a>
        <a href="https://t.me/share/url?url=<?php echo rawurlencode(get_permalink($p)); ?>" target="_blank" rel="noopener" aria-label="Telegram"><i class="fa fa-paper-plane"></i></a>
        <a href="https://wa.me/?text=<?php echo rawurlencode(get_permalink($p)); ?>" target="_blank" rel="noopener" aria-label="WhatsApp"><i class="fa fa-whatsapp"></i></a>
      </div>
    </div>

    <hr><br><br>
    <?php
};

$render_recommendations = static function ($ucoz_ids) {
    if (!$ucoz_ids) return;

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

    if (!$post_ids) return;
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
};
?>

<div
  class="infoblock thai-product-hero"
  style="<?php
    echo esc_attr(
        'user-select:none;background:#3f96dc' .
        ($hero_image !== '' ? ' url(' . esc_url_raw($hero_image) . ') fixed no-repeat' : '') .
        ';background-size:cover;background-position:50%;'
    );
  ?>"
>
  <div class="infoblockOl">
    <div class="page width clearfix">
      <div class="left bread">
        <?php foreach ($breadcrumbs as $index => $crumb): ?>
          <?php if ($index > 0): ?> &raquo; <?php endif; ?>
          <?php if ($crumb['url'] !== ''): ?>
            <a href="<?php echo esc_url(preg_match('#^https?://#', $crumb['url']) ? $crumb['url'] : home_url($crumb['url'])); ?>">
              <?php echo esc_html($crumb['label']); ?>
            </a>
          <?php else: ?>
            <span><?php echo esc_html($crumb['label']); ?></span>
          <?php endif; ?>
        <?php endforeach; ?>
      </div>

      <h1><?php echo esc_html($p->post_title); ?></h1>
    </div>

    <div class="page width clearfix infoBl">
      <?php if ($quick): ?>
        <?php $info_width = 'calc(' . (100 / (count($quick) + ($show_hero_price ? 1 : 0))) . '% - 1px)'; ?>
        <?php foreach ($quick as $fact): ?>
          <div class="colIco left" style="width:<?php echo esc_attr($info_width); ?>;margin-left:0;margin-right:0;padding:0;">
            <?php if ($fact['icon'] !== ''): ?><i class="fa fa-<?php echo esc_attr(sanitize_html_class($fact['icon'])); ?>"></i><?php endif; ?>
            <div class="hdr"><?php echo esc_html($fact['label']); ?></div>
            <div class="txt"><?php echo wp_kses($fact['value'], ['br'=>[]]); ?></div>
          </div>
        <?php endforeach; ?>

        <?php if($show_hero_price): ?><div class="colIco right" style="width:<?php echo esc_attr($info_width); ?>;margin-left:0;margin-right:0;padding:0;">
          <i class="fa fa-male"></i>
          <div class="hdr"><?php echo esc_html($hero_price_label); ?></div>
          <div class="cont">от <?php echo esc_html(number_format((float) $price, 0, '.', '')); ?>฿</div>
        </div><?php endif; ?>
      <?php endif; ?>
    </div>
  </div>
</div>

<div class="page width clearfix thai-product-page" id="maincont">
<div class="content clearfix" style="width:100%">
<div class="content-view">

    <?php if ($has_legacy_product): ?>

        <?php echo $content; ?>

        <?php $render_order_block($p, $ucoz_id, $price, $price_text, $variants, $is_person_pricing); ?>

        <?php $render_reviews($p); ?>

        <?php $render_recommendations($recommended_ids); ?>

    <?php else: ?>

        <div class="clearfix" id="main-product-page">
          <div class="left">
            <?php if ($image): ?>
              <div class="shop-itempage-images">
                <img src="<?php echo esc_url($image); ?>" alt="<?php echo esc_attr($p->post_title); ?>">
              </div>
            <?php endif; ?>
          </div>

          <div class="right">
            <?php if ($quick): ?>
              <div class="thai-quick-facts">
                <?php foreach ($quick as $fact): ?>
                  <div class="thai-quick-fact">
                    <strong><?php echo esc_html($fact['label']); ?></strong>
                    <span><?php echo wp_kses($fact['value'], ['br'=>[]]); ?></span>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>

            <div class="thai-product-description">
              <?php echo apply_filters('the_content', $content); ?>
            </div>
          </div>
        </div>

        <?php $render_order_block($p, $ucoz_id, $price, $price_text, $variants, $is_person_pricing); ?>

        <?php $render_reviews($p); ?>

        <?php
        if ($recommended_ids) {
            $render_recommendations($recommended_ids);
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