<?php

defined('ABSPATH') || exit;

// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- not relevant

$monitor_version = get_option('monitor_version');
if (SATOLLO_MONITOR_VERSION !== $monitor_version) {
    include_once __DIR__ . '/activate.php';
    update_option('monitor_version', SATOLLO_MONITOR_VERSION, false);
}

/**
 * Formats a time interval.
 *
 * @param int $delta
 * @return string
 */
function monitor_format_interval($delta) {
    $delta = intval($delta);
    $seconds = $delta % MINUTE_IN_SECONDS;
    $minutes = intdiv($delta % HOUR_IN_SECONDS, MINUTE_IN_SECONDS);
    $hours = intdiv($delta % DAY_IN_SECONDS, HOUR_IN_SECONDS);
    $days = intdiv($delta, DAY_IN_SECONDS);
    return ($days ? $days . ' days, ' : '')
            . ($days || $hours ? $hours . ' hours, ' : '')
            . ($days || $hours || $minutes ? $minutes . ' minutes, ' : '')
            . $seconds . ' seconds';
}

add_action('init', function () {
    // Monitor is available only to administrators
    if (!current_user_can('administrator')) {
        return;
    }

    if (isset($_GET['page']) && $_GET['page'] === 'monitor') {
        add_action('admin_enqueue_scripts', function ($hook) {
            //wp_enqueue_script('monitor-plotly', 'https://cdn.plot.ly/plotly-3.1.0.min.js');
            wp_enqueue_script('monitor-plotly', plugin_dir_url(__FILE__) . '/assets/plotly-3.3.0.min.js', ['jquery'], SATOLLO_MONITOR_VERSION, true);
            wp_enqueue_style('monitor', plugin_dir_url(__FILE__) . '/assets/admin.css', [], SATOLLO_MONITOR_VERSION);
        });
    }

    add_action('admin_menu', function () {

        add_management_page('Monitor', 'Monitor', 'administrator', 'monitor', function () {
            $section = $_GET['section'] ?? '';
            $subpage = $_GET['subpage'] ?? '';
            switch ($section) {
                case 'settings':
                    include __DIR__ . '/settings.php';
                    break;
                case 'abilities':
                    switch ($subpage) {
                        case 'logs':
                            include __DIR__ . '/abilities/logs.php';
                            break;
                        default:
                            include __DIR__ . '/abilities/index.php';
                    }
                    break;
                case 'rest':
                    switch ($subpage) {
                        case 'logs':
                            include __DIR__ . '/rest/logs.php';
                            break;
                        default:
                            include __DIR__ . '/rest/index.php';
                    }
                    break;
                case 'aiclient':
                    switch ($subpage) {
                        case 'logs':
                            include __DIR__ . '/aiclient/logs.php';
                            break;
                        default:
                            include __DIR__ . '/aiclient/index.php';
                    }
                    break;
                case 'http':
                    switch ($subpage) {
                        case 'logs':
                            include __DIR__ . '/http/logs.php';
                            break;
                        default:
                            include __DIR__ . '/http/index.php';
                    }
                    break;
                case 'php':
                    switch ($subpage) {
                        case 'logs':
                            include __DIR__ . '/php/logs.php';
                            break;
                        default:
                            include __DIR__ . '/php/index.php';
                    }
                    break;
                case 'scheduler':
                    include __DIR__ . '/scheduler/index.php';
                    break;
                case 'emails':
                    switch ($subpage) {
                        case 'logs':
                            include __DIR__ . '/emails/logs.php';
                            break;
                        default:
                            include __DIR__ . '/emails/index.php';
                    }
                    break;
                case 'users':
                    include __DIR__ . '/users/index.php';
                    break;
                default:
                    include __DIR__ . '/index.php';
            }
        }, 6);
    });

    if (defined('DOING_AJAX') && DOING_AJAX) {
        require_once __DIR__ . '/admin-ajax.php';
    }
});
