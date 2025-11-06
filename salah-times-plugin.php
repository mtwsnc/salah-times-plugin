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
include_once(SALAH_PLUGIN_DIR . 'includes/api-service.php');
include_once(SALAH_PLUGIN_DIR . 'includes/fetch-api.php');
include_once(SALAH_PLUGIN_DIR . 'includes/compare-json.php');
include_once(SALAH_PLUGIN_DIR . 'includes/cron-handler.php');
include_once(SALAH_PLUGIN_DIR . 'includes/prayer-times-display.php');

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

// Enqueue frontend styles and scripts
add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style(
        'salah-times-css',
        plugins_url('assets/css/salah-times.css', __FILE__),
        [],
        '1.2'
    );
    wp_enqueue_script(
        'salah-countdown-js',
        plugins_url('assets/js/salah-countdown.js', __FILE__),
        [],
        '1.2',
        true
    );
});

// Register settings for the admin page
add_action('admin_init', function () {
    register_setting('salah_plugin_settings_group', 'salah_plugin_settings', function ($input) {
        $input['fetch_days'] = array_map('intval', $input['fetch_days'] ?? []);
        $input['cron_enabled'] = !empty($input['cron_enabled']);
        $input['api_base_url'] = esc_url_raw($input['api_base_url'] ?? '');
        $input['location_name'] = sanitize_text_field($input['location_name'] ?? '');
        return $input;
    });
});

// Admin settings page
function salah_admin_page()
{
    // Get saved settings
    $options = get_option('salah_plugin_settings', [
        'fetch_days' => [],
        'cron_enabled' => false,
        'api_base_url' => '',
        'location_name' => ''
    ]);
    $fetch_days = $options['fetch_days'];
    $cron_enabled = $options['cron_enabled'];
    $api_base_url = $options['api_base_url'];
    $location_name = $options['location_name'];
    $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

    ?>
    <div class="wrap">
        <h1>Salah Times Plugin</h1>
        <form method="post" action="options.php">
            <?php settings_fields('salah_plugin_settings_group'); ?>
            <?php do_settings_sections('salah_plugin_settings_group'); ?>

            <h2>API Configuration</h2>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="api_base_url">API Base URL</label></th>
                    <td>
                        <input type="url" id="api_base_url" name="salah_plugin_settings[api_base_url]"
                               value="<?php echo esc_attr($api_base_url); ?>" class="regular-text" required>
                        <p class="description">Enter the base URL for the prayer times API (e.g., https://example.com)</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="location_name">Location Name</label></th>
                    <td>
                        <input type="text" id="location_name" name="salah_plugin_settings[location_name]"
                               value="<?php echo esc_attr($location_name); ?>" class="regular-text">
                        <p class="description">Display name for your location (e.g., Durham, NYC)</p>
                    </td>
                </tr>
            </table>

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

        <hr>

        <h2>Tools</h2>
        <p>
            <button type="button" class="button" id="test-connection">Test API Connection</button>
            <button type="button" class="button" id="clear-cache">Clear API Cache</button>
        </p>
        <div id="tools-message" style="margin-top: 10px;"></div>

        <hr>

        <h2>Usage Instructions</h2>
        <p>To display prayer times on your site, use the shortcode:</p>
        <code style="background: #f5f5f5; padding: 5px 10px; display: inline-block;">[salah_times]</code>
        <p>Add this shortcode to any page or post to display the prayer times table.</p>
    </div>

    <script>
    jQuery(document).ready(function($) {
        $('#test-connection').on('click', function() {
            var $btn = $(this);
            var $msg = $('#tools-message');
            $btn.prop('disabled', true);
            $msg.html('<p>Testing connection...</p>');

            $.post(salahAjax.ajaxUrl, { action: 'salah_test_connection' }, function(response) {
                $btn.prop('disabled', false);
                if (response.success) {
                    $msg.html('<p style="color: green;">✓ ' + response.data.message + '</p>');
                } else {
                    $msg.html('<p style="color: red;">✗ ' + response.data.message + '</p>');
                }
            });
        });

        $('#clear-cache').on('click', function() {
            var $btn = $(this);
            var $msg = $('#tools-message');
            $btn.prop('disabled', true);
            $msg.html('<p>Clearing cache...</p>');

            $.post(salahAjax.ajaxUrl, { action: 'salah_clear_cache' }, function(response) {
                $btn.prop('disabled', false);
                $msg.html('<p style="color: green;">✓ ' + response.data.message + '</p>');
            });
        });
    });
    </script>
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
    $result = salah_fetch_api();
    if (isset($result['error'])) {
        wp_send_json_error(['message' => $result['error']]);
    } else {
        wp_send_json_success(['message' => $result['success']]);
    }
}

// AJAX handler for clearing cache
add_action('wp_ajax_salah_clear_cache', 'salah_clear_cache');
function salah_clear_cache()
{
    $api_service = new Salah_API_Service();
    $api_service->clear_cache();
    wp_send_json_success(['message' => 'API cache cleared successfully.']);
}

// AJAX handler for testing API connection
add_action('wp_ajax_salah_test_connection', 'salah_test_connection');
function salah_test_connection()
{
    $api_service = new Salah_API_Service();
    $success = $api_service->test_connection();
    if ($success) {
        wp_send_json_success(['message' => 'API connection successful!']);
    } else {
        wp_send_json_error(['message' => 'Failed to connect to API. Please check your settings.']);
    }
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
