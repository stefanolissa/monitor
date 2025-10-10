<?php

defined('ABSPATH') || exit;

add_action('admin_menu', function () {

    add_menu_page(
            'Monitor', 'Monitor', 'administrator', 'monitor',
            function () {
                include __DIR__ . '/index.php';
            },
            'dashicons-performance', 6
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

add_action('abilities_api_init', function () {

    $r = wp_register_ability('monitor/overview',
            [
                'label' => 'Return an overview of the site activities',
                'description' => 'Return an overview of the site activities',
                'input_schema' => [
                    'type' => 'object',
                    'properties' => [],
                    'additionalProperties' => false,
                ],
                'output_schema' => [
                    'type' => 'object',
                    'properties' => [
                        'sent_emails' => [
                            'type' => 'integer',
                            'description' => 'Number of sent emails',
                            'minLength' => 0
                        ],
                        'scheduler_statistics' => [
                            'type' => 'string',
                            'description' => 'Details of the scheduler',
                            'minLength' => 0
                        ],
                        'abilities_statistics' => [
                            'type' => 'string',
                            'description' => 'Details of the abilities',
                            'minLength' => 0
                        ],
                    ],
                ],
                'execute_callback' => function () {
                    return
                    [
                        'sent_emails' => '35',
                        'scheduler_statistics' => 'Interval between runs: 340 seconds',
                        'abilities_statistics' => 'Abilities invoked 450 times'
                    ];
                },
                'permission_callback' => function () {
                    return current_user_can('administrator');
                },
                'meta' => [
                    'category' => 'test',
                ],
            ]
    );
});
