# Routing
When a request arrives the framework must fulfill it.

The framework automatically maps a request to a resource located at "https://site.com/Home" into "/Home" (GET request).

The /Home string is the URI of the requested resource, the GET request is the HTTP method used to query that resource...
the question is.... 

How do I route that request to what I want to serve? The answer is: using the router!

The router is that fantastic component empowering your user-friendly URLs!


## Custom static rules
Let's see how to create a custom route:

```php
use \Gishiki\Core\Routing;

Routing::setRoute(Routing::GET, "/Home", function() {
    //this is what will be executed when the client asks for "https://site.com/Home"
    
    //let's just forget about MVC pattern this time :)
    echo "Hello, World!";
});
```

To try this rule open rules.php and paste the provided code into it and direct your browser to: https://site.com/Home


## Custom dynamic rules
This route is really simple: just an URI check... but you are creating a dynamic application, 
and URIs cannot be static URIs every time, in fact sooner or later you will need to capture a parameter passed 
as a parameter with the URL.

Let's just consider this simple example:
```php
use \Gishiki\Core\Routing;

Routing::setRoute(Routing::GET, "/Home/{name}", function($params) {
    //this is what will be executed when the client asks for "https://site.com/Home"
    
    //nice to meet you!
    echo "Hello, ".$params->get("name")."!";
});
```

You already know what you are going to do, right? :D

https://site.com/Home/your_name and you will see "Hello, your_name!" nothing complex here, right?


## Different methods
As for HTTP standards a client can perform a resource request using this methods:
  
   -  GET identified as Routing::GET
   -  POST  identified as Routing::POST
   -  DELETE identified as Routing::DELETE
   -  PUT identified as Routing::PUT
   -  HEAD identified as Routing::HEAD
   
When you set a routing rule (either static or dynamic) you have to select the method you want to serve
in that route.

This is particulary handy when creating a RESTful service.


## Error catching
You know.... things doesn't always go as you want....

This means it is necessary to think about unexpected circumstances. You do it by setting an error callback, 
which is nothing more than a bit special routing rule:

```php
use \Gishiki\Core\Routing;

Routing::setErrorCallback(Routing::NotFoudCallback, function() {
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