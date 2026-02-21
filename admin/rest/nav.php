<?php
defined('ABSPATH') || exit;

global $monitor_settings;

if (empty($monitor_settings['rest'])) {
    echo '<div class="notice notice-warning">
            <p>This monitoring is not active.</p>
        </div>';
}
?>
<p>
    <a href="?page=monitor_rest"><?php esc_html_e('Overview', 'monitor'); ?></a>
    | <a href="?page=monitor_rest&subpage=logs"><?php esc_html_e('Logs', 'monitor'); ?></a>
</p>
