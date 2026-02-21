<?php
defined('ABSPATH') || exit;

global $monitor_settings;

if (empty($monitor_settings['scheduler'])) {
    echo '<div class="notice notice-warning">
            <p>This monitoring is not active.</p>
        </div>';
}
?>
<p>
    <a href="?page=monitor_scheduler"><?php esc_html_e('Overview', 'monitor'); ?></a>
    | <a href="?page=monitor_scheduler&subpage=logs"><?php esc_html_e('Logs', 'monitor'); ?></a>
    | <a href="?page=monitor_scheduler&subpage=schedules"><?php esc_html_e('Schedules', 'monitor'); ?></a>
    | <a href="?page=monitor_scheduler&subpage=jobs"><?php esc_html_e('Jobs', 'monitor'); ?></a>
</p>
