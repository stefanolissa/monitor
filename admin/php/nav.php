<?php
defined('ABSPATH') || exit;

global $monitor_settings;

if (empty($monitor_settings['http'])) {
    echo '<div class="monitor-notice monitor-notice-warning">
            This monitoring is not active.
        </div>';
}
?>
<p>
    <a href="?page=monitor&section=php"><?php esc_html_e('Overview', 'satollo-monitor'); ?></a>
    | <a href="?page=monitor&section=php&subpage=logs"><?php esc_html_e('Logs', 'satollo-monitor'); ?></a>
</p>
