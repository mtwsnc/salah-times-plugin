<?php
function salah_compare_json()
{
    if (!file_exists(SALAH_JSON_FILE)) {
        return ['error' => 'Local salah.json file not found.'];
    }

    $local_data = json_decode(file_get_contents(SALAH_JSON_FILE), true);
    $remote_data = salah_fetch_api();

    if (isset($remote_data['error'])) {
        return $remote_data;
    }

    $diff = array_diff_assoc($local_data, $remote_data);
    return ['diff' => $diff];
}
