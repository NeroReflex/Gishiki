# Logging
Gishiki comes with a simple and small logging library that uses the UNIX logging daemon (and its emulation on Windows).

The behaviour of the logger depends on the syslogd configuration: the standard behaviour is to save each log entry 
into a log file, together with any other software message.

## Create & Save
Each log entry instance you create from the Log class is automatically saved using syslogd.

Creating a log entry is pretty simple:

```php
use \Gishiki\Core\Routing;
use \Gishiki\Logging\Log;

Routing::setErrorCallback(Routing::NotFoudCallback, function() {
    //this is what will be executed when the client asks for an unrouted URI
    new Log("404 Not Foud", "A user has tried to access the resource located at URI: ".Routing::getRequestURI());
    
    //error message!
    echo "Sorry man, you are asking for something I can't give you :(";
});
```

Since the syslogd is something that is not (and MUST be not) accessible by the client you can log everything you want, 
the user will never notice the creation of a log entry.

## Priority
You know... priorities are important in software development as they are in real life.

To help you better organizing your priorities you can change the default priority of a log entry:

```php
use \Gishiki\Core\Routing;
use \Gishiki\Logging\Log;
use \Gishiki\Logging\Priority;

Routing::setErrorCallback(Routing::NotFoudCallback, function() {
    //this is what will be executed when the client asks for an unrouted URI
    new Log("404 Not Foud", "A user has tried to access the resource located at URI: ".Routing::getRequestURI(), Priority::INFO);
    
    //error message!
    echo "Sorry man, you are asking for something I can't give you :(";
});
```

You have these log priorities available:
  
   -  Priority::EMERGENCY
   -  Priority::ALERT
   -  Priority::CRITICAL
   -  Priority::ERROR
   -  Priority::WARNING
   -  Priority::NOTICE
   -  Priority::INFO
   -  Priority::DEBUG
   
When create a log choose the best priority for that log entry (default is Priority::WARNING).


## Find out more
This is everything you should know to use the logger, but if you want something more advanced you will have to read the
API documentation.


## Configure syslogd
Configuring syslog is pretty easy, but I can give you an example of file redirection for syslog-ng:

```
destination df_userapp { file("/var/www/html/application/logs/current.log"); };
    
filter f_userapp { program("Gishiki"); };
    
log {
    source(s_all);
    filter(f_userapp);
    destination(df_userapp);
};
```

What i gave you is just a simple example to get you started... There is no limit of what you can do with the logging daemon,
so configure it as you want (read your syslogd documentation).


## Exception autologging
Every exception that inherits from Gishiki_Exception and call the parent constructor is automatically logged with 
the Priority::CRITICAL level of priority.


## Conclusions
The logger is (just like the router is) designed to be fast and super-easy to use, but that is achieved without 
losing the usage flexibility.