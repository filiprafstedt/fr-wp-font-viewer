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

        <p>
            Global Foundry configuration will live here.
        </p>
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