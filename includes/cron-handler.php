<?php
function salah_schedule_cron()
{
    if (!wp_next_scheduled('salah_cron_job')) {
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

add_action('salah_cron_job', 'salah_fetch_api');
