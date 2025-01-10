<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class SalahTimesAdmin {
    private $api_url = 'https://northerly-robin-8705.dataplicity.io/mtws-iqaamah-times/all';
    private $file_path;

    public function __construct() {
        $this->file_path = plugin_dir_path(__FILE__) . '../salah.json';
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_bar_menu', [$this, 'add_admin_bar_button'], 100);
        add_action('wp_ajax_update_salah_times', [$this, 'update_salah_times']);
        add_action('wp_ajax_diff_salah_times', [$this, 'diff_salah_times']);
    }

    public function add_admin_menu() {
        add_menu_page(
            'Salah Times Plugin',
            'Salah Times',
            'manage_options',
            'salah-times-plugin',
            [$this, 'render_admin_page'],
            'dashicons-clock'
        );
    }

    public function render_admin_page() {
        $absolute_url = plugins_url('salah.json', dirname(__FILE__));
        ?>
        <div class="wrap">
            <h1>Salah Times Plugin</h1>
            <button id="manual-update" class="button button-primary">Manual Update</button>
            <button id="compare-diff" class="button">Compare JSON</button>
            <p><strong>Absolute URL:</strong> <a href="<?php echo esc_url($absolute_url); ?>" target="_blank"><?php echo esc_url($absolute_url); ?></a></p>
        </div>
        <script>
        jQuery(document).ready(function($) {
            $('#manual-update').on('click', function() {
                $.post(ajaxurl, { action: 'update_salah_times' }, function(response) {
                    alert(response.success ? 'Salah times updated!' : 'Error updating salah times.');
                });
            });

            $('#compare-diff').on('click', function() {
                $.post(ajaxurl, { action: 'diff_salah_times' }, function(response) {
                    if (response.success) {
                        alert('Differences: ' + response.data);
                    } else {
                        alert('Error fetching comparison.');
                    }
                });
            });
        });
        </script>
        <?php
    }

    public function update_salah_times() {
        $data = fetch_salah_times($this->api_url);
        if (!$data) {
            wp_send_json_error('Failed to fetch data.');
        }

        file_put_contents($this->file_path, json_encode($data));
        wp_send_json_success('Salah times updated.');
    }

    public function diff_salah_times() {
        $remote_data = fetch_salah_times($this->api_url);
        if (!$remote_data) {
            wp_send_json_error('Failed to fetch data.');
        }

        $local_data = file_exists($this->file_path) ? json_decode(file_get_contents($this->file_path), true) : [];
        $diff = array_diff_assoc($remote_data, $local_data);
        wp_send_json_success(json_encode($diff, JSON_PRETTY_PRINT));
    }
}
