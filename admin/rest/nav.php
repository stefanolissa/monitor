<?php
defined('ABSPATH') || exit;

global $monitor_settings;

if (empty($monitor_settings['rest'])) {
    echo '<div class="monitor-notice monitor-notice-warning">
            This monitoring is not active.
        </div>';
}
?>
<p>
    <a href="?page=monitor&section=rest"><?php esc_html_e('Overview', 'monitor'); ?></a>
    | <a href="?page=monitor&section=rest&subpage=logs"><?php esc_html_e('Logs', 'monitor'); ?></a>
</p>
