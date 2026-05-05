<?php
// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- not relevant

defined('ABSPATH') || exit;

global $wpdb;

$per_day = $wpdb->get_results("select date(created) as date, count(*) as total from {$wpdb->prefix}monitor_php where created > DATE_SUB(NOW(), INTERVAL 30 DAY) group by date(created) order by date(created) asc");
$per_day_x = [];
$per_day_y = [];

foreach ($per_day as $data) {
    $per_day_x[] = $data->date;
    $per_day_y[] = $data->total;
}
?>
<?php include __DIR__ . '/../menu.php'; ?>
<div class="wrap">

    <?php include __DIR__ . '/nav.php'; ?>

    <?php if (!$per_day) { ?>
        <div class="monitor-nodata">
            No recent data to show diagrams.
        </div>
    <?php } else { ?>
        <div id="graph" style="margin: 2rem 0"></div>
    <?php } ?>

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