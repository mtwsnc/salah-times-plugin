<?php

function salah_times_admin_page() {
    $api_data = wp_remote_get(SALAH_TIMES_API_URL);
    $api_content = is_wp_error($api_data) ? 'Error fetching API data' : wp_remote_retrieve_body($api_data);
    $local_content = file_exists(SALAH_TIMES_JSON_PATH) ? file_get_contents(SALAH_TIMES_JSON_PATH) : 'No local file found.';

    $selected_days = get_option('salah_times_fetch_days', []);
    $custom_cron = get_option('salah_times_custom_cron', '');

    $absolute_url = plugin_dir_url(__FILE__) . 'salah.json';
    $relative_url = 'salah.json';
    ?>
    <div class="wrap">
        <h1>Salah Times</h1>

        <form method="post" action="options.php">
            <?php
            settings_fields('salah_times_settings');
            do_settings_sections('salah_times_settings');
            ?>
            <h2>Automatic Fetch Settings</h2>

            <label for="fetch_days[]">Select Days of the Week:</label><br>
            <?php
            $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
            foreach ($days as $day) {
                $checked = in_array($day, $selected_days) ? 'checked' : '';
                echo "<label><input type='checkbox' name='salah_times_fetch_days[]' value='$day' $checked> $day</label><br>";
            }
            ?>

            <h3>Or Enter Custom CRON Expression:</h3>
            <input type="text" name="salah_times_custom_cron" value="<?php echo esc_attr($custom_cron); ?>" placeholder="*/5 * * * *"><br><br>

            <button type="submit" class="button button-primary">Save Settings</button>
        </form>

        <h2>Manual Fetch</h2>
        <button id="fetch-salah-times" class="button button-primary">Fetch Salah Times Now</button>

        <h2>API Data</h2>
        <pre><?php echo esc_html($api_content); ?></pre>

        <h2>Local Salah Times (salah.json)</h2>
        <pre><?php echo esc_html($local_content); ?></pre>

        <h2>File Paths</h2>
        <p><strong>Absolute URL:</strong> <?php echo esc_url($absolute_url); ?></p>
        <p><strong>Relative URL:</strong> <?php echo esc_html($relative_url); ?></p>
    </div>
    <script>
        document.getElementById('fetch-salah-times').addEventListener('click', function () {
            fetch(ajaxurl + '?action=fetch_salah_times', { method: 'POST', credentials: 'same-origin' })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Salah times fetched successfully!');
                        location.reload();
                    } else {
                        alert('Error: ' + data.data);
                    }
                })
                .catch(error => alert('Error: ' + error));
        });
    </script>
    <?php
}