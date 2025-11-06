<?php
function salah_compare_json()
{
    if (!file_exists(SALAH_JSON_FILE)) {
        return ['error' => 'Local salah.json file not found.'];
    }

    // Get configured API base URL
    $options = get_option('salah_plugin_settings', ['api_base_url' => '']);
    $api_base_url = $options['api_base_url'];

    if (empty($api_base_url)) {
        return ['error' => 'API base URL not configured. Please configure in plugin settings.'];
    }

    // Fetch remote JSON
    $api_url = trailingslashit($api_base_url) . 'mtws-iqaamah-times/all';
    $response = wp_remote_get($api_url);

    if (is_wp_error($response)) {
        return ['error' => $response->get_error_message()];
    }

    $remote_data = json_decode(wp_remote_retrieve_body($response), true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        return ['error' => 'Invalid JSON response from API.'];
    }

    // Load local JSON
    $local_data = json_decode(file_get_contents(SALAH_JSON_FILE), true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        return ['error' => 'Invalid JSON data in local salah.json file.'];
    }

    // Compare JSON data
    $differences = [];
    foreach ($remote_data as $key => $value) {
        if (!isset($local_data[$key]) || $local_data[$key] !== $value) {
            $differences[$key] = [
                'local' => $local_data[$key] ?? null,
                'remote' => $value
            ];
        }
    }

    return ['diff' => $differences];
}
