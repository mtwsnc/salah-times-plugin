<?php
function salah_fetch_api()
{
    $api_url = 'https://northerly-robin-8705.dataplicity.io/mtws-iqaamah-times/all';
    $response = wp_remote_get($api_url);

    if (is_wp_error($response)) {
        return ['error' => $response->get_error_message()];
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        return ['error' => 'Invalid JSON response from API.'];
    }

    file_put_contents(SALAH_JSON_FILE, json_encode($data, JSON_PRETTY_PRINT));
    return ['success' => true, 'message' => 'Salah times updated successfully.'];
}
