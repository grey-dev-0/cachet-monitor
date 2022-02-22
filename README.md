# Cachet Monitor

A php-based monitor and reporter for [CachetHQ status page](https://github.com/cachetHQ/Cachet) project, this monitor is built with [Laravel Zero](https://github.com/laravel-zero/laravel-zero), this project is inspired by other third party integrations listed in the [official Cachet documentation](https://docs.cachethq.io/docs/addons), all of those integrations provided basic HTTP and domain checks but, none of them had a way to monitor an active websocket server and, that's why I started this small project.

## Work In Progress

This project only supports the following as of now:

- HTTP pings over a constant preset interval of time with response code check.
- Websocket connectivity check, basically by connecting to the `ws` or `wss` server and, report cachet if server disconnects / reconnects.
- Shell process checks by running a command and, checking if its output contains the required substring.

All the checks listed above can only be mapped to Cachet's standard components by updating their statuses only as of now so, incidents and metrics will be implemented later on.

## Attribution

The real credit goes for the maintainers and contributors of Laravel, Laravel Zero and, CachetHQ. 