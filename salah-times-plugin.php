<?php
/*
Plugin Name: Salah Times Plugin
Plugin URI: https://github.com/mtwsnc/salah-times-plugin
Description: Fetch and manage salah times from Ibrahim's remote API
Version: 1.2
Author: Abdur-Rahman Bilal (MTWSNC)
Author URI: https://github.com/aramb-dev
*/

defined('ABSPATH') || exit;

// Define constants
define('SALAH_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('SALAH_JSON_FILE', SALAH_PLUGIN_DIR . 'salah.json');

// Include required files
require_once SALAH_PLUGIN_DIR . 'includes/fetch-api.php';
require_once SALAH_PLUGIN_DIR . 'includes/compare-json.php';
require_once SALAH_PLUGIN_DIR . 'includes/cron-handler.php';

// Register menu page
add_action('admin_menu', function () {
    add_menu_page(
        'Salah Times',
        'Salah Times',
        'manage_options',
        'salah-times',
        'salah_admin_page',
        'dashicons-clock',
        100
    );
});

// Admin page callback
function salah_admin_page()
{
    ?>
    <div class="wrap">
        <h1>Salah Times Plugin</h1>
        <p>Manage and update Salah times from the remote API.</p>
        <button id="manual-update" class="button button-primary">Manual Update</button>
        <pre id="update-result"></pre>
        <p><strong>Local salah.json File URLs:</strong></p>
        <ul>
            <li>Absolute URL: <?php echo plugin_dir_path(__FILE__) . 'salah.json'; ?></li>
            <li>Relative URL: <?php echo plugins_url('salah.json', __FILE__); ?></li>
        </ul>
    </div>
    <?php
}

// Enqueue admin scripts
add_action('admin_enqueue_scripts', function () {
    wp_enqueue_script(
        'salah-admin-js',
        plugins_url('assets/js/admin.js', __FILE__),
        ['jquery'],
        null,
        true
    );
    wp_localize_script('salah-admin-js', 'salahAjax', ['ajaxUrl' => admin_url('admin-ajax.php')]);
});

// AJAX handler for manual update
add_action('wp_ajax_salah_manual_update', function () {
    $result = salah_fetch_api();
    echo json_encode($result);
    wp_die();
});

// CRON schedule
register_activation_hook(__FILE__, 'salah_schedule_cron');
register_deactivation_hook(__FILE__, 'salah_unschedule_cron');
