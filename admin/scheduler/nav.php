<?php
defined('ABSPATH') || exit;

global $monitor_settings;

if (empty($monitor_settings['scheduler'])) {
    echo '<div class="monitor-notice monitor-notice-warning">
            This monitoring is not active.
        </div>';
}
?>
<p>
    <a href="?page=monitor&section=scheduler"><?php esc_html_e('Overview', 'monitor'); ?></a>
    | <a href="?page=monitor&section=scheduler&subpage=logs"><?php esc_html_e('Logs', 'monitor'); ?></a>
    | <a href="?page=monitor&section=scheduler&subpage=schedules"><?php esc_html_e('Schedules', 'monitor'); ?></a>
    | <a href="?page=monitor&section=scheduler&subpage=jobs"><?php esc_html_e('Jobs', 'monitor'); ?></a>
</p>
