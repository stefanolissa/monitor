<?php
// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- not relevant

defined('ABSPATH') || exit;

global $wpdb;

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

$sent = $wpdb->get_var("select count(*) from {$wpdb->prefix}monitor_emails");
$success = $wpdb->get_var("select count(*) from {$wpdb->prefix}monitor_emails where status=0");
$failed = $wpdb->get_var("select count(*) from {$wpdb->prefix}monitor_emails where status=1");

$sent_30_days = $wpdb->get_var("select count(*) from {$wpdb->prefix}monitor_emails where created > DATE_SUB(NOW(), INTERVAL 30 DAY)");

$sent_per_day = $wpdb->get_results("select date(created) as date, count(*) as total from {$wpdb->prefix}monitor_emails where created > DATE_SUB(NOW(), INTERVAL 30 DAY) group by date(created) order by date(created) asc");
$sent_per_day_x = [];
$sent_per_day_y = [];

foreach ($sent_per_day as $data) {
    $sent_per_day_x[] = $data->date;
    $sent_per_day_y[] = $data->total;
}

$avg_duration = $wpdb->get_var("select avg(duration) from {$wpdb->prefix}monitor_emails where status=1");
?>
<?php include __DIR__ . '/../menu.php'; ?>
<div class="wrap">
    <?php include __DIR__ . '/nav.php'; ?>

    <p>For email logging with actions (resend, ...) consider the WP Mail Logging plugin.</p>

    <div class="monitor-dashboard">

        <div>

            <table class="widefat">
                <thead>
                    <tr>
                        <th>Parameter</th>
                        <th>Value</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <th>Total sent</th>
                        <td><?= (int) get_option('monitor_emails_sent_count'); ?></td>
                    </tr>
                    <tr>
                        <th>Total failed</th>
                        <td><?= (int) get_option('monitor_emails_failed_count'); ?></td>
                    </tr>

                </tbody>
            </table>

        </div>



        <div>

            <table class="widefat">
                <thead>
                    <tr>
                        <th>Parameter</th>
                        <th>Value</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <th>Logged success</th>
                        <td><?= (int) $success; ?></td>
                    </tr>
                    <tr>
                        <th>Logged failed</th>
                        <td><?= (int) $failed; ?></td>
                    </tr>
                    <tr>
                        <th>Mean duration to send an email</th>
                        <td><?= esc_html(round($avg_duration, 3)); ?> seconds</td>
                    </tr>
                </tbody>
            </table>

        </div>


    </div>



    <div id="graph" style="margin: 2rem 0 0 0"></div>

    <script>
        jQuery(function () {
            var layout = {
                title: {text: 'Emails sent per day'},
                yaxis: {
                    rangemode: 'tozero'
                }
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
    <p>
        Filter active now. Filter active when sending a specific email are visible in the logs.
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

</div>