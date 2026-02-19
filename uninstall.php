<?php
defined('WP_UNINSTALL_PLUGIN') || exit;

delete_option('monitor_settings');
delete_option('monitor_version');
delete_option('monitor_scheduler_last_run');
delete_option('monitor_scheduler_hooks');
delete_option('monitor_update_data');

wp_unschedule_hook('monitor_clean_logs');
wp_unschedule_hook('monitor_test');

global $wpdb;
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}monitor_http");
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}monitor_rest");
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}monitor_scheduler");
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}monitor_emails");
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}monitor_abilities");

// Case 3: Delete metadata (e.g., from posts)
//delete_metadata('post', 0, 'my_plugin_meta_key', '', true);