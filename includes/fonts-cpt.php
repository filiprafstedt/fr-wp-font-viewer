<?php
if (!defined('ABSPATH')) exit;

// -------------------------------------------
// Add Font Library admin menu
// -------------------------------------------
add_action('admin_menu', function() {
    add_menu_page(
        'Font Library',           // Page title
        'Font Library',           // Menu title
        'manage_options',         // Capability
        'fr_font_library',        // Menu slug
        'fr_font_library_page',   // Callback
        'dashicons-editor-textcolor', // Icon
        25                        // Position
    );
});

// -------------------------------------------
// Font Library admin page callback
// -------------------------------------------
function fr_font_library_page() {

    // Handle font upload
    if (isset($_POST['fr_font_upload_nonce']) && wp_verify_nonce($_POST['fr_font_upload_nonce'], 'fr_font_upload')) {

        if (!empty($_FILES['fr_font_file']['tmp_name'])) {
            $file = $_FILES['fr_font_file'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

            if ($ext === 'woff2') {
                $storage = plugin_dir_path(__FILE__) . '../storage/';
                if (!file_exists($storage)) wp_mkdir_p($storage);

                // Sanitize filename
                $filename = sanitize_file_name($file['name']);
                $target = $storage . $filename;

                // Replace if exists
                if (file_exists($target)) unlink($target);

                if (move_uploaded_file($file['tmp_name'], $target)) {
                    echo '<div class="notice notice-success"><p>Font uploaded successfully!</p></div>';
                } else {
                    echo '<div class="notice notice-error"><p>Upload failed!</p></div>';
                }

            } else {
                echo '<div class="notice notice-error"><p>Only .woff2 files allowed!</p></div>';
            }
        }
    }

    // Handle delete
    if (isset($_GET['delete_font'])) {
        $file_to_delete = plugin_dir_path(__FILE__) . '../storage/' . basename($_GET['delete_font']);
        if (file_exists($file_to_delete)) {
            unlink($file_to_delete);
            echo '<div class="notice notice-success"><p>Font deleted successfully!</p></div>';
            echo '<meta http-equiv="refresh" content="0;URL=' . menu_page_url('fr_font_library', false) . '">';
            exit;
        }
    }

    // List fonts
    $fonts = glob(plugin_dir_path(__FILE__) . '../storage/*.woff2');

    // Display page
    echo '<div class="wrap">';
    echo '<h1>Font Library</h1>';

    // Upload form
    echo '<h2>Upload New Font</h2>';
    echo '<form method="post" enctype="multipart/form-data">';
    wp_nonce_field('fr_font_upload', 'fr_font_upload_nonce');
    echo '<input type="file" name="fr_font_file" accept=".woff2" required />';
    echo ' <input type="submit" class="button button-primary" value="Upload Font">';
    echo '</form>';

    // Existing fonts table
    echo '<h2>Existing Fonts</h2>';
    if (!empty($fonts)) {
        echo '<table class="widefat">';
        echo '<thead><tr><th>Font File</th><th>Actions</th></tr></thead><tbody>';
        foreach ($fonts as $font) {
            $filename = basename($font);
            $delete_url = add_query_arg(['delete_font' => $filename]);
            echo '<tr>';
            echo '<td>' . esc_html($filename) . '</td>';
            echo '<td><a href="' . esc_url($delete_url) . '" class="button button-secondary" onclick="return confirm(\'Delete this font?\')">Delete</a></td>';
            echo '</tr>';
        }
        echo '</tbody></table>';
    } else {
        echo '<p>No fonts uploaded yet.</p>';
    }

    echo '</div>';
}