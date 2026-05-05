<?php
defined('ABSPATH') || exit;

global $monitor_settings;

if (empty($monitor_settings['emails'])) {
    echo '<div class="monitor-notice monitor-notice-warning">
            This monitoring is not active.
        </div>';
}
?>
<p>
    <a href="?page=monitor&section=emails"><?php esc_html_e('Overview', 'satollo-monitor'); ?></a>
    | <a href="?page=monitor&section=emails&subpage=logs"><?php esc_html_e('Logs', 'satollo-monitor'); ?></a>
</p>
