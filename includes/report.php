<?php

namespace Satollo\Monitor;

defined('ABSPATH') || exit;

add_action('monitor_report', 'monitor_report');

function monitor_report() {
    global $wpdb;

    // TODO: Add rate limit (for misconfigured blogs)

    $message = '';

    $now = new \DateTime();
    $now->modify('-1 day');

// TODO: Optimize with a single query
    $sent = (int) $wpdb->get_var($wpdb->prepare("select count(*) from {$wpdb->prefix}monitor_emails where date(created)=%s", $now->format('Y-m-d')));
    $success = $wpdb->get_var($wpdb->prepare("select count(*) from {$wpdb->prefix}monitor_emails where date(created)=%s", $now->format('Y-m-d')));
    $failed = $wpdb->get_var($wpdb->prepare("select count(*) from {$wpdb->prefix}monitor_emails where date(created)=%s", $now->format('Y-m-d')));

    $message .= "Hi,\n";
    $message .= "here your report for the last day\n\n";

    $message .= "## Emails\n";
    $message .= '* Emails processed: ' . $sent . "\n";
    $message .= '* Emails sent with success: ' . $success . "\n";
    $message .= '* Email not sent due to errors: ' . $failed . "\n";
    $message .= "\n";

    // AI Client
    $total = (int) $wpdb->get_var($wpdb->prepare("select count(*) from {$wpdb->prefix}monitor_aiclient where date(created)=%s", $now->format('Y-m-d')));

    $message .= "## AI Client\n";
    $message .= '* Calls: ' . $total . "\n";
    $message .= "\n";

    // HTTP
    $total = (int) $wpdb->get_var($wpdb->prepare("select count(*) from {$wpdb->prefix}monitor_http where date(created)=%s", $now->format('Y-m-d')));

    $message .= "## HTTP\n";
    $message .= '* HTTP calls: ' . $total . "\n";
    $message .= "\n";

    $message .= "\n---\n";
    $message .= "The Monitor plugin for WordPress\n";
    $message .= "https://www.satollo.net/plugins/monitor";

    wp_mail(get_option('admin_email'), 'Daily monitor report - ' . $now->format('Y-m-d'), $message);
}
