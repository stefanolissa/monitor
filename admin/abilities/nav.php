<?php
defined('ABSPATH') || exit;

global $monitor_settings;

if (empty($monitor_settings['abilities'])) {
    echo '<div class="notice notice-warning">
            <p>This monitoring is not active.</p>
        </div>';
}
?>
<p>
    <a href="?page=monitor_abilities"><?php esc_html_e('List', 'monitor'); ?></a>
    | <a href="?page=monitor_abilities&subpage=logs"><?php esc_html_e('Logs', 'monitor'); ?></a>
</p>
