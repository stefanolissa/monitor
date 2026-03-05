<div id="monitor-menu">
    <div id="monitor-menu-title">Monitor</div>
    <div id="monitor-menu-nav">
        <ul>
            <li><a href="?page=monitor&section=scheduler">Scheduler</a></li>
            <li><a href="?page=monitor&section=http">HTTP</a></li>
            <li><a href="?page=monitor&section=abilities">Abilities</a></li>
            <li><a href="?page=monitor&section=rest">REST API</a></li>
            <li><a href="?page=monitor&section=emails">Emails</a></li>
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
