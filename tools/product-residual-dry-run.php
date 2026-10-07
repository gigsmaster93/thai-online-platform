<?php
/** Read-only review of products 158/170/491. No apply or rollback mode is provided. */
if (!defined('ABSPATH') || !defined('WP_CLI') || !WP_CLI) { fwrite(STDERR, "Use wp eval-file; this tool only reads the database.\n"); exit(2); }
function prd_need($ok, $message) { if (!$ok) throw new RuntimeException($message); }
function prd_hash($value) { return hash('sha256', json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)); }
function prd_artifact($item) {
    $path = realpath($item['path'] ?? '');
    prd_need($path && is_file($path) && !str_starts_with($path, realpath(ABSPATH) . '/'), 'Artifact must be outside webroot');
    prd_need(hash_file('sha256', $path) === ($item['sha256'] ?? ''), 'Artifact hash changed');
    return file_get_contents($path);
}
function prd_accounts() {
    global $wpdb;
    return prd_hash([$wpdb->get_results("SELECT * FROM {$wpdb->users} ORDER BY ID", ARRAY_A), $wpdb->get_results("SELECT * FROM {$wpdb->usermeta} ORDER BY umeta_id", ARRAY_A), $wpdb->get_results("SELECT * FROM {$wpdb->options} WHERE option_name IN ('users_can_register','default_role') ORDER BY option_name", ARRAY_A)]);
}
function prd_state($id) {
    global $wpdb;
    return [$wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->posts} WHERE ID=%d", $id), ARRAY_A), prd_hash($wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->postmeta} WHERE post_id=%d ORDER BY meta_id", $id), ARRAY_A))];
}
function prd_description($html) {
    libxml_use_internal_errors(true); $doc = new DOMDocument(); $doc->loadHTML('<?xml encoding="UTF-8">' . $html); $xpath = new DOMXPath($doc);
    foreach (iterator_to_array($xpath->query('//script|//style')) as $node) $node->parentNode->removeChild($node);
    $nodes = $xpath->query('//*[@id="dscr"]'); prd_need($nodes->length === 1, 'Expected exactly one description');
    return trim(preg_replace('/\s+/u', ' ', str_replace("\xc2\xa0", ' ', $nodes->item(0)->textContent)));
}
function prd_outside_description($html) {
    preg_match_all('~</?div\b[^>]*>~i', $html, $tags, PREG_OFFSET_CAPTURE); $depth = null; $start = null;
    foreach ($tags[0] as [$tag, $offset]) {
        if ($depth === null) { if (preg_match('~\bid=["\']dscr["\']~', $tag)) { $depth = 1; $start = $offset + strlen($tag); } continue; }
        $depth += str_starts_with($tag, '</') ? -1 : 1;
        if ($depth === 0) return [substr($html, 0, $start), substr($html, $offset)];
    }
    throw new RuntimeException('Cannot isolate description');
}
function prd_review($manifest) {
    prd_need(($manifest['schema'] ?? null) === 1 && ($manifest['scope'] ?? '') === 'residual_product_data_review', 'Wrong manifest scope');
    prd_need(($manifest['apply_allowed'] ?? null) === false, 'This manifest must remain review-only');
    $age = time() - strtotime($manifest['created_at'] ?? ''); prd_need($age >= 0 && $age <= 86400, 'Manifest expired');
    prd_need(get_option('siteurl') === $manifest['site_url'], 'Wrong WordPress site');
    prd_need((string)get_option('users_can_register') === '0' && get_option('default_role') === 'subscriber', 'Registration/default role drift');
    prd_need(prd_accounts() === $manifest['accounts_sha256'], 'Accounts changed');
    prd_need(trim(file_get_contents('/home/thaionline/autodeploy/last-deployed-commit')) === $manifest['production_commit'], 'Deploy marker drift');
    prd_need(trim((string)shell_exec('git -C /home/thaionline/autodeploy/thai-online-platform rev-parse HEAD 2>/dev/null')) === $manifest['production_commit'], 'Develop drift');
    prd_need(trim((string)shell_exec('git -C /home/thaionline/autodeploy/thai-online-platform status --porcelain --untracked-files=no')) === '', 'Production checkout dirty');
    foreach ($manifest['production_files'] as $path => $sha) prd_need(is_file($path) && hash_file('sha256', $path) === $sha, 'Live code drift');
    $catalog = json_decode(prd_artifact($manifest['old_catalog']), true, 512, JSON_THROW_ON_ERROR);
    $catalog_age = time() - strtotime($catalog['checked_at'] ?? ''); prd_need($catalog_age >= 0 && $catalog_age <= 86400 && empty($catalog['errors']), 'Catalog evidence stale/incomplete');
    prd_need(count($catalog['old_catalog_pages']) === 10 && $catalog['old_discovered_pages'] === range(2, 10), 'Catalog coverage changed');
    foreach ($catalog['old_catalog_pages'] as $page) prd_need($page['host'] === 'thai-online.org' && $page['status'] === 200, 'Invalid catalog source');
    $allowed = [158 => [670, 'post_content'], 170 => [674, 'post_status'], 491 => [996, 'post_status']]; $seen = []; $delta = [];
    foreach ($manifest['items'] as $item) {
        $legacy = $item['legacy_id']; prd_need(isset($allowed[$legacy]) && !isset($seen[$legacy]), 'Unreviewed/duplicate product'); $seen[$legacy] = true;
        [$id, $field] = $allowed[$legacy]; prd_need($id === $item['wp_id'] && $field === $item['field'], 'Unexpected identity/field');
        [$row, $meta] = prd_state($id);
        prd_need($row && $row['post_type'] === 'thai_excursion' && $row['post_status'] === 'publish' && (int)get_post_meta($id, '_ucoz_shop_id', true) === $legacy, 'Live product identity/status drift');
        prd_need(prd_hash($row) === $item['row_sha256'] && $meta === $item['meta_sha256'], 'Live row/metadata drift');
        prd_need($item['source']['url'] === 'https://thai-online.org/shop/' . $legacy . '/desc/' . $row['post_name'], 'Wrong authoritative URL');
        $source_age = time() - strtotime($item['source']['fetched_at']); prd_need($source_age >= 0 && $source_age <= 86400, 'Source expired');
        $source = prd_artifact($item['source']);
        if ($field === 'post_content') {
            $before = prd_artifact($item['before']); $after = prd_artifact($item['after']);
            prd_need($before === $row['post_content'] && $before !== $after, 'Unexpected content precondition');
            prd_need(!preg_match('~<script\b|\bon[a-z]+\s*=|javascript:~i', $after), 'Executable proposed content');
            prd_need(prd_outside_description($before) === prd_outside_description($after), 'Change outside description');
            prd_need(prd_description($source) === prd_description($after), 'Proposed description differs from source');
        } else {
            $before = $item['before_value']; $after = $item['after_value'];
            prd_need($before === 'publish' && $after === 'draft' && $row[$field] === $before, 'Only reviewed deactivation is allowed');
            prd_need(str_contains($source, 'Это направление сейчас недоступно!') && str_contains($source, 'id="u_social"'), 'Unavailable-source evidence missing');
            prd_need(!isset($catalog['old_product_paths'][$legacy]), 'Source catalog still lists product');
        }
        $projected = $row; $projected[$field] = $after;
        prd_need(prd_hash($projected) === $item['projected_row_sha256'], 'Projected row/rollback guard mismatch');
        $delta[] = ['legacy_id' => $legacy, 'wp_id' => $id, 'field' => $field, 'before_bytes' => strlen($before), 'after_bytes' => strlen($after)];
    }
    prd_need(count($seen) === 3, 'Expected all three reviewed records');
    return ['mode' => 'DRY_RUN', 'production_mutations' => 0, 'apply_supported' => false, 'delta' => $delta, 'prices_metadata_accounts_preserved' => true, 'registration' => 0];
}
if (defined('THAI_PRODUCT_RESIDUAL_LIBRARY_ONLY')) return;
try {
    prd_need(($args[1] ?? 'dry-run') === 'dry-run', 'Read-only reviewer: apply/rollback are not supported or authorized');
    $raw = file_get_contents($args[0] ?? ''); $result = prd_review(json_decode($raw, true, 512, JSON_THROW_ON_ERROR));
    $result['manifest_sha256'] = hash('sha256', $raw); echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), "\n";
} catch (Throwable $error) { WP_CLI::error($error->getMessage()); }
