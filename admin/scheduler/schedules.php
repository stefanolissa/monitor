<?php
defined('ABSPATH') || exit;

$schedules = wp_get_schedules();
if (!is_array($schedules)) {
    $schedules = [];
}
?>
<div class="wrap">
    <h2><?php esc_html_e('Recurring schedules', 'monitor'); ?></h2>
    <?php include __DIR__ . '/nav.php'; ?>

    <?php if (count($schedules) === 0) { ?>
    <div class="notice notice-error">
        <p>There are no schedules available a plugin/theme is removing even the standard WP schedules.</p>
    </div>
    <?php } ?>

    <p>
        They can be used to plan the recurring jobs execution. WP provides some standard schedules, other are created by plugins/themes.
    </p>

    <table class="widefat" style="width: auto">
        <thead>
            <tr>
                <th><?php esc_html_e('Name', 'monitor'); ?></th>
                <th><?php esc_html_e('Key', 'monitor'); ?></th>
                <th><?php esc_html_e('Interval', 'monitor'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($schedules as $key => $data) { ?>
                <tr>
                    <td><?php echo esc_html($data['display']); ?></td>
                    <td><?php echo esc_html($key); ?></td>
                    <td><?php echo esc_html(monitor_format_interval($data['interval'])); ?> </td>
                </tr>
            <?php } ?>
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