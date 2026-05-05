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
    <a href="?page=monitor&section=scheduler"><?php esc_html_e('Overview', 'satollo-monitor'); ?></a>
    | <a href="?page=monitor&section=scheduler&subpage=logs"><?php esc_html_e('Logs', 'satollo-monitor'); ?></a>
    | <a href="?page=monitor&section=scheduler&subpage=schedules"><?php esc_html_e('Schedules', 'satollo-monitor'); ?></a>
    | <a href="?page=monitor&section=scheduler&subpage=jobs"><?php esc_html_e('Jobs', 'satollo-monitor'); ?></a>
</p>
