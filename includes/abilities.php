<?php

namespace Satollo\Monitor;

defined('ABSPATH') || exit;

global $monitor_settings;

$mcp_meta = [
    'public' => isset($monitor_settings['mcp'])
];

$r = wp_register_ability('monitor/emails-statistics',
        [
            'label' => 'Statistics of sent emails',
            'description' => 'Statistics of sent emails',
            'category' => 'monitor',
            'input_schema' => [],
            'output_schema' => [
                'type' => 'object',
                'properties' => []
            ],
            'permission_callback' => function () {
                return current_user_can('administrator');
            },
            'meta' => [
                'mcp' => $mcp_meta,
                'annotations' => [
                    'readonly' => true
                ],
                'show_in_rest' => true,
            //'instructions' => 'Response in JSON format, make it verbose'
            ],
            'execute_callback' => function ($input = null) {
                global $wpdb;
                $sent = (int) $wpdb->get_var("select count(*) from {$wpdb->prefix}monitor_emails");
                $success = $wpdb->get_var("select count(*) from {$wpdb->prefix}monitor_emails where status=0");
                $failed = $wpdb->get_var("select count(*) from {$wpdb->prefix}monitor_emails where status=1");
                $avg_duration = $wpdb->get_var("select avg(duration) from {$wpdb->prefix}monitor_emails where status=1");
                return
                [
                    'total emails sent' => $sent,
                    'total emails sent with success' => $success,
                    'total emails sent which failed' => $failed,
                    'average time in seconds to send an email' => $avg_duration
                ];
            },
        ]
);

//error_log(print_r($r, true));

$r = wp_register_ability('monitor/emails-by-day',
        [
            'label' => 'Number of email sent by day',
            'description' => 'Number of email sent by day. Date format y-m-d.',
            'category' => 'monitor',
            'input_schema' => [],
            'output_schema' => [],
            'permission_callback' => function () {
                return current_user_can('administrator');
            },
            'meta' => [
                'mcp' => $mcp_meta,
                'annotations' => [
                    'readonly' => true
                ],
                'show_in_rest' => true,
            //'instructions' => 'Response in JSON format, make it verbose'
            ],
            'execute_callback' => function ($input = null) {
                global $wpdb;
                $sent_per_day = $wpdb->get_results("select date(created) as date, count(*) as total from {$wpdb->prefix}monitor_emails where created > DATE_SUB(NOW(), INTERVAL 30 DAY) group by date(created) order by date(created) asc");

                return $sent_per_day;
            },
        ]
);

$r = wp_register_ability('monitor/http-statistics',
        [
            'label' => 'Statistics of the HTTP calls',
            'description' => 'Statistics of the HTTP calls made by WP, plugins and the theme',
            'category' => 'monitor',
            'input_schema' => [],
            'output_schema' => [
                'type' => 'object',
                'properties' => []
            ],
            'permission_callback' => function () {
                return current_user_can('administrator');
            },
            'meta' => [
                'mcp' => $mcp_meta,
                'annotations' => [
                    'readonly' => true
                ],
                'show_in_rest' => true,
            //'instructions' => 'Response in JSON format, make it verbose'
            ],
            'execute_callback' => function ($input = null) {
                global $wpdb;
                $total = (int) $wpdb->get_var("select count(*) from {$wpdb->prefix}monitor_http");

                $duration = $wpdb->get_row("select sum(duration) as total, avg(duration) as average from {$wpdb->prefix}monitor_http");
                return
                [
                    'total http calls' => $total,
                    'average time in seconds to complete a call' => $duration->average,
                    'average time consumed in HTTP calls' => $duration->total,
                ];
            },
        ]
);

$r = wp_register_ability('monitor/ai-client-statistics',
        [
            'label' => 'Statistics of the internal AI client',
            'description' => 'Statistics of the internal AI client: number of calls, tokens consumed',
            'category' => 'monitor',
            'input_schema' => [],
            'output_schema' => [
                'type' => 'object',
                'properties' => []
            ],
            'permission_callback' => function () {
                return current_user_can('administrator');
            },
            'meta' => [
                'mcp' => $mcp_meta,
                'annotations' => [
                    'readonly' => true
                ],
                'show_in_rest' => true,
                'instructions' => 'Display values using the English math notation'
            ],
            'execute_callback' => function ($input = null) {
                global $wpdb;
                $total = (int) $wpdb->get_var("select count(*) from {$wpdb->prefix}monitor_aiclient");

                $data = $wpdb->get_row("select sum(tokens) as total_tokens, avg(tokens) as avg_tokens, sum(duration) as total_duration, avg(duration) as avg_duration from {$wpdb->prefix}monitor_aiclient");
                return
                [
                    'total ai client calls to ai providers' => $total,
                    'average time in seconds to complete a call' => $data->avg_duration,
                    'total time consumed in AI client calls' => $data->total_duration,
                    'total consumed tokens' => (int) $data->total_tokens,
                    'average consumed tokens per call' => (int) $data->avg_tokens
                ];
            },
        ]
);
