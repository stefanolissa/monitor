<?php

// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery -- not relevant
// phpcs:disable WordPress.DB.DirectDatabaseQuery.NoCaching -- not relevant
// phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange -- not relevant

defined('WP_UNINSTALL_PLUGIN') || exit;

delete_option('monitor_settings');
delete_option('monitor_version');
delete_option('monitor_scheduler_last_run');
delete_option('monitor_scheduler_hooks');
delete_option('monitor_update_data');

wp_unschedule_hook('monitor_clean_logs');

global $wpdb;
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}monitor_http");
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}monitor_rest");
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}monitor_scheduler");
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}monitor_emails");
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}monitor_abilities");
