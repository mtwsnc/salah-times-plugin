<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class SalahTimesCron {
    public function __construct() {
        add_action('salah_times_cron', [$this, 'update_salah_times']);
        register_activation_hook(__FILE__, [$this, 'schedule_cron']);
        register_deactivation_hook(__FILE__, [$this, 'unschedule_cron']);
    }

    public function schedule_cron() {
        if (!wp_next_scheduled('salah_times_cron')) {
            wp_schedule_event(time(), 'hourly', 'salah_times_cron');
        }
    }

    public function unschedule_cron() {
        $timestamp = wp_next_scheduled('salah_times_cron');
        if ($timestamp) {
            wp_unschedule_event($timestamp, 'salah_times_cron');
        }
    }

    public function update_salah_times() {
        do_action('wp_ajax_update_salah_times');
    }
}
