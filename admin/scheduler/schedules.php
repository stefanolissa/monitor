<?php
defined('ABSPATH') || exit;

$schedules = wp_get_schedules();
if (!is_array($schedules))
    $schedules = [];
?>
<div class="wrap">
    <h2>Recurring schedules</h2>
    <?php include __DIR__ . '/nav.php'; ?>

    <p>
        They can be used to plan the recurring jobs execution.
    </p>

    <table class="widefat" style="width: auto">
        <thead>
            <tr>
                <th>Name</th>
                <th>Key</th>
                <th>Interval</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($schedules as $key => $data) { ?>
                <tr>
                    <td><?php echo esc_html($data['display']); ?></td>
                    <td><?php echo esc_html($key); ?></td>
                    <td><?php echo esc_html($data['interval']); ?> </td>
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