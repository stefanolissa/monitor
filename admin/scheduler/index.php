<?php

// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- not relevant

global $wpdb;

defined('ABSPATH') || exit;

// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- not relevant
$subpage = sanitize_key($_GET['subpage'] ?? '');

switch ($subpage) {
    case 'logs':
        include __DIR__ . '/logs.php';
        return;
    case 'schedules':
        include __DIR__ . '/schedules.php';
        return;
    case 'filters':
        include __DIR__ . '/filters.php';
        return;
    case 'jobs':
        include __DIR__ . '/jobs.php';
        return;
}

$starts = $wpdb->get_results("select UNIX_TIMESTAMP(created) as ts from {$wpdb->prefix}monitor_scheduler WHERE created > DATE_SUB(NOW(), INTERVAL 3 DAY) order by id asc");
$deltas = [];
$avg = 0;
$max = 0;
$min = 0;
if (count($starts) >= 2) {
    $deltas = [];
    $ts = $starts[0]->ts;
    for ($i = 1; $i < count($starts); $i++) {
        $deltas[] = $starts[$i]->ts - $ts;
        $ts = $starts[$i]->ts;
    }
    $avg = array_sum($deltas) / count($deltas);
    $max = max($deltas);
    $min = min($deltas);
} else {

}

//
//if (count($deltas) > 5) {
//    $moving_window = 10;
//    $deltas_moving = [];
//    $deltas_moving[0] = array_sum(array_slice($deltas, 0, $moving_window)) / $moving_window;
//    $current = 1;
//    for ($i = $moving_window; $i < count($deltas); $i++) {
//        $deltas_moving[$current] = $deltas[$i] / $moving_window + $deltas_moving[$current - 1] - $deltas[$i - $moving_window] / $moving_window;
//        $current++;
//
//    }
//}

$ready_crons = wp_get_ready_cron_jobs();
$oldest_timestamp = $ready_crons ? min(array_keys($ready_crons)) : PHP_INT_MAX;
$ready_jobs = [];
foreach ($ready_crons as $ts => $hooks) {
    foreach ($hooks as $hook => $keys) {
        $ready_jobs[] = ['timestamp' => $ts, 'hook' => $hook];
    }
}

$crons = _get_cron_array();
$jobs = [];
foreach ($crons as $ts => $hooks) {
    foreach ($hooks as $hook => $keys) {
        $jobs[] = ['timestamp' => $ts, 'hook' => $hook];
    }
}
$last_run = (int) get_option('monitor_scheduler_last_run');
$skipped = $oldest_timestamp < $last_run;
$doing_cron = get_transient('doing_cron');

// Compute the minimum interval
$min_interval = MONTH_IN_SECONDS;
$schedules = wp_get_schedules();
if (is_array($schedules)) {
    foreach ($schedules as $key => $data) {
        if ($data['interval'] < $min_interval) {
            $min_interval = $data['interval'];
        }
    }
}

?>

