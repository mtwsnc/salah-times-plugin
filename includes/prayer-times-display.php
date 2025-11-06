<?php
/**
 * Prayer Times Display Module
 * Handles UI rendering with current prayer highlighting
 */

class Salah_Prayer_Times_Display
{
    private $prayer_order = ['Fajr', 'Sunrise', 'Dhuhr', 'Asr', 'Maghrib', 'Isha'];

    public function __construct()
    {
        add_shortcode('salah_times', [$this, 'render_prayer_times']);
    }

    /**
     * Get prayer times data from local JSON
     */
    private function get_prayer_times()
    {
        if (!file_exists(SALAH_JSON_FILE)) {
            return null;
        }

        $data = json_decode(file_get_contents(SALAH_JSON_FILE), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return null;
        }

        return $data;
    }

    /**
     * Determine which prayer is currently active
     */
    private function get_current_prayer($prayer_times)
    {
        if (empty($prayer_times)) {
            return null;
        }

        $current_time = current_time('H:i');
        $current_timestamp = strtotime($current_time);

        $prayer_timestamps = [];
        foreach ($this->prayer_order as $prayer) {
            $key = strtolower($prayer);
            if ($key === 'sunrise') {
                $key = 'shurooq';
            }

            $time = null;
            if ($key === 'shurooq') {
                // Sunrise uses adhan
                if (isset($prayer_times['adhan'][$key])) {
                    $time = $prayer_times['adhan'][$key];
                }
            } else {
                // Prayers use iqamah
                if (isset($prayer_times['iqamah'][$key])) {
                    $time = $prayer_times['iqamah'][$key];
                }
            }

            if ($time) {
                $prayer_timestamps[$prayer] = strtotime($time);
            }
        }

        // Find current prayer window
        $current_prayer = null;
        $previous_prayer = null;

        foreach ($prayer_timestamps as $prayer => $timestamp) {
            if ($current_timestamp >= $timestamp) {
                $current_prayer = $prayer;
            } else {
                break;
            }
        }

        // If before Fajr, it's still Isha time
        if ($current_prayer === null && isset($prayer_timestamps['Isha'])) {
            $current_prayer = 'Isha';
        }

        return $current_prayer;
    }

    /**
     * Get next prayer and time remaining
     */
    private function get_next_prayer($prayer_times)
    {
        if (empty($prayer_times)) {
            return null;
        }

        $current_time = current_time('timestamp');
        $prayer_timestamps = [];

        foreach ($this->prayer_order as $prayer) {
            $key = strtolower($prayer);
            if ($key === 'sunrise') {
                continue; // Skip Sunrise for next prayer
            }

            // Use iqamah time for prayers
            $time = null;
            if (isset($prayer_times['iqamah'][$key])) {
                $time = $prayer_times['iqamah'][$key];
            }

            if ($time) {
                $timestamp = strtotime(date('Y-m-d') . ' ' . $time);
                $prayer_timestamps[$prayer] = $timestamp;
            }
        }

        // Find next prayer
        $next_prayer = null;
        $next_timestamp = null;

        foreach ($prayer_timestamps as $prayer => $timestamp) {
            if ($timestamp > $current_time) {
                $next_prayer = $prayer;
                $next_timestamp = $timestamp;
                break;
            }
        }

        // If no prayer found today, next is tomorrow's Fajr
        if ($next_prayer === null && isset($prayer_times['iqamah']['fajr'])) {
            $next_prayer = 'Fajr';
            $next_timestamp = strtotime('tomorrow ' . $prayer_times['iqamah']['fajr']);
        }

        if ($next_timestamp) {
            $time_diff = $next_timestamp - $current_time;
            $hours = floor($time_diff / 3600);
            $minutes = floor(($time_diff % 3600) / 60);
            $seconds = $time_diff % 60;

            return [
                'name' => $next_prayer,
                'time_remaining' => sprintf('%dh %dm %ds', $hours, $minutes, $seconds),
                'timestamp' => $next_timestamp
            ];
        }

        return null;
    }

    /**
     * Format time for display
     */
    private function format_time($time)
    {
        return date('g:i A', strtotime($time));
    }

    /**
     * Render prayer times table
     */
    public function render_prayer_times($atts)
    {
        $prayer_times = $this->get_prayer_times();

        if (!$prayer_times) {
            return '<div class="salah-error">Prayer times not available. Please update from admin panel.</div>';
        }

        $current_prayer = $this->get_current_prayer($prayer_times);
        $next_prayer = $this->get_next_prayer($prayer_times);

        $options = get_option('salah_plugin_settings', ['location_name' => '']);
        $location = $options['location_name'] ?: 'Local';

        ob_start();
        ?>
        <div class="salah-times-container" id="salah-times-widget">
            <div class="salah-header">
                <h3><?php echo esc_html($location); ?> Prayer Times</h3>
                <div class="salah-date">
                    <?php echo date('l, F j, Y'); ?>
                </div>
            </div>

            <table class="salah-times-table">
                <tbody>
                    <?php foreach ($this->prayer_order as $prayer): ?>
                        <?php
                        $key = strtolower($prayer);
                        if ($key === 'sunrise') {
                            $key = 'shurooq';
                        }

                        // Get iqamah time (or adhan for sunrise)
                        $time = null;

                        if ($key === 'shurooq') {
                            // Sunrise only has adhan, no iqamah
                            if (isset($prayer_times['adhan'][$key])) {
                                $time = $prayer_times['adhan'][$key];
                            }
                        } else {
                            // For prayers, use iqamah time
                            if (isset($prayer_times['iqamah'][$key]) && $prayer_times['iqamah'][$key] !== null) {
                                $time = $prayer_times['iqamah'][$key];
                            }
                        }

                        if (!$time) {
                            continue;
                        }

                        $is_current = ($current_prayer === $prayer);
                        $row_class = $is_current ? 'current-prayer' : '';
                        ?>
                        <tr class="<?php echo esc_attr($row_class); ?>">
                            <td class="prayer-name"><?php echo esc_html(strtoupper($prayer)); ?></td>
                            <td class="prayer-time"><?php echo esc_html($this->format_time($time)); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <?php if ($next_prayer): ?>
                <div class="salah-next-prayer">
                    <strong>Next:</strong> <?php echo esc_html($next_prayer['name']); ?>
                    in <span class="countdown" data-timestamp="<?php echo esc_attr($next_prayer['timestamp']); ?>">
                        <?php echo esc_html($next_prayer['time_remaining']); ?>
                    </span>
                </div>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }
}

// Initialize display class
new Salah_Prayer_Times_Display();
