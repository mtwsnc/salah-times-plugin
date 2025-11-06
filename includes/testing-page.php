<?php
/**
 * Testing Page - Visual Testing Interface
 * Displays all plugin components for pre-deployment testing
 */

// Add testing page to admin menu
add_action('admin_menu', 'salah_add_testing_page');
function salah_add_testing_page()
{
    add_submenu_page(
        'salah_times_plugin',
        'Visual Testing',
        'Testing Page',
        'manage_options',
        'salah_testing_page',
        'salah_render_testing_page'
    );
}

function salah_render_testing_page()
{
    $api_service = new Salah_API_Service();
    $options = get_option('salah_plugin_settings', [
        'api_base_url' => '',
        'location_name' => '',
        'fetch_days' => [],
        'cron_enabled' => false
    ]);

    // Get prayer times data
    $prayer_data = null;
    $prayer_data_source = 'Not Available';
    if (file_exists(SALAH_JSON_FILE)) {
        $prayer_data = json_decode(file_get_contents(SALAH_JSON_FILE), true);
        $prayer_data_source = 'Local JSON File';
    }

    // Check API connection
    $api_connected = !empty($options['api_base_url']) ? $api_service->test_connection() : false;

    // Get next scheduled CRON
    $next_cron = wp_next_scheduled('salah_cron_job');
    $cron_status = $next_cron ? date('Y-m-d H:i:s', $next_cron) : 'Not Scheduled';

    ?>
    <div class="wrap">
        <h1>🧪 Salah Times Plugin - Visual Testing Page</h1>
        <p>This page allows you to test all plugin components before deploying on your live website.</p>

        <hr>

        <!-- System Status -->
        <div class="card" style="max-width: 100%; padding: 20px; margin: 20px 0;">
            <h2>📊 System Status</h2>
            <table class="widefat">
                <tr>
                    <td style="width: 30%; font-weight: bold;">API Base URL</td>
                    <td>
                        <?php if (!empty($options['api_base_url'])): ?>
                            <span style="color: green;">✓</span> <?php echo esc_html($options['api_base_url']); ?>
                        <?php else: ?>
                            <span style="color: red;">✗ Not Configured</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <td style="font-weight: bold;">API Connection</td>
                    <td>
                        <?php if ($api_connected): ?>
                            <span style="color: green;">✓ Connected</span>
                        <?php else: ?>
                            <span style="color: red;">✗ Failed or Not Configured</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <td style="font-weight: bold;">Location Name</td>
                    <td><?php echo esc_html($options['location_name'] ?: 'Not Set'); ?></td>
                </tr>
                <tr>
                    <td style="font-weight: bold;">Prayer Data Source</td>
                    <td><?php echo esc_html($prayer_data_source); ?></td>
                </tr>
                <tr>
                    <td style="font-weight: bold;">CRON Job Status</td>
                    <td>
                        <?php if ($options['cron_enabled']): ?>
                            <span style="color: green;">✓ Enabled</span> (Next run: <?php echo esc_html($cron_status); ?>)
                        <?php else: ?>
                            <span style="color: orange;">⚠ Disabled</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <td style="font-weight: bold;">Fetch Days</td>
                    <td>
                        <?php
                        if (!empty($options['fetch_days'])) {
                            $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                            $selected_days = array_map(function ($index) use ($days) {
                                return $days[$index];
                            }, $options['fetch_days']);
                            echo esc_html(implode(', ', $selected_days));
                        } else {
                            echo 'None Selected';
                        }
                        ?>
                    </td>
                </tr>
            </table>
        </div>

        <hr>

        <!-- Quick Actions -->
        <div class="card" style="max-width: 100%; padding: 20px; margin: 20px 0;">
            <h2>⚡ Quick Actions</h2>
            <p>
                <button type="button" class="button button-primary" id="test-fetch-api">
                    Fetch Prayer Times from API
                </button>
                <button type="button" class="button" id="test-connection-btn">
                    Test API Connection
                </button>
                <button type="button" class="button" id="test-clear-cache">
                    Clear Cache
                </button>
            </p>
            <div id="action-result" style="margin-top: 15px;"></div>
        </div>

        <hr>

        <!-- Prayer Times Data Preview -->
        <?php if ($prayer_data): ?>
            <div class="card" style="max-width: 100%; padding: 20px; margin: 20px 0;">
                <h2>📅 Raw Prayer Times Data</h2>
                <pre style="background: #f5f5f5; padding: 15px; overflow: auto; max-height: 300px;"><?php echo esc_html(json_encode($prayer_data, JSON_PRETTY_PRINT)); ?></pre>
            </div>
        <?php endif; ?>

        <hr>

        <!-- Frontend Display Preview -->
        <div class="card" style="max-width: 100%; padding: 20px; margin: 20px 0; background: #f9f9f9;">
            <h2>🎨 Frontend Display Preview</h2>
            <p><strong>This is how the prayer times will appear on your website:</strong></p>
            <div style="background: #ffffff; padding: 20px; border: 2px dashed #ccc;">
                <?php
                if ($prayer_data) {
                    // Render the shortcode
                    $display = new Salah_Prayer_Times_Display();
                    echo $display->render_prayer_times([]);
                } else {
                    echo '<div class="salah-error">No prayer times data available. Click "Fetch Prayer Times from API" above.</div>';
                }
                ?>
            </div>
            <p style="margin-top: 15px;">
                <strong>Shortcode to use on pages/posts:</strong>
                <code style="background: #fff3cd; padding: 5px 10px; display: inline-block; border: 1px solid #ffc107;">[salah_times]</code>
            </p>
        </div>

        <hr>

        <!-- API Endpoints Test -->
        <div class="card" style="max-width: 100%; padding: 20px; margin: 20px 0;">
            <h2>🔗 API Endpoints Testing</h2>
            <p>Test individual prayer time endpoints:</p>
            <table class="widefat" style="margin-top: 15px;">
                <thead>
                    <tr>
                        <th>Prayer</th>
                        <th>Endpoint</th>
                        <th>Action</th>
                        <th>Result</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $endpoints = [
                        'all' => 'All Prayer Times',
                        'fajr' => 'Fajr',
                        'dhuhr' => 'Dhuhr',
                        'asr' => 'Asr',
                        'maghrib' => 'Maghrib',
                        'isha' => 'Isha',
                        'shurooq' => 'Sunrise'
                    ];
                    foreach ($endpoints as $endpoint => $label):
                    ?>
                        <tr>
                            <td><strong><?php echo esc_html($label); ?></strong></td>
                            <td><code>/<?php echo esc_html($endpoint); ?></code></td>
                            <td>
                                <button type="button" class="button button-small test-endpoint" data-endpoint="<?php echo esc_attr($endpoint); ?>">
                                    Test
                                </button>
                            </td>
                            <td class="endpoint-result-<?php echo esc_attr($endpoint); ?>">
                                <span style="color: #999;">Not tested</span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <hr>

        <!-- Styling Test -->
        <div class="card" style="max-width: 100%; padding: 20px; margin: 20px 0;">
            <h2>🎨 Styling & Responsiveness Test</h2>
            <p>Preview the prayer times display at different viewport sizes:</p>
            <div style="margin-top: 15px;">
                <button type="button" class="button" onclick="resizePreview('100%', 'Desktop')">Desktop (100%)</button>
                <button type="button" class="button" onclick="resizePreview('768px', 'Tablet')">Tablet (768px)</button>
                <button type="button" class="button" onclick="resizePreview('375px', 'Mobile')">Mobile (375px)</button>
            </div>
            <div id="responsive-preview" style="margin-top: 20px; overflow: auto;">
                <div id="preview-container" style="max-width: 100%; margin: 0 auto; transition: max-width 0.3s;">
                    <?php
                    if ($prayer_data) {
                        $display = new Salah_Prayer_Times_Display();
                        echo $display->render_prayer_times([]);
                    }
                    ?>
                </div>
            </div>
            <p id="preview-size" style="text-align: center; color: #666; margin-top: 10px;">Current: Desktop (100%)</p>
        </div>

        <hr>

        <!-- Help Section -->
        <div class="card" style="max-width: 100%; padding: 20px; margin: 20px 0; background: #e7f3ff;">
            <h2>❓ Testing Checklist</h2>
            <ol style="line-height: 2;">
                <li>✓ Verify API connection is successful</li>
                <li>✓ Fetch prayer times from API and check data</li>
                <li>✓ Confirm current prayer is highlighted correctly</li>
                <li>✓ Check countdown timer is updating every second</li>
                <li>✓ Test responsive design at different screen sizes</li>
                <li>✓ Verify all individual API endpoints work</li>
                <li>✓ Test cache clearing functionality</li>
                <li>✓ Check CRON job is scheduled if enabled</li>
            </ol>
            <p><strong>Once all tests pass, you can safely deploy the shortcode <code>[salah_times]</code> on your live pages!</strong></p>
        </div>
    </div>

    <script>
    jQuery(document).ready(function($) {
        // Fetch API test
        $('#test-fetch-api').on('click', function() {
            var $btn = $(this);
            var $result = $('#action-result');
            $btn.prop('disabled', true).text('Fetching...');
            $result.html('<p>⏳ Fetching prayer times from API...</p>');

            $.post(salahAjax.ajaxUrl, { action: 'salah_manual_update' }, function(response) {
                $btn.prop('disabled', false).text('Fetch Prayer Times from API');
                if (response.success) {
                    $result.html('<p style="color: green; font-weight: bold;">✓ ' + response.data.message + ' Reloading page...</p>');
                    setTimeout(function() { location.reload(); }, 1500);
                } else {
                    $result.html('<p style="color: red; font-weight: bold;">✗ Error: ' + response.data.message + '</p>');
                }
            }).fail(function() {
                $btn.prop('disabled', false).text('Fetch Prayer Times from API');
                $result.html('<p style="color: red; font-weight: bold;">✗ Request failed. Check your API configuration.</p>');
            });
        });

        // Test connection
        $('#test-connection-btn').on('click', function() {
            var $btn = $(this);
            var $result = $('#action-result');
            $btn.prop('disabled', true).text('Testing...');
            $result.html('<p>⏳ Testing API connection...</p>');

            $.post(salahAjax.ajaxUrl, { action: 'salah_test_connection' }, function(response) {
                $btn.prop('disabled', false).text('Test API Connection');
                if (response.success) {
                    $result.html('<p style="color: green; font-weight: bold;">✓ ' + response.data.message + '</p>');
                } else {
                    $result.html('<p style="color: red; font-weight: bold;">✗ ' + response.data.message + '</p>');
                }
            });
        });

        // Clear cache
        $('#test-clear-cache').on('click', function() {
            var $btn = $(this);
            var $result = $('#action-result');
            $btn.prop('disabled', true).text('Clearing...');
            $result.html('<p>⏳ Clearing cache...</p>');

            $.post(salahAjax.ajaxUrl, { action: 'salah_clear_cache' }, function(response) {
                $btn.prop('disabled', false).text('Clear Cache');
                $result.html('<p style="color: green; font-weight: bold;">✓ ' + response.data.message + '</p>');
            });
        });

        // Test individual endpoints
        $('.test-endpoint').on('click', function() {
            var $btn = $(this);
            var endpoint = $btn.data('endpoint');
            var $result = $('.endpoint-result-' + endpoint);

            $btn.prop('disabled', true).text('Testing...');
            $result.html('<span style="color: orange;">⏳ Testing...</span>');

            $.post(salahAjax.ajaxUrl, {
                action: 'salah_test_endpoint',
                endpoint: endpoint
            }, function(response) {
                $btn.prop('disabled', false).text('Test');
                if (response.success) {
                    $result.html('<span style="color: green; font-weight: bold;">✓ Success</span>');
                } else {
                    $result.html('<span style="color: red; font-weight: bold;">✗ ' + response.data.message + '</span>');
                }
            }).fail(function() {
                $btn.prop('disabled', false).text('Test');
                $result.html('<span style="color: red; font-weight: bold;">✗ Failed</span>');
            });
        });
    });

    function resizePreview(width, label) {
        document.getElementById('preview-container').style.maxWidth = width;
        document.getElementById('preview-size').textContent = 'Current: ' + label + ' (' + width + ')';
    }
    </script>

    <style>
    .card {
        background: #fff;
        border: 1px solid #ccd0d4;
        box-shadow: 0 1px 1px rgba(0,0,0,.04);
    }
    .widefat td {
        padding: 12px 10px;
    }
    </style>
    <?php
}

// AJAX handler for testing individual endpoints
add_action('wp_ajax_salah_test_endpoint', 'salah_test_endpoint_ajax');
function salah_test_endpoint_ajax()
{
    $endpoint = sanitize_text_field($_POST['endpoint'] ?? '');
    if (empty($endpoint)) {
        wp_send_json_error(['message' => 'No endpoint specified']);
    }

    $api_service = new Salah_API_Service();
    $method_map = [
        'all' => 'get_all_prayer_times',
        'fajr' => 'get_fajr',
        'dhuhr' => 'get_dhuhr',
        'asr' => 'get_asr',
        'maghrib' => 'get_maghrib',
        'isha' => 'get_isha',
        'shurooq' => 'get_shurooq'
    ];

    if (!isset($method_map[$endpoint])) {
        wp_send_json_error(['message' => 'Invalid endpoint']);
    }

    $method = $method_map[$endpoint];
    $result = $api_service->$method();

    if (isset($result['error'])) {
        wp_send_json_error(['message' => $result['error']]);
    } else {
        wp_send_json_success(['data' => $result]);
    }
}
