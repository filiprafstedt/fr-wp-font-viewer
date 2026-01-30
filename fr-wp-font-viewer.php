
<?php
/**
 * Plugin Name: FR—WP Font Viewer
 * Description: Secure WOFF2 font viewer with Font Collections.
 * Version: 0.2
 * Author: Filip Rafstedt
 */

if (!defined('ABSPATH')) exit;

define('WPFV_PATH', plugin_dir_path(__FILE__));
define('WPFV_URL', plugin_dir_url(__FILE__));

require_once WPFV_PATH . 'includes/fonts-cpt.php';
require_once WPFV_PATH . 'includes/collections-cpt.php';
require_once WPFV_PATH . 'includes/shortcode.php';

