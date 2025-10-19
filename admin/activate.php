<?php

defined('ABSPATH') || exit;

global $wpdb, $charset_collate;

if (WP_DEBUG) {
    error_log('Monitor > Activating');
}

require_once ABSPATH . 'wp-admin/includes/upgrade.php'; // Isn't there a constant for the admin inclusion path?

$sql = "CREATE TABLE `" . $wpdb->prefix . "monitor_abilities` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `status` int NOT NULL DEFAULT 0,
            `user_id` int NOT NULL DEFAULT 0,
            `name` varchar(200) NOT NULL DEFAULT '',
            `context` varchar(50) NOT NULL DEFAULT '',
            `method` varchar(50) NOT NULL DEFAULT '',
            `input` TEXT,
            `output` TEXT,
            PRIMARY KEY (`id`),
            KEY `name` (`name`)
            ) $charset_collate;";

dbDelta($sql);
if ($wpdb->last_error) {
    error_log($wpdb->last_error);
}

$sql = "CREATE TABLE `" . $wpdb->prefix . "monitor_emails` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `user_id` int NOT NULL DEFAULT 0,
            `from` varchar(200) NOT NULL DEFAULT '',
            `to` varchar(200) NOT NULL DEFAULT '',
            `subject` varchar(200) NOT NULL DEFAULT '',
            `context` varchar(50) NOT NULL DEFAULT '',
            `filters` longtext,
            PRIMARY KEY (`id`)
            ) $charset_collate;";

dbDelta($sql);
if ($wpdb->last_error) {
    error_log($wpdb->last_error);
}

$sql = "CREATE TABLE `" . $wpdb->prefix . "monitor_scheduler` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `type` varchar(50) NOT NULL DEFAULT '',
            `ip` varchar(200) NOT NULL DEFAULT '',
            `context` varchar(50) NOT NULL DEFAULT '',
            `text` varchar(250) NOT NULL DEFAULT '',
            `filters` longtext,
            PRIMARY KEY (`id`)
            ) $charset_collate;";

dbDelta($sql);
if ($wpdb->last_error) {
    error_log($wpdb->last_error);
}

// Cleanup process
if (!wp_next_scheduled('monitor_clean_logs') && (!defined('WP_INSTALLING') || !WP_INSTALLING)) {
    wp_schedule_event(time() + 30, 'daily', 'monitor_clean_logs');
}