<?php
defined('ABSPATH') || exit;

function hook_functions($tag) {
    static $hooks = null;

    if (is_null($hooks)) {
        $hooks = get_option('monitor_scheduler_hooks', []);
    }
    foreach ($hooks[$tag] ?? [] as $function) {
        echo esc_html($function), '<br>';
    }
}
?>
<div class="wrap">
    <h2>Actions and Filters</h2>
    <?php include __DIR__ . '/nav.php'; ?>
    <p>
        Filters and Actions are "attaching points" where plugins can do something during the
        different scheduler activities. Functions attached to filters and actions can modify the
        scheduler behavior (and sometimes break it...).
    </p>

    <p>
        The functions attached to filters and actions are <em>captured</em> during the scheduler run.
    </p>

    <p>
        To see filters and actions active during a specific shceduler run, find it on the logs page.
    </p>

    <table class="widefat" style="width: auto">
        <thead>
            <tr>
                <th>Filter or Action</th>
                <th>Attached Functions</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <th><code>pre_get_ready_cron_jobs</code></th>
                <td><?php hook_functions('pre_get_ready_cron_jobs'); ?></td>
            </tr>

            <tr>
                <th><code>pre_unschedule_event</code></th>
                <td><?php hook_functions('pre_unschedule_event'); ?></td>
            </tr>
            <tr>
                <th><code>schedule_event</code></th>
                <td><?php hook_functions('schedule_event'); ?></td>
            </tr>

            <tr>
                <th><code>pre_reschedule_event</code></th>
                <td><?php hook_functions('pre_reschedule_event'); ?></td>
            </tr>
            <tr>
                <th><code>cron_request</code></th>
                <td><?php hook_functions('cron_request'); ?></td>
            </tr>

            <tr>
                <th><code>http_request_args</code></th>
                <td><?php hook_functions('http_request_args'); ?></td>
            </tr>
            <tr>
                <th><code>pre_http_request</code></th>
                <td><?php hook_functions('pre_http_request'); ?></td>
            </tr>
        </tbody>
    </table>
</div>