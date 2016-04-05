# Routing
When a request arrives the framework must fulfill it.

The framework automatically maps a request to a resource located at "https://site.com/Home" into "/Home" (GET request).

The /Home string is the URI of the requested resource, the GET request is the HTTP method used to query that resource...
the question is.... 

How do I route that request to what I want to serve? The answer is: using the router!

The router is that fantastic component empowering your user-friendly URLs!


## Different methods
As for HTTP standards a client can perform a resource request using these verbs:
  
   -  GET identified as Route::GET
   -  POST  identified as Route::POST
   -  DELETE identified as Route::DELETE
   -  PUT identified as Route::PUT
   -  HEAD identified as Route::HEAD
   
When you set a routing rule (either static or dynamic) you have to select the 
verb that you want to be served in that route.

This is particulary handy when creating a RESTful service.


## Custom static rules
Let's see how to create a custom route:

```php
use \Gishiki\Core\Route;

Route::get("/Home", function() {
    //this is what will be executed when the client asks for "https://site.com/Home"
    
    //let's just forget about MVC pattern this time :)
    echo "Hello, World!";
});
```

To try this rule out you have to open rules.php and paste the provided code into 
it and direct your browser to: https://site.com/Home .


## Custom dynamic rules
This route is really simple: just an URI check... but you are creating a dynamic application, 
and URIs cannot be static URIs every time, in fact sooner or later you will need to capture a parameter passed 
as a parameter with the URL.

Let's just consider this simple example:
```php
use \Gishiki\Core\Route;

Route::get("/Home/{name}", function($params) {
    //this is what will be executed when the client asks for "https://site.com/Home/some_name"
    
    //nice to meet you!
    echo "Hello, ".$params->get("name")."!";
});
```

You already know what you are going to do, right? :D

https://site.com/Home/your_name and you will see "Hello, your_name!" nothing complex here, right?


## All request methods
Sometimes you may need to register a route that responds to all HTTP verbs, you 
are alowed to do that by using 'any':

```php
use \Gishiki\Core\Route;

Route::any("/Home", function() {
    //do something here
});
```

the action is taken if that URI is hit, regardless of the method the client used 
to perform the request.


## Two or more request methods
An interesting feature of the router is how you create a route for two or more
request methods:

```php
use \Gishiki\Core\Route;

Route::match([Route::GET, Route::POST], "/Home", function() {
    //do something here
});
```

the action is taken if that URI is hit only when using get or post request method. 


## Error catching
You know, things doesn't always go as you want: it is necessary to think about 
unexpected circumstances. You do it by setting an error callback, 
which is nothing more than a bit special routing rule:

```php
use \Gishiki\Core\Route;

Route::error(Route::NotFound, function() {
    //this is what will be executed when the client asks for an unrouted URI
    
    //error message!
    echo "Sorry man, you are asking for something I can't give you :(";
});
```

As you can see an error routing rule (or error callback) is unique for all request methods.


## Limitation
You cannot route URIs that start with /API/ or /service/ because they are reserved for web services (explained in a different chapter).

You cannot match something that has a slash '/' character using a dynamic placeholder: {name} cannot capture something like "mario/rossi".


## Conclusions
You can consider your anonymous functions passed to the routing as your controllers: they will be the glue between
your models and your views! 

The router is the fastest and easiest component within Gishiki, 
because it is the first component that is used and the only one you __REALLY HAVE TO__ use.

This means that you are now good to go... Everything you *MUST* know in order to use Gishiki ends here.

Everything else is a plus you may need to accelerate the development of your projects!