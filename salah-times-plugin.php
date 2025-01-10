<?php
/**
 * Plugin Name: Salah Times Plugin
 * Plugin URI: https://github.com/mtwsnc/salah-times-plugin
 * Description: Fetch and manage salah times from Ibrahim's remote API
 * Version: 1.2
 * Author: Abdur-Rahman Bilal (MTWSNC)
 * Author URI: https://github.com/aramb-dev
 */

// Define the plugin directory
define('SALAH_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('SALAH_JSON_FILE', SALAH_PLUGIN_DIR . 'salah.json');

// Include necessary files
include_once(SALAH_PLUGIN_DIR . 'includes/fetch-api.php');
include_once(SALAH_PLUGIN_DIR . 'includes/compare-json.php');
include_once(SALAH_PLUGIN_DIR . 'includes/cron-handler.php');

// Add the manual update button to the admin bar
add_action('admin_bar_menu', function ($wp_admin_bar) {
    if (current_user_can('manage_options')) {
        $wp_admin_bar->add_node([
            'id'    => 'manual-update',
            'title' => 'Manual Update',
            'href'  => '#',
            'meta'  => ['class' => 'manual-update-button']
        ]);
    }
}, 100);

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

// Register settings for the admin page
add_action('admin_init', function () {
    register_setting('salah_plugin_settings_group', 'salah_plugin_settings', function ($input) {
        $input['fetch_days'] = array_map('intval', $input['fetch_days'] ?? []);
        $input['cron_enabled'] = !empty($input['cron_enabled']);
        return $input;
    });
});

// Admin settings page
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

// Add admin page to the menu
add_action('admin_menu', function () {
    add_menu_page('Salah Times Plugin', 'Salah Times', 'manage_options', 'salah_times_plugin', 'salah_admin_page');
});

// AJAX handler for manual update
add_action('wp_ajax_salah_manual_update', 'salah_manual_update');
function salah_manual_update()
{
    salah_fetch_api();
    wp_send_json_success(['message' => 'Salah times updated manually.']);
}

// Handle CRON scheduling
function salah_schedule_cron()
{
    $options = get_option('salah_plugin_settings', ['cron_enabled' => false]);
    if ($options['cron_enabled'] && !wp_next_scheduled('salah_cron_job')) {
        wp_schedule_event(time(), 'daily', 'salah_cron_job');
    }
}

function salah_unschedule_cron()
{
    $timestamp = wp_next_scheduled('salah_cron_job');
    if ($timestamp) {
        wp_unschedule_event($timestamp, 'salah_cron_job');
    }
}

add_action('update_option_salah_plugin_settings', function ($old_value, $value) {
    if ($value['cron_enabled']) {
        salah_schedule_cron();
    } else {
        salah_unschedule_cron();
    }
}, 10, 2);

// Handle CRON job
add_action('salah_cron_job', 'salah_cron_handler');
function salah_cron_handler()
{
    $options = get_option('salah_plugin_settings', ['fetch_days' => []]);
    $fetch_days = $options['fetch_days'];
    if (in_array(date('w'), $fetch_days)) {
        salah_fetch_api();
    }
}
