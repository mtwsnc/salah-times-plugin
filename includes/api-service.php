<?php
/**
 * API Service Module
 * Handles all API requests with configurable endpoints
 */

class Salah_API_Service
{
    private $base_url;
    private $cache_duration = 3600; // 1 hour cache

    public function __construct()
    {
        $options = get_option('salah_plugin_settings', ['api_base_url' => '']);
        $this->base_url = trailingslashit($options['api_base_url']);
    }

    /**
     * Get all prayer times
     */
    public function get_all_prayer_times($date = null)
    {
        return $this->make_request('all', $date);
    }

    /**
     * Get Fajr prayer time
     */
    public function get_fajr($date = null)
    {
        return $this->make_request('fajr', $date);
    }

    /**
     * Get Dhuhr prayer time
     */
    public function get_dhuhr($date = null)
    {
        return $this->make_request('dhuhr', $date);
    }

    /**
     * Get Asr prayer time
     */
    public function get_asr($date = null)
    {
        return $this->make_request('asr', $date);
    }

    /**
     * Get Maghrib prayer time
     */
    public function get_maghrib($date = null)
    {
        return $this->make_request('maghrib', $date);
    }

    /**
     * Get Isha prayer time
     */
    public function get_isha($date = null)
    {
        return $this->make_request('isha', $date);
    }

    /**
     * Get Shurooq (sunrise) time
     */
    public function get_shurooq($date = null)
    {
        return $this->make_request('shurooq', $date);
    }

    /**
     * Make API request with caching
     */
    private function make_request($endpoint, $date = null)
    {
        if (empty($this->base_url)) {
            return ['error' => 'API base URL not configured'];
        }

        // Build URL with optional date parameter
        $url = $this->base_url . $endpoint;
        if ($date) {
            $url .= '?date=' . urlencode($date);
        }

        // Check cache first
        $cache_key = 'salah_api_' . md5($url);
        $cached = get_transient($cache_key);
        if ($cached !== false) {
            return $cached;
        }

        // Make request
        $response = wp_remote_get($url, [
            'timeout' => 15,
            'headers' => [
                'Accept' => 'application/json'
            ]
        ]);

        if (is_wp_error($response)) {
            return ['error' => $response->get_error_message()];
        }

        $status_code = wp_remote_retrieve_response_code($response);
        if ($status_code !== 200) {
            return ['error' => "API returned status code: {$status_code}"];
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return ['error' => 'Invalid JSON response from API'];
        }

        // Cache the result
        set_transient($cache_key, $data, $this->cache_duration);

        return $data;
    }

    /**
     * Clear all cached API responses
     */
    public function clear_cache()
    {
        global $wpdb;
        $wpdb->query(
            "DELETE FROM {$wpdb->options}
             WHERE option_name LIKE '_transient_salah_api_%'
             OR option_name LIKE '_transient_timeout_salah_api_%'"
        );
    }

    /**
     * Validate API connection
     */
    public function test_connection()
    {
        $result = $this->get_all_prayer_times();
        return !isset($result['error']);
    }
}
