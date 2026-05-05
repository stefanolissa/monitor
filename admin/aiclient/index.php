<?php
defined('ABSPATH') || exit;

// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- not relevant

global $wpdb;

$per_day = $wpdb->get_results("select date(created) as date, count(*) as total, sum(tokens) as tokens from {$wpdb->prefix}monitor_aiclient where created > DATE_SUB(NOW(), INTERVAL 30 DAY) group by date(created) order by date(created) asc");
$per_day_x = [];
$per_day_y = [];

foreach ($per_day as $data) {
    $per_day_x[] = $data->date;
    $per_day_y[] = $data->total;
    $per_day_tokens[] = $data->tokens;
}
?>
<?php include __DIR__ . '/../menu.php'; ?>
<div class="wrap">
    <?php include __DIR__ . '/nav.php'; ?>

    <div id="graph-calls" style="margin: 2rem 0"></div>
    <div id="graph-tokens" style="margin: 2rem 0"></div>

    <script>
        jQuery(function () {
            var layout = {
                title: {text: 'Calls per day'}
            };
            var data = [{
                    x: <?= json_encode($per_day_x); ?>,
                    y: <?= json_encode($per_day_y); ?>,
                    type: 'scatter'
                }

            ];

            Plotly.newPlot('graph-calls', data, layout);

            var layout = {
                title: {text: 'Calls per day'}
            };
            var data_tokens = [{
                    x: <?= json_encode($per_day_x); ?>,
                    y: <?= json_encode($per_day_tokens); ?>,
                    type: 'scatter'
                }

            ];

            Plotly.newPlot('graph-tokens', data_tokens, {
                title: {text: 'Tokens per day'}
            });
        });
    </script>

    <table class="widefat" style="width: auto">
        <thead>
            <tr>
                <th><?php esc_html_e('Date', 'satollo-monitor'); ?></th>
                <th><?php esc_html_e('Count', 'satollo-monitor'); ?></th>
                <th><?php esc_html_e('Tokens', 'satollo-monitor'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($per_day as $data) { ?>
                <tr>
                    <td><?php echo esc_html($data->date); ?></td>
                    <td><?php echo esc_html($data->total); ?></td>
                    <td><?php echo esc_html($data->tokens); ?></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>

</div>