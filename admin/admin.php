<?php

defined('ABSPATH') || exit;

$monitor_version = get_option('monitor_version');
if (MONITOR_VERSION !== $monitor_version) {
    if (WP_DEBUG) {
        error_log('Monitor > Version change');
    }
    include_once __DIR__ . '/activate.php';
    update_option('monitor_version', MONITOR_VERSION, false);
}

function monitor_format_interval($delta) {
    $seconds = $delta % MINUTE_IN_SECONDS;
    $minutes = floor($delta % HOUR_IN_SECONDS / MINUTE_IN_SECONDS);
    $hours = floor($delta % DAY_IN_SECONDS / HOUR_IN_SECONDS);
    $days = floor($delta / DAY_IN_SECONDS);
    return ($days ? $days . ' days, ' : '')
            . ($days || $hours ? $hours . ' hours, ' : '')
            . ($days || $hours || $minutes ? $minutes . ' minutes, ' : '')
            . $seconds . ' seconds';
}

function monitor_get_emails_statistics() {
    return [];
}

add_action('admin_init', function () {
    // That old compatibility script when the emojii/unicode symbols were not correctly managed by nrowsers...
    remove_action('admin_print_scripts', 'print_emoji_detection_script');
});

add_action('admin_menu', function () {

    add_menu_page('Monitor', 'Monitor', 'administrator', 'monitor', '', 'dashicons-performance', 6);

    add_submenu_page(
            'monitor', 'Settings', 'Settings', 'administrator', 'monitor',
            function () {
                include __DIR__ . '/index.php';
            }
    );

    add_submenu_page(
            'monitor', 'Abilities', 'Abilities', 'administrator', 'monitor-abilities',
            function () {
                include __DIR__ . '/abilities/index.php';
            }
    );

    add_submenu_page(
            'monitor', 'Emails', 'Emails', 'administrator', 'monitor-emails',
            function () {
                include __DIR__ . '/emails/index.php';
            }
    );

    add_submenu_page(
            'monitor', 'Scheduler', 'Scheduler', 'administrator', 'monitor-scheduler',
            function () {
                include __DIR__ . '/scheduler/index.php';
            }
    );

    add_submenu_page(
            'monitor', 'HTTP', 'HTTP', 'administrator', 'monitor-http',
            function () {
                include __DIR__ . '/http/index.php';
            }
    );
});

add_action('wp_ajax_monitor-ability-data', function () {
    global $wpdb;
    check_ajax_referer('monitor-ability-data');
    $id = (int) $_GET['id'];
    $log = $wpdb->get_row($wpdb->prepare("select * from {$wpdb->prefix}monitor_abilities where id=%d limit 1", $id));
    if (!$log) {
        wp_send_json_error();
    } else {
        echo '<h3>Input</h3>';
        echo '<pre style="white-space: normal;">', esc_html($log->input), '</pre>';
        echo '<h3>Output</h3>';
        echo '<pre style="white-space: normal;">', esc_html($log->output), '</pre>';
    }
    die();
});

add_action('wp_ajax_monitor-emails-filters', function () {
    global $wpdb;
    check_ajax_referer('monitor-emails-filters');
    $id = (int) $_GET['id'];
    $log = $wpdb->get_row($wpdb->prepare("select * from {$wpdb->prefix}monitor_emails where id=%d limit 1", $id));
    if (!$log) {
        wp_send_json_error();
    } else {
        echo '<pre style="white-space: normal;">';
        $filters = unserialize($log->filters);
        foreach ($filters as $tag => $functions) {
            echo '<strong>', esc_html($tag), '</strong><br>';
            foreach ($functions ?? [] as $function) {
                echo esc_html($function), '<br>';
            }
            echo '<br>';
        }
    }
    die();
});

add_action('wp_ajax_monitor-scheduler-filters', function () {
    global $wpdb;
    check_ajax_referer('monitor-scheduler-filters');
    $id = (int) $_GET['id'];
    $log = $wpdb->get_row($wpdb->prepare("select * from {$wpdb->prefix}monitor_scheduler where id=%d limit 1", $id));
    if (!$log) {
        wp_send_json_error();
    } else {
        echo '<pre style="white-space: normal;">';
        $filters = unserialize($log->filters);
        foreach ($filters as $tag => $functions) {
            echo '<strong>', esc_html($tag), '</strong><br>';
            foreach ($functions ?? [] as $function) {
                echo esc_html($function), '<br>';
            }
            echo '<br>';
        }
    }
    die();
});

add_action('wp_ajax_monitor-http-args', function () {
    global $wpdb;
    check_ajax_referer('monitor-http-args');
    $id = (int) $_GET['id'];
    $log = $wpdb->get_row($wpdb->prepare("select args from {$wpdb->prefix}monitor_http where id=%d limit 1", $id));
    if (!$log) {
        wp_send_json_error();
    } else {
        echo '<pre style="white-space: normal;">';
        $args = unserialize($log->args);
        foreach ($args as $k => $v) {
            if (is_array($v)) {
                echo '<strong>', esc_html($k), '</strong>:<br>';
                foreach ($v as $k2 => $v2) {
                    echo '&nbsp;&nbsp;&nbsp;', esc_html($k2), ': ', esc_html($v2), '<br>';
                }
            } else {
                echo '<strong>', esc_html($k), '</strong>: ', esc_html($v), '<br>';
            }
        }
    }
    die();
});
