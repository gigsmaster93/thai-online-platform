<?php
if (!defined('ABSPATH')) exit;

define('THAI_ONLINE_THEME_VERSION', '4.4.19');
require_once get_template_directory() . '/inc/parity.php';

add_action('after_setup_theme', function () {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('custom-logo', ['height' => 120, 'width' => 420, 'flex-height' => true, 'flex-width' => true]);
    add_theme_support('html5', ['search-form', 'gallery', 'caption', 'style', 'script']);
    register_nav_menus(['primary' => 'Primary Navigation']);
});

add_action('init', function () {
    add_rewrite_tag('%top_page%', '([0-9]+)');
    add_rewrite_rule('^shop/all/?$', 'index.php?top_module=shop_all&top_page=1', 'top');
    add_rewrite_rule('^shop/all/([0-9]+)/?$', 'index.php?top_module=shop_all&top_page=$matches[1]', 'top');
}, 20);

add_action('init', function () {
    if (post_type_exists('thai_excursion')) {
        add_post_type_support('thai_excursion', 'comments');
    }
}, 100);

/* Legacy module routes are real pages, not WordPress 404s. */
add_filter('pre_handle_404', function ($preempt, $wp_query) {
    if (get_query_var('top_module')) {
        $wp_query->is_404 = false;
        return true;
    }
    return $preempt;
}, 10, 2);

add_action('wp', function () {
    if (get_query_var('top_module')) {
        global $wp_query;
        if ($wp_query instanceof WP_Query) {
            $wp_query->is_404 = false;
        }
        status_header(200);
    }
}, 0);

add_filter('body_class', function ($classes) {
    if (get_query_var('top_module')) {
        $classes = array_values(array_diff($classes, ['error404']));
    }
    return $classes;
});

add_filter('redirect_canonical', function ($redirect_url, $requested_url) {
    if (get_query_var('top_module')) {
        return false;
    }
    return $redirect_url;
}, 10, 2);

add_filter('pre_get_document_title', function ($title) {
    $module = get_query_var('top_module');

    if (is_front_page()) {
        $year = (int) wp_date('Y');
        return 'Экскурсии в Паттайе Таиланде ' . ($year - 1) . '-' . $year . ' цены описание отзывы';
    }

    if ($module === 'shop_all') {
        return 'Все товары - Экскурсии Паттайя ' . wp_date('Y') . ' - Сервис поиска экскурсий в Таиланде';
    }

    if ($module === 'shop_single') {
        $ucoz_id = (int) get_query_var('top_id');
        $posts = get_posts([
            'post_type'      => 'thai_excursion',
            'post_status'    => 'publish',
            'meta_key'       => '_ucoz_shop_id',
            'meta_value'     => $ucoz_id,
            'posts_per_page' => 1,
        ]);

        if ($posts) {
            $seo_title = (string) get_post_meta($posts[0]->ID, '_thai_seo_title', true);

            if ($seo_title !== '') {
                return $seo_title;
            }

            return get_the_title($posts[0]) . ' - Экскурсии Таиланд Паттайя ' . wp_date('Y') . ' прайс лист с ценами описаниями отзывами';
        }
    }

    return $title;
}, 50);

add_action('wp_head', function () {
    if (get_query_var('top_module') !== 'shop_single') {
        return;
    }

    $ucoz_id = (int) get_query_var('top_id');
    $posts = get_posts([
        'post_type'      => 'thai_excursion',
        'post_status'    => 'publish',
        'meta_key'       => '_ucoz_shop_id',
        'meta_value'     => $ucoz_id,
        'posts_per_page' => 1,
    ]);

    if (!$posts) {
        return;
    }

    $description = (string) get_post_meta($posts[0]->ID, '_thai_seo_description', true);

    if ($description !== '') {
        echo '<meta name="description" content="' . esc_attr($description) . '">' . "\n";
    }
}, 20);

