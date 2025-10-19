<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_admin_referer('monitor-save');

    // TODO: cleanup the options
    update_option('monitor', $_POST['data']);
}

$data = get_option('monitor', []);

wp_enqueue_script('dashboard');
?>
<div class="wrap">
    <h2>Monitor</h2>

    <p>
        Monitor keeps an eye on WP events (emails, background tasks, ...) providing
        statistics and logs. If you have any specific need, write me at stefano@satollo.net.
    </p>


    <form method="post">
    <?php wp_nonce_field('monitor-save'); ?>
        <table class="form-table" role=""presentation">
            <tr>
                <th>
                    Monitor emails
                </th>
                <td>
                    <input type="checkbox" value="1" name="data[emails]" <?php echo isset($data['emails']) ? 'checked' : ''; ?>>
                </td>
            </tr>
            <tr>
                <th>
                    Monitor abilities
                </th>
                <td>
                    <input type="checkbox" value="1" name="data[abilities]" <?php echo isset($data['abilities']) ? 'checked' : ''; ?>>
                </td>
            </tr>
            <tr>
                <th>
                    Monitor scheduler
                </th>
                <td>
                    <input type="checkbox" value="1" name="data[scheduler]" <?php echo isset($data['scheduler']) ? 'checked' : ''; ?>>
                </td>
            </tr>
            <tr>
                <th>
                    Keep logs for
                </th>
                <td>
                    <select name="data[days]">
                        <option value="30">30 days</option>
                        <option value="30">60 days</option>
                        <option value="30">90 days</option>
                    </select>
                </td>
            </tr>
        </table>
        <button>Save</button>
    </form>
    <pre><?php echo esc_html(print_r($data, true)); ?></pre>



    <div id="dashboard-widgets-wrap">
        <div id="dashboard-widgets" class="metabox-holder">


            <div id="postbox-container-1" class="postbox-container">

                <div id="normal-sortables" class="meta-box-sortables">

                    <div id="monitor-emails" class="postbox " >

                        <div class="postbox-header">
                            <h2 class="hndle">Emails</h2>
                            <div class="handle-actions hide-if-no-js">
                                <button type="button" class="handle-order-higher" aria-disabled="false" aria-describedby="dashboard_site_health-handle-order-higher-description">
                                    <span class="screen-reader-text">Move up</span>
                                    <span class="order-higher-indicator" aria-hidden="true"></span>
                                </button>
                                <span class="hidden" id="dashboard_site_health-handle-order-higher-description">Move Site Health Status box up</span>
                                <button type="button" class="handle-order-lower" aria-disabled="false" aria-describedby="dashboard_site_health-handle-order-lower-description">
                                    <span class="screen-reader-text">Move down</span>
                                    <span class="order-lower-indicator" aria-hidden="true"></span>
                                </button>
                                <span class="hidden" id="dashboard_site_health-handle-order-lower-description">Move Site Health Status box down</span>
                                <button type="button" class="handlediv" aria-expanded="true">
                                    <span class="screen-reader-text">Toggle panel: Site Health Status</span>
                                    <span class="toggle-indicator" aria-hidden="true"></span>
                                </button>
                            </div>
                        </div>

                        <div class="inside">
                            Here an overview of the sent emails.

                            <!--
                            <div class="health-check-widget">
                                <div class="health-check-widget-title-section site-health-progress-wrapper loading hide-if-no-js">
                                    <div class="site-health-progress">
                                        <svg aria-hidden="true" focusable="false" width="100%" height="100%" viewBox="0 0 200 200" version="1.1" xmlns="http://www.w3.org/2000/svg">
                                            <circle r="90" cx="100" cy="100" fill="transparent" stroke-dasharray="565.48" stroke-dashoffset="0"></circle>
                                            <circle id="bar" r="90" cx="100" cy="100" fill="transparent" stroke-dasharray="565.48" stroke-dashoffset="0"></circle>
                                        </svg>
                                    </div>
                                    <div class="site-health-progress-label">
                                        Results are still loading&hellip;							</div>
                                </div>

                                <div class="site-health-details">
                                    <p>
                                        Your site has a critical issue that should be addressed as soon as possible to improve its performance and security.									</p>

                                    <p>
                                        Take a look at the <strong>7 items</strong> on the <a href="http://www.wordpress.lan/ai/wp-admin/site-health.php">Site Health screen</a>.				</p>
                                </div>
                            </div>
                            -->
                        </div>
                    </div>

                    <div id="dashboard_right_now" class="postbox " >

                        <div class="postbox-header">
                            <h2 class="hndle">At a Glance</h2>
                            <div class="handle-actions hide-if-no-js"><button type="button" class="handle-order-higher" aria-disabled="false" aria-describedby="dashboard_right_now-handle-order-higher-description"><span class="screen-reader-text">Move up</span><span class="order-higher-indicator" aria-hidden="true"></span></button><span class="hidden" id="dashboard_right_now-handle-order-higher-description">Move At a Glance box up</span><button type="button" class="handle-order-lower" aria-disabled="false" aria-describedby="dashboard_right_now-handle-order-lower-description"><span class="screen-reader-text">Move down</span><span class="order-lower-indicator" aria-hidden="true"></span></button><span class="hidden" id="dashboard_right_now-handle-order-lower-description">Move At a Glance box down</span><button type="button" class="handlediv" aria-expanded="true"><span class="screen-reader-text">Toggle panel: At a Glance</span><span class="toggle-indicator" aria-hidden="true"></span></button></div></div>
                        <div class="inside">

                            <div class="main">
                                <ul>
                                    <li class="post-count"><a href="edit.php?post_type=post">1 Post</a></li><li class="page-count"><a href="edit.php?post_type=page">1 Page</a></li>		<li class="comment-count">
                                        <a href="edit-comments.php">1 Comment</a>
                                    </li>
                                    <li class="comment-mod-count hidden">
                                        <a href="edit-comments.php?comment_status=moderated" class="comments-in-moderation-text">0 Comments in moderation</a>
                                    </li>
                                </ul>
                                <p id='wp-version-message'><span id="wp-version">WordPress 6.8.3 running <a href="themes.php">Twenty Twenty-Five</a> theme.</span></p>
                            </div>
                        </div>
                    </div>

                    <div id="dashboard_activity" class="postbox " >
                        <div class="postbox-header"><h2 class="hndle">Activity</h2>
                            <div class="handle-actions hide-if-no-js"><button type="button" class="handle-order-higher" aria-disabled="false" aria-describedby="dashboard_activity-handle-order-higher-description"><span class="screen-reader-text">Move up</span><span class="order-higher-indicator" aria-hidden="true"></span></button><span class="hidden" id="dashboard_activity-handle-order-higher-description">Move Activity box up</span><button type="button" class="handle-order-lower" aria-disabled="false" aria-describedby="dashboard_activity-handle-order-lower-description"><span class="screen-reader-text">Move down</span><span class="order-lower-indicator" aria-hidden="true"></span></button><span class="hidden" id="dashboard_activity-handle-order-lower-description">Move Activity box down</span><button type="button" class="handlediv" aria-expanded="true"><span class="screen-reader-text">Toggle panel: Activity</span><span class="toggle-indicator" aria-hidden="true"></span></button></div></div>

                    </div>
                </div>
            </div>


            <div id="postbox-container-2" class="postbox-container">

                <div id="normal-sortables" class="meta-box-sortables">

                    <div id="monitor-scheduler" class="postbox " >

                        <div class="postbox-header">
                            <h2 class="hndle">Scheduler</h2>
                            <div class="handle-actions hide-if-no-js">
                                <button type="button" class="handle-order-higher" aria-disabled="false" aria-describedby="dashboard_site_health-handle-order-higher-description">
                                    <span class="screen-reader-text">Move up</span>
                                    <span class="order-higher-indicator" aria-hidden="true"></span>
                                </button>
                                <span class="hidden" id="dashboard_site_health-handle-order-higher-description">Move Site Health Status box up</span>
                                <button type="button" class="handle-order-lower" aria-disabled="false" aria-describedby="dashboard_site_health-handle-order-lower-description">
                                    <span class="screen-reader-text">Move down</span>
                                    <span class="order-lower-indicator" aria-hidden="true"></span>
                                </button>
                                <span class="hidden" id="dashboard_site_health-handle-order-lower-description">Move Site Health Status box down</span>
                                <button type="button" class="handlediv" aria-expanded="true">
                                    <span class="screen-reader-text">Toggle panel: Site Health Status</span>
                                    <span class="toggle-indicator" aria-hidden="true"></span>
                                </button>
                            </div>
                        </div>

                        <div class="inside">
                            Here an overview of the scheduler.

                        </div>
                    </div>

                </div>
            </div>


            <div id="postbox-container-2" class="postbox-container">

                <div id="normal-sortables" class="meta-box-sortables">

                    <div id="monitor-abilities" class="postbox " >

                        <div class="postbox-header">
                            <h2 class="hndle">Abilities</h2>
                            <div class="handle-actions hide-if-no-js">
                                <button type="button" class="handle-order-higher" aria-disabled="false" aria-describedby="dashboard_site_health-handle-order-higher-description">
                                    <span class="screen-reader-text">Move up</span>
                                    <span class="order-higher-indicator" aria-hidden="true"></span>
                                </button>
                                <span class="hidden" id="dashboard_site_health-handle-order-higher-description">Move Site Health Status box up</span>
                                <button type="button" class="handle-order-lower" aria-disabled="false" aria-describedby="dashboard_site_health-handle-order-lower-description">
                                    <span class="screen-reader-text">Move down</span>
                                    <span class="order-lower-indicator" aria-hidden="true"></span>
                                </button>
                                <span class="hidden" id="dashboard_site_health-handle-order-lower-description">Move Site Health Status box down</span>
                                <button type="button" class="handlediv" aria-expanded="true">
                                    <span class="screen-reader-text">Toggle panel: Site Health Status</span>
                                    <span class="toggle-indicator" aria-hidden="true"></span>
                                </button>
                            </div>
                        </div>

                        <div class="inside">
                            Here an overview of the abilities.

                        </div>
                    </div>

                </div>
            </div>



        </div>
    </div>
</div>
