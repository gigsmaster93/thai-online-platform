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

$main_override = get_post_meta($p->ID, '_thai_main_image', true);

if ($main_override) {
    $image = $main_override;
} elseif (function_exists('thai_excursion_card_image')) {
    $image = thai_excursion_card_image($p->ID, $ucoz_id);
}

$content = (string) $p->post_content;
$has_legacy_product = strpos($content, 'id="main-product-page"') !== false;

$variants_raw = (string) get_post_meta(
    $p->ID,
    '_thai_price_variants',
    true
);

$variants = [];

foreach (explode('#', $variants_raw) as $variant) {
    $parts = explode('&', $variant);

    if (
        isset($parts[0], $parts[1]) &&
        trim($parts[0]) !== '' &&
        is_numeric($parts[1])
    ) {
        $variants[] = [
            'label' => trim($parts[0]),
            'price' => (float) $parts[1],
        ];
    }
}

$quick_raw = (string) get_post_meta(
    $p->ID,
    '_thai_quick_facts',
    true
);

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
?>

<div class="page width clearfix thai-product-page">
<div class="content clearfix" style="width:100%">
<div class="content-view">

    <div class="thai-breadcrumbs">
        <a href="<?php echo esc_url(home_url('/')); ?>">Главная</a>
        »
        <a href="<?php echo esc_url(home_url('/shop/all')); ?>">
            Экскурсии и места
        </a>
    </div>

    <div class="thai-product-heading clearfix">
        <h1><?php echo esc_html($p->post_title); ?></h1>

        <div class="product-page-price">
            <span class="shop-itempage-price">
                от
                <span class="id-good-<?php echo esc_attr($ucoz_id); ?>-price">
                    <?php echo esc_html($price_text); ?>
                </span>
            </span>
        </div>

        <div class="priceupperbutton">
            <a class="printBtn" href="#calculatey">
                Оформить / Рассчитать заказ ↓
            </a>
        </div>
    </div>

    <?php
    /* Imported uCoz product pages already contain the complete legacy body. */
    if ($has_legacy_product) {
        echo $content;
    } else {
    ?>

        <div id="main-product-page" class="thai-generated-product">

            <?php if ($image): ?>
                <div class="shop-itempage-images">
                    <a href="<?php echo esc_url($image); ?>">
                        <img
                            src="<?php echo esc_url($image); ?>"
                            alt="<?php echo esc_attr($p->post_title); ?>"
                        >
                    </a>
                </div>
            <?php endif; ?>

            <?php if ($quick): ?>
                <div class="thai-quick-facts">
                    <?php foreach ($quick as $fact): ?>
                        <div class="thai-quick-fact">
                            <strong><?php echo esc_html($fact['label']); ?></strong>
                            <span><?php echo esc_html($fact['value']); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="thai-product-description">
                <?php echo apply_filters('the_content', $content); ?>
            </div>

        </div>

        <section id="calculatey" class="thai-order-calculator">
            <h2>Узнать стоимость</h2>

            <?php if ($variants): ?>
                <label for="thai-tour-variant">Вариант тура</label>

                <select id="thai-tour-variant">
                    <?php foreach ($variants as $variant): ?>
                        <option value="<?php echo esc_attr($variant['price']); ?>">
                            <?php
                            echo esc_html(
                                $variant['label'] .
                                ' — ' .
                                number_format($variant['price'], 2, '.', '') .
                                '฿'
                            );
                            ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            <?php endif; ?>

            <div class="thai-order-total">
                Всего:
                <strong><?php echo esc_html($price_text); ?></strong>
            </div>

            <a
                class="basket now thai-book-now"
                href="https://wa.me/66838383539?text=<?php
                    echo rawurlencode(
                        'Хочу забронировать: ' .
                        $p->post_title .
                        ' (' .
                        home_url('/shop/' . $ucoz_id . '/desc/' . $p->post_name) .
                        ')'
                    );
                ?>"
                target="_blank"
                rel="noopener"
            >
                Забронировать сейчас!
            </a>
        </section>

        <section class="thai-product-reviews">
            <h2>Отзывы о <?php echo esc_html($p->post_title); ?></h2>
            <p>
                Прочитать реальные отзывы и поделиться своими впечатлениями
                о качестве обслуживания.
            </p>
        </section>

        <?php
        $recommended = new WP_Query([
            'post_type'      => 'thai_excursion',
            'post_status'    => 'publish',
            'posts_per_page' => 3,
            'post__not_in'   => [$p->ID],
            'orderby'        => 'rand',
        ]);

        if ($recommended->have_posts()):
        ?>
            <div id="recommended_products">
                <div id="recommended_products_title">
                    Рекомендуем!
                </div>

                <div class="goods-list with-clear">
                    <?php
                    while ($recommended->have_posts()):
                        $recommended->the_post();

                        if (function_exists('thai_render_excursion_card')) {
                            thai_render_excursion_card(
                                get_the_ID(),
                                'recommended_products'
                            );
                        }
                    endwhile;

                    wp_reset_postdata();
                    ?>
                </div>
            </div>
        <?php endif; ?>

    <?php } ?>

</div>
</div>
</div>

<?php get_footer(); ?>