<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_admin_referer('monitor-save');
    $data = wp_kses_post_deep(wp_unslash($_POST['data']));

    // TODO: cleanup the options
    update_option('monitor', $data);

    // Report
    wp_unschedule_hook('monitor_report');
    if (!empty($data['report'])) {
        $when = $data['report'] == 'weekly' ? WEEK_IN_SECONDS : DAY_IN_SECONDS;
        wp_schedule_event(time() + $when, $data['report'], 'monitor_report');
    }
}

$data = get_option('monitor', []);

wp_enqueue_script('dashboard');

$log_days = $data['log_days'] ?? 30;
$report = $data['report'] ?? '';
$alerts = $data['alerts'] ?? '';
?>
<div class="wrap">
    <h2>Monitor</h2>

    <p>
        Monitor keeps an eye on WP events (emails, background tasks, ...) providing
        statistics and logs. If you have any specific need, write me at stefano@satollo.net.
    </p>

    <p>
        <a href="https://www.satollo.net/plugins/monitor" target="_blank">Please read the official page</a> until I find the time to integrate more information
        directly on the monitor pages. Thank you.
    </p>


    <form method="post">
        <?php wp_nonce_field('monitor-save'); ?>
        <table class="form-table" role=""presentation">
            <tr>
                <th>
                    Monitor emails
                </th>
                <td>
                    <input type="checkbox" value="1" name="data[emails]" <?php echo isset($data['emails']) ? 'checked' : ''; ?>>
                </td>
            </tr>
            <tr>
                <th>
                    Monitor abilities
                </th>
                <td>
                    <input type="checkbox" value="1" name="data[abilities]" <?php echo isset($data['abilities']) ? 'checked' : ''; ?>>
                </td>
            </tr>
            <tr>
                <th>
                    Monitor scheduler
                </th>
                <td>
                    <input type="checkbox" value="1" name="data[scheduler]" <?php echo isset($data['scheduler']) ? 'checked' : ''; ?>>
                </td>
            </tr>
            <tr>
                <th>
                    Monitor HTTP
                </th>
                <td>
                    <input type="checkbox" value="1" name="data[http]" <?php echo isset($data['http']) ? 'checked' : ''; ?>>
                </td>
            </tr>
            <tr>
                <th>
                    Include the wp-cron.php autocall
                </th>
                <td>
                    <input type="checkbox" value="1" name="data[http_wpcron]" <?php echo isset($data['http_wpcron']) ? 'checked' : ''; ?>>
                </td>
            </tr>
            <tr>
                <th>
                    Keep logs for
                </th>
                <td>
                    <select name="data[log_days]">
                        <option value="30" <?php echo $log_days == 30 ? 'selected' : ''; ?>>30 days</option>
                        <option value="60" <?php echo $log_days == 60 ? 'selected' : ''; ?>>60 days</option>
                        <option value="90" <?php echo $log_days == 90 ? 'selected' : ''; ?>>90 days</option>
                    </select>
                </td>
            </tr>
            <tr>
                <th>
                    Send report
                </th>
                <td>
                    <select name="data[report]">
                        <option value="0" <?php echo!$report ? 'selected' : ''; ?>>never</option>
                        <option value="daily" <?php echo $report == 'daily' ? 'selected' : ''; ?>>daily</option>
                        <option value="weekly" <?php echo $report == 'weekly' ? 'selected' : ''; ?>>weekly</option>
                    </select>

                    <?php echo gmdate('Y-m-d, h:i:s', wp_next_scheduled('monitor_report')); ?> UTC
                </td>
            </tr>
            <tr>
                <th>
                    Send alerts
                </th>
                <td>
                    <input type="checkbox" value="1" name="data[alerts]" <?php echo isset($data['alerts']) ? 'checked' : ''; ?>>
                </td>
            </tr>
        </table>
        <button class="button button-primary">Save</button>
    </form>

    <h3>Debug</h3>
    <pre><?php echo esc_html(print_r($data, true)); ?></pre>

</div>
