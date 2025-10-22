=== Monitor ===
Tags: monitor, emails, abilities
Tested up to: 6.8.3
Contributors: satollo
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Requires at least: 6.1
Requires PHP: 7.0

Records events like sent email, abilities execution, scheduler triggers. For developers and dignosis.

== Description ==

**Monitor** records some classes of WP events with a small set of data and makes them easily available on the administration side.

= Monitored events =

* **Abilities**: both the registered abilities and the executions
* **Emails**: sent emails (for more detailed logging I suggest the WP Mail Logging plugin
* **Scheduler**: records the trigger call top the wp-cron.php script
* **HTTP**: records the requests to external URLs

== Frequently Asked Questions ==

None, at moment.

== Screenshots ==

None, at moment.

== Changelog ==

= 0.0.7 =

* Improved HTTP monitoring
* Fixed monitoring of long URLs
* Excluded wp-cron.php autocall from HTTP monitoring
* Added test email

= 0.0.6 =

* Fixed HTTP monitoring

= 0.0.5 =

* Added HTTP monitoring


