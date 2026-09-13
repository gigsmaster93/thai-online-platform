<?php
get_header();

function thai_home_cards($args, $prefix) {
    $q = new WP_Query(array_merge([
        'post_type' => 'thai_excursion',
        'post_status' => 'publish',
        'posts_per_page' => 7,
        'no_found_rows' => true,
    ], $args));
    echo '<div class="shop-main-list"><div class="goods-list with-clear">';
    while ($q->have_posts()) {
        $q->the_post();
        thai_render_excursion_card(get_the_ID(), $prefix);
    }
    echo '</div></div>';
    wp_reset_postdata();
}
?>
<br>
<h1>Экскурсии в Паттайе - Паттайя экскурсии <?php echo esc_html(wp_date('Y')); ?></h1>

<section class="goods-tab">
  <ul>
    <li class="active"><a href="#tab-1">Свежие экскурсии</a></li>
    <li><a href="#tab-2">Просматриваемые</a></li>
    <li><a href="#tab-3">Популярные</a></li>
    <li><a href="#tab-4">Бесплатно</a></li>
  </ul>
  <div id="tab-1" class="tab-body"><?php thai_home_cards(['orderby'=>'date','order'=>'DESC'], 'new'); ?></div>
  <div id="tab-2" class="tab-body" style="display:none"><?php thai_home_cards(['orderby'=>'rand'], 'view'); ?></div>
  <div id="tab-3" class="tab-body" style="display:none"><?php thai_home_cards(['orderby'=>'date','order'=>'ASC'], 'popular'); ?></div>
  <div id="tab-4" class="tab-body" style="display:none"><?php thai_home_cards(['meta_query'=>[['key'=>'_thai_price','value'=>0,'compare'=>'=','type'=>'NUMERIC']]], 'free'); ?></div>
  <a class="viewAllbtn" href="<?php echo esc_url(home_url('/shop/all')); ?>">Смотреть все варианты</a>
</section>

<section class="category-main">
  <h2>Категории туров <?php echo esc_html(wp_date('Y')); ?></h2>
  <?php thai_render_category_grid(true); ?>
</section>

<ul class="mf5 clearfix whywe">
  <li><a href="#" onclick="return false;"><div class="mf-icon"><span class="fa fa-mobile"></span></div><div class="mf-title">Удобный сервис</div><div class="mf-body">Используйте любое устройство, где бы Вы ни находились</div></a></li>
  <li><a href="#" onclick="return false;"><div class="mf-icon"><span class="fa fa-calendar"></span></div><div class="mf-title">Упрощённое бронирование</div><div class="mf-body">Оставьте заявку на любой тур всего в 3 шага</div></a></li>
  <li><a href="#" onclick="return false;"><div class="mf-icon"><span class="fa fa-shield"></span></div><div class="mf-title">Без предоплаты</div><div class="mf-body">Большую часть предложений можно оплатить по факту</div></a></li>
  <li><a href="#" onclick="return false;"><div class="mf-icon"><span class="fa fa-money"></span></div><div class="mf-title">Весь ценовой спектр</div><div class="mf-body">Тут можно найти экскурсии абсолютно любой стоимости</div></a></li>
  <li><a href="#" onclick="return false;"><div class="mf-icon"><span class="fa fa-phone"></span></div><div class="mf-title">Горячая линия</div><div class="mf-body">Где бы Вы ни были и какие бы вопросы у Вас ни возникли, мы всегда на связи</div></a></li>
</ul>

<?php
$reviews = new WP_Query(['post_type'=>'thai_guestbook','post_status'=>'publish','posts_per_page'=>3,'orderby'=>'date','order'=>'DESC','no_found_rows'=>true]);
if ($reviews->have_posts()) {
    echo '<section class="thai-home-reviews"><h2>Отзывы туристов</h2><div id="allEntries">';
    while ($reviews->have_posts()) {
        $reviews->the_post();
        echo '<div><strong>' . esc_html(get_the_title()) . '</strong><div class="eMessage">' . wp_kses_post(wpautop(get_the_content())) . '</div></div>';
    }
    echo '</div><div id="otzivi"></div></section>';
    wp_reset_postdata();
}
get_footer();
