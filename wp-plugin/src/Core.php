<?php
if (!defined('ABSPATH')) exit;
require_once __DIR__ . '/LegacyRoutes.php';
require_once __DIR__ . '/ShopCategories.php';

class TOP_Core {
    public static function boot() {
        add_action('init', [__CLASS__, 'register_content_types']);
        add_action('init', [__CLASS__, 'register_routes']);
        add_filter('template_include', [__CLASS__, 'template_router']);
        add_action('admin_menu', [__CLASS__, 'admin_menu']);
        add_action('admin_init', [__CLASS__, 'register_settings']);
        add_action('wp_enqueue_scripts', [__CLASS__, 'enqueue_assets']);

        if (defined('WP_CLI') && WP_CLI) {
            TOP_CLI::register();
        }
    }

    public static function activate() {
        self::register_content_types();
        self::register_routes();
        flush_rewrite_rules();
        if (!get_option('top_site_profile')) {
            update_option('top_site_profile', [
                'site_code' => 'ru',
                'frontend_language' => 'ru',
                'admin_language' => 'en',
                'timezone' => 'Asia/Bangkok',
                'currency' => 'THB',
            ]);
        }
    }

    public static function enqueue_assets() {
        wp_enqueue_style('thai-online-platform', plugins_url('assets/platform.css', TOP_PLUGIN_FILE), [], TOP_VERSION);
    }

    public static function register_settings() {
        register_setting('top_settings', 'top_site_profile');
    }

    public static function admin_menu() {
        add_menu_page(
            'Thai Online',
            'Thai Online',
            'manage_options',
            'thai-online',
            [__CLASS__, 'admin_dashboard'],
            'dashicons-palmtree',
            3
        );
        add_submenu_page('thai-online', 'Dashboard', 'Dashboard', 'manage_options', 'thai-online', [__CLASS__, 'admin_dashboard']);
        add_submenu_page('thai-online', 'Migration', 'Migration', 'manage_options', 'thai-online-migration', [__CLASS__, 'admin_migration']);
        add_submenu_page('thai-online', 'Diagnostics', 'Diagnostics', 'manage_options', 'thai-online-diagnostics', [__CLASS__, 'admin_diagnostics']);
        add_submenu_page('thai-online', 'Settings', 'Settings', 'manage_options', 'thai-online-settings', [__CLASS__, 'admin_settings']);
    }

    public static function admin_dashboard() {
        $profile = get_option('top_site_profile', []);
        echo '<div class="wrap"><h1>Thai Online Platform</h1>';
        echo '<p><strong>Version:</strong> ' . esc_html(TOP_VERSION) . '</p>';
        echo '<p><strong>Site profile:</strong> ' . esc_html($profile['site_code'] ?? 'ru') . '</p>';
        echo '<h2>Modules</h2><table class="widefat striped"><thead><tr><th>Module</th><th>Post type</th><th>Published</th></tr></thead><tbody>';
        foreach (self::modules() as $module => $data) {
            $count = wp_count_posts($data['post_type']);
            $published = $count && isset($count->publish) ? $count->publish : 0;
            echo '<tr><td>' . esc_html($data['label']) . '</td><td>' . esc_html($data['post_type']) . '</td><td>' . esc_html($published) . '</td></tr>';
        }
        echo '<tr><td>Site Pages</td><td>page</td><td>' . esc_html(wp_count_posts('page')->publish ?? 0) . '</td></tr>';
        echo '</tbody></table></div>';
    }

    public static function admin_migration() {
        echo '<div class="wrap"><h1>Migration</h1>';
        echo '<p>Run migration through WP-CLI for reliability:</p>';
        echo '<pre>wp thai-platform migrate --profile=ru --backup=/home/thaionline/backups/full-backup</pre>';
        echo '<p>Browser-based full migration will be added after CLI pipeline is verified.</p>';
        echo '</div>';
    }

