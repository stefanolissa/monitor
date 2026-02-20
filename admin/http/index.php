<?php

// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- not relevant

defined('ABSPATH') || exit;

global $wpdb;

// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- not relevant
$subpage = sanitize_key($_GET['subpage'] ?? '');

switch ($subpage) {
    case 'logs':
        include __DIR__ . '/logs.php';
        return;
}

$per_day = $wpdb->get_results("select date(created) as date, count(*) as total from {$wpdb->prefix}monitor_http where created > DATE_SUB(NOW(), INTERVAL 30 DAY) group by date(created) order by date(created) asc");
$per_day_x = [];
$per_day_y = [];

foreach ($per_day as $data) {
    $per_day_x[] = $data->date;
    $per_day_y[] = $data->total;
}

?>
<div class="wrap">
    <h2>HTTP</h2>
    <?php include __DIR__ . '/nav.php'; ?>

    <table class="widefat" style="width: auto">
        <thead>
            <tr>
                <th><?php esc_html_e('Date', 'monitor'); ?></th>
                <th><?php esc_html_e('Count', 'monitor'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($per_day as $data) { ?>
                <tr>
                    <td><?php echo esc_html($data->date); ?></td>
                    <td><?php echo esc_html($data->total); ?></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>

    <div id="graph" style="margin: 2rem 0"></div>

    <script>
        jQuery(function () {
            var layout = {
                title: {text: 'Requests per day'},
                yaxis: {
                    rangemode: 'tozero'
                }
            };
            var data = [{
                    x: <?php echo json_encode($per_day_x); ?>,
                    y: <?php echo json_encode($per_day_y); ?>,
                    type: 'scatter'
                }];

            Plotly.newPlot('graph', data, layout);
        });
    </script>

</div>