<?php
defined('ABSPATH') || exit;

global $monitor_settings;

if (empty($monitor_settings['emails'])) {
    echo '<div class="notice notice-warning">
            <p>This monitoring is not active.</p>
        </div>';
}
?>
<p>
    <a href="?page=monitor&section=emails">Overview</a>
    | <a href="?page=monitor&section=emails&subpage=logs">Logs</a>
</p>
