# Logging
Gishiki comes with a simple and small logging library that may use different logging
technologies like the UNIX logging daemon (and its emulation on Windows).

The logger included in Gishiki is PSR-3 compilant and is super-simple to setup and use!


## Getting a logger
Within Gishiki each time you want to store a log entry a logger object is required.

The logger object is derived from the Gishiki\Logging\Logger class.

When creating a new instance of the logger class you may and may not pass
(to the class constructor) a string that identify the method used to store log entries.


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