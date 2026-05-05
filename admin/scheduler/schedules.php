<?php
defined('ABSPATH') || exit;

$schedules = wp_get_schedules();
if (!is_array($schedules)) {
    $schedules = [];
}
?>
<?php include __DIR__ . '/../menu.php'; ?>
<div class="wrap">
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
                <th><?php esc_html_e('Name', 'satollo-monitor'); ?></th>
                <th><?php esc_html_e('Key', 'satollo-monitor'); ?></th>
                <th><?php esc_html_e('Interval', 'satollo-monitor'); ?></th>
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

</div>
