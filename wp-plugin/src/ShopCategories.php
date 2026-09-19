<?php
if (!defined('ABSPATH')) exit;

/** Native category archives at the original shop URLs. */
class TOP_Shop_Categories {
    public static function boot() {
        add_action('init', [__CLASS__, 'routes'], 30);
        add_filter('pre_get_document_title', [__CLASS__, 'title'], 99);
        add_filter('term_link', [__CLASS__, 'term_link'], 10, 3);
    }

    public static function routes() {
        add_rewrite_tag('%top_category%', '([^&]+)');
        // Only slug paths: never intercept /shop/all or numbered product URLs.
        $path = '([a-z][a-z0-9-]*(?:/[a-z][a-z0-9-]*)*)';
        add_rewrite_rule('^shop/(?!all(?:[;/]|$))' . $path . '(?:;|/)([0-9]+)/?$', 'index.php?top_module=shop_category&top_category=$matches[1]&top_page=$matches[2]', 'top');
        add_rewrite_rule('^shop/(?!all(?:[;/]|$))' . $path . '/?$', 'index.php?top_module=shop_category&top_category=$matches[1]', 'top');
    }

    public static function path($term) {
        $parts = [];
        foreach (array_reverse(get_ancestors($term->term_id, 'thai_excursion_cat', 'taxonomy')) as $id) {
            $parts[] = get_term($id, 'thai_excursion_cat')->slug;
        }
        $parts[] = $term->slug;
        return '/shop/' . implode('/', $parts);
    }

    public static function term() {
        $path = trim((string) get_query_var('top_category'), '/');
        $parts = explode('/', $path);
        $term = get_term_by('slug', end($parts), 'thai_excursion_cat');
        return $term && self::path($term) === '/shop/' . $path ? $term : null;
    }

    public static function data($term) {
        static $data;
        if ($data === null) $data = json_decode(file_get_contents(TOP_PLUGIN_DIR . 'data/shop-categories.json'), true) ?: [];
        return $data[$term->slug] ?? [];
    }

    public static function title($title) {
        if (get_query_var('top_module') !== 'shop_category') return $title;
        $term = self::term();
        return $term ? (self::data($term)['title'] ?? $term->name . ' — Thai Online') : $title;
    }

    public static function term_link($url, $term, $taxonomy) {
        return $taxonomy === 'thai_excursion_cat' ? home_url(self::path($term)) : $url;
    }

    public static function render_children($term) {
        $data = self::data($term);
        $children = $data['children'] ?? [];
        if (!$children) return;
        echo '<hr class="hidempty"><h2 class="catshdr hidempty">Категории</h2><br><div id="catLog">';
        foreach ($children as $child) {
            $url = home_url($child['path']);
            echo '<div class="catalog-item list-item"><a href="' . esc_url($url) . '" title="' . esc_attr($child['name']) . '"><img class="catalog-item-img" src="' . esc_url($child['image']) . '" alt="' . esc_attr($child['name']) . '"></a><h3><a href="' . esc_url($url) . '">' . esc_html($child['name']) . '</a></h3><div></div></div>';
        }
        echo '<div class="clr"></div></div>';
    }
}
TOP_Shop_Categories::boot();