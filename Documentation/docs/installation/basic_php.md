# Basic PHP
You __have to__ download and install PHP on your development environment and on
your production server.

You don't need a webserver, nor a database manager, nor a graylog2 server:
you *only* need PHP version 5.6 or greater!


## PHP Webserver
You may not want to install a webserver in you development machine, but you might
want to test your products by yourself and locally before performing the push/deploy.

You can test your products by starting the PHP own's webserver:

```shell
php -S localhost:8080 -t ./
```

This feature is meant for testing purpouse because it is IDE friendly and easy to
use without an ide, but you __should avoid__ using PHP webserver in production!