    public static function admin_diagnostics() {
        echo '<div class="wrap"><h1>Diagnostics</h1><pre>';
        echo esc_html(self::diagnostics_text());
        echo '</pre></div>';
    }

    public static function admin_settings() {
        $profile = get_option('top_site_profile', []);
        echo '<div class="wrap"><h1>Settings</h1>';
        echo '<p><strong>Frontend language:</strong> ' . esc_html($profile['frontend_language'] ?? 'ru') . '</p>';
        echo '<p><strong>Admin language:</strong> ' . esc_html($profile['admin_language'] ?? 'en') . '</p>';
        echo '<p><strong>Current site code:</strong> ' . esc_html($profile['site_code'] ?? 'ru') . '</p>';
        echo '</div>';
    }

    public static function modules() {
        return [
            'shop' => ['label' => 'Excursions', 'post_type' => 'thai_excursion', 'slug' => 'shop'],
            'faq' => ['label' => 'FAQ', 'post_type' => 'thai_faq', 'slug' => 'faq'],
            'guestbook' => ['label' => 'Guestbook', 'post_type' => 'thai_guestbook', 'slug' => 'gb'],
            'news' => ['label' => 'News', 'post_type' => 'thai_news', 'slug' => 'news'],
            'publ' => ['label' => 'Articles', 'post_type' => 'thai_article', 'slug' => 'publ'],
            'photo' => ['label' => 'Gallery', 'post_type' => 'thai_photo', 'slug' => 'photo'],
            'forum' => ['label' => 'Forum Topics', 'post_type' => 'thai_forum_topic', 'slug' => 'forum'],
            'partner' => ['label' => 'Partners', 'post_type' => 'thai_partner', 'slug' => 'partners'],
        ];
    }

    public static function register_content_types() {
        foreach (self::modules() as $module => $data) {
            register_post_type($data['post_type'], [
                'label' => $data['label'],
                'public' => true,
                'show_ui' => true,
                'show_in_menu' => 'thai-online',
                'supports' => ['title', 'editor', 'excerpt', 'thumbnail', 'custom-fields'],
                'has_archive' => false,
                'rewrite' => false,
                'show_in_rest' => true,
            ]);
        }

        register_taxonomy('thai_excursion_cat', ['thai_excursion'], [
            'label' => 'Excursion Categories',
            'public' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'hierarchical' => true,
            'show_in_rest' => true,
            'rewrite' => false,
        ]);
    }

    public static function register_routes() {
        add_rewrite_rule('^about/?$', 'index.php?top_module=legacy_static_page&top_id=2', 'top');
        add_rewrite_rule('^other_countries_ru/?$', 'index.php?top_module=legacy_static_page&top_id=19', 'top');
        add_rewrite_rule('^([a-z0-9-]+)-301/?$', 'index.php?pagename=301-$matches[1]', 'top');
        add_rewrite_rule('^thailand_2023_pricelist/?$', 'index.php?top_module=pricelist', 'top');
        add_rewrite_rule('^gb/?$', 'index.php?top_module=guestbook', 'top');
        add_rewrite_rule('^faq/?$', 'index.php?top_module=faq', 'top');
        add_rewrite_rule('^faq/([0-9]+)-([0-9]+)/?$', 'index.php?top_module=faq_single&top_id=$matches[2]', 'top');
        add_rewrite_rule('^shop/all/?$', 'index.php?top_module=shop_all', 'top');
        add_rewrite_rule('^shop/wishlist/?$', 'index.php?top_module=shop_wishlist', 'top');
        add_rewrite_rule('^news/?$', 'index.php?top_module=news', 'top');
        add_rewrite_rule('^news/([0-9]+)-([0-9]+)/?$', 'index.php?top_module=news_single&top_id=$matches[2]', 'top');
        add_rewrite_rule('^publ/?$', 'index.php?top_module=publ', 'top');
        add_rewrite_rule('^publ/([0-9]+)-([0-9]+)/?$', 'index.php?top_module=publ_single&top_id=$matches[2]', 'top');
        add_rewrite_rule('^shop/([0-9]+)/desc/([^/]+)/?$', 'index.php?top_module=shop_single&top_id=$matches[1]&top_slug=$matches[2]', 'top');
        add_rewrite_rule('^index/0-([0-9]+)/?$', 'index.php?top_module=legacy_page&top_id=$matches[1]', 'top');
        add_rewrite_tag('%top_module%', '([^&]+)');
        add_rewrite_tag('%top_id%', '([^&]+)');
        add_rewrite_tag('%top_slug%', '([^&]+)');
    }

