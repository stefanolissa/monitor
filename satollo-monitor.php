<?php

// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- not relevant

/**
 * Plugin Name: Monitor: Scheduler, Emails, API, HTTP and more
 * Description: Records and displays WP events: abilities calls, scheduler job executions, http calls, emails, and so on
 * Version: 1.0.5
 * Author: Stefano Lissa
 * Author URI: https://www.satollo.net
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: satollo-monitor
 * Requires at least: 6.9
 * Requires PHP: 8.1
 * Plugin URI: https://www.satollo.net/plugins/monitor
 */
defined('ABSPATH') || exit;

define('SATOLLO_MONITOR_VERSION', '1.0.5');

/** @var wpdb $wpdb */
register_deactivation_hook(__FILE__, function () {
    delete_option('monitor_emails_hooks');
    delete_option('monitor_scheduler_hooks');
    wp_unschedule_hook('monitor');
});

$monitor_settings = get_option('monitor_settings');

if (is_admin()) {
    require_once __DIR__ . '/admin/admin.php';
}

// Selective loading
// When used with the MCP Adapter, they hook should be always registsred
//if (is_admin() || defined('REST_REQUEST') && REST_REQUEST) {
add_action('wp_abilities_api_categories_init', function () {
    wp_register_ability_category('monitor', [
        'label' => 'Monitor',
        'description' => 'Abilities to get data from the Monitor plugin',
    ]);
});

