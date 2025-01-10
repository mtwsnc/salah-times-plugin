<?php
function salah_fetch_api()
{
    // Fetch data from API
    $api_url = 'https://northerly-robin-8705.dataplicity.io/mtws-iqaamah-times/all';
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
