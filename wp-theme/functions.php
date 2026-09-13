<?php
if (!defined('ABSPATH')) exit;

define('THAI_ONLINE_THEME_VERSION', '4.3.2');
require_once get_template_directory() . '/inc/parity.php';

add_action('after_setup_theme', function () {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('custom-logo', ['height' => 120, 'width' => 420, 'flex-height' => true, 'flex-width' => true]);
    add_theme_support('html5', ['search-form', 'gallery', 'caption', 'style', 'script']);
    register_nav_menus(['primary' => 'Primary Navigation']);
});

add_action('init', function () {
    add_rewrite_rule('^shop/all/([0-9]+)/?$', 'index.php?top_module=shop_all&paged=$matches[1]', 'top');
}, 20);

add_action('wp_enqueue_scripts', function () {
    wp_dequeue_style('thai-online-platform');
    wp_deregister_style('thai-online-platform');
    $uri = get_template_directory_uri();
    wp_enqueue_style('thai-original-bundle', $uri . '/assets/original.css', [], THAI_ONLINE_THEME_VERSION);
    wp_enqueue_style('thai-online-theme', get_stylesheet_uri(), ['thai-original-bundle'], THAI_ONLINE_THEME_VERSION);
    wp_enqueue_script('thai-shell', $uri . '/assets/shell.js', ['jquery'], THAI_ONLINE_THEME_VERSION, true);
}, 100);

add_action('customize_register', function ($wp_customize) {
    $wp_customize->add_section('thai_branding', ['title' => 'Thai Online — Header & Contacts', 'priority' => 25]);
    $settings = [
        'thai_brand_name' => ['Brand name', 'Thai-Online'],
        'thai_slogan' => ['Slogan', "Все экскурсии\nбез переплат"],
        'thai_phone' => ['Phone', '+66-838-383-539'],
        'thai_email' => ['E-mail', 'info@thai-online.org'],
        'thai_whatsapp' => ['WhatsApp number', '66838383539'],
        'thai_telegram' => ['Telegram username', 'thaionlinetours'],
        'thai_work_hours' => ['Work hours', '10:00 - 22:00'],
        'thai_footer_about' => ['Footer about text', 'На нашем сайте представлен весь ассортимент экскурсий, выполняемых из Паттайи и из Бангкока для англоязычных и русскоязычных гостей Королевства Таиланд. Помимо туров и путешествий, сегодня у нас можно заказать такси по Таиланду, трансферы на острова, забронировать авиабилеты и отели по всему миру, найти жилье в аренду в Паттайе, забронировать бесплатный трансфер в магазины Паттайи.'],
        'thai_home_seo_text' => ['Homepage SEO text', 'Купить экскурсии в Паттайе онлайн. Отдохнуть в Тайланде в 2026 году и узнать цены на самые популярные экскурсии в Паттайе, грин парки, 3Д галереи, вечерние шоу Паттайи можно на сервисе "Thai-Online". Удобный поиск по турам позволит Вам в режиме онлайн изучить и заказать самые популярные туры в Паттайе, а также ознакомиться с отзывами на них. Если Вы любитель экзотических путешествий, то в 2026 году мы готовы предложить индивидуальные туры по Тайланду. Для тех, кто предпочитает активный отдых, мы предложим различные программы по дайвингу, полёты на тарзанке, дельтаплане или круиз на лайнере. Помимо этого мы предоставим: русского гида, страховку, такси в любую точку, оформление визы и трансферы. С нами весь Тайланд как на ладони!'],
    ];
    foreach ($settings as $id => [$label, $default]) {
        $wp_customize->add_setting($id, ['default' => $default, 'sanitize_callback' => $id === 'thai_email' ? 'sanitize_email' : 'sanitize_textarea_field', 'transport' => 'refresh']);
        $wp_customize->add_control($id, ['label' => $label, 'section' => 'thai_branding', 'type' => in_array($id, ['thai_slogan', 'thai_footer_about', 'thai_home_seo_text'], true) ? 'textarea' : 'text']);
    }
});

function thai_online_primary_menu_fallback(): void { ?>
<ul class="uMenuRoot">
<li><a href="<?php echo esc_url(home_url('/about')); ?>"><span><i class="fa fa-chevron-circle-right"></i> О Нас</span></a></li>
<li><a href="<?php echo esc_url(home_url('/shop/all')); ?>"><span><i class="fa fa-globe"></i> Экскурсии и места</span></a><ul class="subM" style="display:none"><li><a href="/shop/short-tour-ekskursii-thailanda-pattaya-2023">Короткие экскурсии</a></li><li><a href="/shop/one-day-tour-thailand-2023">Экскурсии на 1 день</a></li><li><a href="/shop/night-tour-thailand-2023">Экскурсии с ночёвкой</a></li><li><a href="/shop/besplatnye-transfery-2023">Бесплатные трансферы</a></li><li><a href="/301-hotels">Отели и кондоминиумы</a></li><li><a href="/shop/prochie-uslugi-thailand-2023/oformit-visy-v-thailande-2023">Визовые услуги</a></li><li><a href="/301-air-tickets">Авиабилеты</a></li><li><a href="/other_countries_ru">Другие страны</a></li></ul></li>
<li><a href="/shop/uslugi-taxi-v-thailande-2023"><span><i class="fa fa-car"></i> Такси</span></a><ul class="subM" style="display:none"><li><a href="/shop/uslugi-taxi-v-thailande-2023/transfery-na-ostrova-thailand-2023">Трансферы на острова</a></li><li><a href="/shop/uslugi-taxi-v-thailande-2023/zakazat-vstrechu-v-aehroportu-v-thailande-2023">Встреча в аэропорту</a></li><li><a href="/shop/uslugi-taxi-v-thailande-2023/zakazat-taksi-v-bangkok-thailand-2023">Такси Паттайя-Бангкок</a></li><li><a href="/shop/uslugi-taxi-v-thailande-2023/zakazat-taksi-iz-pattaji-v-aehroport-pattaja-2023">Паттайя-аэропорт-Паттайя</a></li><li><a href="/shop/uslugi-taxi-v-thailande-2023/taksi-iz-pattaji-v-aeroporti-2023">Такси Паттайя-аэропорты</a></li><li><a href="/shop/uslugi-taxi-v-thailande-2023/prochie-napravlenija-taksi-thailand-2023">Прочие направления</a></li><li><a href="/301-bus-train-tickets">Автобусы и поезда</a></li><li><a href="https://t.me/+2_YYrwdczltjZDU9" rel="nofollow" target="_blank">Аренда автомобилей</a></li></ul></li>
<li><a href="/photo"><span><i class="fa fa-camera"></i> Галерея</span></a></li>
<li><a id="pricelist" target="_blank" href="/thailand_2023_pricelist"><span><i class="fa fa-list-alt"></i> Прайс-Лист</span></a></li>
<li><a href="/forum"><span><i class="fa fa-comments"></i> Форум</span></a></li>
<li><a href="/gb"><span><i class="fa fa-book"></i> Отзывы</span></a></li>
<li><a href="/contact"><span><i class="fa fa-info-circle"></i> Контакты</span></a></li>
</ul><?php }
