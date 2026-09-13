<?php
get_header();

$pages = get_posts([
    'post_type' => 'page',
    'post_status' => 'publish',
    'meta_key' => '_ucoz_site_id',
    'meta_value' => '1',
    'numberposts' => 1,
]);

if ($pages) {
    $page = $pages[0];
    echo '<article class="top-page-content">';
    echo apply_filters('the_content', $page->post_content);
    echo '</article>';
} elseif (have_posts()) {
    while (have_posts()) {
        the_post();
        echo '<article class="top-page-content">';
        the_content();
        echo '</article>';
    }
}

get_footer();
