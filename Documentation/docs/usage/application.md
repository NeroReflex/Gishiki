# Application
An __Application__ object is the entrypoint of the Gishiki framework.

An application instance is responsible of:
   1. loading application [settings](configuration.md)
   1. creating a PSR-7 Request out of the request received by the web server
   1. running the router to generate a PSR-7 Response
   1. emit the generated Response
   
When you are done configuring your web server to call index.php on each request you are
ready to write the entrypoint for your application:

```php
use Gishiki\Core\Application;
use Gishiki\Core\Router\Router;
use Gishiki\Core\Router\Route;

// you can also avoid passing parameters if you want to use the default configuration file (settings.json)
$app = new Application(null, "settings.json");

// create the router and populate it with routes
$router = new Router();
$router->add( ... );

// run the router to call the correct code and generate the Response
$app->run($router);

// emit the generated response
$app->emit();
```

This is it. This is the entrypoint of your application.

## Caching
As you might expect every request force the framework to read a file: this may become
a major performance issue, this is why memcached is used:

```php
use Gishiki\Core\Application;
use Gishiki\Core\Router\Router;
use Gishiki\Core\Router\Route;

// create a Memcached instance and connect to servers
$caching = new Memcached();
$caching->addServer("127.0.0.1", 11211);

// notice that settings will be load from cache
$app = new Application(null, "settings.json", $caching);

// create the router and populate it with routes
$router = new Router();
$router->add( ... );

// run the router to call the correct code and generate the Response
$app->run($router);

// emit the generated response
$app->emit();
```

You just can't do better than that.

## Emitters
The standard Zend Emitter is brilliant and works great out of the box, however you may need
to use a different one, especially when dealing with large files.

You can change the default emitter by passing the preferred one to the Application constructor as the first argument.

```php
use Gishiki\Core\Application;
use Gishiki\Core\Router\Router;
use Gishiki\Core\Router\Route;

$customEmitter = new CustomEmitter();

//the custom emitter will be used
$app = new Application($customEmitter);

// create the router and populate it with routes
$router = new Router();
$router->add( ... );

// run the router to call the correct code and generate the Response
$app->run($router);

// emit the generated response with the custom emitter
$app->emit();
```

Notice that to be used an emitter __must__ implement EmitterInterface (From [Zend Diactoros](https://github.com/zendframework/zend-diactoros)).

A ready to use alternative emitter is the zend diactoros SapiStreamEmitter.