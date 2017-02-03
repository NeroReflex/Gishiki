# Basic PHP
You may not want to install a webserver in you development machine, but you might
want to test your products by yourself and locally before performing the push/deploy.

If your php version is greather or equal than 5.6 you can test your products by
starting the PHP built-in webserver:

```shell
php -S localhost:8080 -t ./
```

This feature is meant for testing purpouse __ONLY__ because it is IDE friendly
and easy to use without an IDE, but you __should avoid__ using PHP webserver
in a production environment due to its *really* low performance!