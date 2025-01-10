<?php
function salah_cron_handler()
{
    $options = get_option('salah_plugin_settings', ['fetch_days' => [], 'cron_enabled' => false]);
    $fetch_days = $options['fetch_days'];

    if (in_array(date('w'), $fetch_days)) {
        salah_fetch_api();
    }
}

if (get_option('salah_plugin_settings')['cron_enabled']) {
    add_action('salah_cron_job', 'salah_cron_handler');
}

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
