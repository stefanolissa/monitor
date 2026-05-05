<?php
defined('ABSPATH') || exit;

$monitor_settings = get_option('monitor_settings');
$abilities_on = isset($monitor_settings['abilities']);
$rest_on = isset($monitor_settings['rest']);
$emails_on = isset($monitor_settings['emails']);
$http_on = isset($monitor_settings['http']);
$scheduler_on = isset($monitor_settings['scheduler']);
$aiclient_on = isset($monitor_settings['aiclient']);
$php_on = isset($monitor_settings['php']);
?>

<div id="monitor-menu">
    <div id="monitor-menu-title">Monitor</div>
    <div id="monitor-menu-nav">
        <ul>
            <li><a href="?page=monitor&section=scheduler" class="<?= $scheduler_on ? 'monitor-on' : '' ?>">Scheduler</a></li>
            <li><a href="?page=monitor&section=http" class="<?= $http_on ? 'monitor-on' : '' ?>">HTTP</a></li>
            <li><a href="?page=monitor&section=abilities" class="<?= $abilities_on ? 'monitor-on' : '' ?>">Abilities</a></li>
            <li><a href="?page=monitor&section=aiclient" class="<?= $aiclient_on ? 'monitor-on' : '' ?>">AI Client</a></li>
            <li><a href="?page=monitor&section=rest" class="<?= $rest_on ? 'monitor-on' : '' ?>">REST API</a></li>
            <li><a href="?page=monitor&section=emails" class="<?= $emails_on ? 'monitor-on' : '' ?>">Emails</a> </li>
            <li><a href="?page=monitor&section=php" class="<?= $php_on ? 'monitor-on' : '' ?>">PHP</a> </li>
            <li><a href="?page=monitor&section=users">Users</a></li>
            <li><a href="?page=monitor&section=settings">Settings</a></li>
        </ul>
    </div>

    <div></div>
</div>
<script>
    jQuery(function () {
        jQuery('#monitor-menu-nav a').each(function () {
            if (location.href.indexOf(this.href) >= 0) {
                jQuery(this).addClass('monitor-active');
            }
        });
    });
</script>
