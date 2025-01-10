<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

function fetch_salah_times($api_url) {
    $response = wp_remote_get($api_url);
    if (is_wp_error($response)) {
        return false;
    }

    $body = wp_remote_retrieve_body($response);
    return json_decode($body, true);
}
