<?php
if (!defined('ABSPATH')) exit;

class TOP_Admin_UX {
    const NONCE_ACTION = 'top_excursion_admin_save';
    const NONCE_NAME = 'top_excursion_admin_nonce';

    public static function boot() {
        add_action('init', [__CLASS__, 'remove_technical_custom_fields'], 30);
        add_action('add_meta_boxes_thai_excursion', [__CLASS__, 'add_meta_boxes']);
        add_action('save_post_thai_excursion', [__CLASS__, 'save_excursion'], 20, 3);
        add_action('admin_enqueue_scripts', [__CLASS__, 'enqueue_assets']);
        add_filter('manage_thai_excursion_posts_columns', [__CLASS__, 'columns']);
        add_action('manage_thai_excursion_posts_custom_column', [__CLASS__, 'column_value'], 10, 2);
    }

    public static function remove_technical_custom_fields() {
        remove_post_type_support('thai_excursion', 'custom-fields');
    }

    public static function enqueue_assets($hook) {
        if (!in_array($hook, ['post.php', 'post-new.php'], true)) return;
        $screen = get_current_screen();
        if (!$screen || $screen->post_type !== 'thai_excursion') return;

        wp_enqueue_media();
        wp_enqueue_style(
            'thai-online-admin-excursion',
            plugins_url('assets/admin-excursion.css', TOP_PLUGIN_FILE),
            [],
            TOP_VERSION
        );
        wp_enqueue_script(
            'thai-online-admin-excursion',
            plugins_url('assets/admin-excursion.js', TOP_PLUGIN_FILE),
            ['jquery', 'jquery-ui-sortable'],
            TOP_VERSION,
            true
        );
    }

    public static function add_meta_boxes() {
        add_meta_box(
            'top-excursion-main',
            'Параметры экскурсии',
            [__CLASS__, 'render_main_box'],
            'thai_excursion',
            'normal',
            'high'
        );
        add_meta_box(
            'top-excursion-gallery',
            'Фотографии товара',
            [__CLASS__, 'render_gallery_box'],
            'thai_excursion',
            'normal',
            'high'
        );
        add_meta_box(
            'top-excursion-pricing',
            'Цены и варианты',
            [__CLASS__, 'render_pricing_box'],
            'thai_excursion',
            'normal',
            'high'
        );
        add_meta_box(
            'top-excursion-facts',
            'Быстрые факты и транспорт',
            [__CLASS__, 'render_facts_box'],
            'thai_excursion',
            'normal',
            'default'
        );
        add_meta_box(
            'top-excursion-links',
            'Связи, рекомендации и навигация',
            [__CLASS__, 'render_links_box'],
            'thai_excursion',
            'normal',
            'default'
        );
    }

    private static function meta($post_id, $key, $default = '') {
        $value = get_post_meta($post_id, $key, true);
        return $value === '' ? $default : $value;
    }

    private static function field($name, $value, $args = []) {
        $type = $args['type'] ?? 'text';
        $class = $args['class'] ?? 'regular-text';
        $placeholder = $args['placeholder'] ?? '';
        $attrs = '';
        if (isset($args['step'])) $attrs .= ' step="' . esc_attr($args['step']) . '"';
        if (isset($args['min'])) $attrs .= ' min="' . esc_attr($args['min']) . '"';
        echo '<input type="' . esc_attr($type) . '" class="' . esc_attr($class) . '" name="top_excursion[' . esc_attr($name) . ']" value="' . esc_attr($value) . '" placeholder="' . esc_attr($placeholder) . '"' . $attrs . '>';
    }

    private static function image_field($post_id, $name, $meta_key, $label, $help = '') {
        $value = (string) get_post_meta($post_id, $meta_key, true);
        echo '<div class="top-admin-image-field" data-image-field>';
        echo '<label><strong>' . esc_html($label) . '</strong></label>';
        echo '<div class="top-admin-image-preview">';
        if ($value !== '') {
            echo '<img src="' . esc_url(self::preview_url($value)) . '" alt="">';
        }
        echo '</div>';
        echo '<div class="top-admin-image-controls">';
        echo '<input type="text" class="regular-text code" name="top_excursion[' . esc_attr($name) . ']" value="' . esc_attr($value) . '" placeholder="/wp-content/uploads/...">';
        echo '<button type="button" class="button" data-media-pick>Выбрать из медиатеки</button>';
        echo '<button type="button" class="button-link-delete" data-media-clear>Очистить</button>';
        echo '</div>';
        if ($help !== '') echo '<p class="description">' . esc_html($help) . '</p>';
        echo '</div>';
    }

