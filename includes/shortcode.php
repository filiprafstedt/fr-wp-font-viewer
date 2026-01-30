<?php
if (!defined('ABSPATH')) exit;

/**
 * Shortcodes: [wp_font_viewer] and [fr-font-viewer]
 */
add_shortcode('wp_font_viewer', 'wpfv_render_viewer');
add_shortcode('fr-font-viewer', 'wpfv_render_viewer');

function wpfv_render_viewer($atts) {

    $atts = shortcode_atts([
        'collection' => '',
    ], $atts);

    if (!$atts['collection']) return '';

    $collection = get_page_by_path(
        sanitize_title($atts['collection']),
        OBJECT,
        'wpfv_collection'
    );

    if (!$collection) return '';

    $fonts = get_post_meta($collection->ID, '_fr_collection_fonts', true);
    if (!is_array($fonts) || empty($fonts)) {
        return '<p>No fonts in this collection.</p>';
    }

    $default_font = get_post_meta($collection->ID, '_fr_collection_default_font', true);
    if (!$default_font || !in_array($default_font, $fonts, true)) {
        $default_font = $fonts[0];
    }

    $defaults = get_post_meta($collection->ID, '_fr_collection_defaults', true);
    if (!is_array($defaults)) {
        $defaults = [
            'text' => 'Hamburgefontsiv',
            'font_size' => '48px',
            'line_height' => '1.1',
            'alignment' => 'left',
        ];
    }

    $ajax_url = admin_url('admin-ajax.php');

    ob_start();
    ?>

    <div class="fr-font-viewer"
         data-fonts='<?php echo esc_attr(json_encode(array_values($fonts))); ?>'
         data-default-font="<?php echo esc_attr($default_font); ?>"
         data-ajax-url="<?php echo esc_url($ajax_url); ?>">

        <div class="fr-controls">
            <select class="fr-font-select">
                <?php foreach ($fonts as $file): ?>
                    <option value="<?php echo esc_attr($file); ?>"
                        <?php selected($file, $default_font); ?>>
                        <?php echo esc_html(
                            trim(str_replace(['-', '.woff2'], [' ', ''], $file))
                        ); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <p class="fr-font-stage"
           contenteditable="true"
           spellcheck="false"
           style="
               font-size:<?php echo esc_attr($defaults['font_size']); ?>;
               line-height:<?php echo esc_attr($defaults['line_height']); ?>;
               text-align:<?php echo esc_attr($defaults['alignment']); ?>;
           ">
            <?php echo esc_html($defaults['text']); ?>
        </p>

    </div>

    <?php
    return ob_get_clean();
}

/**
 * Serve WOFF2 fonts securely
 */
add_action('wp_ajax_wpfv_font', 'wpfv_serve_font');
add_action('wp_ajax_nopriv_wpfv_font', 'wpfv_serve_font');

function wpfv_serve_font() {

    if (ob_get_length()) {
        ob_end_clean();
    }

    $file = sanitize_file_name($_GET['file'] ?? '');
    if (!$file) {
        status_header(400);
        exit;
    }

    $plugin_root = dirname(__DIR__);
    $path = $plugin_root . '/storage/' . $file;

    if (!file_exists($path)) {
        status_header(404);
        exit;
    }

    header('Content-Type: font/woff2');
    header('Content-Length: ' . filesize($path));
    header('Cache-Control: public, max-age=31536000');
    header('Access-Control-Allow-Origin: *');
    header('X-Content-Type-Options: nosniff');

    readfile($path);
    exit;
}

add_action('wp_footer', function () {
?>
<script>
(function () {

    function loadFont(file, ajaxUrl) {
        if (document.querySelector('style[data-font="' + file + '"]')) return;

        const family = 'fr-font-' + file.replace(/[^a-z0-9]/gi, '');
        const style = document.createElement('style');
        style.setAttribute('data-font', file);
        style.textContent =
            "@font-face{" +
            "font-family:'" + family + "';" +
            "src:url('" + ajaxUrl + "?action=wpfv_font&file=" + encodeURIComponent(file) + "') format('woff2');" +
            "font-display:swap;" +
            "}";

        document.head.appendChild(style);
    }

    document.querySelectorAll('.fr-font-viewer').forEach(function (viewer) {

        const stage   = viewer.querySelector('.fr-font-stage');
        const select  = viewer.querySelector('.fr-font-select');
        const ajaxUrl = viewer.dataset.ajaxUrl;
        const defaultFont = viewer.dataset.defaultFont;

        function applyFont(file) {
            loadFont(file, ajaxUrl);
            const family = 'fr-font-' + file.replace(/[^a-z0-9]/gi, '');
            stage.style.fontFamily = family;
        }

        // Initial font
        applyFont(defaultFont);

        // Change font
        select.addEventListener('change', function () {
            applyFont(this.value);
        });
    });

})();
</script>
<?php
});