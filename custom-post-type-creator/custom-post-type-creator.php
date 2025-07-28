<?php
/**
 * Plugin Name: Custom Post Type Creator
 * Description: Adds an admin UI to register custom post types.
 * Version: 1.0
 * Author: Cryptoball cryptoball7@gmail.com
 */

if (!defined('ABSPATH')) exit;

// Register saved CPTs on init
add_action('init', function() {
    $custom_post_types = get_option('cptc_custom_post_types', []);
    foreach ($custom_post_types as $cpt) {
        register_post_type($cpt['post_type'], [
            'label' => $cpt['label'],
            'public' => true,
            'show_in_menu' => true,
            'supports' => ['title', 'editor', 'thumbnail'],
            'has_archive' => true,
        ]);
    }
});

// Add admin menu
add_action('admin_menu', function() {
    add_management_page(
        'Custom Post Type Creator',
        'CPT Creator',
        'manage_options',
        'cptc_creator',
        'cptc_creator_page'
    );
});

// Render admin page
function cptc_creator_page() {
    if (!current_user_can('manage_options')) return;

    if (isset($_POST['cptc_submit'])) {
        check_admin_referer('cptc_create_nonce');

        $post_type = sanitize_key($_POST['post_type']);
        $label = sanitize_text_field($_POST['label']);

        if ($post_type && $label) {
            $cpts = get_option('cptc_custom_post_types', []);
            $cpts[$post_type] = [
                'post_type' => $post_type,
                'label' => $label,
            ];
            update_option('cptc_custom_post_types', $cpts);
            echo '<div class="notice notice-success"><p>Custom post type registered!</p></div>';
        } else {
            echo '<div class="notice notice-error"><p>Both fields are required.</p></div>';
        }
    }

    $registered = get_option('cptc_custom_post_types', []);

    ?>
    <div class="wrap">
        <h1>Custom Post Type Creator</h1>
        <form method="post">
            <?php wp_nonce_field('cptc_create_nonce'); ?>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="post_type">Post Type Slug</label></th>
                    <td><input name="post_type" type="text" id="post_type" class="regular-text" required></td>
                </tr>
                <tr>
                    <th scope="row"><label for="label">Label</label></th>
                    <td><input name="label" type="text" id="label" class="regular-text" required></td>
                </tr>
            </table>
            <?php submit_button('Register Custom Post Type', 'primary', 'cptc_submit'); ?>
        </form>

        <?php if (!empty($registered)) : ?>
            <h2>Registered Post Types</h2>
            <ul>
                <?php foreach ($registered as $cpt): ?>
                    <li><strong><?php echo esc_html($cpt['post_type']); ?></strong>: <?php echo esc_html($cpt['label']); ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
    <?php
}
