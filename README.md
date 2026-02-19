# Monitor

A WordPress plugin to monitor events (emails, scheduler, abilities, ...). It logs
everything and keeps all the information into the database, so it can be used as
"recorder".

It's not a plugin to debug what happens during a single request, you may prefer using
WP Query Monitor instead.

The two most useful features are the Scheduler and the Emails monitoring. Emails
can be monitored even with the plugin WP Mail Logging.

## Install

See the instructions in the [official Monitor page](https://www.satollo.net/plugins/monitor).

## What can be monitored

- Emails
- Scheduler
- REST API calls
- HTTP calls
- Abilities calls
- Overview on users

I'm open to add more features, just ask!

## Disclaimer

This plugin is an experiment, do not use it on production sites. The code is naif.

## How To and FAQ

When the plugin is deactivated and deleted from the site plugins page, all data is deleted as well.
The cleanup should not leave any trace. If you find remains of the plugin once deleted, let me know
so I can adjust the unistall procedure.

The plugin stores a lot of information inside the database, it is required when monitoring
to find problems: you should activate only the monitoring features you need.

Please, set the clean up period to something consistent with your analysis needs, like
15 days.

Emails sent not using the wp_mail() function cannot be monitored.

HTTP calls made with file_get_contents, curl or sockets cannot be monitored (usually
malaware uses those ones...).


