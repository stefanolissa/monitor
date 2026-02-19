<?php
global $wpdb;

defined('ABSPATH') || exit;

$subpage = $_GET['subpage'] ?? '';

switch ($subpage) {
    case 'logs':
        include __DIR__ . '/logs.php';
        return;
    case 'filters':
        include __DIR__ . '/filters.php';
        return;
    case 'roles':
        include __DIR__ . '/roles.php';
        return;
}

$reg_per_day = $wpdb->get_results("select date(user_registered) as date, count(*) as total from {$wpdb->prefix}users where user_registered > DATE_SUB(NOW(), INTERVAL 365 DAY) group by date(user_registered) order by date(user_registered) asc");
$reg_per_day_x = [];
$reg_per_day_y = [];

foreach ($reg_per_day as $data) {
    $reg_per_day_x[] = $data->date;
    $reg_per_day_y[] = $data->total;
}

// Yes, I know, it's not the right place. I know.
wp_enqueue_script('monitor-plotly', 'https://cdn.plot.ly/plotly-3.1.0.min.js');
?>

<div class="wrap">
    <h2>Users</h2>
    <?php include __DIR__ . '/nav.php'; ?>
    <p>
        Still nothing here.
    </p>

    <div id="graph" style="margin: 2rem 0 0 0"></div>

    <script>
        jQuery(function () {
            var layout = {
                title: {text: 'Users registered per day'}
            };
            var data = [{
                    x: <?php echo json_encode($reg_per_day_x); ?>,
                    y: <?php echo json_encode($reg_per_day_y); ?>,
                    type: 'scatter'
                }];

            Plotly.newPlot('graph', data, layout);
        });
    </script>
</div>
