# Civirules logger

This extensions logs civirules errors to the database and provides a user interface.

The user interface is accesed by going to the Civirule form and then click the link to see the log entries

There is also a daily scheduled job, "Call CivirulesLogger.PruneLog API", which prunes the log:

- `retention_days` (default 90): entries older than this are deleted.
- `debug_retention_days` (default 7): debug entries, written for every evaluation of a rule with "Enable Debug Logging" on, are deleted sooner. Turn debug logging off again once the rule is fixed: on a busy rule the log grows by gigabytes.
- `batch_size` (default 10000): entries deleted per query.