    private static function preview_url($value) {
        if ($value === '') return '';
        if (preg_match('#^https?://#i', $value)) return $value;
        return home_url('/' . ltrim($value, '/'));
    }

    public static function render_main_box($post) {
        wp_nonce_field(self::NONCE_ACTION, self::NONCE_NAME);
        echo '<input type="hidden" name="top_excursion_present" value="1">';

        $legacy_id = (int) get_post_meta($post->ID, '_ucoz_shop_id', true);
        $order_type = (string) get_post_meta($post->ID, '_thai_order_type', true);
        $gallery = (string) get_post_meta($post->ID, '_thai_gallery_url', true);

        echo '<div class="top-admin-grid top-admin-grid-3">';
        echo '<div><label><strong>Legacy ID</strong></label><input class="small-text" type="text" readonly value="' . esc_attr($legacy_id) . '"><p class="description">Системный ID старого сайта. Не редактируется.</p></div>';

        echo '<div><label for="top-order-type"><strong>Тип заказа</strong></label>';
        echo '<select id="top-order-type" name="top_excursion[order_type]">';
        $options = [
            '' => 'Автоматически / стандартный',
            'calculator' => 'Калькулятор стоимости',
            'form' => 'Форма заказа',
        ];
        foreach ($options as $value => $label) {
            echo '<option value="' . esc_attr($value) . '" ' . selected($order_type, $value, false) . '>' . esc_html($label) . '</option>';
        }
        echo '</select><p class="description">Определяет правый блок заказа на странице.</p></div>';

        echo '<div><label><strong>URL галереи</strong></label>';
        echo '<input type="text" class="regular-text code" name="top_excursion[gallery_url]" value="' . esc_attr($gallery) . '" placeholder="/photo/...">';
        if ($gallery !== '') echo '<p><a href="' . esc_url(self::preview_url($gallery)) . '" target="_blank" rel="noopener">Открыть галерею ↗</a></p>';
        echo '</div>';
        echo '</div>';

        echo '<hr>';
        echo '<div class="top-admin-grid top-admin-grid-3">';
        self::image_field($post->ID, 'main_image', '_thai_main_image', 'Главное изображение', 'Приоритетный источник основной картинки страницы.');
        self::image_field($post->ID, 'hero_image', '_thai_hero_image', 'Hero-изображение', 'Если пусто, используется главное/карточное изображение.');
        self::image_field($post->ID, 'card_image', '_thai_card_image', 'Изображение карточки', 'Используется в каталогах и рекомендациях.');
        echo '</div>';
    }

    public static function render_gallery_box($post) {
        $managed = (string) get_post_meta($post->ID, '_thai_gallery_mode', true) === 'managed';
        $legacy_items = self::resolve_gallery_items(self::legacy_gallery_items((string) $post->post_content));
        $items = $managed
            ? get_post_meta($post->ID, '_thai_gallery_items', true)
            : $legacy_items;
        if (!is_array($items)) $items = [];
        $items = self::resolve_gallery_items($items);

        echo '<div class="top-admin-gallery-editor" data-gallery-editor data-gallery-source="' . ($managed ? 'managed' : 'legacy') . '">';
        echo '<input type="hidden" name="top_excursion[gallery_mode]" value="' . ($managed ? 'managed' : 'legacy') . '" data-gallery-mode>';
        echo '<input type="hidden" name="top_excursion[gallery_json]" value="' . esc_attr(wp_json_encode($items, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)) . '" data-gallery-json>';

        echo '<div class="top-admin-gallery-toolbar">';
        echo '<div>';
        echo '<strong>Источник:</strong> <span data-gallery-mode-label>' . ($managed ? 'управляемая галерея WordPress' : 'исходная legacy-галерея') . '</span>';
        echo '<p class="description">Пока legacy-список не меняется, frontend использует исходный HTML без изменений. Добавление, удаление, перестановка или редактирование фото автоматически переводит товар в управляемый режим.</p>';
        echo '</div>';
        echo '<div class="top-admin-gallery-buttons">';
        echo '<button type="button" class="button button-primary" data-gallery-add>+ Добавить фото из медиатеки</button>';
        if ($managed) echo '<button type="button" class="button" data-gallery-reset>Вернуть исходную legacy-галерею</button>';
        echo '<button type="button" class="button-link-delete" data-gallery-clear>Очистить галерею</button>';
        echo '</div>';
        echo '</div>';

        echo '<div class="top-admin-gallery-grid" data-gallery-grid>';
        foreach ($items as $i => $item) self::render_gallery_item($i, $item);
        echo '</div>';

        if (!$items) {
            echo '<p class="top-admin-gallery-empty" data-gallery-empty>Фотографий нет. Добавьте изображения из медиатеки.</p>';
        } else {
            echo '<p class="top-admin-gallery-empty" data-gallery-empty style="display:none">Фотографий нет. Добавьте изображения из медиатеки.</p>';
        }

        echo '<template data-gallery-item-template>';
        self::render_gallery_item('__INDEX__', [
            'attachment_id' => 0,
            'src' => '',
            'alt' => '',
            'title' => '',
        ], true);
        echo '</template>';
        echo '<script type="application/json" data-gallery-legacy-json>' . wp_json_encode(
            $legacy_items,
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
        ) . '</script>';
        echo '</div>';
    }

