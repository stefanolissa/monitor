<?php

/**
 * Plugin Name:       Monitor
 * Description:       Monitor
 * Version:           1.0.0
 * Author:            satollo
 * Author URI:        https://gemini.google.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       abilities-ui
 */
defined('ABSPATH') || exit;

register_activation_hook(__FILE__, function () {
    require_once __DIR__ . '/admin/activate.php';
});

if (is_admin()) {
    require_once __DIR__ . '/admin/admin.php';
}

// TODO: event collectors on their own class/file
// Intercept the emails

add_filter('wp_mail', function ($atts) {
    global $wpdb;

    $context = 'frontend';
    if (defined('DOING_CRON') && DOING_CRON) {
        $context = 'cron';
    } elseif (defined('DOING_AJAX') && DOING_AJAX) {
        $context = 'ajax';
    } elseif (defined('REST_REQUEST') && REST_REQUEST) {
        $context = 'rest';
    } elseif (is_admin()) {
        $context = 'backend';
    }

    $user_id = is_user_logged_in() ? get_current_user_id() : 0;
    $wpdb->insert($wpdb->prefix . 'monitor_emails', ['user_id' => $user_id, 'subject' => $atts['subject'], 'to' => $atts['to'],
        'context' => $context]);
    return $atts;
}, 0);

// Intercept abilities

add_action('before_execute_ability', function ($name) {
    global $wpdb;

    // It could not be useful, anyway...
    $context = 'frontend';
    if (defined('DOING_CRON') && DOING_CRON) {
        $context = 'cron';
    } elseif (defined('WP_CLI') && WP_CLI) {
        $context = 'cli';
    } elseif (defined('DOING_AJAX') && DOING_AJAX) {
        $context = 'ajax'; // Should use the referrer to distinguish frontent and backend ajax calls...
    } elseif (defined('REST_REQUEST') && REST_REQUEST) {
        $context = 'rest';
    } elseif (is_admin()) {
        $context = 'admin';
    }

    $method = (defined('REST_REQUEST') && REST_REQUEST) ? 'rest' : 'php';
    $user_id = is_user_logged_in() ? get_current_user_id() : 0;

    $wpdb->insert($wpdb->prefix . 'monitor_abilities', ['user_id' => $user_id, 'method' => $method, 'name' => $name, 'context' => $context]);
});

if (defined('DOING_CRON') && DOING_CRON) {
    $context = '';
    if (defined('WP_CLI') && WP_CLI) {
        $context = 'cli';
    }
    $wpdb->insert($wpdb->prefix . 'monitor_scheduler', ['ip' => '', 'context' => $context]);
}