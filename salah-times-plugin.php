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
    // Get saved settings
    $options = get_option('salah_plugin_settings', ['fetch_days' => [], 'cron_enabled' => false]);
    $fetch_days = $options['fetch_days'];
    $cron_enabled = $options['cron_enabled'];
    $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

    ?>
    <div class="wrap">
        <h1>Salah Times Plugin</h1>
        <form method="post" action="options.php">
            <?php settings_fields('salah_plugin_settings_group'); ?>
            <?php do_settings_sections('salah_plugin_settings_group'); ?>

            <h2>Fetch Settings</h2>
            <p>Select the days to fetch Salah times:</p>
            <?php foreach ($days as $index => $day): ?>
                <label>
                    <input type="checkbox" name="salah_plugin_settings[fetch_days][]"
                           value="<?php echo $index; ?>"
                           <?php checked(in_array($index, $fetch_days)); ?>>
                    <?php echo $day; ?>
                </label><br>
            <?php endforeach; ?>

            <h2>CRON Job</h2>
            <label>
                <input type="checkbox" name="salah_plugin_settings[cron_enabled]"
                       value="1" <?php checked($cron_enabled); ?>>
                Enable Daily CRON Job
            </label>

            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

add_action('admin_init', function () {
    register_setting('salah_plugin_settings_group', 'salah_plugin_settings', function ($input) {
        // Validate settings
        $input['fetch_days'] = array_map('intval', $input['fetch_days'] ?? []);
        $input['cron_enabled'] = !empty($input['cron_enabled']);
        return $input;
    });
});


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
