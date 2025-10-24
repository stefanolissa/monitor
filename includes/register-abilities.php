<?php
defined('ABSPATH') || exit;

add_action('abilities_api_init', function () {

    $r = wp_register_ability('monitor/overview',
            [
                'label' => 'Return an overview of the site health',
                'description' => 'Return an overview of the site health: emails sent, activities invoked, scheduler statistics',
                'input_schema' => [
                    'type' => 'object',
                    'properties' => [],
                    'additionalProperties' => false,
                ],
                'output_schema' => [
                    'type' => 'object',
                    'properties' => [
                        'result' => [
                            'type' => 'string',
                            'description' => 'Human readable results of the system checks woith statistics and possible warnings.',
                        ],
//                        'sent_emails' => [
//                            'type' => 'integer',
//                            'description' => 'Number of sent emails',
//                        ],
//                        'scheduler_statistics' => [
//                            'type' => 'string',
//                            'description' => 'Details of the scheduler status and statistics',
//                        ],
//                        'scheduler_notes' => [
//                            'type' => 'string',
//                            'description' => 'Notes about the scheduler statitics if needed.',
//                            'minLength' => 0
//                        ],
//                        'abilities_statistics' => [
//                            'type' => 'string',
//                            'description' => 'Details of the abilities',
//                            'minLength' => 0
//                        ],
                    ],
                ],
                'execute_callback' => function () {
                    global $wpdb;

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
                            if ($ok && $avg > $data['interval'] * 1.3) {
                                $ok = false;
                            }
                        }
                    }

                    $result = '';



                    $sent_emails = (int) $wpdb->get_var("select count(*) from {$wpdb->prefix}monitor_emails WHERE created > DATE_SUB(NOW(), INTERVAL 30 DAY)");

                    $result .= "The site sent {$sent_emails} over the last 30 days. ";

//                    $result = [
//                        'sent_emails' => $sent_emails,
//                        'scheduler_statistics' => "Average call interval {$avg} seconds, maximum interval {$max} seconds, minimum interval {$min} seconds. The required average interval is {$min_interval}",
//                        'abilities_statistics' => 'Abilities invoked 450 times'
//                    ];
                    if (false && $ok) {
                        $result = "The scheduler is working correctly, no corrective actions are required ";
                    } else {
                        $result .= "The scheduler shows some problems. It is activated every {$avg} seconds by mean while the minimim interval required is {$min_interval} seconds. ";
                        $result .= "It is recommended to setup an external scheduler trigger. ";
                    }

                    //$result['scheduler_notes'] = $ok ? 'Everything fine' : "The scheduler is not triggered enough often, the minumum interval should be {$min_interval}";

                    return ['result' => $result];
                },
                'permission_callback' => function () {
                    return current_user_can('administrator');
                },
                'meta' => [
                    'category' => 'monitor',
                ],
            ]
    );
});
