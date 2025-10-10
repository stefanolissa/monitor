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
 * Update URI: satollo_monitor
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

function monitor_get_context() {
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
    return $context;
}

/**
 * Emails monitoring
 */

add_filter('wp_mail', function ($atts) {
    global $wpdb;

    $context = monitor_get_context();

    $user_id = is_user_logged_in() ? get_current_user_id() : 0;
    $wpdb->insert($wpdb->prefix . 'monitor_emails', ['user_id' => $user_id, 'subject' => $atts['subject'], 'to' => $atts['to'],
        'context' => $context]);
    return $atts;
}, 0);

$monitor_ability_method = 'php';

/**
 * Abilities monitoring
 */

// Uhm, it is defined after the filter... bah...
//if (defined('REST_REQUEST') && REST_REQUEST) {
//}

// Attempt to track how the ability is invoked
add_filter('rest_pre_dispatch', function ($value, $server, WP_REST_Request $request) {
    global $monitor_ability_method;
    if (str_starts_with($request->get_route(), '/wp/v2/abilities/')) {
        $monitor_ability_method = 'rest';
    }
    return $value;
}, 0, 3);

add_action('after_execute_ability', function ($name, $input, $result) {
    global $wpdb, $monitor_ability_method;

    //die('xxxx');

    // It could not be useful, anyway...
    $context = monitor_get_context();

    $user_id = is_user_logged_in() ? get_current_user_id() : 0;
    $input = wp_json_encode($input);
    $result = wp_json_encode($result);
    $wpdb->insert($wpdb->prefix . 'monitor_abilities', ['user_id' => $user_id,
        'method' => $monitor_ability_method, 'name' => $name, 'context' => $context,
        'input' => $input, 'output' => $result]);
    if ($wpdb->last_error) {
        error_log($wpdb->last_error);
    }
}, 0, 3);

/**
 * Scheduler monitoring
 */

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

        // TODO: add delay as db field
        $delay = time() - $timestamp;
        $context = '';
        $wpdb->insert($wpdb->prefix . 'monitor_scheduler', ['type' => 'job', 'ip' => '', 'text' => 'Executing "' . $hook . '" with a delay of ' . $delay . ' seconds']);

        return $pre;
    }, 0, 5);
}

// Daily cleanup process
function monitor_clean_logs() {
    // TODO
}

/**
 * Update
 */

//add_filter('update_plugins_satollo_monitor', function ($update, $plugin_data, $plugin_file, $locales) {
//    error_log(print_r($update, true));
//    error_log(print_r($plugin_data, true));
//    error_log(print_r($plugin_file, true));
//
//    $update = [
//        'version' => '1.0.1',
//        'slug' => 'monitor',
//        'url' => 'https://www.satollo.net/plugins/monitor',
//        'package' => 'https://medinskdjhd'
//    ];
//    return $update;
//}, 0, 4);