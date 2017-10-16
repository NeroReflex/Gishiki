# Routing
When a request is made to the web server the framework must fulfill it.

The framework automatically maps a request to a resource located at "https://site.com/Home" into "/Home",
instantiates the correct [Controller](controller.md) to perform the requested action.

The router is that __fantastic__ component empowering your user-friendly URLs by mapping a rule
to a controller and action withing that controller.


## HTTP methods
As for HTTP standards a client can perform a resource request using these verbs:

   -  GET identified as RouteInterface::GET
   -  POST  identified as RouteInterface::POST
   -  DELETE identified as RouteInterface::DELETE
   -  PUT identified as RouteInterface::PUT
   -  HEAD identified as RouteInterface::HEAD
   -  PATCH identified as RouteInterface::PATCH
   -  OPTIONS identified as RouteInterface::OPTIONS

When you set a routing rule (either static or dynamic) you have to select the
verb that you want to be served in that route.

This is really handy when creating a RESTful service.


## Static Rules
A static rule is a rule that maps only to itself.

The most obvious example can be the __/__ rule that is the one called when the user tries to
direct his browser to www.yoursite.com

Another example can be the __/Home__ rule that is followed when the used address is something like:
wwww.yoursite.com/Home


## Dynamic Rules
You may need to catch input from the URL requested by the client.

This is handy when creating a service with user-friendly URLs.

The most obvious example would be __/{user}__ that will map every URL like
www.yoursite.com/User1, www.yoursite.com/John www.yoursite.com/45

If you want to query an user by its ID you would use the __/{ID:uint}__ rule,
that will match www.yoursite.com/45 and every other *unsigned* integer.

That's great isn't it? Actually what you can filter is:

   -  'string' a generic string
   -  'email' an email address
   -  'int' an integer number
   -  'uint' an integer number
   -  'float' an integer number

However keep in mind that you __cannot__ match something that has a slash '/'
character using a dynamic placeholder: {name} cannot capture something like "mario/rossi".

Also, to keep router simple and fast the opening character __MUST__ be used immediately
after the / character (something like /user/id={id:uint} is __NOT__ valid).


## Error catching
You know, things do not always go as you want: it is necessary to think about
unexpected circumstances.


## Limitation
You __SHOULD NOT__ attempt to route URIs that start with */api* or */service*
as those are reserved to framework plugins and core API.


## Using the router
In order to use the router you have to:
  1. create one or more rules: those rules are represented as a __Route__ instance
  1. register wanted rules within a __Router__ instance
  1. run the framework by calling $app->run(router)
  1. send back the response to the client by running $app->emit()

A Route instance is created by passing an associative array containing:
  - 'controller' the name of the target controller
  - 'action' the name of the function of the controller
  - 'uri' what has been called "rule" above