add_action('wp_abilities_api_init', function () {
    require_once __DIR__ . '/includes/abilities.php';
});
//}

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

        $sent = (int) get_option('monitor_emails_sent_count');
        update_option('monitor_emails_sent_count', $sent + 1, false);

        return $atts;
    }, 9999);

    add_action('wp_mail_succeeded', function () {
        global $wpdb, $monitor_emails_log_id, $monitor_emails_log_start, $phpmailer;

        /** @var \PHPMailer\PHPMailer\PHPMailer $phpmailer */
        $wpdb->update($wpdb->prefix . 'monitor_emails',
                ['duration' => microtime(true) - $monitor_emails_log_start, 'status' => 0, 'host' => $phpmailer ? $phpmailer->Host ?? '' : ''],
                ['id' => $monitor_emails_log_id]);
        $monitor_emails_log_id = 0;
    }, 0);

    add_action('wp_mail_failed', function ($wp_error) {

        global $wpdb, $monitor_emails_log_id, $monitor_emails_log_start, $phpmailer;

        /** @var \PHPMailer\PHPMailer\PHPMailer $phpmailer */
        /** @var WP_Error $wp_error */
        // The error information inside phpmailer is far more accurate, this is a "bug" oh phpmailer not
        // reporting the whole error message inside the exception
        $wpdb->update($wpdb->prefix . 'monitor_emails',
                [
                    'duration' => microtime(true) - $monitor_emails_log_start,
                    'status' => 1,
                    'text' => $phpmailer ? $phpmailer->ErrorInfo : $wp_error->get_error_message(),
                    'host' => $phpmailer ? $phpmailer->Host ?? '' : ''
                ],
                ['id' => $monitor_emails_log_id]);
        $monitor_emails_log_id = 0;

        $failed = (int) get_option('monitor_emails_failed_count');
        update_option('monitor_emails_failed_count', $failed + 1, false);
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

    add_action('wp_after_execute_ability', function ($name, $input, $result) {
        global $wpdb, $monitor_ability_method;

        // It could not be useful, anyway...
        $context = monitor_get_context();

        $user_id = is_user_logged_in() ? get_current_user_id() : 0;
        $input = wp_json_encode($input);
        $result = wp_json_encode($result);
        $wpdb->insert($wpdb->prefix . 'monitor_abilities', ['user_id' => $user_id,
            'method' => $monitor_ability_method, 'name' => $name, 'context' => $context,
            'input' => $input, 'output' => $result]);
    }, 0, 3);
}


/**
 * Scheduler monitoring
 */
if (!empty($monitor_settings['scheduler'])) {
    $monitor_scheduler_log_id = 0; // The current log line to be updated
    $monitor_scheduler_log_jobs = []; // Records the executed jobs
    add_action('init', function () {
        global $monitor_scheduler_log_id, $monitor_scheduler_log_jobs, $wpdb;

        if (defined('DOING_CRON') && DOING_CRON) {
            $context = monitor_get_context();
            $monitor_doing_cron_transient = get_transient('doing_cron');

            update_option('monitor_scheduler_last_run', time(), false);

            $monitor_ip = wp_strip_all_tags(wp_unslash($_SERVER['REMOTE_ADDR'] ?? ''));

            $ready_jobs = [];
            foreach (wp_get_ready_cron_jobs() as $ts => $hooks) {
                foreach ($hooks as $hook => $keys) {
                    $ready_jobs[] = ['hook' => $hook];
                }
            }
            $monitor_note = '';
            if (!$ready_jobs) {
                $monitor_note = 'No jobs, early stop';
            } else {
                $monitor_gmt_time = microtime(true);
                if (empty($_GET['doing_wp_cron'])) {
                    // Called from external script/job. Try setting a lock.
                    if ($monitor_doing_cron_transient && ( $monitor_doing_cron_transient + WP_CRON_LOCK_TIMEOUT > $monitor_gmt_time )) {
                        $monitor_note = 'Lock active from less than ' . WP_CRON_LOCK_TIMEOUT . ' seconds, early stop';
                    }
                } else {
                    $monitor_doing_wp_cron = sprintf('%.22F', microtime(true));
                    if ($monitor_doing_cron_transient !== $_GET['doing_wp_cron']) {
                        $monitor_note = 'Get lock (' . $_GET['doing_wp_cron'] . ') does not match transient lock (' . $monitor_doing_cron_transient . '), early stop';
                    }
                }
            }
            $wpdb->insert($wpdb->prefix . 'monitor_scheduler', ['ip' => $monitor_ip,
                'context' => $context,
                'get_lock' => $_GET['doing_wp_cron'] ?? '',
                'transient_lock' => (string) $monitor_doing_cron_transient,
                'note' => $monitor_note, 'ready_jobs' => serialize($ready_jobs), 'executed_jobs' => serialize([])]);

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
    });
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
}

if (!empty($monitor_settings['php'])) {
    set_error_handler(function ($errno, $errstr, $errfile, $errline) {
        global $wpdb;

        try {
            if ($errno === E_NOTICE) {
                return false;
            }

            $r = $wpdb->insert($wpdb->prefix . 'monitor_php', ['context' => monitor_get_context(),
                'code' => $errno, 'message' => substr($errstr, 0, 1024),
                'file' => $errfile,
                'line' => $errline]);
        } catch (Exception $e) {

        } finally {
            return false;
        }
    });

    register_shutdown_function(function () {
        global $wpdb;

        try {
            $error = error_get_last();
            if (!$error || empty($error['type'])) {
                return;
            }

            // Same errors intercepted by WP
            if (in_array($error['type'], [E_ERROR, E_PARSE, E_USER_ERROR, E_COMPILE_ERROR, E_RECOVERABLE_ERROR])) {

                $r = $wpdb->insert($wpdb->prefix . 'monitor_php', ['context' => monitor_get_context(),
                    'code' => $error['type'], 'message' => substr($error['message'] ?? '', 0, 1024),
                    'file' => $error['file'] ?? '',
                    'line' => $error['line'] ?? 0]);
            }
        } catch (Exception $e) {

        }
    });
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

if (!empty($monitor_settings['aiclient'])) {
    global $monitor_aiclient_log_start;
    add_action('wp_ai_client_before_generate_result', function ($event) {
        global $monitor_aiclient_log_start;
        $monitor_aiclient_log_start = microtime(true);
    });

    add_action('wp_ai_client_after_generate_result', function ($event) {
        global $wpdb, $monitor_settings, $monitor_aiclient_log_start;
        /** @var WordPress\AiClient\Events\AfterGenerateResultEvent $event */
        $context = monitor_get_context();

        $tokens = $event->getResult()->getTokenUsage()->getTotalTokens();
        $model = $event->getModel()->metadata()->getName() ?? '';
        $provider = $event->getModel()->providerMetadata()->getName() ?? '';

        $wpdb->insert($wpdb->prefix . 'monitor_aiclient', [
            'provider' => $provider,
            'model' => $model,
            'tokens' => $tokens,
            'context' => $context,
            'duration' => microtime(true) - $monitor_aiclient_log_start]);
    });
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
    $wpdb->query($wpdb->prepare("delete from `{$wpdb->prefix}monitor_rest` where created < date_sub(now(), interval %d day)", $days));
    $wpdb->query($wpdb->prepare("delete from `{$wpdb->prefix}monitor_php` where created < date_sub(now(), interval %d day)", $days));
    $wpdb->query($wpdb->prepare("delete from `{$wpdb->prefix}monitor_aiclient` where created < date_sub(now(), interval %d day)", $days));
}

if (isset($monitor_settings['report']) && is_admin() || wp_doing_cron()) {
    include_once __DIR__ . '/includes/report.php';
}