<div class="wrap">
    <h2><?php esc_html_e('Scheduler', 'monitor'); ?></h2>
    <?php include __DIR__ . '/nav.php'; ?>

    <?php if ($skipped) { ?>
        <div class="notice notice-error">
            <p>
                On last run not all scheduled tasks have been executed. Usually it's due to fatal error, PHP execution timeout,
                or bad object cache implementation.
            </p>
        </div>
    <?php } ?>

    <p>
        For detailed information on job scheduling install the WP Crontrol plugin.
    </p>



    <div id="dashboard-widgets-wrap">
        <div id="dashboard-widgets" class="metabox-holder">


            <div id="postbox-container-1" class="postbox-container">

                <div id="normal-sortables" class="meta-box-sortables">
                    <div id="monitor-emails" class="postbox">

                        <div class="postbox-header">
                            <h2 class="hndle">Statistics</h2>
                        </div>

                        <div class="inside">

                            <table class="widefat" style="width: 100%">
                                <thead>
                                    <tr>
                                        <th>Parameter</th>
                                        <th>Value</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <th>Average delay</th>
                                        <td>
                                            <?php
                                            if ($avg > $min_interval) {
                                                echo '<span class="red">', esc_html(monitor_format_interval($avg)), '</span>';
                                                echo '<br><small>Greater than the minimim frequency</small>';
                                            } else {
                                                echo '<span class="green">', esc_html(monitor_format_interval($avg)), '</span>';
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Maximum delay</th>
                                        <td><?php echo esc_html(monitor_format_interval($max)); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Minimum delay</th>
                                        <td><?php echo esc_html($min); ?> seconds</td>
                                    </tr>
                                    <tr>
                                        <th>Last run</th>
                                        <td><?php echo esc_html(wp_date('Y-m-d H:i:s', $last_run)); ?></td>
                                    </tr>

                                </tbody>
                            </table>


                        </div>


                    </div>

                </div>
            </div>

            <div id="postbox-container-2" class="postbox-container">

                <div id="normal-sortables" class="meta-box-sortables">

                    <div id="monitor-emails" class="postbox">

                        <div class="postbox-header">
                            <h2 class="hndle">Values</h2>

                        </div>

                        <div class="inside">
                            <table class="widefat" style="width: 100%">
                                <thead>
                                    <tr>
                                        <th><?php esc_html_e('Parameter', 'monitor'); ?></th>
                                        <th><?php esc_html_e('Value', 'monitor'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <th><?php esc_html_e('Minimum frequency', 'monitor'); ?></th>
                                        <td><?php echo esc_html(monitor_format_interval($min_interval)); ?></td>
                                    </tr>
                                    <tr>
                                        <th><code>DISABLE_WP_CRON</code></th>
                                        <td><?php echo esc_html((defined('DISABLE_WP_CRON') && DISABLE_WP_CRON) ? 'true' : 'false'); ?></td>
                                    </tr>
                                    <tr>
                                        <th><code>ALTERNATE_WP_CRON</code></th>
                                        <td><?php echo esc_html((defined('ALTERNATE_WP_CRON') && ALTERNATE_WP_CRON) ? 'true' : 'false'); ?></td>
                                    </tr>
                                    <tr>
                                        <th><code>WP_CRON_LOCK_TIMEOUT</code></th>
                                        <td><?php echo esc_html(WP_CRON_LOCK_TIMEOUT) ?></td>
                                    </tr>

                                    <tr>
                                        <td>Transient <code>doing_cron</code></td>

                                        <td>
                                            <?php
                                            if ($doing_cron) {
                                                echo '<span style="color: orange;">', esc_html(monitor_format_interval(time() - (int) $doing_cron)), '</span>';
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>


            <div id="postbox-container-2" class="postbox-container">

                <div id="normal-sortables" class="meta-box-sortables">

                    <div id="monitor-schduler-jobs" class="postbox">

                        <div class="postbox-header">
                            <h2 class="hndle">Jobs</h2>

                        </div>

                        <div class="inside">
                            <table class="widefat" style="width: 100%">
                                <thead>
                                    <tr>
                                        <th>Parameter</th>
                                        <th>Value</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <th><a href="?page=monitor_scheduler&subpage=jobs">Jobs to be executed</a></th>
                                        <td><?php echo count($ready_jobs); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Scheduled jobs</th>
                                        <td><?php echo count($jobs); ?></td>
                                    </tr>

                                </tbody>
                            </table>

                        </div>
                    </div>

                </div>
            </div>



        </div>
    </div>




    <div id="graph" style="margin: 1.5rem 0"></div>

    <div id="moving-avg" style="margin: 1.5rem 0"></div>

    <form method="post">
        <?php wp_nonce_field('monitor-reset'); ?>
        <button name="reset" class="button button-secondary">Reset</button>
    </form>
</div>

<script>
    jQuery(function () {
        var layout = {
            title: {text: 'Interval between scheduler activations (seconds)'}
        };
        var data = [{
                //x: [1, 2, 3, 4, 5],
                y: <?php echo json_encode($deltas); ?>
            }];

        Plotly.newPlot('graph', data, layout);

//        var data2 = [{
//                //x: [1, 2, 3, 4, 5],
//                y: <?php //echo json_encode($deltas_moving); ?>
//            }];
//
//        Plotly.newPlot('moving-avg', data2, layout);
    });
</script>