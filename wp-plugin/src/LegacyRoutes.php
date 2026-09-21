<?php
if (!defined('ABSPATH')) exit;

/** Public URL compatibility; does not create users or publish content. */
class TOP_Legacy_Routes {
    public static function boot() {
        add_action('init', [__CLASS__, 'routes'], 40);
        add_action('template_redirect', [__CLASS__, 'forum_jump'], 0);
        add_filter('template_include', [__CLASS__, 'missing_jump'], 200);
    }

    public static function routes() {
        add_rewrite_rule('^photo/(?:.+/)?([0-9]+)-([1-9][0-9]*)/?$', 'index.php?top_community=photo&top_section=$matches[1]&top_page=$matches[2]', 'top');
        add_rewrite_tag('%top_forum_jump%', '([01])');
        add_rewrite_rule('^forum/([0-9]+)-([0-9]+)-0-17(?:-1)?/?$', 'index.php?top_forum_jump=1&top_section=$matches[1]&top_id=$matches[2]', 'top');
        add_rewrite_rule('^immigration-to-thailand-2022/?$', 'index.php?top_module=legacy_static_page&top_id=92', 'top');
        add_rewrite_rule('^art_in_paradise/?$', 'index.php?top_community=photo&top_section=30', 'top');
        add_rewrite_rule('^free-transfers-shopping-pattaya/?$', 'index.php?top_community=photo&top_section=329', 'top');
    }

    public static function jump_target($section, $legacy_topic) {
        if ($section < 1 || $legacy_topic < 1) return null;
        $topics = get_posts([
            'post_type'=>'thai_forum_topic', 'post_status'=>'publish', 'numberposts'=>1,
            'meta_query'=>[
                ['key'=>'_ucoz_forum_id', 'value'=>$legacy_topic, 'type'=>'NUMERIC'],
                ['key'=>'_thai_forum_section', 'value'=>$section, 'type'=>'NUMERIC'],
            ],
        ]);
        if (!$topics) return null;
        $post_id = $topics[0]->ID;
        $count = (int) get_comments(['post_id'=>$post_id, 'status'=>'approve', 'count'=>true]);
        $last = get_comments([
            'post_id'=>$post_id, 'status'=>'approve', 'number'=>1,
            'orderby'=>['comment_date'=>'DESC', 'comment_ID'=>'DESC'],
        ]);
        $url = home_url('/forum/' . $section . '-' . $legacy_topic . '-' . max(1, (int) ceil($count / 20)));
        if ($last) {
            $legacy_post = (int) get_comment_meta($last[0]->comment_ID, '_ucoz_forum_post', true);
            $url .= '#' . ($legacy_post ? 'post' . $legacy_post : 'comment' . $last[0]->comment_ID);
        }
        return $url;
    }

    public static function forum_jump() {
        if (!get_query_var('top_forum_jump')) return;
        $target = self::jump_target((int) get_query_var('top_section'), (int) get_query_var('top_id'));
        if (!$target) return;
        // The destination changes as approved replies arrive; never cache as permanent.
        nocache_headers();
        wp_safe_redirect($target, 302, 'Thai Online');
        exit;
    }

    public static function missing_jump($template) {
        if (!get_query_var('top_forum_jump')) return $template;
        global $wp_query;
        $wp_query->set_404();
        status_header(404);
        nocache_headers();
        return TOP_PLUGIN_DIR . 'templates/not-found.php';
    }
}
TOP_Legacy_Routes::boot();