<?php
if (!defined('ABSPATH')) exit;

/** reCAPTCHA v2 protection for the product price-match form only. */
class TOP_Recaptcha {
    const VERSION = '1.0.0';

    private static function setting($name, $fallback = '') {
        $value = defined($name) ? constant($name) : getenv($name);
        if ($value === false) return $fallback;
        return is_string($value) ? trim($value) : '';
    }

    public static function site_key() {
        return self::setting('THAI_RECAPTCHA_SITE_KEY', '6LesJOMtAAAAACLMzyJ18Z4MMAeqVGxCY71YzxNW');
    }

    private static function secret_key() {
        return self::setting('THAI_RECAPTCHA_SECRET_KEY');
    }

    public static function configured() {
        return self::site_key() !== '' && self::secret_key() !== '';
    }

    public static function enqueue() {
        if (get_query_var('top_module') !== 'shop_single' || !self::configured()) return;
        wp_enqueue_style('thai-price-match-recaptcha', plugins_url('assets/recaptcha.css', TOP_PLUGIN_FILE), [], self::VERSION);
        wp_enqueue_script('thai-price-match-recaptcha', plugins_url('assets/recaptcha.js', TOP_PLUGIN_FILE), [], self::VERSION, true);
    }

    public static function widget() {
        if (!self::configured()) {
            echo '<p role="alert">Проверка временно недоступна. Попробуйте позже.</p>';
            return;
        }
        echo '<div class="thai-recaptcha" data-thai-recaptcha data-sitekey="' . esc_attr(self::site_key()) . '"></div>';
        echo '<p class="thai-recaptcha-status" data-thai-recaptcha-status role="status" aria-live="polite" hidden></p>';
        echo '<noscript><p>Для проверки и отправки формы включите JavaScript в браузере.</p></noscript>';
    }

    /** No writes, mail or redirects occur before this returns true. */
    public static function verify($raw_token) {
        if (!self::configured()) return new WP_Error('thai_recaptcha_unavailable', 'Проверка временно недоступна. Попробуйте позже.', ['status' => 503]);
        if (!is_string($raw_token) || strlen($raw_token) > 4096) return self::invalid();
        $token = trim(wp_unslash($raw_token));
        if ($token === '') return self::invalid();

        $response = wp_remote_post('https://www.google.com/recaptcha/api/siteverify', [
            'timeout' => 10,
            'redirection' => 0,
            'sslverify' => true,
            'limit_response_size' => 16384,
            'body' => ['secret' => self::secret_key(), 'response' => $token],
        ]);
        if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
            return new WP_Error('thai_recaptcha_unavailable', 'Проверка временно недоступна. Попробуйте позже.', ['status' => 503]);
        }
        $result = json_decode(wp_remote_retrieve_body($response), true);
        if (!is_array($result) || !isset($result['success']) || !is_bool($result['success'])) {
            return new WP_Error('thai_recaptcha_unavailable', 'Проверка временно недоступна. Попробуйте позже.', ['status' => 503]);
        }
        if ($result['success'] !== true) {
            $errors = $result['error-codes'] ?? [];
            if (is_array($errors) && (in_array('invalid-input-secret', $errors, true) || in_array('missing-input-secret', $errors, true))) {
                return new WP_Error('thai_recaptcha_unavailable', 'Проверка временно недоступна. Попробуйте позже.', ['status' => 503]);
            }
            return self::invalid();
        }

        // Trust WordPress's configured home URL, never the request Host header.
        $expected = wp_parse_url(home_url('/'), PHP_URL_HOST);
        $hostname = $result['hostname'] ?? null;
        if (!is_string($expected) || $expected === '' || !is_string($hostname) ||
            strtolower(rtrim($hostname, '.')) !== strtolower(rtrim($expected, '.'))) {
            return self::invalid();
        }
        return true;
    }

    private static function invalid() {
        return new WP_Error('thai_recaptcha_invalid', 'Подтвердите, что вы не робот. Если проверка истекла, выполните её ещё раз.', ['status' => 400]);
    }

    public static function verify_submission() {
        $result = self::verify($_POST['g-recaptcha-response'] ?? '');
        if (is_wp_error($result)) wp_die($result->get_error_message(), '', ['response' => $result->get_error_data()['status']]);
    }
}
