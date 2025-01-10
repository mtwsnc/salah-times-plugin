<?php
/*
Plugin Name: Salah Times Plugin
Plugin URI: https://github.com/mtwsnc/salah-times-plugin
Description: Fetch and manage salah times from Ibrahim's remote API.
Version: 1.2
Author: Abdur-Rahman Bilal (MTWSNC)
Author URI: https://github.com/aramb-dev
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Include necessary files
require_once plugin_dir_path(__FILE__) . 'includes/helpers.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-salah-times-admin.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-salah-times-cron.php';

// Initialize plugin
new SalahTimesAdmin();
new SalahTimesCron();
