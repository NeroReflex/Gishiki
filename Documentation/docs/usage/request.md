# Request
The __Gishiki\HttpKernel\Request__ class is used to fully represent an HTTP request.

The Request class is PSR-7 conformant and follows that specification sheet.

 
## HTTP Method
When an HTTP request is done sent to a server it have to specify the type of the
request, that request type is called 'method'.

Usually you work with the following methods:

  - GET
  - POST
  - PUT
  - DELETE
  - HEAD
  - PATCH
  - OPTIONS

but your application can also support your own methods!

You can inspect the HTTP request’s method with the Request object method
appropriately named getMethod()

```php
use Gishiki\Core\Route;
use Gishiki\Logging\Logger;
use Gishiki\HttpKernel\Request;
use Gishiki\HttpKernel\Response;
use Gishiki\Algorithms\Collections\GenericCollection;

Route::any("/method_test",
    function (Request $request, Response &$response, GenericCollection &$arguments)
{
    $method = $request->getMethod();

    //this is what will be executed when the client asks for an unrouted URI
    $response->withStatus(200);

    //error message!
    $response->write("You have used the ".$method." method to fetch this page!");
});
```

However you can override the standard HTTP method including in the header an
X-Http-Method-Override property, for example:

```
POST /path HTTP/1.1
Host: example.com
Content-type: application/json
Content-length: 16
X-Http-Method-Override: PUT
```

And the code before will return the string "PUT", and not the string "POST".

If you want to retrive the real HTTP method used (non-overridden) you can do it
using another function which is called getOriginalMethod():

```php
use Gishiki\Core\Route;
use Gishiki\Logging\Logger;
use Gishiki\HttpKernel\Request;
use Gishiki\HttpKernel\Response;
use Gishiki\Algorithms\Collections\GenericCollection;

Route::any("/method_test",
    function (Request $request, Response &$response, GenericCollection &$arguments)
{
    $method = $request->getMethod();
    $origin_method = $request->getOriginalMethod();

    //this is what will be executed when the client asks for an unrouted URI
    $response->withStatus(200);

    //error message!
    $response->write("You have used the ".$method." method to fetch this page!\n");
    
    if ($method != $origin_method) 
        $response->write("However the request used the ".$origin_method." method to fetch this page!\n");
});
```

If you try overriding the HTTP method you will see the you will be found cheating :D


## Request URI
Every HTTP request has a URI that identifies the requested application resource.
The HTTP request URI is composite of several parts:

  - Scheme (e.g. http or https)
  - Host (e.g. example.com)
  - Port (e.g. 80 or 443)
  - Path (e.g. /users/1)
  - Query string (e.g. sort=created&dir=asc)

You can fetch the Request object’s URI using its getUri() method:

```php
use Gishiki\Core\Route;
use Gishiki\Logging\Logger;
use Gishiki\HttpKernel\Request;
use Gishiki\HttpKernel\Response;
use Gishiki\Algorithms\Collections\GenericCollection;

Route::any("/method_test",
    function (Request $request, Response &$response, GenericCollection &$arguments)
{
    //get the URI of the current request
    $uri = $request->getUri();
    
    //do something with this URI
});
```

Operation allowed on that URI are:

   - getScheme()
   - getAuthority()
   - getUserInfo()
   - getHost()
   - getPort()
   - getPath()
   - getBasePath()
   - getQuery() (returns the full query string, e.g. a=1&b=2)
   - getFragment()
   - getBaseUrl()

