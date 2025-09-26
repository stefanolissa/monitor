<?php

/**
 * Plugin Name:       Monitor
 * Description:       Records and display WP events: emails, abilities, scheduler
 * Version:           1.0.0
 * Author:            satollo
 * Author URI:        https://www.satollo.net
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       monitor
 * Requires at least: 6.1
 * Requires PHP: 7.0
 * Plugin URI: https://www.satollo.net/plugins/monitor
 */
defined('ABSPATH') || exit;

register_activation_hook(__FILE__, function () {
    require_once __DIR__ . '/admin/activate.php';
});

//register_deactivation_hook(__FILE__, function () {
//});

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

$monitor_is_ability_rest = false;

// Uhm, it is defined after the filter... bah...
//if (defined('REST_REQUEST') && REST_REQUEST) {
//}
// Attempt to track how the ability is invoked
add_filter('rest_pre_dispatch', function ($value, $server, WP_REST_Request $request) {
    global $monitor_is_ability_rest;
    if (str_starts_with($request->get_route(), '/wp/v2/abilities/')) {
        $monitor_is_ability_rest = true;
    }
    return $value;
}, 0, 3);

// Intercept abilities

add_action('before_execute_ability', function ($name) {
    global $wpdb, $monitor_is_ability_rest;

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

    $method = $monitor_is_ability_rest ? 'rest' : 'php';
    $user_id = is_user_logged_in() ? get_current_user_id() : 0;

    $wpdb->insert($wpdb->prefix . 'monitor_abilities', ['user_id' => $user_id, 'method' => $method, 'name' => $name, 'context' => $context]);
});

// Scheduler activation monitor

if (defined('DOING_CRON') && DOING_CRON) {
    $context = '';
    if (defined('WP_CLI') && WP_CLI) {
        $context = 'cli';
    }
    $wpdb->insert($wpdb->prefix . 'monitor_scheduler', ['type' => 'start', 'ip' => '', 'context' => $context, 'text' => 'Started']);

//    add_action('cron_unschedule_event_error', function ($result, $hook, $v) {
//        /** @var WP_Error $result */
//        //$wpdb->insert($wpdb->prefix . 'monitor_scheduler', ['type'=>'error', 'ip' => '', 'context' => $context]);
//    }, 0, 3);
    // When executing the cron, the unschedule is called for each job before executing it. I use this
    // filter to know which job is starting.
    add_filter('pre_unschedule_event', function ($pre, $timestamp, $hook, $args, $wp_error) {
        global $wpdb;

        $delay = time() - $timestamp;
        $context = '';
        $wpdb->insert($wpdb->prefix . 'monitor_scheduler', ['type' => 'job', 'ip' => '', 'text' => 'Executing "' . $hook . '" with a delay of ' . $delay . ' seconds']);

        return $pre;
    }, 0, 5);
}

// Daily cleanup process
function monitor_clean() {
    // TODO
}
