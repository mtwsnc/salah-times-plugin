<?php
function salah_compare_json()
{
    if (!file_exists(SALAH_JSON_FILE)) {
        return ['error' => 'Local salah.json file not found.'];
    }

    // Use API service to fetch remote data
    $api_service = new Salah_API_Service();
    $remote_data = $api_service->get_all_prayer_times();

    if (isset($remote_data['error'])) {
        return ['error' => $remote_data['error']];
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
