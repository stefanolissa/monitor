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

$sent = $wpdb->get_var("select count(*) from {$wpdb->prefix}monitor_emails");

$sent_30_days = $wpdb->get_var("select count(*) from {$wpdb->prefix}monitor_emails where created > DATE_SUB(NOW(), INTERVAL 30 DAY)");

$sent_per_day = $wpdb->get_results("select date(created) as date, count(*) as total from {$wpdb->prefix}monitor_emails where created > DATE_SUB(NOW(), INTERVAL 30 DAY) group by date(created) order by date(created) asc");
$sent_per_day_x = [];
$sent_per_day_y = [];

foreach ($sent_per_day as $data) {
    $sent_per_day_x[] = $data->date;
    $sent_per_day_y[] = $data->total;
}

// Yes, I know, it's not the right place. I know.
wp_enqueue_script('monitor-plotly', 'https://cdn.plot.ly/plotly-3.1.0.min.js');
?>
<div class="wrap">
    <h2>Emails</h2>
    <?php include __DIR__ . '/nav.php'; ?>

    <p>With too less data, graphs could be broken, just wait some time...</p>

    <h2>Statistics</h2>
    <table class="widefat" style="width: auto">
        <thead>
            <tr>
                <th>Parameter</th>
                <th>Value</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <th>Emails sent</th>
                <td><?php echo (int) $sent; ?></td>
            </tr>
            <tr>
                <th>Emails sent last 30 days</th>
                <td><?php echo (int) $sent_30_days; ?></td>
            </tr>
        </tbody>
    </table>

    <div id="graph" style="margin: 2rem 0 0 0"></div>

    <script>
        jQuery(function () {
            var layout = {
                title: {text: 'Emails sent per day'}
            };
            var data = [{
                    x: <?php echo json_encode($sent_per_day_x); ?>,
                    y: <?php echo json_encode($sent_per_day_y); ?>,
                    type: 'scatter'
                }];

            Plotly.newPlot('graph', data, layout);
        });
    </script>

    <h3>Filters</h3>

    <table class="widefat" style="width: auto">
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

    <table class="widefat" style="width: auto">
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