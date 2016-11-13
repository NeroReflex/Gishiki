# Logging

Gishiki comes with a simple and small logging library that may use different logging
technologies like the UNIX logging daemon (and its emulation on Windows).

The logger included in Gishiki is PSR-3 compilant and is super-simple to setup and use!


## Getting a logger

Within Gishiki each time you want to store a log entry a logger object is required.

The logger object is derived from the Gishiki\Logging\Logger class.

When creating a new instance of the logger class you may and may not pass
(to the class constructor) a string that identify the method used to store log entries.

If you don't pass an URL to the newly created Logger instance than the default one
will be used (see [configuration](configuration.md) for an example of configuration
with "autolog" set to null).


## Connection

The connection string is an URL with the following structure:

```
adapter://resource
```

When the adapter is the adapter used to access the resource and the resource is specific
with one adapter.

At the moment supported adapters are:

   - file
   - gelf
   - stream
   - syslog
   - null

A valid example can be:

```php
$logger = new Gishiki\Logger\Logging("file://logs.txt");
```
   
You need to read the correct sub-character of the page to understand how to identify the
resource to be used.


### File

If a file is used to register log entries the resource is given as a path to the file.

The file MUST be accessible with write permissions to php.

The file path can either be given as a relative path or an absolute file.

```
file:///var/logs/myApp.log
```

this will store the log in the /var/logs/myApp.log file.


### Syslog

If you want the Unix syslogd service to be responsible for log management than the
configuration string must be something like:

```
syslog://myAppName
```

Where myAppName is the name of your application.

This is particularly useful if you want to be able to entirely customize the 
log management process or avoid file-write permission problems.


### Stream

Sometimes, due to the system configuration, you may want to save your logs on stdout,
stderr or a memory stream.

```
stream://<stream_name>
```

The *stream name* can be one of the following:

   - stdout
   - stderr
   - stdmem


### Graylog

If you have an active graylog server you can send log entries to that server by
enstabilishing a connection using a query like:

```
gelf://<protocol>:<hostname>:<port>
```

Where *protocol* can be either __UDP__ or __TCP__.


## Writing a log entry

To send a log entry you must call the __log__ function.

The first argument is the level of severity, it can be one of the following:

   - \Psr\Log\LogLevel::EMERGENCY -> 'emergency'
   - \Psr\Log\LogLevel::ALERT -> 'alert'
   - \Psr\Log\LogLevel::CRITICAL -> 'critical'
   - \Psr\Log\LogLevel::ERROR -> 'error'
   - \Psr\Log\LogLevel::WARNING -> 'warning'
   - \Psr\Log\LogLevel::NOTICE -> 'notice'
   - \Psr\Log\LogLevel::INFO -> 'info'
   - \Psr\Log\LogLevel::DEBUG -> 'debug'

you can use either the string or the contant provided by the psr package (suggested).

The second argument is the message, and the third argument is a collection of
details as a simple PHP array:

```php
$logger = new Logger("gelf://<protocol>:<hostname>:<port>");
$logger->log(\Psr\Log\LogLevel::EMERGENCY, "Environmental temperature too high", [
        'temperature' => 56.7,
    ]);
```

With stream and file adapters the collection of values are a sobstitution mask
for the message:

```php
$logger = new Logger("stream://stdout");
$logger->log(\Psr\Log\LogLevel::EMERGENCY, "Environmental temperature too high: {{temperature}} °C", [
        'temperature' => 56.7,
    ]);
```

That code will print out the string "Environmental temperature too high: 56.7 °C"
as a result.


## Specialized Writers

It is possible (permitted by the PSR-3 standard) to call a specialized function
that identify itselfs the severity of the log entry, those functions are called
like the severity to be used:

```php
$logger = new Logger("stream://stdout");
$logger->emergency("Environmental temperature too high: {{temperature}} °C", [
        'temperature' => 56.7,
    ]);
```

The complete list of those functions is:

   - emergency
   - alert
   - critical
   - error
   - warning
   - notice
   - info
   - debug