    private static function render_gallery_item($i, $item, $template = false) {
        $idx = esc_attr((string) $i);
        $src = (string) ($item['src'] ?? '');
        $preview = $src !== '' ? self::preview_url($src) : '';
        $attachment_id = absint($item['attachment_id'] ?? 0);
        echo '<div class="top-admin-gallery-item" data-gallery-item>';
        echo '<div class="top-admin-gallery-thumb" data-gallery-drag title="Перетащите для изменения порядка">';
        if ($preview !== '') echo '<img src="' . esc_url($preview) . '" alt="">';
        else echo '<span class="dashicons dashicons-format-image"></span>';
        echo '<span class="top-admin-gallery-grip dashicons dashicons-move"></span>';
        echo '</div>';
        echo '<input type="hidden" value="' . esc_attr($attachment_id) . '" data-gallery-attachment>';
        echo '<label>Файл / URL<input type="text" class="widefat code" value="' . esc_attr($src) . '" data-gallery-src></label>';
        echo '<label>Alt<input type="text" class="widefat" value="' . esc_attr($item['alt'] ?? '') . '" data-gallery-alt></label>';
        echo '<label>Title<input type="text" class="widefat" value="' . esc_attr($item['title'] ?? '') . '" data-gallery-title></label>';
        echo '<button type="button" class="button-link-delete" data-gallery-remove>Удалить</button>';
        echo '</div>';
    }

    public static function legacy_gallery_items($content) {
        $items = [];
        $content = (string) $content;
        if ($content === '') return $items;
        if (!preg_match('~<div\b[^>]*class=(["\'])[^"\']*\bslideout-sidebar\b[^"\']*\1[^>]*>(.*?)</div>~is', $content, $m)) {
            return $items;
        }
        if (!preg_match_all('~<img\b[^>]*>~i', $m[2], $imgs)) return $items;
        foreach ($imgs[0] as $tag) {
            $src = self::html_attr($tag, 'src');
            if ($src === '') $src = self::html_attr($tag, 'data-src');
            if ($src === '') continue;
            $items[] = [
                'attachment_id' => 0,
                'src' => html_entity_decode($src, ENT_QUOTES | ENT_HTML5, 'UTF-8'),
                'alt' => html_entity_decode(self::html_attr($tag, 'alt'), ENT_QUOTES | ENT_HTML5, 'UTF-8'),
                'title' => html_entity_decode(self::html_attr($tag, 'title'), ENT_QUOTES | ENT_HTML5, 'UTF-8'),
            ];
        }
        return $items;
    }

    private static function html_attr($tag, $name) {
        if (preg_match('~\b' . preg_quote($name, '~') . '\s*=\s*(["\'])(.*?)\1~is', (string) $tag, $m)) {
            return (string) $m[2];
        }
        return '';
    }

