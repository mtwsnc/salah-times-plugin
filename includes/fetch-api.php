<?php
function salah_fetch_api()
{
    // Use API service to fetch data
    $api_service = new Salah_API_Service();
    $data = $api_service->get_all_prayer_times();

    if (isset($data['error'])) {
        return ['error' => $data['error']];
    }

    // Save data to salah.json file
    file_put_contents(SALAH_JSON_FILE, json_encode($data, JSON_PRETTY_PRINT));

    return ['success' => 'Salah times updated successfully.'];
}