    public static function template_router($template) {
        $module = get_query_var('top_module');
        if (!$module) return $template;
        $map = [
            'guestbook' => 'templates/guestbook.php',
            'faq' => 'templates/faq.php',
            'faq_single' => 'templates/faq-single.php',
            'shop_all' => 'templates/shop-all.php',
            'shop_wishlist' => 'templates/shop-all.php',
            'shop_category' => 'templates/shop-all.php',
            'shop_single' => 'templates/shop-single.php',
            'news' => 'templates/news.php',
            'news_single' => 'templates/news-single.php',
            'publ' => 'templates/publ.php',
            'publ_single' => 'templates/publ-single.php',
            'legacy_page' => 'templates/legacy-page.php',
            'legacy_static_page' => 'templates/legacy-static-page.php',
            'pricelist' => 'templates/pricelist.php',
        ];
        if ($module === 'shop_category' && !TOP_Shop_Categories::term()) {
            global $wp_query;
            $wp_query->set_404();
            set_query_var('top_module', '');
            status_header(404);
            nocache_headers();
            return (get_404_template() ?: TOP_PLUGIN_DIR . 'templates/not-found.php');
        }
        if (isset($map[$module])) {
            $file = TOP_PLUGIN_DIR . $map[$module];
            if (file_exists($file)) return $file;
        }
        return $template;
    }

    public static function lang($key) {
        $profile = get_option('top_site_profile', []);
        $lang = $profile['frontend_language'] ?? 'ru';
        $strings = [
            'ru' => [
                'guestbook_title' => 'Отзывы туристов',
                'faq_title' => 'Часто задаваемые вопросы',
                'shop_title' => 'Экскурсии и услуги',
                'news_title' => 'Новости',
                'publ_title' => 'Статьи',
                'not_found' => 'Материал не найден',
                'read_more' => 'Подробнее',
                'price_from' => 'от',
            ],
            'en' => [
                'guestbook_title' => 'Traveler Reviews',
                'faq_title' => 'Frequently Asked Questions',
                'shop_title' => 'Tours and Services',
                'news_title' => 'News',
                'publ_title' => 'Articles',
                'not_found' => 'Content not found',
                'read_more' => 'Read more',
                'price_from' => 'from',
            ],
        ];
        return $strings[$lang][$key] ?? $strings['ru'][$key] ?? $key;
    }

    public static function diagnostics_text() {
        $out = [];
        $out[] = 'Thai Online Platform ' . TOP_VERSION;
        $out[] = 'WordPress: ' . get_bloginfo('version');
        $out[] = 'PHP: ' . PHP_VERSION;
        $out[] = 'Profile: ' . json_encode(get_option('top_site_profile', []), JSON_UNESCAPED_UNICODE);
        foreach (self::modules() as $module => $data) {
            $count = wp_count_posts($data['post_type']);
            $out[] = $module . ' / ' . $data['post_type'] . ': ' . (($count && isset($count->publish)) ? $count->publish : 0) . ' published';
        }
        $out[] = 'site / page: ' . (wp_count_posts('page')->publish ?? 0) . ' published';
        return implode("\n", $out);
    }

    public static function render_header($title) {
        status_header(200);
        get_header();
        echo '<main class="top-platform-page">';
        echo '<div class="top-container">';
        echo '<h1 class="top-page-title">' . esc_html($title) . '</h1>';
    }

    public static function render_footer() {
        echo '</div></main>';
        get_footer();
    }
}