    public static function sanitize_gallery_items($items) {
        if (!is_array($items)) return [];
        $out = [];
        foreach ($items as $item) {
            if (!is_array($item)) continue;
            $attachment_id = absint($item['attachment_id'] ?? 0);
            $src = self::sanitize_url_or_path($item['src'] ?? '');
            if ($attachment_id) {
                $attachment_url = wp_get_attachment_url($attachment_id);
                if ($attachment_url) $src = self::localize_url($attachment_url);
            }
            if ($src === '') continue;
            $out[] = [
                'attachment_id' => $attachment_id,
                'src' => $src,
                'alt' => trim(self::sanitize_inline_preserve($item['alt'] ?? '')),
                'title' => trim(self::sanitize_inline_preserve($item['title'] ?? '')),
            ];
        }
        return $out;
    }

    public static function resolve_gallery_items($items) {
        if (!is_array($items)) return [];
        $out = [];
        foreach ($items as $item) {
            if (!is_array($item)) continue;
            $attachment_id = absint($item['attachment_id'] ?? 0);
            $src = (string) ($item['src'] ?? '');
            $alt = (string) ($item['alt'] ?? '');
            $title = (string) ($item['title'] ?? '');
            if ($attachment_id) {
                $attachment_url = wp_get_attachment_url($attachment_id);
                if ($attachment_url) $src = self::localize_url($attachment_url);
                if ($alt === '') $alt = (string) get_post_meta($attachment_id, '_wp_attachment_image_alt', true);
                if ($title === '') $title = (string) get_the_title($attachment_id);
            }
            if ($src === '') continue;
            $out[] = [
                'attachment_id' => $attachment_id,
                'src' => $src,
                'alt' => $alt,
                'title' => $title,
            ];
        }
        return $out;
    }

    private static function localize_url($url) {
        $url = (string) $url;
        $parts = wp_parse_url($url);
        $home = wp_parse_url(home_url('/'));
        if (!empty($parts['host']) && !empty($home['host']) && strtolower($parts['host']) === strtolower($home['host'])) {
            $path = $parts['path'] ?? '/';
            if (!empty($parts['query'])) $path .= '?' . $parts['query'];
            return $path;
        }
        return $url;
    }

    public static function strip_legacy_gallery_sidebar($content) {
        return preg_replace(
            '~<div\b[^>]*class=(["\'])[^"\']*\bslideout-sidebar\b[^"\']*\1[^>]*>.*?</div>~is',
            '',
            (string) $content,
            1
        );
    }

    public static function render_pricing_box($post) {
        $price = (string) get_post_meta($post->ID, '_thai_price', true);
        $variants = self::parse_price_variants((string) get_post_meta($post->ID, '_thai_price_variants', true));

        echo '<div class="top-admin-grid top-admin-grid-3 top-admin-price-head">';
        echo '<div><label><strong>Базовая цена, ฿</strong></label>';
        self::field('price', $price, ['type'=>'number','class'=>'small-text','step'=>'0.01','min'=>'0']);
        echo '<p class="description">Используется в каталогах и как базовая цена.</p></div>';

        $person_labels = get_post_meta($post->ID, '_thai_person_labels', true);
        if (!is_array($person_labels)) $person_labels = [];
        echo '<div class="top-admin-span-2"><label><strong>Подписи количества людей</strong></label>';
        echo '<textarea class="large-text" rows="2" name="top_excursion[person_labels]" placeholder="Взрослые&#10;Дети 4–11 лет&#10;Дети до 4 лет">' . esc_textarea(implode("\n", $person_labels)) . '</textarea>';
        echo '<p class="description">Необязательно. По одной подписи на строку; соответствует строкам первой группы цен.</p></div>';
        echo '</div>';

        echo '<h3>Варианты цен</h3>';
        echo '<p class="description">Строка с отметкой «Новая группа» начинает следующий вариант тура. Это сохраняет существующую группировку виз/туров.</p>';
        self::render_price_table($variants);
    }

    private static function render_price_table($rows) {
        echo '<table class="widefat striped top-admin-repeatable" data-repeatable="price">';
        echo '<thead><tr><th class="top-admin-drag">№</th><th>Название</th><th>Цена, ฿</th><th>Новая группа</th><th></th></tr></thead><tbody>';
        if (!$rows) $rows = [['label'=>'','price'=>'','group'=>false]];
        foreach ($rows as $i => $row) self::render_price_row($i, $row);
        echo '</tbody></table>';
        echo '<p><button type="button" class="button" data-add-row="price">+ Добавить вариант цены</button></p>';
        echo '<template data-row-template="price">';
        self::render_price_row('__INDEX__', ['label'=>'','price'=>'','group'=>false], true);
        echo '</template>';
    }

