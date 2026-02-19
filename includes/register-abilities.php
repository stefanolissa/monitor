<?php

defined('ABSPATH') || exit;

add_action('wp_abilities_api_categories_init', function () {
    wp_register_ability_category('monitor', [
        'label' => 'Monitor',
        'description' => 'Abilities provided by the Monitor plugin',
    ]);
});

add_action('wp_abilities_api_init', function () {

    $r = wp_register_ability('monitor/overview',
            [
                'label' => 'Return an overview of the site health',
                'description' => 'Return an overview of the site health: emails sent, abilities invoked, scheduler statistics, HTTP use',
                'category' => 'monitor',
                'input_schema' => [
                    'type' => 'object',
                    'properties' => [],
                    'additionalProperties' => false,
                ],
                'output_schema' => [
                    'type' => 'object',
                    'properties' => [
                        'emails' => [
                            'type' => 'string',
                            'description' => 'Notes ans statistics about the emails sent by the site',
                        ],
                        'scheduler' => [
                            'type' => 'string',
                            'description' => 'Notes and statistics about the scheduler and jobs',
                        ],
                    ],
                ],
                'execute_callback' => function () {
                    global $wpdb;

                    $results = [];

                    // TODO: Move the stats computation to a class
                    $starts = $wpdb->get_results("select *, UNIX_TIMESTAMP(created) as ts from {$wpdb->prefix}monitor_scheduler WHERE type='start' AND created > DATE_SUB(NOW(), INTERVAL 30 DAY) order by id asc");
                    $deltas = [];
                    $ts = $starts[0]->ts;
                    for ($i = 1; $i < count($starts); $i++) {
                        $deltas[] = $starts[$i]->ts - $ts;
                        $ts = $starts[$i]->ts;
                    }
                    $avg = array_sum($deltas) / count($deltas);
                    $max = max($deltas);
                    $min = min($deltas);

                    $schedules = wp_get_schedules();
                    $ok = true;
                    $min_interval = DAY_IN_SECONDS;
                    if (!empty($schedules)) {
                        foreach ($schedules as $key => $data) {
                            $min_interval = min($min_interval, $data['interval']);
                        }
                    }

                    if ($avg > $min_interval * 1.3) {
                        $results['scheduler'] = "The scheduler is not activated enough often and external trigger should set up with an interval of $min_interval seconds "
                            . " while it is activate by mean every $avg seconds.";
                    } else {
                        $results['scheduler'] = "The scheduler is working correctly.";
                    }

                    $ready_crons = wp_get_ready_cron_jobs();
                    $ready_jobs_count = count($ready_crons);
                    $oldest_timestamp = $ready_crons ? min(array_keys($ready_crons)) : PHP_INT_MAX;
                    $delta = time() - $oldest_timestamp;
                    if ($ready_jobs_count) {
                        $results['scheduler'] .= "There are $ready_jobs_count jobs ready to be executed.";
                        if ($delta > $min_interval) {
                            $interval = monitor_format_interval($delta);
                            $results['scheduler'] .= "The most delayed job is $interval late.";
                        }
                    }

                    $sent_emails = (int) $wpdb->get_var("select count(*) from {$wpdb->prefix}monitor_emails WHERE created > DATE_SUB(NOW(), INTERVAL 30 DAY)");
                    $avg_duration = (float)$wpdb->get_var("select avg(duration) from {$wpdb->prefix}monitor_emails");

                    $results['emails'] = "The site sent {$sent_emails} over the last 30 days.";
                    if ($avg_duration > 2) {
                        $results['emails'] .= "The average time to send an email is $avg_duration seconds; it is considered a too long time, ask the hosting provider about it";
                    } else {
                        $results['emails'] .= "The average time to send an email is $avg_duration seconds; it is ok";
                    }

                    return $results;
                },
                'permission_callback' => function () {
                    return current_user_can('administrator');
                },
            ]
    );
});
