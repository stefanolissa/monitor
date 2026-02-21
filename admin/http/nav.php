<?php
defined('ABSPATH') || exit;

global $monitor_settings;

if (empty($monitor_settings['http'])) {
    echo '<div class="notice notice-warning">
            <p>This monitoring is not active.</p>
        </div>';
}
?>
<p>
    <a href="?page=monitor_http"><?php esc_html_e('Overview', 'monitor'); ?></a>
    | <a href="?page=monitor_http&subpage=logs"><?php esc_html_e('Logs', 'monitor'); ?></a>
</p>
