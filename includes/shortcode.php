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

    $ot_features = get_post_meta($collection->ID, '_fr_collection_ot_features', true);
    if (!is_array($ot_features)) {
        $ot_features = [];
    }

    $defaults = get_post_meta($collection->ID, '_fr_collection_defaults', true);
    if (!is_array($defaults)) {
        $defaults = [
            'text' => 'Hamburgefontsiv',
            'font_size' => '128px',
            'line_height' => '1.2',
            'alignment' => 'left',
        ];
    }

    // Load collection features

    $collection_features = get_post_meta(
        $collection->ID,
        'fr_wpfv_collection_features',
        true
    );

    if (!is_array($collection_features)) {
        $collection_features = [];
    }

    $global_features = get_option('fr_wpfv_font_features', []);

    // Build preview-visible feature list

    $preview_features = [];

    foreach ($global_features as $feature) {
        $tag = $feature['tag'];

        $settings = $collection_features[$tag] ?? [
            'enabled' => true,
            'preview' => true,
        ];

        if (!$settings['preview']) {
            continue;
        }

        $preview_features[] = [
            'tag'     => $tag,
            'name'    => $feature['name'],
            'enabled' => $settings['enabled'],
        ];
    }

    // -------------------

    $ajax_url = admin_url('admin-ajax.php');

    ob_start();
    ?>
    <div class="fr-font-viewer"
         data-default-font="<?php echo esc_attr($default_font); ?>"
         data-ajax-url="<?php echo esc_url($ajax_url); ?>"
         data-features='<?php echo esc_attr(json_encode($preview_features)); ?>'>

        <div class="fr-controls">

            <!-- Font selector -->
            <div class="fr-control fr-font">
                <select class="fr-font-select">
                    <?php foreach ($fonts as $file): ?>
                        <option value="<?php echo esc_attr($file); ?>"
                            <?php selected($file, $default_font); ?>>
                            <?php echo esc_html(pathinfo($file, PATHINFO_FILENAME)); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Alignment -->
            <!--
            <div class="fr-control fr-alignment">
                <button type="button" data-align="left">L</button>
                <button type="button" data-align="center">C</button>
                <button type="button" data-align="right">R</button>
            </div>
            -->

            <div class="fr-control fr-alignment">
                <button type="button" data-align="left" aria-label="Align left">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <rect x="3" y="3" width="16" height="3"/>
                        <rect x="3" y="10" width="24" height="3"/>
                        <rect x="3" y="17" width="14" height="3"/>
                    </svg>
                </button>

                <button type="button" data-align="center" aria-label="Align center">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <rect x="5" y="5" width="14" height="2"/>
                        <rect x="3" y="10" width="18" height="2"/>
                        <rect x="5" y="15" width="14" height="2"/>
                    </svg>
                </button>

                <button type="button" data-align="right" aria-label="Align right">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <rect x="7" y="5" width="14" height="2"/>
                        <rect x="3" y="10" width="18" height="2"/>
                        <rect x="7" y="15" width="14" height="2"/>
                    </svg>
                </button>
            </div>

            <!-- Font size -->
            <div class="fr-control fr-size">
                <input type="range" class="fr-font-size" min="8" max="200"
                       value="<?php echo intval($defaults['font_size']); ?>">
            </div>

            <!-- Line height -->
            <div class="fr-control fr-lineheight">
                <input type="range" class="fr-line-height"
                       min="0.6" max="2" step="0.05"
                       value="<?php echo esc_attr($defaults['line_height']); ?>">
            </div>

            <!-- OpenType -->
            <div class="fr-control fr-ot">
                <button type="button" class="fr-ot-toggle">OpenType</button>
                <div class="fr-ot-panel" hidden></div>
            </div>

        </div>

        <p class="fr-font-stage"
           contenteditable="true"
           spellcheck="false"
           style="
               font-size:<?php echo esc_attr($defaults['font_size']); ?>;
               line-height:<?php echo esc_attr($defaults['line_height']); ?>;
               text-align:<?php echo esc_attr($defaults['alignment']); ?>;
               font-feature-settings: <?php echo esc_attr(fr_ot_features_to_css($ot_features)); ?>;
           ">
            <?php echo esc_html($defaults['text']); ?>
        </p>
    </div>
    <?php

    return ob_get_clean();

}

function fr_ot_features_to_css($features) {
    if (empty($features)) return 'normal';
    return implode(', ', array_map(fn($f) => "\"$f\" 1", $features));
}