<?php

defined('ABSPATH') || exit;

// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- not relevant
// Returns the details about an ability call
add_action('wp_ajax_monitor-ability-data', function () {
    global $wpdb;
    check_ajax_referer('monitor-ability-data');
    // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- not necessary
    $id = (int) ($_GET['id'] ?? 0);
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
        echo '</pre>';
    }
    die();
});

add_action('wp_ajax_monitor-users-role', function () {
    global $wpdb;

    check_ajax_referer('monitor-users-role');

    $id = sanitize_key($_GET['id']);
    $role = wp_roles()->get_role($id);

    if (!$role) {
        wp_send_json_error();
    } else {
        echo '<pre>', esc_html(wp_json_encode($role, JSON_PRETTY_PRINT)), '</pre>';
    }
    die();
});

add_action('wp_ajax_monitor-scheduler-filters', function () {
    global $wpdb;
    check_ajax_referer('monitor-scheduler-filters');
    // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- not necessary
    $id = (int) ($_GET['id'] ?? 0);
    $log = $wpdb->get_row($wpdb->prepare("select * from {$wpdb->prefix}monitor_scheduler where id=%d limit 1", $id));
    if (!$log) {
        wp_send_json_error();
    } else {
        echo esc_html('Get lock: ' . $log->get_lock ?: '-') . '<br>' .
        esc_html('Transient lock: ' . $log->transient_lock ?: '-') . '<br>';
        echo '<pre style="white-space: normal;">';
        $filters = unserialize($log->filters);
        foreach ($filters as $tag => $functions) {
            echo '<strong>', esc_html($tag), '</strong><br>';
            foreach ($functions ?? [] as $function) {
                echo esc_html($function), '<br>';
            }
            echo '<br>';
        }
        echo '</pre>';
    }
    die();
});

// Returns the details about a HTTP call
add_action('wp_ajax_monitor-http-args', function () {
    global $wpdb;
    check_ajax_referer('monitor-http-args');
    // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- not necessary
    $id = (int) ($_GET['id'] ?? 0);
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
        echo '</pre>';
    }
    die();
});

// Returns the details about a REST call
add_action('wp_ajax_monitor-rest-params', function () {
    global $wpdb;
    check_ajax_referer('monitor-rest-params');
    // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- not necessary
    $id = (int) ($_GET['id'] ?? 0);
    $log = $wpdb->get_row($wpdb->prepare("select params from {$wpdb->prefix}monitor_rest where id=%d limit 1", $id));
    if (!$log) {
        wp_send_json_error();
    } else {
        echo '<pre style="white-space: normal;">';
        $args = unserialize($log->params);
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
        echo '</pre>';
    }
    die();
});
