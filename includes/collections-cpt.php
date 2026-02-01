
<?php
if (!defined('ABSPATH')) exit;

add_action('init', function () {
    register_post_type('wpfv_collection', [
        'labels' => [
            'name' => 'Font Collections',
            'singular_name' => 'Font Collection',
            'add_new' => 'New Font Collection',
            'add_new_item' => 'New Font Collection',
            'edit_item' => 'Edit Font Collection',
        ],
        'public' => false,
        'show_ui' => true,
        'menu_icon' => 'dashicons-portfolio',
        'supports' => ['title'],
    ]);
});

add_action('add_meta_boxes', function () {
    add_meta_box(
        'fr_collection_fonts',
        'Fonts in this Collection',
        'fr_collection_fonts_box', // ✅ correct function name
        'wpfv_collection',         // ✅ correct CPT slug
        'normal',
        'default'
    );
});

add_action('add_meta_boxes', function () {
    add_meta_box(
        'fr_collection_shortcode',
        'Shortcode',
        'fr_collection_shortcode_box',
        'wpfv_collection',
        'side',
        'high'
    );
});

add_action('add_meta_boxes', function () {
    add_meta_box(
        'fr_collection_defaults',
        'Viewer Defaults',
        'fr_collection_defaults_box',
        'wpfv_collection',
        'normal',
        'default'
    );
});

function fr_collection_defaults_box($post) {

    wp_nonce_field('fr_collection_defaults', 'fr_collection_defaults_nonce');

    $defaults = get_post_meta($post->ID, '_fr_collection_defaults', true);
    if (!is_array($defaults)) {
        $defaults = [
            'text' => 'The quick brown fox jumps over the lazy dog & €1.234.567,89',
            'font_size' => '48px',
            'line_height' => '1.1',
            'alignment' => 'left',
        ];
    }

    ?>
    <p>
        <label><strong>Default Text</strong></label><br>
        <textarea name="fr_defaults[text]" rows="3" style="width:100%;"><?php echo esc_textarea($defaults['text']); ?></textarea>
    </p>

    <p>
        <label><strong>Font Size</strong></label><br>
        <input type="text" name="fr_defaults[font_size]" value="<?php echo esc_attr($defaults['font_size']); ?>">
    </p>

    <p>
        <label><strong>Line Height</strong></label><br>
        <input type="text" name="fr_defaults[line_height]" value="<?php echo esc_attr($defaults['line_height']); ?>">
    </p>

    <p>
        <label><strong>Alignment</strong></label><br>
        <select name="fr_defaults[alignment]">
            <?php foreach (['left','center','right'] as $align): ?>
                <option value="<?php echo $align; ?>" <?php selected($defaults['alignment'], $align); ?>>
                    <?php echo ucfirst($align); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </p>
    <?php
}

function fr_collection_shortcode_box($post) {
    if ($post->post_status !== 'publish') {
        echo '<p><em>Publish this collection to get the shortcode.</em></p>';
        return;
    }

    $slug = $post->post_name;

    echo '<p>Use this shortcode:</p>';
    echo '<input type="text" readonly style="width:100%;font-family:monospace;" value="[fr-font-viewer collection=&quot;' . esc_attr($slug) . '&quot;]">';
}

function wpfv_collection_slug_box($post) {
    echo '<code>' . esc_html($post->post_name) . '</code>';
}

function fr_collection_fonts_box($post) {

    wp_nonce_field('fr_collection_fonts', 'fr_collection_fonts_nonce');

    $storage = plugin_dir_path(__FILE__) . '../storage/';
    $fonts = glob($storage . '*.woff2');

    $selected = get_post_meta($post->ID, '_fr_collection_fonts', true);
    if (!is_array($selected)) $selected = [];

    $default = get_post_meta($post->ID, '_fr_collection_default_font', true);

    if (empty($fonts)) {
        echo '<p><em>No fonts uploaded yet.</em></p>';
        return;
    }

    echo '<table style="width:100%;border-spacing:0 6px;">';

    foreach ($fonts as $font) {
        $filename = basename($font);
        $checked  = in_array($filename, $selected, true);
        $is_default = ($filename === $default);

        echo '<tr>';
        echo '<td style="width:24px;">';
        echo '<input type="checkbox" name="fr_collection_fonts[]" value="' . esc_attr($filename) . '" ' . checked($checked, true, false) . '>';
        echo '</td>';

        echo '<td style="width:24px;">';
        echo '<input type="radio" name="fr_collection_default_font" value="' . esc_attr($filename) . '" ' . checked($is_default, true, false) . '>';
        echo '</td>';

        echo '<td>';
        echo esc_html($filename);
        echo '</td>';
        echo '</tr>';
    }

    echo '</table>';

    echo '<p style="margin-top:8px;"><em>Radio button = default font</em></p>';
}

add_action('save_post_wpfv_collection', function ($post_id) {

    // 1. Autosave / revision guard
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (wp_is_post_revision($post_id)) return;

    // 2. Capability check
    if (!current_user_can('edit_post', $post_id)) return;

    // 3. Nonce check
    if (
        !isset($_POST['fr_collection_fonts_nonce']) ||
        !wp_verify_nonce($_POST['fr_collection_fonts_nonce'], 'fr_collection_fonts')
    ) {
        return;
    }

    // 4. Save fonts
    $fonts = isset($_POST['fr_collection_fonts'])
        ? array_map('sanitize_text_field', $_POST['fr_collection_fonts'])
        : [];

    update_post_meta($post_id, '_fr_collection_fonts', $fonts);
});

add_action('save_post_wpfv_collection', function ($post_id) {

    if (
        !isset($_POST['fr_collection_defaults_nonce']) ||
        !wp_verify_nonce($_POST['fr_collection_defaults_nonce'], 'fr_collection_defaults')
    ) return;

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (wp_is_post_revision($post_id)) return;
    if (!current_user_can('edit_post', $post_id)) return;

    if (isset($_POST['fr_defaults']) && is_array($_POST['fr_defaults'])) {
        $clean = [];
        foreach ($_POST['fr_defaults'] as $key => $value) {
            $clean[$key] = sanitize_text_field($value);
        }
        update_post_meta($post_id, '_fr_collection_defaults', $clean);
    }

    // Save default font
    if (isset($_POST['fr_collection_default_font'])) {
        $default = sanitize_text_field($_POST['fr_collection_default_font']);

        // Only allow default if it's in the selected fonts
        if (
            isset($_POST['fr_collection_fonts']) &&
            in_array($default, $_POST['fr_collection_fonts'], true)
        ) {
            update_post_meta($post_id, '_fr_collection_default_font', $default);
        }
    } else {
        delete_post_meta($post_id, '_fr_collection_default_font');
    }
});