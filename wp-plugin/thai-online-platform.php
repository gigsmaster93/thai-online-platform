<?php
/**
 * Plugin Name: Thai Online Platform
 * Description: uCoz-compatible migration and CMS platform for Thai Online RU/EN websites.
 * Version: 1.0.1-lts
 * Author: Thai Online
 * Text Domain: thai-online-platform
 */

if (!defined('ABSPATH')) exit;

define('TOP_VERSION', '1.0.1-lts');
define('TOP_PLUGIN_FILE', __FILE__);
define('TOP_PLUGIN_DIR', plugin_dir_path(__FILE__));

require_once TOP_PLUGIN_DIR . 'src/Core.php';
require_once TOP_PLUGIN_DIR . 'src/CLI.php';

add_action('plugins_loaded', ['TOP_Core', 'boot']);
register_activation_hook(__FILE__, ['TOP_Core', 'activate']);
