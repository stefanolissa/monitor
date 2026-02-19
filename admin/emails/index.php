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
}

function hook_functions($tag) {
    $functions = monitor_get_hook_functions($tag);

    foreach ($functions as $function) {
        echo esc_html($function), '<br>';
    }
}

function hook_functions_frontend($tag) {
    static $hooks = null;

    if (is_null($hooks)) {
        $hooks = get_option('monitor_emails_hooks', []);
    }
    foreach ($hooks[$tag] ?? [] as $function) {
        echo esc_html($function), '<br>';
    }
}

$sent = $wpdb->get_var("select count(*) from {$wpdb->prefix}satollo_monitor_emails");
$success = $wpdb->get_var("select count(*) from {$wpdb->prefix}satollo_monitor_emails where status=0");
$failed = $wpdb->get_var("select count(*) from {$wpdb->prefix}satollo_monitor_emails where status=1");

$sent_30_days = $wpdb->get_var("select count(*) from {$wpdb->prefix}satollo_monitor_emails where created > DATE_SUB(NOW(), INTERVAL 30 DAY)");

$sent_per_day = $wpdb->get_results("select date(created) as date, count(*) as total from {$wpdb->prefix}satollo_monitor_emails where created > DATE_SUB(NOW(), INTERVAL 30 DAY) group by date(created) order by date(created) asc");
$sent_per_day_x = [];
$sent_per_day_y = [];

foreach ($sent_per_day as $data) {
    $sent_per_day_x[] = $data->date;
    $sent_per_day_y[] = $data->total;
}

$avg_duration = $wpdb->get_var("select avg(duration) from {$wpdb->prefix}satollo_monitor_emails");

// Yes, I know, it's not the right place. I know.
wp_enqueue_script('monitor-plotly', 'https://cdn.plot.ly/plotly-3.1.0.min.js');
?>
<style>
    <?php include __DIR__ . '/../style.css'; ?>
</style>
<div class="wrap">
    <h2>Emails</h2>
    <?php include __DIR__ . '/nav.php'; ?>

    <p>With not enough data, graphs could be broken, just wait some time...</p>

    <div id="dashboard-widgets-wrap">
        <div id="dashboard-widgets" class="metabox-holder">


            <div id="postbox-container-1" class="postbox-container">
                <div id="normal-sortables" class="meta-box-sortables">
                    <div id="monitor-emails-1" class="postbox">

                        <div class="postbox-header">
                            <h2 class="hndle">Statistics</h2>
                        </div>

                        <div class="inside">

                            <table class="widefat">
                                <thead>
                                    <tr>
                                        <th>Parameter</th>
                                        <th>Value</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <th>Emails sent</th>
                                        <td><?= (int) $sent; ?></td>
                                    </tr>
                                    <tr>
                                        <th>Emails sent last 30 days</th>
                                        <td><?= (int) $sent_30_days; ?></td>
                                    </tr>
                                    <tr>
                                        <th>Mean duration to send an email</th>
                                        <td><?= round($avg_duration, 3); ?> seconds</td>
                                    </tr>
                                </tbody>
                            </table>

                        </div>
                    </div>
                </div>
            </div>

            <div id="postbox-container-2" class="postbox-container">
                <div id="normal-sortables" class="meta-box-sortables">
                    <div id="monitor-emails-2" class="postbox">

                        <div class="postbox-header">
                            <h2 class="hndle">Statistics</h2>
                        </div>

                        <div class="inside">

                            <table class="widefat">
                                <thead>
                                    <tr>
                                        <th>Parameter</th>
                                        <th>Value</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <th>Successfully sent</th>
                                        <td><?= (int) $success; ?></td>
                                    </tr>
                                    <tr>
                                        <th>Failed</th>
                                        <td><?= (int) $failed; ?></td>
                                    </tr>
                                    <tr>
                                        <th>Mean duration to send an email</th>
                                        <td><?= round($avg_duration, 3); ?> seconds</td>
                                    </tr>
                                </tbody>
                            </table>

                        </div>
                    </div>
                </div>
            </div>


        </div>

    </div>



    <div id="graph" style="margin: 2rem 0 0 0"></div>

    <script>
        jQuery(function () {
            var layout = {
                title: {text: 'Emails sent per day'}
            };
            var data = [{
                    x: <?= json_encode($sent_per_day_x); ?>,
                    y: <?= json_encode($sent_per_day_y); ?>,
                    type: 'scatter'
                }];

            Plotly.newPlot('graph', data, layout);
        });
    </script>

    <h3>Filters</h3>

    <table class="widefat">
        <thead>
            <tr>
                <th>Parameter</th>
                <th>Value</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <th><code>wp_mail</code></th>
                <td><?php hook_functions('wp_mail'); ?></td>
            </tr>
            <tr>
                <th><code>pre_wp_mail</code></th>
                <td><?php hook_functions('pre_wp_mail'); ?></td>
            </tr>
            <tr>
                <th><code>wp_mail_from</code></th>
                <td><?php hook_functions('wp_mail_from'); ?></td>
            </tr>

            <tr>
                <th><code>wp_mail_from_name</code></th>
                <td><?php hook_functions('wp_mail_from_name'); ?></td>
            </tr>
            <tr>
                <th><code>wp_mail_failed</code></th>
                <td><?php hook_functions('wp_mail_failed'); ?></td>
            </tr>
            <tr>
                <th><code>wp_mail_content_type</code></th>
                <td><?php hook_functions('wp_mail_content_type'); ?></td>
            </tr>
            <tr>
                <th><code>wp_mail_charset</code></th>
                <td><?php hook_functions('wp_mail_charset'); ?></td>
            </tr>
            <tr>
                <th><code>phpmailer_init</code></th>
                <td><?php hook_functions('phpmailer_init'); ?></td>
            </tr>
            <tr>
                <th><code>wp_mail_succeeded</code></th>
                <td><?php hook_functions('wp_mail_succeeded'); ?></td>
            </tr>

        </tbody>
    </table>

    <h3>Filters and Actions</h3>
    <p>
        Filter and actions active while sending the last email.
    </p>

    <table class="widefat">
        <thead>
            <tr>
                <th>Parameter</th>
                <th>Value</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <th><code>wp_mail</code></th>
                <td><?php hook_functions_frontend('wp_mail'); ?></td>
            </tr>
            <tr>
                <th><code>pre_wp_mail</code></th>
                <td><?php hook_functions_frontend('pre_wp_mail'); ?></td>
            </tr>
            <tr>
                <th><code>wp_mail_from</code></th>
                <td><?php hook_functions_frontend('wp_mail_from'); ?></td>
            </tr>

            <tr>
                <th><code>wp_mail_from_name</code></th>
                <td><?php hook_functions_frontend('wp_mail_from_name'); ?></td>
            </tr>
            <tr>
                <th><code>wp_mail_failed</code></th>
                <td><?php hook_functions_frontend('wp_mail_failed'); ?></td>
            </tr>
            <tr>
                <th><code>wp_mail_content_type</code></th>
                <td><?php hook_functions_frontend('wp_mail_content_type'); ?></td>
            </tr>
            <tr>
                <th><code>wp_mail_charset</code></th>
                <td><?php hook_functions_frontend('wp_mail_charset'); ?></td>
            </tr>
            <tr>
                <th><code>phpmailer_init</code></th>
                <td><?php hook_functions_frontend('phpmailer_init'); ?></td>
            </tr>
            <tr>
                <th><code>wp_mail_succeeded</code></th>
                <td><?php hook_functions_frontend('wp_mail_succeeded'); ?></td>
            </tr>

        </tbody>
    </table>

</div>