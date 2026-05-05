=== Monitor: Scheduler, Emails, API, HTTP and more ===
Contributors: satollo
Tags: debug,logging,ai
Requires at least: 6.9
Tested up to: 6.9
Requires PHP: 8.1
Stable tag: 1.0.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Track and store internal WP events for analysis and debugging: abilities calls, http calls, emails, scheduled jobs, ...

== Description ==

When it's important to record what happens in your site internal, this plugin is an handy tool.

It can track abilities calls, http calls, record the result and in particular the filters attached to them that can modify the bahavior.

It's perfect to be used side-by-side with other logging and monitoring plugins, like wp mail logging and query monitor.

It can collect a lot of data, but you can change the retention period on your needs.

When unistalled all the logged data and settings are cleaned up.

Please, [read the official page for more details](htps://www.satollo.net/plugins/monitor).

= AI Ready =

The plugin provides a set of abilities that can be used by AI agents.
Not directly, they can be exposed via MCP, for example.
You can use the [AI official plugin](https://wordpress.org/plugins/ai/) and its ability explorer to play with the abilities.

= What's monitored =

* Emails
* HTTP calls (using the wp_remote_* functions)
* REST API calls
* Scheduler runs with detailed jobs data
* Abilities invocation
* AI Client calls (WP 7.0+)
* PHP errors

Please, [read the official page for more details](https://www.satollo.net/plugins/monitor) for more detail, examples of problems and solutions.

= Contacts, Requests, Bugs =

Please, contact me using the support forum or directly if you find problems or have new monitoring ideas.

= Tech details =

* All logged data is stored on your WP database (nothing is sent externally!) into dedicated tables named wp_monitor*.
* The HTTP tests (ok, bad, not found) just call my site (https://www.satollo.net) to produce a log, nothing is stored on my side
* The email tests, send an email to the admin address configured on your site (see the WP Settings)

== Frequently Asked Questions ==

= My database is clogged up due to Monitor logs! =

* Clean the logs from the single monitoring page. If it's an emergency delete the plugin for a full clean up.
* Reduce the retention period on Monitor/Settings
* Activate monitors only for what you really need on Monitor/Settings

= Does exist an extended version of Monitor? =

No, but if you have specific needs, let's discuss them.

= Is collected data shared with someone? =

No, all the data is stored into your WP database. No log files are created.

= Can I give access to Monitor to non administrator users? =

No, Monitor can be used only by administrators.

= Can I define specific roles/capabilities to grant access to otehr users? =

Not right now.

== Screenshots ==

1. WordPress scheduler overview with statistical values and graphs
2. Detailed scheduler runs with jobs
3. HTTP monitoring with calls per day
4. Overview of the email monitoring


== Changelog ==

= 1.0.5 =

* Fixed unistall and log clean up for PHP monitoring
* Added "emails by day" ability
* Changed a few ability names
* Added option to make the abilities visible with the default MCP server created by the plugin MCP Adapter

= 1.0.4 =

* Added PHP monitoring

= 1.0.3 =

* Added some experimental abilities: use the Monitor/Abilities page to see the Montor's abilities ;-)
* Added check for early stop of the scheduler
* Fixed unistall to delete the aiclient log table

= 1.0.2 =

* Added monitoring of AI Client (WP 7.0+)

= 1.0.1 =

* Fixed some typos
* Fixed log clean for the REST API monitoring
* Added the logs clean button on Abilities monitor page
* Fixed abilities tracking (WP 6.9 has a different hook than the original library...)

= 1.0.0 =

* First release

