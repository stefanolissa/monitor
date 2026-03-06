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
    <a href="?page=monitor&section=emails">Overview</a>
    | <a href="?page=monitor&section=emails&subpage=logs">Logs</a>
</p>