    private static function render_price_row($i, $row, $template = false) {
        $idx = esc_attr((string)$i);
        echo '<tr data-row>';
        echo '<td class="top-admin-row-number">' . ($template ? '#' : esc_html((string)((int)$i + 1))) . '</td>';
        echo '<td><input class="large-text" type="text" name="top_excursion[price_variants][' . $idx . '][label]" value="' . esc_attr($row['label'] ?? '') . '"></td>';
        echo '<td><input class="small-text" type="text" inputmode="decimal" name="top_excursion[price_variants][' . $idx . '][price]" value="' . esc_attr($row['price'] ?? '') . '"></td>';
        echo '<td><input type="hidden" name="top_excursion[price_variants][' . $idx . '][separator]" value="' . esc_attr($row['separator'] ?? '') . '"><label><input type="checkbox" name="top_excursion[price_variants][' . $idx . '][group]" value="1" ' . checked(!empty($row['group']), true, false) . '> Да</label></td>';
        echo '<td><button type="button" class="button-link-delete" data-remove-row>Удалить</button></td>';
        echo '</tr>';
    }

    public static function render_facts_box($post) {
        $facts = self::parse_quick_facts((string) get_post_meta($post->ID, '_thai_quick_facts', true));
        $transport = self::parse_pairs((string) get_post_meta($post->ID, '_thai_transport_variants', true));

        echo '<h3>Быстрые факты</h3>';
        echo '<p class="description">Например: «Ежедневно / 10:00–23:30 / calendar». В значении разрешён перенос <code>&lt;br&gt;</code>.</p>';
        self::render_fact_table($facts);

        echo '<hr><h3>Варианты транспорта</h3>';
        echo '<p class="description">Используются в транспортном калькуляторе, если заполнены.</p>';
        self::render_pair_table('transport', $transport, 'Название транспорта', 'Цена, ฿');
    }

    private static function render_fact_table($rows) {
        echo '<table class="widefat striped top-admin-repeatable" data-repeatable="fact">';
        echo '<thead><tr><th class="top-admin-drag">№</th><th>Название</th><th>Значение</th><th>Иконка</th><th></th></tr></thead><tbody>';
        if (!$rows) $rows = [['label'=>'','value'=>'','icon'=>'']];
        foreach ($rows as $i=>$row) self::render_fact_row($i,$row);
        echo '</tbody></table><p><button type="button" class="button" data-add-row="fact">+ Добавить факт</button></p>';
        echo '<template data-row-template="fact">';
        self::render_fact_row('__INDEX__',['label'=>'','value'=>'','icon'=>''],true);
        echo '</template>';
    }

    private static function render_fact_row($i, $row, $template=false) {
        $idx=esc_attr((string)$i);
        echo '<tr data-row>';
        echo '<td class="top-admin-row-number">'.($template?'#':esc_html((string)((int)$i+1))).'</td>';
        echo '<td><input class="large-text" type="text" name="top_excursion[quick_facts]['.$idx.'][label]" value="'.esc_attr($row['label']??'').'"></td>';
        echo '<td><input class="large-text" type="text" name="top_excursion[quick_facts]['.$idx.'][value]" value="'.esc_attr($row['value']??'').'"></td>';
        echo '<td><input type="text" name="top_excursion[quick_facts]['.$idx.'][icon]" value="'.esc_attr($row['icon']??'').'" placeholder="calendar"></td>';
        echo '<td><button type="button" class="button-link-delete" data-remove-row>Удалить</button></td>';
        echo '</tr>';
    }

    private static function render_pair_table($name, $rows, $label_head, $price_head) {
        echo '<table class="widefat striped top-admin-repeatable" data-repeatable="'.esc_attr($name).'">';
        echo '<thead><tr><th class="top-admin-drag">№</th><th>'.esc_html($label_head).'</th><th>'.esc_html($price_head).'</th><th></th></tr></thead><tbody>';
        if (!$rows) $rows=[['label'=>'','price'=>'']];
        foreach($rows as $i=>$row) self::render_pair_row($name,$i,$row);
        echo '</tbody></table><p><button type="button" class="button" data-add-row="'.esc_attr($name).'">+ Добавить</button></p>';
        echo '<template data-row-template="'.esc_attr($name).'">';
        self::render_pair_row($name,'__INDEX__',['label'=>'','price'=>''],true);
        echo '</template>';
    }

