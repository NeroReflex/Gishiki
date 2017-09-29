# Logging
Gishiki comes with [monolog](https://github.com/Seldaek/monolog) and uses it as its logging engine.

Every PSR-3 compatible logger can be used and the integration is monolog own business. 


## Getting a logger
Within Gishiki each time you want to store a log entry a logger object is required.

The logger object is defined in the application configuration.

Using a logger is trivial: you call LoggerManager::retrieve() to retrieve the PSR-3
compatible logger instance.

If you pass null to LoggerManager::retrieve() than the default one
will be returned (see [configuration](configuration.md) for an example of configuration).


## Connection
Defining a logger instance, also erroneously called "connection" depends on the logger class to be used.

Each connection in the connection pool is expressed as "connection name": [{ ...attributes... }, { ...attributes... }].

There are two attributes to be used: "class" and "connection", where:
   
   - class is the name for the PSR-3 logger class
   - connection is an array of values to be passed to that class constructor
   
An example can be Monolog\Handler\StreamHandler: the class constructor accept the log file path
and the log level (standard PSR-3 integer).

```json
"default": [
  {
    "class": "StreamHandler",
    "connection": [
      "customLog.log",
      400
    ]
  }
]      
```

In the above example a logger instance called "default" is created.
That logger wrapper is bind to a StreamHandler logger instance that will be used for errors (400 code).


## Usage
The most trivial operation will be something like

```php
$logger = LoggerManager::retrieve("default");
$logger->error("something bad happened!");
```

For more, read the [PSR-3](http://www.php-fig.org/psr/psr-3/) specification.