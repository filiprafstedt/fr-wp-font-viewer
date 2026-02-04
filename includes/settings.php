<?php
if (!defined('ABSPATH')) exit;

// --------------------------------------------------
// Foundry → Settings submenu
// --------------------------------------------------

add_action('admin_menu', function () {

    add_submenu_page(
        'fr_font_library',        // Parent: Foundry
        'Foundry Settings',
        'Settings',
        'manage_options',
        'fr-foundry-settings',
        'fr_render_foundry_settings'
    );

}, 99);

// --------------------------------------------------
// Settings page render
// --------------------------------------------------

function fr_render_foundry_settings() {
    ?>
    <div class="wrap">
        <h1>Foundry Settings</h1>

        <div id="fr-font-features-box" class="postbox">
            <h2 class="hndle">Font Features</h2>
            <div class="inside">
                <table class="widefat striped" id="fr-font-features-table">
                    <thead>
                        <tr>
                            <th style="width:80px;">Tag</th>
                            <th>Name</th>
                            <th style="width:80px;">Edit</th>
                            <th style="width:80px;">Delete</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $features = fr_wpfv_get_font_features();
                        foreach ($features as $index => $feature):
                        ?>
                            <tr data-index="<?php echo esc_attr($index); ?>">
                                <td><input type="text" value="<?php echo esc_attr($feature['tag']); ?>" class="fr-feature-tag" /></td>
                                <td><input type="text" value="<?php echo esc_attr($feature['name']); ?>" class="fr-feature-name" /></td>
                                <td><button class="button fr-edit-feature">Edit</button></td>
                                <td><button class="button fr-delete-feature">Delete</button></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <p>
                    <button class="button button-primary" id="fr-add-feature">
                        Add Feature
                    </button>
                </p>
            </div>
        </div>
    </div>
    <?php
}

// --------------------------------------------------
// Font Features: defaults
// --------------------------------------------------

function fr_wpfv_default_font_features() {
    return [
        ['tag' => 'kern', 'name' => 'Kerning'],
        ['tag' => 'calt', 'name' => 'Contextual Alternates'],
        ['tag' => 'clig', 'name' => 'Contextual Ligatures'],
        ['tag' => 'salt', 'name' => 'Stylistic Alternates'],
        ['tag' => 'liga', 'name' => 'Standard Ligatures'],
        ['tag' => 'dlig', 'name' => 'Discretionary Ligatures'],
        ['tag' => 'swsh', 'name' => 'Swashes'],
        ['tag' => 'cswh', 'name' => 'Contextual Swashes'],
        ['tag' => 'smcp', 'name' => 'Small Caps'],
        ['tag' => 'case', 'name' => 'Case Sensitive Forms'],
        ['tag' => 'onum', 'name' => 'Oldstyle Figures'],
        ['tag' => 'lnum', 'name' => 'Lining Figures'],
        ['tag' => 'pnum', 'name' => 'Proportional Figures'],
        ['tag' => 'tnum', 'name' => 'Tabular Figures'],
    ];
}

// --------------------------------------------------
// Initialize Font Features option
// --------------------------------------------------

add_action('admin_init', function () {

    if (get_option('fr_wpfv_font_features') === false) {
        add_option(
            'fr_wpfv_font_features',
            fr_wpfv_default_font_features()
        );
    }

});

// --------------------------------------------------
// Helper getter
// --------------------------------------------------

function fr_wpfv_get_font_features() {
    return get_option('fr_wpfv_font_features', []);
}

add_action('admin_enqueue_scripts', function ($hook) {
    // Only load on our Settings page
    if ($hook !== 'foundry_page_fr-foundry-settings') return;

    // jQuery UI Sortable (WP includes it)
    wp_enqueue_script('jquery-ui-sortable');

    // Our custom JS (create this next)
    wp_enqueue_script(
        'fr-font-features',
        plugins_url('../assets/js/font-features.js', __FILE__),
        ['jquery', 'jquery-ui-sortable'],
        '1.0',
        true
    );

    // Optional: CSS tweaks for the table
    wp_enqueue_style(
        'fr-font-features-css',
        plugins_url('../assets/css/font-viewer-ui.css', __FILE__),
        [],
        '1.0'
    );
});