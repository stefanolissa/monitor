<?php
defined('ABSPATH') || exit;

// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- not necessary
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    check_admin_referer('monitor-save');

    if (isset($_POST['save'])) {
        $data = wp_kses_post_deep(wp_unslash($_POST['data']));

        update_option('monitor_settings', $data);
    }

    if (isset($_POST['clean'])) {
        monitor_clean_logs();
    }
}

$data = get_option('monitor_settings', []);

wp_enqueue_script('dashboard');

$log_days = $data['log_days'] ?? 30;
$report = $data['report'] ?? '';
$alerts = $data['alerts'] ?? '';
?>
<?php include __DIR__ . '/menu.php'; ?>
<div class="wrap">
    <h2>Monitor</h2>

    <?php if (is_plugin_active('satollo-monitor/plugin.php')) { ?>
        <div class="notice notice-error">
            <p>
                The Satollo Monitor plugin is active, you must deactivate it, or you're going to record all events twice.
                Monitor contains a superset of features.
            </p>
        </div>
    <?php } ?>

    <p>
        Monitor keeps an eye on WP events (emails, background tasks, ...) providing
        statistics and logs. If you have any specific need, write me at stefano@satollo.net.
    </p>

    <p>
        <a href="https://www.satollo.net/plugins/monitor" target="_blank">Please read the official page</a> until I find the time to integrate more information
        directly on the monitor pages. Thank you.
    </p>

</div>
