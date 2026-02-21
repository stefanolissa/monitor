<?php

// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- not relevant

/**
 * Plugin Name: Monitor
 * Description: Records and displays WP events: abilities, scheduler, http, REST API, emails, ...
 * Version: 0.1.7
 * Author: satollo
 * Author URI: https://www.satollo.net
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: monitor
 * Requires at least: 6.9
 * Requires PHP: 8.1
 * Plugin URI: https://www.satollo.net/plugins/monitor
 * Update URI: satollo-monitor
 */

defined('ABSPATH') || exit;

define('MONITOR_VERSION', '0.1.7');

/** @var wpdb $wpdb */
register_deactivation_hook(__FILE__, function () {
    delete_option('monitor_emails_hooks');
    delete_option('monitor_scheduler_hooks');
    delete_option('monitor_update_data');
    wp_unschedule_hook('monitor');
});

$monitor_settings = get_option('monitor_settings');

if (is_admin()) {
    require_once __DIR__ . '/admin/admin.php';
}

/**
 * Return the context of the current event.
 *
 * @return string
 */
function monitor_get_context() {
    $context = 'frontend';
    if (defined('WP_CLI') && WP_CLI) {
        $context = 'cli';
    } elseif (defined('DOING_CRON') && DOING_CRON) {
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
if (!empty($monitor_settings['emails'])) {
    $monitor_emails_log_id = 0; // The comple log record is built in separated phases
    $monitor_emails_log_start = 0; // To register the time required to complete the email sending

    add_filter('wp_mail', function ($atts) {
        global $wpdb, $monitor_emails_log_id, $monitor_emails_log_start;

        $context = monitor_get_context();

        $user_id = is_user_logged_in() ? get_current_user_id() : 0;
        $hooks = [
            'wp_mail' => monitor_get_hook_functions('wp_mail'),
            'pre_wp_mail' => monitor_get_hook_functions('pre_wp_mail'),
            'wp_mail_from' => monitor_get_hook_functions('wp_mail_from'),
            'wp_mail_from_name' => monitor_get_hook_functions('wp_mail_from_name'),
            'wp_mail_failed' => monitor_get_hook_functions('wp_mail_failed'),
            'wp_mail_content_type' => monitor_get_hook_functions('wp_mail_content_type'),
            'wp_mail_charset' => monitor_get_hook_functions('wp_mail_charset'),
            'phpmailer_init' => monitor_get_hook_functions('phpmailer_init'),
            'wp_mail_succeeded' => monitor_get_hook_functions('wp_mail_succeeded'),
        ];
        $wpdb->insert($wpdb->prefix . 'monitor_emails', ['user_id' => $user_id, 'subject' => $atts['subject'], 'to' => $atts['to'],
            'context' => $context, 'filters' => serialize($hooks)]);
        $monitor_emails_log_id = $wpdb->insert_id;
        $monitor_emails_log_start = microtime(true);
        return $atts;
    }, 9999);

    add_action('wp_mail_succeeded', function () {
        global $wpdb, $monitor_emails_log_id, $monitor_emails_log_start;
        $wpdb->update($wpdb->prefix . 'monitor_emails',
                ['duration' => microtime(true) - $monitor_emails_log_start, 'status' => 0],
                ['id' => $monitor_emails_log_id]);
        $monitor_emails_log_id = 0;
    }, 0);

    add_action('wp_mail_failed', function ($wp_error) {
        global $wpdb, $monitor_emails_log_id, $monitor_emails_log_start;
        $wpdb->update($wpdb->prefix . 'monitor_emails',
                ['duration' => microtime(true) - $monitor_emails_log_start, 'status' => 1, 'text' => $wp_error->get_error_message()],
                ['id' => $monitor_emails_log_id]);
        $monitor_emails_log_id = 0;
    }, 0);
}


/**
 * Abilities monitoring
 */
if (!empty($monitor_settings['abilities'])) {
    $monitor_ability_method = 'php';

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
}



/**
 * Scheduler monitoring
 */
if (!empty($monitor_settings['scheduler'])) {
    $monitor_scheduler_log_id = 0; // The current log line to be updated
    $monitor_scheduler_log_jobs = []; // Records the executed jobs

    if (defined('DOING_CRON') && DOING_CRON) {
        $context = monitor_get_context();

        update_option('monitor_scheduler_last_run', time(), false);

        $ip = wp_strip_all_tags($_SERVER['REMOTE_ADDR'] ?? '');

        $ready_jobs = [];
        foreach (wp_get_ready_cron_jobs() as $ts => $hooks) {
            foreach ($hooks as $hook => $keys) {
                $ready_jobs[] = ['hook' => $hook];
            }
        }

        $wpdb->insert($wpdb->prefix . 'monitor_scheduler', ['ip' => $ip,
            'context' => $context, 'ready_jobs' => serialize($ready_jobs), 'executed_jobs' => serialize([])]);

        $monitor_scheduler_log_id = $wpdb->insert_id;

        // Capture all the relevant active filters
        add_action('wp_loaded', function () {
            global $wpdb, $monitor_scheduler_log_id;
            $hooks = [
                'pre_unschedule_event' => monitor_get_hook_functions('pre_unschedule_event'),
                'pre_reschedule_event' => monitor_get_hook_functions('pre_reschedule_event'),
                'cron_request' => monitor_get_hook_functions('cron_request'),
                'http_request_args' => monitor_get_hook_functions('http_request_args'),
                'pre_http_request' => monitor_get_hook_functions('pre_http_request'),
                'pre_get_ready_cron_jobs' => monitor_get_hook_functions('pre_get_ready_cron_jobs'),
                'schedule_event' => monitor_get_hook_functions('schedule_event'),
            ];
            $wpdb->update($wpdb->prefix . 'monitor_scheduler', ['filters' => serialize($hooks)], ['id' => $monitor_scheduler_log_id]);
        });

//    add_action('cron_unschedule_event_error', function ($result, $hook, $v) {
//        /** @var WP_Error $result */
//        //$wpdb->insert($wpdb->prefix . 'monitor_scheduler', ['type'=>'error', 'ip' => '', 'context' => $context]);
//    }, 0, 3);
        // When executing the cron, the unschedule is called for each job BEFORE executing it. I use this
        // filter to know which job is starting.
        add_filter('pre_unschedule_event', function ($pre, $timestamp, $hook, $args, $wp_error) {
            global $wpdb, $monitor_scheduler_log_jobs, $monitor_scheduler_log_id;

            $delay = time() - $timestamp;
            $monitor_scheduler_log_jobs[] = ['hook' => $hook, 'delay' => $delay];
            $wpdb->update($wpdb->prefix . 'monitor_scheduler', ['executed_jobs' => serialize($monitor_scheduler_log_jobs)], ['id' => $monitor_scheduler_log_id]);

            return $pre;
        }, 0, 5);

        add_action('wp_loaded', function () {
            $hooks = [
                'updated' => time(),
                'pre_unschedule_event' => monitor_get_hook_functions('pre_unschedule_event'),
                'pre_reschedule_event' => monitor_get_hook_functions('pre_reschedule_event'),
                'cron_request' => monitor_get_hook_functions('cron_request'),
                'http_request_args' => monitor_get_hook_functions('http_request_args'),
                'pre_http_request' => monitor_get_hook_functions('pre_http_request'),
                'pre_get_ready_cron_jobs' => monitor_get_hook_functions('pre_get_ready_cron_jobs'),
                'schedule_event' => monitor_get_hook_functions('schedule_event'),
            ];
            update_option('monitor_scheduler_hooks', $hooks, false);
        }, 9999);
    }
}

if (!empty($monitor_settings['http'])) {
    $monitor_http_log_id = 0;
    $monitor_http_log_start = 0;

    // See class-wp-http.php
    add_filter('pre_http_request', function ($value, $args, $url) {
        global $wpdb, $monitor_http_log_id, $monitor_http_log_start, $monitor_settings;

        if (empty($monitor_settings['http_wpcron']) && strpos($url, '/wp-cron.php') !== false) {
            return $value;
        }

        $args['url'] = $url; // Save the full URL
        $url = substr($url, 0, min(255, strlen($url)));

        $r = $wpdb->insert($wpdb->prefix . 'monitor_http', ['url' => $url, 'method' => $args['method'],
            'context' => monitor_get_context(),
            'args' => serialize($args)]);
        if (!$r) {
            error_log($wpdb->last_error);
        }

        $monitor_http_log_id = $wpdb->insert_id;

        $monitor_http_log_start = microtime(true);

        return $value;
    }, 9999, 3);

    // See class-wp-http.php
    add_filter('http_api_debug', function ($response) {
        global $wpdb, $monitor_http_log_id, $monitor_http_log_start;

        if (!$monitor_http_log_id) {
            return $response;
        }

        if (is_wp_error($response)) {
            $wpdb->update($wpdb->prefix . 'monitor_http',
                    ['status' => 1, 'text' => $response->get_error_message()],
                    ['id' => $monitor_http_log_id]);
        } else {
            $wpdb->update($wpdb->prefix . 'monitor_http',
                    ['status' => 0, 'duration' => microtime(true) - $monitor_http_log_start,
                        'code' => wp_remote_retrieve_response_code($response)],
                    ['id' => $monitor_http_log_id]);
        }
        $monitor_http_log_id = 0;
        return $response;
    }, 1, 9999);

//    add_filter('http_response', function ($response) {
//        global $wpdb, $monitor_http_log_id, $monitor_http_log_start;
//        return $response;
//    }, 1, 9999);
}

if (!empty($monitor_settings['rest'])) {
    add_filter('rest_post_dispatch', function ($result, $server, $request) {
        global $wpdb, $monitor_settings;
        $route = $request->get_route();
        if (!isset($monitor_settings['rest_wpv2']) && str_starts_with($route, '/wp/v2')) {
            return $result;
        }

        // Is it possible with the latest WP versions?
        if (is_wp_error($result)) {
            /** @var WP_Error $result */
            $code = $result->get_error_data();
        } else {
            /** @var WP_REST_Response $result */
            $code = $result->get_status();
        }

        /** @var WP_REST_Request $request */
        $r = $wpdb->insert($wpdb->prefix . 'monitor_rest', ['route' => $request->get_route(), 'method' => $request->get_method(),
            'params' => serialize($request->get_params()), 'code' => $code]);

        return $result;
    }, 99, 3);
}

/**
 * Returns the list of functions attached to a specific hook.
 *
 * @global array $wp_filter
 * @param string $tag
 * @return array
 */
function monitor_get_hook_functions($tag) {
    global $wp_filter;
    if (!isset($wp_filter[$tag])) {
        return [];
    }

    $list = [];

    foreach ($wp_filter[$tag]->callbacks as $priority => $functions) {

        foreach ($functions as $function) {
            $b = '';
            $b .= '[' . $priority . '] ';
            if (is_array($function['function'])) {
                if (is_object($function['function'][0])) {
                    $b .= get_class($function['function'][0]) . '::' . $function['function'][1];
                } else {
                    $b .= $function['function'][0] . '::' . $function['function'][1];
                }
            } else {
                if (is_object($function['function'])) {
                    $r = new ReflectionFunction($function['function']);
                    //$b .= get_class($fn->getClosureThis()) . '(closure)';
                    if ($r) {
                        $b .= 'Closure (' . $r->getFileName() . ')';
                    } else {
                        $b .= 'Closure';
                    }
                } else {
                    $b .= $function['function'];
                }
            }
            $list[] = $b;
        }
    }

    return $list;
}

// Daily cleanup process
add_action('monitor_clean_logs', 'monitor_clean_logs');

function monitor_clean_logs() {
    global $wpdb;
    $settings = get_option('monitor');
    $days = (int) ($settings['log_days'] ?? 30);
    $days = max($days, 1);
    $wpdb->query($wpdb->prepare("delete from `{$wpdb->prefix}monitor_abilities` where created < date_sub(now(), interval %d day)", $days));
    $wpdb->query($wpdb->prepare("delete from `{$wpdb->prefix}monitor_emails` where created < date_sub(now(), interval %d day)", $days));
    $wpdb->query($wpdb->prepare("delete from `{$wpdb->prefix}monitor_scheduler` where created < date_sub(now(), interval %d day)", $days));
    $wpdb->query($wpdb->prepare("delete from `{$wpdb->prefix}monitor_http` where created < date_sub(now(), interval %d day)", $days));
}

// Only for alpha/beta versions
if (is_admin() || defined('DOING_CRON') && DOING_CRON && file_exists(__DIR__ . '/includes/repo.php')) {
    require_once __DIR__ . '/includes/repo.php';
}
