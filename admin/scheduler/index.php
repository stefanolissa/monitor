<?php
global $wpdb;

defined('ABSPATH') || exit;

$subpage = $_GET['subpage'] ?? '';

switch ($subpage) {
    case 'logs':
        include __DIR__ . '/logs.php';
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
$deltas = [];
$ts = $starts[0]->ts;
for ($i = 1; $i < count($starts); $i++) {
    $deltas[] = $starts[$i]->ts - $ts;
    $ts = $starts[$i]->ts;
}
$avg = array_sum($deltas) / count($deltas);
$max = max($deltas);
$min = min($deltas);
?>
<div class="wrap">
    <h2>Scheduler activations</h2>
    <p>
        <a href="?page=monitor-scheduler">Overview</a> | <a href="?page=monitor-scheduler&subpage=logs">Logs</a>
    </p>
    <p>
        For detailed information on job scheduling install the WP Crontrol plugin.
    </p>

    <table class="widefat" style="width: auto">
        <thead>
            <tr>
                <th>Parameter</th>
                <th>Value</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <th>Average delay</th>
                <td><?php echo esc_html((int)$avg); ?></td>
            </tr>
            <tr>
                <th>Maximum delay</th>
                <td><?php echo esc_html($max); ?></td>
            </tr>
            <tr>
                <th>Minimum delay</th>
                <td><?php echo esc_html($min); ?></td>
            </tr>
        </tbody>
    </table>

    <div id="graph"></div>
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