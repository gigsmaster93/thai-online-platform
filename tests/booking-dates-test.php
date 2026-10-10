<?php
// Hermetic booking-controller tests: no WordPress bootstrap, DB, network or real mail.
define('ABSPATH', __DIR__ . '/fixture/');
define('TOP_PLUGIN_FILE', __DIR__ . '/fixture/plugin.php');
class BookingDateDie extends Exception { public $status; function __construct($message, $status) { parent::__construct($message); $this->status = $status; } }
class BookingDateRedirect extends Exception {}
$checks = []; $events = []; $scripts = []; $profile = ['timezone' => 'Asia/Bangkok'];
$clock = strtotime('2026-10-10T16:59:59Z'); $nonce = true; $limited = false;
function check($name, $pass) { global $checks; $checks[] = ['name' => $name, 'pass' => (bool) $pass]; if (!$pass) throw new Exception($name); }
function add_action(...$args) {}
function add_filter(...$args) {}
function add_shortcode(...$args) {}
function get_option($name, $default = []) { global $profile; return $name === 'top_site_profile' ? $profile : $default; }
function wp_date($format, $timestamp = null, $timezone = null) { global $clock; return (new DateTimeImmutable('@' . ($timestamp ?? $clock)))->setTimezone($timezone ?? new DateTimeZone('UTC'))->format($format); }
function wp_unslash($v) { return is_string($v) ? stripslashes($v) : $v; }
function wp_slash($v) { return $v; }
function sanitize_textarea_field($v) { return trim((string) $v); }
function sanitize_text_field($v) { return trim((string) $v); }
function sanitize_key($v) { return preg_replace('/[^a-z0-9_-]/', '', strtolower($v)); }
function absint($v) { return abs((int) $v); }
function is_email($v) { return filter_var($v, FILTER_VALIDATE_EMAIL) !== false; }
function wp_die($message, $title = '', $args = []) { throw new BookingDateDie($message, $args['response']); }
function get_post($id) { return (object) ['ID' => $id, 'post_type' => 'thai_excursion', 'post_status' => 'publish', 'post_title' => 'Fixture tour', 'post_name' => 'fixture']; }
function get_post_meta(...$args) { return 511; }
function wp_verify_nonce(...$args) { global $nonce; return $nonce; }
function get_transient($key) { global $limited; return $limited; }
function set_transient(...$args) { global $events; $events[] = 'write-rate'; }
function wp_insert_post($post, $return_error) { global $events; $events[] = ['insert' => $post]; return 123; }
function is_wp_error($v) { return false; }
function wp_mail(...$args) { global $events; $events[] = 'mail'; return true; }
function home_url($path) { return 'https://fixture.example' . $path; }
function wp_safe_redirect($url) { throw new BookingDateRedirect($url); }
function esc_attr($v) { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); }
function esc_html($v) { return esc_attr($v); }
function esc_textarea($v) { return esc_attr($v); }
function esc_url($v) { return esc_attr($v); }
function admin_url($path) { return 'https://fixture.example/wp-admin/' . $path; }
function plugins_url($path, $file) { return 'https://fixture.example/plugin/' . $path; }
function wp_nonce_field($action) { echo '<input type="hidden" name="_wpnonce" value="fixture">'; }
function wp_enqueue_script($handle, $src, $deps, $version, $footer) { global $scripts; $scripts[$handle] = compact('src', 'deps', 'version', 'footer'); }
require __DIR__ . '/../wp-plugin/src/Community.php';
function submit($date, $overrides = []) {
    global $events; $events = [];
    $_SERVER = ['REQUEST_METHOD' => 'POST', 'REMOTE_ADDR' => '192.0.2.1'];
    $_POST = array_merge(['product' => '511', 'policy' => '1', 'date' => $date, 'quantity' => '2', 'phone' => '+66000000000', 'email' => '', '_wpnonce' => 'fixture', 'website' => ''], $overrides);
    try { TOP_Community::submit_booking(); return ['status' => null, 'message' => 'no redirect']; }
    catch (BookingDateDie $e) { return ['status' => $e->status, 'message' => $e->getMessage()]; }
    catch (BookingDateRedirect $e) { return ['status' => 302, 'message' => $e->getMessage()]; }
}
try {
    foreach (['2026-10-09', '1900-01-01', '2026-02-30', '2027-02-29', '2100-02-29', '2026-13-01', '2026-00-01', '2026-10-00', '0000-01-01', "2026-10-10\n", '2026-1-01', '10.10.2026', '2026-10-10T00:00:00', '', ' 2026-10-10', [], ['2026-10-10'], 20261010, null] as $i => $date) {
        $r = submit($date);
        check('invalid/past date rejected ' . $i, $r['status'] === 400);
        check('invalid/past has no save/rate write/mail ' . $i, !$events);
    }
    foreach (['2026-10-10', '2026-10-11', '2026-11-01', '2027-01-01', '2028-02-29', '2400-02-29'] as $date) {
        check('today/future accepted ' . $date, submit($date)['status'] === 302);
        check('accepted date retained ' . $date, count($events) === 3 && isset($events[1]['insert']) && str_contains($events[1]['insert']['post_content'], 'Дата выезда: ' . $date));
    }
    $clock = strtotime('2026-10-10T17:00:00Z');
    check('Bangkok midnight rejects previous day while UTC still yesterday', submit('2026-10-10')['status'] === 400 && !$events);
    check('Bangkok new day accepted', submit('2026-10-11')['status'] === 302);
    $clock = strtotime('2026-12-31T17:00:00Z');
    check('Bangkok year rollover rejects Dec 31', submit('2026-12-31')['status'] === 400 && !$events);
    check('Bangkok year rollover accepts Jan 1', submit('2027-01-01')['status'] === 302);
    $clock = strtotime('2026-10-10T17:00:00Z');
    foreach ([[], ['timezone' => 'invalid/zone'], ['timezone' => []], ['timezone' => ''], 'bad-profile'] as $i => $p) {
        $profile = $p;
        check('missing/invalid business timezone falls back to Bangkok ' . $i, submit('2026-10-10')['status'] === 400 && !$events);
    }
    $profile = ['timezone' => '+07:00'];
    check('fixed-offset business profile supported', submit('2026-10-10')['status'] === 400 && !$events);
    $profile = ['timezone' => 'Asia/Bangkok'];
    $nonce = false;
    check('existing nonce guard retained', submit('2026-10-11')['status'] === 403 && !$events); $nonce = true;
    check('existing honeypot retained', submit('2026-10-11', ['website' => 'spam'])['status'] === 400 && !$events);
    $limited = true; check('existing rate guard retained', submit('2026-10-11')['status'] === 429 && !$events); $limited = false;
    check('payment allowlist retained', submit('2026-10-11', ['payment' => 'bogus'])['status'] === 400 && !$events);
    check('optional valid payment retained', submit('2026-10-11', ['payment' => 'office'])['status'] === 302 && str_contains($events[1]['insert']['post_content'], 'Оплата в офисе'));
    foreach ([false, true] as $checkout) {
        ob_start(); TOP_Community::booking_form(get_post(511), ['quantity' => 2, 'wishes' => 'Fixture choice', 'checkout' => $checkout]); $html = ob_get_clean();
        check('date minimum on both renderers ' . (int) $checkout, str_contains($html, 'min="2026-10-11" data-thai-booking-date'));
        check('business timezone metadata on both renderers ' . (int) $checkout, str_contains($html, 'data-thai-booking-timezone="Asia/Bangkok"') && str_contains($html, 'data-thai-booking-offset="25200"'));
        check('native required date retained ' . (int) $checkout, preg_match('/<input[^>]+type="date"[^>]+required/', $html) === 1);
    }
    check('date asset enqueued once by WP handle and in footer', count($scripts) === 1 && $scripts['thai-booking-dates']['footer'] === true && $scripts['thai-booking-dates']['version'] === '1.0.0');
    echo json_encode(['tests' => count($checks), 'passed' => count($checks), 'failed' => [], 'production_mutations' => 0, 'cases' => $checks], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
} catch (Throwable $e) { fwrite(STDERR, 'FAIL: ' . $e->getMessage() . "\n"); exit(1); }
