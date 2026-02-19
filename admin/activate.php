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
            `status` int NOT NULL DEFAULT 0,
            `user_id` int NOT NULL DEFAULT 0,
            `from` varchar(200) NOT NULL DEFAULT '',
            `to` varchar(200) NOT NULL DEFAULT '',
            `subject` varchar(200) NOT NULL DEFAULT '',
            `context` varchar(50) NOT NULL DEFAULT '',
            `filters` longtext,
            `duration` double default 0,
            `text` varchar(250) NOT NULL DEFAULT '',
            PRIMARY KEY (`id`)
            ) $charset_collate;";

dbDelta($sql);
if ($wpdb->last_error) {
    error_log($wpdb->last_error);
}

$sql = "CREATE TABLE `" . $wpdb->prefix . "monitor_scheduler` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `ip` varchar(200) NOT NULL DEFAULT '',
            `context` varchar(50) NOT NULL DEFAULT '',
            `filters` longtext,
            `ready_jobs` longtext,
            `executed_jobs` longtext,
            PRIMARY KEY (`id`)
            ) $charset_collate;";

dbDelta($sql);
if ($wpdb->last_error) {
    error_log($wpdb->last_error);
}

$sql = "CREATE TABLE `" . $wpdb->prefix . "monitor_http` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `status` int NOT NULL DEFAULT 0,
            `context` varchar(50) NOT NULL DEFAULT '',
            `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `url` varchar(2048) NOT NULL DEFAULT '',
            `method` varchar(20) NOT NULL DEFAULT '',
            `text` varchar(250) NOT NULL DEFAULT '',
            `duration` double NOT NULL default 0,
            `code` int NOT NULL default 0,
            `args` longtext,
            PRIMARY KEY (`id`)
            ) $charset_collate;";

dbDelta($sql);
if ($wpdb->last_error) {
    error_log($wpdb->last_error);
}

$sql = "CREATE TABLE `" . $wpdb->prefix . "monitor_rest` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `code` int NOT NULL default 0,
            `route` varchar(1024) NOT NULL DEFAULT '',
            `method` varchar(20) NOT NULL DEFAULT '',
            `params` longtext,
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
