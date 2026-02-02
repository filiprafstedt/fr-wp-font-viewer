
<?php
/**
 * Plugin Name: FR—WP Font Viewer
 * Description: Secure WOFF2 font viewer with Font Collections.
 * Version: 0.2
 * Author: Filip Rafstedt
 */

define('FR_WPFV_VERSION', '0.1.0');

if (!defined('ABSPATH')) exit;

define('WPFV_PATH', plugin_dir_path(__FILE__));
define('WPFV_URL', plugin_dir_url(__FILE__));

require_once WPFV_PATH . 'includes/fonts-cpt.php';
require_once WPFV_PATH . 'includes/collections-cpt.php';
require_once WPFV_PATH . 'includes/shortcode.php';

add_action('wp_enqueue_scripts', function () {

    wp_enqueue_script(
        'fr-font-viewer-ui',
        plugins_url('assets/js/font-viewer-ui.js', __FILE__),
        [],
        '1.0',
        true
    );

    wp_enqueue_style(
        'fr-font-viewer-ui',
        plugins_url('assets/css/font-viewer-ui.css', __FILE__),
        [],
        '1.0'
    );

});