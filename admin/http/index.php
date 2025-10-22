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
    $wpdb->query("truncate {$wpdb->prefix}monitor_http");
}

$per_day = $wpdb->get_results("select date(created) as date, count(*) as total from {$wpdb->prefix}monitor_http where created > DATE_SUB(NOW(), INTERVAL 30 DAY) group by date(created) order by date(created) asc");
$per_day_x = [];
$per_day_y = [];

foreach ($per_day as $data) {
    $per_day_x[] = $data->date;
    $per_day_y[] = $data->total;
}

// Yes, I know, it's not the right place. I know.
wp_enqueue_script('monitor-plotly', 'https://cdn.plot.ly/plotly-3.1.0.min.js');
?>
<div class="wrap">
    <h2>HTTP</h2>
    <?php include __DIR__ . '/nav.php'; ?>

    <p></p>

    <div id="graph" style="margin: 2rem 0"></div>

    <form method="post">
        <?php wp_nonce_field('monitor-reset'); ?>
        <button name="reset" class="button button-secondary">Reset</button>
    </form>
    <script>
        jQuery(function () {
            var layout = {
                title: {text: 'Requests per day'}
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