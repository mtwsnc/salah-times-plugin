<?php
// Handle CRON job scheduling and execution
function salah_cron_handler_include()
{
    $options = get_option('salah_plugin_settings', ['fetch_days' => []]);
    $fetch_days = $options['fetch_days'];
    if (in_array(date('w'), $fetch_days)) {
        salah_fetch_api();
    }
}