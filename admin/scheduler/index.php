<?php
global $wpdb;

defined('ABSPATH') || exit;

$subpage = $_GET['subpage'] ?? '';

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
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_admin_referer('monitor-reset');
    // TODO
}

// Yes, I know, it's not the right place. I know.
wp_enqueue_script('monitor-plotly', 'https://cdn.plot.ly/plotly-3.1.0.min.js');

// TODO: compute statistics
$starts = $wpdb->get_results($wpdb->prepare("select *, UNIX_TIMESTAMP(created) as ts from {$wpdb->prefix}monitor_scheduler WHERE type='start' AND created > DATE_SUB(NOW(), INTERVAL 30 DAY) order by id asc"));
if (count($starts) > 2) {
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
    echo 'Still no data';
    return;
}
?>
<div class="wrap">
    <h2>Scheduler activations</h2>
    <?php include __DIR__ . '/nav.php'; ?>

    <p>
        For detailed information on job scheduling install the WP Crontrol plugin.
    </p>





    <div id="dashboard-widgets-wrap">
        <div id="dashboard-widgets" class="metabox-holder">


            <div id="postbox-container-1" class="postbox-container">

                <div id="normal-sortables" class="meta-box-sortables">

                    <div id="monitor-emails" class="postbox " >

                        <div class="postbox-header">
                            <h2 class="hndle">Emails</h2>
                            <div class="handle-actions hide-if-no-js">
                                <button type="button" class="handle-order-higher" aria-disabled="false" aria-describedby="dashboard_site_health-handle-order-higher-description">
                                    <span class="screen-reader-text">Move up</span>
                                    <span class="order-higher-indicator" aria-hidden="true"></span>
                                </button>
                                <span class="hidden" id="dashboard_site_health-handle-order-higher-description">Move Site Health Status box up</span>
                                <button type="button" class="handle-order-lower" aria-disabled="false" aria-describedby="dashboard_site_health-handle-order-lower-description">
                                    <span class="screen-reader-text">Move down</span>
                                    <span class="order-lower-indicator" aria-hidden="true"></span>
                                </button>
                                <span class="hidden" id="dashboard_site_health-handle-order-lower-description">Move Site Health Status box down</span>
                                <button type="button" class="handlediv" aria-expanded="true">
                                    <span class="screen-reader-text">Toggle panel: Site Health Status</span>
                                    <span class="toggle-indicator" aria-hidden="true"></span>
                                </button>
                            </div>
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
                <td><?php echo esc_html((int) $avg); ?></td>
            </tr>
            <tr>
                <th>Maximum delay</th>
                <td><?php echo esc_html($max); ?></td>
            </tr>
            <tr>
                <th>Minimum delay</th>
                <td><?php echo esc_html($min); ?></td>
            </tr>
            <tr>
                <th><code>DISABLE_WP_CRON</code></th>
                <td><?php echo (defined('DISABLE_WP_CRON') && DISABLE_WP_CRON) ? 'true' : 'false'; ?></td>
            </tr>
            <tr>
                <th><code>ALTERNATE_WP_CRON</code></th>
                <td><?php echo (defined('ALTERNATE_WP_CRON') && ALTERNATE_WP_CRON) ? 'true' : 'false'; ?></td>
            </tr>
            <tr>
                <th><code>WP_CRON_LOCK_TIMEOUT</code></th>
                <td><?php echo esc_html(WP_CRON_LOCK_TIMEOUT); ?></td>
            </tr>
        </tbody>
    </table>


                        </div>
                    </div>




                </div>
            </div>
        </div>
    </div>




    <div id="graph" style="margin: 2rem 0 0 0"></div>
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
    });
</script>