<?php
/**
 * Plugin Name: Salah Times Plugin
 * Description: Fetch and manage salah times from a Ibrahim's remote API.
 * Version: 1.0
 * Author: Abdur-Rahamn Bilal (MTWSNC)
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Define constants
define('SALAH_TIMES_API_URL', 'https://northerly-robin-8705.dataplicity.io/mtws-iqaamah-times/all');
define('SALAH_TIMES_JSON_PATH', plugin_dir_path(__FILE__) . 'salah.json');

// Register activation hook
register_activation_hook(__FILE__, function () {
    salah_times_schedule_dynamic_fetch();
});

// Register deactivation hook
register_deactivation_hook(__FILE__, function () {
    wp_clear_scheduled_hook('fetch_salah_times_event');
});

// Admin page
add_action('admin_menu', function () {
    add_menu_page('Salah Times', 'Salah Times', 'manage_options', 'salah-times', 'salah_times_admin_page');
});

// Register settings
add_action('admin_init', function () {
    register_setting('salah_times_settings', 'salah_times_fetch_days');
    register_setting('salah_times_settings', 'salah_times_custom_cron');
});

// Fetch salah times
add_action('fetch_salah_times_event', 'fetch_salah_times');
add_action('wp_ajax_fetch_salah_times', 'fetch_salah_times');

function fetch_salah_times() {
    $response = wp_remote_get(SALAH_TIMES_API_URL);

    if (is_wp_error($response)) {
        wp_send_json_error('Failed to fetch data from API.');
        return;
    }

    $data = wp_remote_retrieve_body($response);

    if (!empty($data)) {
        file_put_contents(SALAH_TIMES_JSON_PATH, $data);
        wp_send_json_success('Salah times fetched successfully!');
    } else {
        wp_send_json_error('No data received from API.');
    }
}

// Schedule fetch
function salah_times_schedule_dynamic_fetch() {
    wp_clear_scheduled_hook('fetch_salah_times_event');

    $custom_cron = get_option('salah_times_custom_cron', '');
    $fetch_days = get_option('salah_times_fetch_days', []);

    if ($custom_cron) {
        // Use custom CRON expression (requires manual handling, simplified here)
        wp_schedule_event(time(), 'hourly', 'fetch_salah_times_event');
    } elseif (!empty($fetch_days)) {
        foreach ($fetch_days as $day) {
            $timestamp = strtotime("next $day midnight");
            wp_schedule_event($timestamp, 'weekly', 'fetch_salah_times_event');
        }
    }
}

// Add Fetch Salah Times button to admin bar
add_action('admin_bar_menu', function ($wp_admin_bar) {
    if (current_user_can('manage_options')) {
        $wp_admin_bar->add_node([
            'id'    => 'fetch_salah_times',
            'title' => 'Fetch Salah Times',
            'href'  => '#',
            'meta'  => [
                'onclick' => 'fetchSalahTimes(); return false;',
            ],
        ]);
    }
}, 100);

// Enqueue the script for the admin bar button
add_action('admin_enqueue_scripts', 'enqueue_fetch_salah_times_script');
add_action('wp_enqueue_scripts', 'enqueue_fetch_salah_times_script');

function enqueue_fetch_salah_times_script() {
    if (current_user_can('manage_options')) {
        wp_enqueue_script('fetch-salah-times', plugin_dir_url(__FILE__) . 'fetch-salah-times.js', ['jquery'], null, true);
    }
}