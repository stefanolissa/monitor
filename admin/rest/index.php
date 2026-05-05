<?php

defined('ABSPATH') || exit;

// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- not relevant

global $wpdb;

$per_day = $wpdb->get_results("select date(created) as date, count(*) as total from {$wpdb->prefix}monitor_rest where created > DATE_SUB(NOW(), INTERVAL 30 DAY) group by date(created) order by date(created) asc");
$per_day_x = [];
$per_day_y = [];

foreach ($per_day as $data) {
    $per_day_x[] = $data->date;
    $per_day_y[] = $data->total;
}

?>
<?php include __DIR__ . '/../menu.php'; ?>
<div class="wrap" id="monitor-emails">
    <?php include __DIR__ . '/nav.php'; ?>

    <table class="widefat" style="width: auto">
        <thead>
            <tr>
                <th><?php esc_html_e('Date', 'satollo-monitor'); ?></th>
                <th><?php esc_html_e('Count', 'satollo-monitor'); ?></th>
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

</div>