/* Match the actual uCoz stylesheet stack instead of approximating it. */
add_action('wp_enqueue_scripts', function () {
    foreach (['thai-online-platform', 'tocms-frontend', 'wp-block-library', 'wp-block-library-theme', 'classic-theme-styles', 'global-styles'] as $handle) {
        wp_dequeue_style($handle);
        wp_deregister_style($handle);
    }

    $v = THAI_ONLINE_THEME_VERSION;
    $module = get_query_var('top_module');
    $is_shop_catalog = ($module === 'shop_all');

    wp_enqueue_style('ucoz-my', home_url('/_st/my.css'), [], $v);
    $previous = 'ucoz-my';

    if (is_front_page()) {
        wp_enqueue_style('ucoz-main-page', home_url('/css/mainPage.css'), [$previous], $v);
        $previous = 'ucoz-main-page';
    } elseif ($is_shop_catalog) {
        wp_enqueue_style('ucoz-katalog', home_url('/css/katalog/1.css'), [$previous], $v);
        $previous = 'ucoz-katalog';
    }

    wp_enqueue_style('ucoz-shop', home_url('/_st/shop.css'), [$previous], $v);
    wp_enqueue_style('ucoz-bottom', home_url('/css/bottom/1.css'), ['ucoz-shop'], $v);

    $previous = 'ucoz-bottom';
    if (!is_front_page()) {
        wp_enqueue_style('ucoz-header-search', home_url('/css/header/1.css'), [$previous], $v);
        $previous = 'ucoz-header-search';
    }
    wp_enqueue_style('ucoz-header', home_url('/css/header/2.css'), [$previous], $v);
    $previous = 'ucoz-header';

    if ($module === 'shop_single') {
        wp_enqueue_style(
            'ucoz-tovar-2',
            home_url('/css/tovar/2.css'),
            [$previous],
            '525'
        );

        wp_enqueue_style(
            'ucoz-tovar-1',
            home_url('/css/tovar/1.css'),
            ['ucoz-tovar-2'],
            '525'
        );

        /*
         * Product pages depend on legacy tourpage rules for the radio-tab
         * layout and the desktop/mobile product columns. uCoz injected these
         * rules through its page stack, so load the preserved asset explicitly.
         */
        wp_enqueue_style(
            'ucoz-tourpage',
            home_url('/css/tourpage.css'),
            ['ucoz-tovar-1'],
            $v
        );

        wp_enqueue_style(
            'ucoz-rightbl',
            home_url('/css/rightblstyle.css'),
            ['ucoz-tourpage'],
            $v
        );

        $previous = 'ucoz-rightbl';
    }

    wp_enqueue_style(
        'ucoz-bottom-page',
        home_url('/css/btmPage.css'),
        [$previous],
        $v
    );

    $previous = 'ucoz-bottom-page';

    if (is_front_page()) {
        /* uCoz loads mainPage.css again after document.ready; keep it last in cascade. */
        wp_enqueue_style('ucoz-main-page-final', home_url('/css/mainPage.css'), [$previous], $v . '-final');
        $previous = 'ucoz-main-page-final';
    }

    wp_enqueue_style('thai-online-theme', get_stylesheet_uri(), [$previous], $v);
    wp_enqueue_script('thai-shell', get_template_directory_uri() . '/assets/shell.js', ['jquery'], $v, true);
}, 999);

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

/* Custom legacy modules must not inherit WordPress home/blog state. */
add_action('wp', function () {
    if (!get_query_var('top_module')) {
        return;
    }

    global $wp_query;

    if ($wp_query instanceof WP_Query) {
        $wp_query->is_home       = false;
        $wp_query->is_front_page = false;
        $wp_query->is_404        = false;
    }
}, 50);

add_filter('body_class', function ($classes) {
    $module = get_query_var('top_module');

    if (!$module) {
        return $classes;
    }

    $classes = array_values(array_diff(
        $classes,
        ['home', 'blog', 'error404']
    ));

    $classes[] = 'thai-module';
    $classes[] = 'thai-module-' . sanitize_html_class($module);

    return array_unique($classes);
}, 99);