<?php
get_header();

function thai_home_cards_by_ucoz(array $ucoz_ids, string $prefix): void {
    echo '<div class="shop-main-list"><div class="goods-list with-clear">';

    foreach ($ucoz_ids as $ucoz_id) {
        $posts = get_posts([
            'post_type'      => 'thai_excursion',
            'post_status'    => 'publish',
            'meta_key'       => '_ucoz_shop_id',
            'meta_value'     => (int) $ucoz_id,
            'posts_per_page' => 1,
            'fields'         => 'ids',
        ]);

        if ($posts) {
            thai_render_excursion_card((int) $posts[0], $prefix);
        }
    }

    echo '</div></div>';
}

$home_fresh = [87, 47, 24, 33, 55, 56, 86];
$home_viewed = [496, 47, 480, 172, 137, 86, 24, 89, 82];
$home_popular = [93, 497, 495, 505, 492, 485, 504, 496, 491];
$home_free = [470, 267, 266, 258, 257, 256, 255];
?>
<br>
<h1 style="text-align:center;">Экскурсии в Паттайе - Паттайя экскурсии <?php echo esc_html(wp_date('Y')); ?></h1>

<section class="goods-tab">
  <ul>
    <li class="active"><a href="#tab-1">Свежие экскурсии</a></li>
    <li><a href="#tab-2">Просматриваемые</a></li>
    <li><a href="#tab-3">Популярные</a></li>
    <li><a href="#tab-4">Бесплатно</a></li>
  </ul>
  <div id="tab-1" class="tab-body"><?php thai_home_cards_by_ucoz($home_fresh, 'inf10'); ?></div>
  <div id="tab-2" class="tab-body" style="display:none"><?php thai_home_cards_by_ucoz($home_viewed, 'top_view'); ?></div>
  <div id="tab-3" class="tab-body" style="display:none"><?php thai_home_cards_by_ucoz($home_popular, 'top_sold'); ?></div>
  <div id="tab-4" class="tab-body" style="display:none"><?php thai_home_cards_by_ucoz($home_free, 'free'); ?></div>
  <div class="viewAlldiv"><a class="viewAllbtn" href="<?php echo esc_url(home_url('/shop/all')); ?>">Смотреть все варианты</a></div>
</section>

<section class="category-main">
  <h2>Категории туров <?php echo esc_html(wp_date('Y')); ?></h2>
  <?php thai_render_category_grid(true); ?>
</section>
</div>

<section class="main-features width">
  <h2 style="text-align:center;">Почему Thai-Online?</h2>
  <br>
  <ul class="mf5 clearfix whywe">
    <li><a href="#" onclick="return false;"><div class="mf-icon"><span class="fa fa-mobile"></span></div><div class="mf-title">Удобный сервис</div><div class="mf-body">Используйте любое устройство, где бы Вы ни находились</div></a></li>
    <li><a href="#" onclick="return false;"><div class="mf-icon"><span class="fa fa-calendar"></span></div><div class="mf-title">Упрощённое бронирование</div><div class="mf-body">Оставьте заявку на любой тур всего в 3 шага</div></a></li>
    <li><a href="#" onclick="return false;"><div class="mf-icon"><span class="fa fa-shield"></span></div><div class="mf-title">Без предоплаты</div><div class="mf-body">Большую часть предложений можно оплатить по факту</div></a></li>
    <li><a href="#" onclick="return false;"><div class="mf-icon"><span class="fa fa-money"></span></div><div class="mf-title">Весь ценовой спектр</div><div class="mf-body">Тут можно найти экскурсии абсолютно любой стоимости</div></a></li>
    <li><a href="#" onclick="return false;"><div class="mf-icon"><span class="fa fa-phone"></span></div><div class="mf-title">Горячая линия</div><div class="mf-body">Где бы Вы ни были и какие бы вопросы у Вас ни возникли, мы всегда на связи</div></a></li>
  </ul>
</section>

<section class="main-features width clearfix thai-home-reviews">
  <h2 style="text-align:center;">Отзывы о Thai-Online</h2>
  <br>
  <div id="otzivi"><div id="allEntries">
  <?php
  $reviews = new WP_Query(['post_type'=>'thai_guestbook','post_status'=>'publish','posts_per_page'=>3,'orderby'=>'date','order'=>'DESC','no_found_rows'=>true]);
  $n = 0;
  while ($reviews->have_posts()) {
      $reviews->the_post(); $n++;
      echo '<div class="report-spam-target">';
      echo '<div class="gb_hdr"><span class="gb_nmbr"><b style="color:#fff;">' . esc_html($n) . '</b></span><span class="cDate">' . esc_html(get_the_date('d.m.Y H:i')) . '</span><div class="cTop"><b>' . esc_html(get_the_title()) . '</b></div></div>';
      echo '<table class="cBlock' . ($n % 2 ? '1' : '2') . '" width="100%"><tr><td><div class="cMessage">' . wp_kses_post(wpautop(get_the_content())) . '</div><div class="cDetails"></div></td></tr></table><br>';
      echo '</div>';
  }
  wp_reset_postdata();
  ?>
  </div></div>
</section>

<section class="main-features width clearfix thai-home-seo" style="margin-top:0;">
  <div class="shop-cat-descr with-clear" style="max-width:60%;margin:auto;">
    <?php echo esc_html(get_theme_mod('thai_home_seo_text', 'Купить экскурсии в Паттайе онлайн. Отдохнуть в Тайланде в 2026 году и узнать цены на самые популярные экскурсии в Паттайе, грин парки, 3Д галереи, вечерние шоу Паттайи можно на сервисе "Thai-Online". Удобный поиск по турам позволит Вам в режиме онлайн изучить и заказать самые популярные туры в Паттайе, а также ознакомиться с отзывами на них. Если Вы любитель экзотических путешествий, то в 2026 году мы готовы предложить индивидуальные туры по Тайланду. Для тех, кто предпочитает активный отдых, мы предложим различные программы по дайвингу, полёты на тарзанке, дельтаплане или круиз на лайнере. Помимо этого мы предоставим: русского гида, страховку, такси в любую точку, оформление визы и трансферы. С нами весь Тайланд как на ладони!')); ?>
  </div>
</section>
<div class="top-wp-runtime-footer-fix">
<?php get_footer(); ?>