    private static function render_pair_row($name,$i,$row,$template=false) {
        $idx=esc_attr((string)$i);
        echo '<tr data-row>';
        echo '<td class="top-admin-row-number">'.($template?'#':esc_html((string)((int)$i+1))).'</td>';
        echo '<td><input class="large-text" type="text" name="top_excursion['.esc_attr($name).']['.$idx.'][label]" value="'.esc_attr($row['label']??'').'"></td>';
        echo '<td><input class="small-text" type="text" inputmode="decimal" name="top_excursion['.esc_attr($name).']['.$idx.'][price]" value="'.esc_attr($row['price']??'').'"></td>';
        echo '<td><button type="button" class="button-link-delete" data-remove-row>Удалить</button></td>';
        echo '</tr>';
    }

    public static function render_links_box($post) {
        $recommended=(string)get_post_meta($post->ID,'_thai_recommended_ids',true);
        $breadcrumbs=(string)get_post_meta($post->ID,'_thai_breadcrumbs',true);

        echo '<div class="top-admin-grid top-admin-grid-2">';
        echo '<div><label><strong>Рекомендуемые экскурсии</strong></label>';
        echo '<input class="large-text" type="text" name="top_excursion[recommended_ids]" value="'.esc_attr($recommended).'" placeholder="96,103,406">';
        echo '<p class="description">Legacy ID экскурсий через запятую. Порядок сохраняется.</p>';
        $ids=array_values(array_filter(array_map('absint',preg_split('/[^0-9]+/',$recommended))));
        if($ids){
            echo '<ul class="top-admin-related">';
            foreach($ids as $legacy){
                $p=get_posts(['post_type'=>'thai_excursion','post_status'=>'any','numberposts'=>1,'meta_key'=>'_ucoz_shop_id','meta_value'=>$legacy]);
                if($p) echo '<li><a href="'.esc_url(get_edit_post_link($p[0]->ID)).'">#'.esc_html($legacy).' — '.esc_html($p[0]->post_title).'</a> <span class="description">('.esc_html($p[0]->post_status).')</span></li>';
                else echo '<li class="top-admin-warning">#'.esc_html($legacy).' — не найдено</li>';
            }
            echo '</ul>';
        }
        echo '</div>';

        echo '<div><label><strong>Breadcrumbs</strong></label>';
        echo '<textarea class="large-text code" rows="7" name="top_excursion[breadcrumbs]" placeholder="Главная|/&#10;Экскурсии на 1 день|/shop/...">'.esc_textarea($breadcrumbs).'</textarea>';
        echo '<p class="description">Одна строка: <code>Название|/путь</code>. Если пусто, frontend использует стандартную цепочку.</p></div>';
        echo '</div>';
    }

