<?php
if (!defined('ABSPATH')) exit;

function thai_excursion_price($post_id) {
    $price = (float) get_post_meta($post_id, '_thai_price', true);
    if ($price > 0) return $price;
    $variants = (string) get_post_meta($post_id, '_thai_price_variants', true);
    $values = [];
    foreach (preg_split('/#/', $variants) as $variant) {
        $parts = explode('&', $variant);
        if (isset($parts[1]) && is_numeric($parts[1]) && (float)$parts[1] > 0) $values[] = (float)$parts[1];
    }
    return $values ? min($values) : 0;
}

function thai_excursion_legacy_stats($post_id) {
    $raw = (string) get_post_meta($post_id, '_ucoz_raw_record', true);
    $f = explode('|', $raw);
    return [
        'views' => isset($f[30]) && is_numeric($f[30]) ? (int)$f[30] : 0,
        'orders' => isset($f[31]) && is_numeric($f[31]) ? (int)$f[31] : 0,
    ];
}

function thai_excursion_card_image($post_id, $ucoz_id = '') {
    $ucoz_id = (int) $ucoz_id;
    if ($ucoz_id > 0) {
        $dir = $ucoz_id < 100 ? '00' : (string) floor($ucoz_id / 100);
        foreach (['webp','jpg','jpeg','png'] as $ext) {
            $rel = '/_sh/' . $dir . '/' . $ucoz_id . 'm.' . $ext;
            if (file_exists(ABSPATH . ltrim($rel, '/'))) return $rel;
        }
    }
    $thumb = get_the_post_thumbnail_url($post_id, 'large');
    if ($thumb) return $thumb;
    $body = (string) get_post_field('post_content', $post_id);
    if (preg_match('/<img[^>]+(?:data-src|src)=["\']([^"\']+)["\']/i', $body, $m)) return $m[1];
    return '/img/forum_ico_have_new.png';
}

function thai_excursion_url($post_id) {
    $ucoz = (int) get_post_meta($post_id, '_ucoz_shop_id', true);
    return home_url('/shop/' . $ucoz . '/desc/' . get_post_field('post_name', $post_id));
}

function thai_render_excursion_card($post_id, $prefix = 'all') {
    $ucoz = (int) get_post_meta($post_id, '_ucoz_shop_id', true);
    $price = thai_excursion_price($post_id);
    $stats = thai_excursion_legacy_stats($post_id);
    $url = thai_excursion_url($post_id);
    $image = thai_excursion_card_image($post_id, $ucoz);
    $date = get_the_date('d.m.Y', $post_id);
    $price_text = $price > 0 ? number_format($price, 2, '.', '') . '฿' : '0.00฿';
    ?>
    <div class="list-item" id="<?php echo esc_attr($prefix . '-item-' . $ucoz); ?>">
      <a class="product-url lazyload replacex" data-added-time="<?php echo esc_attr($date); ?>" data-discount-text="—" data-new-text="Новое" data-old-price="" data-price="<?php echo esc_attr(number_format($price, 2, '.', '')); ?>" data-src="<?php echo esc_url($image); ?>" href="<?php echo esc_url($url); ?>" varid="<?php echo esc_attr($ucoz); ?>">
        <div class="sml-price right"><span title="Просмотров"><i class="fa fa-eye"></i> <?php echo esc_html($stats['views']); ?></span> <span title="Количество покупок"><i class="fa fa-cart-arrow-down"></i> <?php echo esc_html($stats['orders']); ?></span></div>
        <div class="sml-price"><span class="<?php echo esc_attr($prefix . '-good-' . $ucoz . '-price'); ?>"><?php echo esc_html($price_text); ?></span></div>
        <div class="sml-img">
          <div class="sml-meta">
            <div class="sml-title"><?php echo esc_html(get_the_title($post_id)); ?></div><br>
            <span class="buy-it-now" title="Подробнее"><span class="flaticon-search"></span></span>
            <span class="wish wadd" id="last_add-<?php echo esc_attr($ucoz); ?>-wish" title="В закладки"></span>
          </div>
        </div>
      </a>
    </div>
    <?php
}

function thai_parity_categories($home = false) {
    $items = [
        ['/shop/short-tour-ekskursii-thailanda-pattaya-2023','Короткие экскурсии','/img/catPh/mainCats/korotkie-ekskursii-pattaja-2020.webp'],
        ['/shop/one-day-tour-thailand-2023','Экскурсии на 1 день','/img/catPh/mainCats/odnodnevnie-ekskursii-thailand-2020.webp'],
        ['/shop/night-tour-thailand-2023','Экскурсии с ночёвкой','/img/catPh/mainCats/ekskursii-s-nochevkoi-thailand-2020.webp'],
        ['/shop/uslugi-taxi-v-thailande-2023',$home ? 'Услуги такси Паттайя' : 'Услуги такси в Паттайе','/img/catPh/mainCats/taxi-thailand-2020.webp'],
        ['/shop/besplatnye-transfery-2023',$home ? 'Бесплатные трансферы Паттайя' : 'Бесплатные трансферы','/img/catPh/mainCats/transferi-thailand-2020.webp'],
        ['/301-tours-to-thai','Купить тур в Таиланд','/img/catPh/mainCats/otpusk-v-tailande.webp'],
        ['/other_countries_ru',$home ? 'Другие города и страны' : 'Другие страны и курорты','/img/catPh/mainCats/other_countries.webp'],
        ['/301-air-tickets',$home ? 'Поиск авиабилетов' : 'Поиск и заказ авиабилетов','/img/catPh/mainCats/avia-bileti-thailand-2020.webp'],
        ['/301-hotels',$home ? 'Поиск отелей' : 'Поиск и бронь отелей','/img/catPh/mainCats/oteli-pattaja-2020.webp'],
        ['/301-bus-train-tickets','Билеты на автобусы и поезда','/img/catPh/mainCats/bus_and_train_tickets.webp'],
        ['/shop/prochie-uslugi-thailand-2023','Прочие услуги','/img/catPh/mainCats/spa-pattaja-2020.webp'],
        ['/shop/prochie-uslugi-thailand-2023/oformit-visy-v-thailande-2023','Бордер-ран и Виза-ран','/img/catPh/mainCats/visa.webp'],
        ['/immigration-to-thailand-2022','Переезд в Таиланд','/img/catPh/mainCats/run-to-thailand.webp'],
        ['/301-currency-exchanger',$home ? 'Самый лучший обменник' : 'Самый выгодный обменник','/img/catPh/mainCats/exchange.webp'],
    ];
    return apply_filters('thai_parity_categories', $items, $home);
}

function thai_render_category_grid($home = false) {
    $class = $home ? 'maincatLog' : '';
    echo '<div id="catLog" class="' . esc_attr($class) . '">';
    foreach (thai_parity_categories($home) as $item) {
        [$url,$title,$image] = $item;
        echo '<div class="catalog-item list-item"><a href="' . esc_url(home_url($url)) . '" title="' . esc_attr($title) . '"><img class="lazyload replacex" data-src="' . esc_url($image) . '" src="' . esc_url($image) . '" alt="' . esc_attr($title) . '"></a><h3><a href="' . esc_url(home_url($url)) . '">' . esc_html($title) . '</a></h3><div></div></div>';
    }
    echo '</div><div class="clr"></div>';
}
