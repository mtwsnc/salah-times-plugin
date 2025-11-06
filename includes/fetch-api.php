<?php
function salah_fetch_api()
{
    // Get configured API base URL
    $options = get_option('salah_plugin_settings', ['api_base_url' => '']);
    $api_base_url = $options['api_base_url'];

    if (empty($api_base_url)) {
        return ['error' => 'API base URL not configured. Please configure in plugin settings.'];
    }

    // Construct API endpoint URL
    $api_url = trailingslashit($api_base_url) . 'mtws-iqaamah-times/all';
    $response = wp_remote_get($api_url);

    if (is_wp_error($response)) {
        return ['error' => $response->get_error_message()];
    }

    $data = json_decode(wp_remote_retrieve_body($response), true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        return ['error' => 'Invalid JSON response from API.'];
    }

    // Save data to salah.json file
    file_put_contents(SALAH_JSON_FILE, json_encode($data, JSON_PRETTY_PRINT));

    return ['success' => 'Salah times updated successfully.'];
}