    public static function save_excursion($post_id, $post, $update) {
        if (!$post || $post->post_type !== 'thai_excursion') return;
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        if (wp_is_post_revision($post_id)) return;
        if (!isset($_POST['top_excursion_present'])) return;
        if (!isset($_POST[self::NONCE_NAME]) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST[self::NONCE_NAME])), self::NONCE_ACTION)) return;
        if (!current_user_can('edit_post', $post_id)) return;

        $data = isset($_POST['top_excursion']) && is_array($_POST['top_excursion'])
            ? wp_unslash($_POST['top_excursion'])
            : [];

        self::set_meta($post_id, '_thai_price', self::normalize_price($data['price'] ?? ''));
        self::set_meta($post_id, '_thai_order_type', self::allowed_order_type($data['order_type'] ?? ''));
        self::set_meta($post_id, '_thai_gallery_url', self::sanitize_url_or_path($data['gallery_url'] ?? ''));
        self::set_meta($post_id, '_thai_main_image', self::sanitize_url_or_path($data['main_image'] ?? ''));
        self::set_meta($post_id, '_thai_hero_image', self::sanitize_url_or_path($data['hero_image'] ?? ''));
        self::set_meta($post_id, '_thai_card_image', self::sanitize_url_or_path($data['card_image'] ?? ''));

        $gallery_mode = (($data['gallery_mode'] ?? '') === 'managed') ? 'managed' : 'legacy';
        $gallery_payload = json_decode((string) ($data['gallery_json'] ?? '[]'), true);
        if (!is_array($gallery_payload)) $gallery_payload = [];
        if ($gallery_mode === 'managed') {
            update_post_meta($post_id, '_thai_gallery_mode', 'managed');
            update_post_meta($post_id, '_thai_gallery_items', self::sanitize_gallery_items($gallery_payload));
        } else {
            delete_post_meta($post_id, '_thai_gallery_mode');
            delete_post_meta($post_id, '_thai_gallery_items');
        }

        self::set_meta($post_id, '_thai_price_variants', self::serialize_price_variants($data['price_variants'] ?? []));
        self::set_meta($post_id, '_thai_quick_facts', self::serialize_quick_facts($data['quick_facts'] ?? []));
        self::set_meta($post_id, '_thai_transport_variants', self::serialize_pairs($data['transport'] ?? []));

        $labels = self::sanitize_lines($data['person_labels'] ?? '');
        if ($labels) {
            update_post_meta($post_id, '_thai_person_labels', $labels);
        } elseif (metadata_exists('post', $post_id, '_thai_person_labels')) {
            update_post_meta($post_id, '_thai_person_labels', []);
        } else {
            delete_post_meta($post_id, '_thai_person_labels');
        }

        $ids = array_values(array_unique(array_filter(array_map('absint', preg_split('/[^0-9]+/', (string)($data['recommended_ids'] ?? ''))))));
        self::set_meta($post_id, '_thai_recommended_ids', $ids ? implode(',', $ids) : '');

        self::set_meta($post_id, '_thai_breadcrumbs', self::sanitize_breadcrumbs($data['breadcrumbs'] ?? ''));
    }

    private static function set_meta($post_id, $key, $value) {
        if ($value === '' || $value === []) {
            // Preserve an existing explicit empty value so opening/saving an excursion
            // is a true no-op at the metadata level. If the key never existed, do not
            // create a new empty row.
            if (metadata_exists('post', $post_id, $key)) {
                update_post_meta($post_id, $key, $value);
            } else {
                delete_post_meta($post_id, $key);
            }
        } else {
            update_post_meta($post_id, $key, $value);
        }
    }

    public static function normalize_price($value) {
        $value = trim(str_replace(',', '.', (string)$value));
        if ($value === '' || !preg_match('/^(?:\d+)(?:\.\d+)?$/', $value)) return '';
        if ((float)$value < 0) return '';
        return $value;
    }

    private static function sanitize_inline_preserve($value) {
        $value = wp_check_invalid_utf8((string)$value);
        $value = strip_tags($value);
        $value = preg_replace('/[\r\n\t]+/', ' ', $value);
        return (string)$value;
    }

    private static function sanitize_variant_value($value) {
        $value = (string)$value;
        if (trim($value) === '') return '';
        $numeric = self::normalize_price($value);
        if ($numeric !== '') return $numeric;
        return self::sanitize_inline_preserve($value);
    }

    private static function allowed_order_type($value) {
        $value=(string)$value;
        return in_array($value,['','calculator','form'],true)?$value:'';
    }

    public static function sanitize_url_or_path($value) {
        $value = trim((string)$value);
        if ($value === '') return '';
        if (preg_match('#^https?://#i',$value)) return esc_url_raw($value);
        if ($value[0] !== '/') $value = '/' . $value;
        return '/' . ltrim(sanitize_text_field($value), '/');
    }

    private static function sanitize_lines($value) {
        $out=[];
        foreach(preg_split('/\r?\n/',(string)$value) as $line){
            $line=trim(self::sanitize_inline_preserve($line));
            if($line!=='')$out[]=$line;
        }
        return $out;
    }

    public static function parse_price_variants($raw) {
        $rows=[];
        $raw=(string)$raw;
        if($raw==='')return $rows;

        $tokens=preg_split('/(#%|%|#)/',$raw,-1,PREG_SPLIT_DELIM_CAPTURE);
        $group=false;
        $separator='';
        foreach($tokens as $token){
            if($token==='')continue;
            if(in_array($token,['#','%','#%'],true)){
                $group=($token==='%' || $token==='#%');
                $separator=$group?$token:'';
                continue;
            }
            $parts=explode('&',$token,2);
            $rows[]=[
                'label'=>$parts[0]??'',
                'price'=>$parts[1]??'',
                'group'=>$group,
                'separator'=>$separator,
            ];
            $group=false;
            $separator='';
        }
        return $rows;
    }

    public static function serialize_price_variants($rows) {
        if(!is_array($rows))return '';
        $out='';
        $first=true;
        foreach($rows as $row){
            if(!is_array($row))continue;
            $label=self::sanitize_inline_preserve($row['label']??'');
            $price=self::sanitize_variant_value($row['price']??'');
            if(trim($label)==='' || trim($price)==='')continue;

            if(!$first){
                if(!empty($row['group'])){
                    $separator=(string)($row['separator']??'');
                    if(!in_array($separator,['%','#%'],true))$separator='#%';
                    $out.=$separator;
                }else{
                    $out.='#';
                }
            }

            $out.=$label.'&'.$price;
            $first=false;
        }
        return $out;
    }

    public static function parse_quick_facts($raw) {
        $rows=[];
        foreach(explode('#',(string)$raw) as $entry){
            if($entry==='')continue;
            $parts=explode('&',$entry,3);
            if(($parts[0]??'')==='')continue;
            $rows[]=['label'=>$parts[0]??'','value'=>$parts[1]??'','icon'=>$parts[2]??''];
        }
        return $rows;
    }

    public static function serialize_quick_facts($rows) {
        if(!is_array($rows))return '';
        $out=[];
        foreach($rows as $row){
            if(!is_array($row))continue;
            $label=trim(sanitize_text_field($row['label']??''));
            $value=trim(wp_kses((string)($row['value']??''),['br'=>[]]));
            $icon=trim(preg_replace('/[^a-zA-Z0-9_-]/','',(string)($row['icon']??'')));
            if($label==='' || $value==='')continue;
            $entry=$label.'&'.$value;
            if($icon!=='')$entry.='&'.$icon;
            $out[]=$entry;
        }
        return implode('#',$out);
    }

    public static function parse_pairs($raw) {
        $rows=[];
        foreach(explode('#',(string)$raw) as $entry){
            if($entry==='')continue;
            $parts=explode('&',$entry,2);
            if(($parts[0]??'')==='')continue;
            $rows[]=['label'=>str_replace('--',"'",$parts[0]??''),'price'=>$parts[1]??''];
        }
        return $rows;
    }

    public static function serialize_pairs($rows) {
        if(!is_array($rows))return '';
        $out=[];
        foreach($rows as $row){
            if(!is_array($row))continue;
            $label=self::sanitize_inline_preserve($row['label']??'');
            $price=self::sanitize_variant_value($row['price']??'');
            if(trim($label)==='' || trim($price)==='')continue;
            $label=str_replace("'","--",$label);
            $out[]=$label.'&'.$price;
        }
        return implode('#',$out);
    }

    public static function sanitize_breadcrumbs($raw) {
        $lines=[];
        foreach(preg_split('/\r?\n/',(string)$raw) as $line){
            $line=trim($line);
            if($line==='')continue;
            $parts=explode('|',$line,2);
            $label=trim(sanitize_text_field($parts[0]??''));
            if($label==='')continue;
            $url=isset($parts[1])?self::sanitize_url_or_path($parts[1]):'';
            $lines[]=$label.'|'.$url;
        }
        return implode("\n",$lines);
    }

    public static function columns($columns) {
        $out=[];
        foreach($columns as $key=>$label){
            $out[$key]=$label;
            if($key==='title'){
                $out['top_legacy_id']='Legacy ID';
                $out['top_price']='Цена';
                $out['top_order']='Заказ';
                $out['top_gallery']='Галерея';
            }
        }
        return $out;
    }

    public static function column_value($column,$post_id) {
        if($column==='top_legacy_id'){
            echo esc_html((string)get_post_meta($post_id,'_ucoz_shop_id',true));
        }elseif($column==='top_price'){
            $price=(string)get_post_meta($post_id,'_thai_price',true);
            echo $price!==''?esc_html(number_format((float)$price,2,'.','').'฿'):'—';
        }elseif($column==='top_order'){
            $type=(string)get_post_meta($post_id,'_thai_order_type',true);
            $labels=[''=>'Стандарт','calculator'=>'Калькулятор','form'=>'Форма'];
            echo esc_html($labels[$type]??$type);
        }elseif($column==='top_gallery'){
            $url=(string)get_post_meta($post_id,'_thai_gallery_url',true);
            echo $url!==''?'<span class="dashicons dashicons-yes-alt" title="'.esc_attr($url).'"></span>':'—';
        }
    }
}