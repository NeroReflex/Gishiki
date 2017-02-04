# Request
The __Gishiki\HttpKernel\Request__ class is used to fully represent an HTTP request.

The Request class is PSR-7 conformant and follows that specification sheet.

 
## Request Method
When an HTTP request is sent to the server the client has to specify the type of the
request, that request type is called 'method'.

Usually you work with following methods:

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
use Gishiki\Algorithms\Collections\SerializableCollection;

Route::any("/method_test",
    function (Request $request, Response &$response, SerializableCollection &$arguments)
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
use Gishiki\Algorithms\Collections\SerializableCollection;

Route::any("/method_test",
    function (Request $request, Response &$response, SerializableCollection &$arguments)
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
use Gishiki\Algorithms\Collections\SerializableCollection;

Route::any("/method_test",
    function (Request $request, Response &$response, SerializableCollection &$arguments)
{
    //get the URI of the current request
    $uri = $request->getUri();
    
    //do something with this URI
});
```

Operation allowed on an URI are:

   - getScheme()
   - getAuthority()
   - getUserInfo()
   - getHost()
   - getPort()
   - getPath()
   - getBasePath()
   - getQuery()
   - getFragment()
   - getBaseUrl()
   - getQueryParams()

where getQueryParams() returns an associative array, getQuery() returns the complete
query string and getBaseUrl() the complete URL of the request.


## Request Headers
Headers are metadata that describe the HTTP request but are not visible in the
request’s body.

Each header can contain more values: this is why the velue of a single header is
represented as a non-associative array.

You can have the complete list of headers, in form of an associative array by
calling the getHeaders() function the interested request.

A simple example can be:

```php
use Gishiki\Core\Route;
use Gishiki\Logging\Logger;
use Gishiki\HttpKernel\Request;
use Gishiki\HttpKernel\Response;
use Gishiki\Algorithms\Collections\SerializableCollection;

Route::any("/",
    function (Request $request, Response &$response, SerializableCollection &$arguments)
{
    foreach ($request->getHeaders() as $name => $values) {
        $response->write("name: ". $name . " => values:" . implode(", ", $values));
    }
});
```

separing each value of the request with a comma is equal is equal to calling the
getHeaderLine('header_name') function on the interested request.

The previous example can be rewritten as:

```php
use Gishiki\Core\Route;
use Gishiki\Logging\Logger;
use Gishiki\HttpKernel\Request;
use Gishiki\HttpKernel\Response;
use Gishiki\Algorithms\Collections\SerializableCollection;

Route::any("/",
    function (Request $request, Response &$response, SerializableCollection &$arguments)
{
    foreach (array_keys($request->getHeaders()) as $name) {
        $response->write("name: ". $name . " => values:" . $request->getHeaderLine($name);
    }
});
```

You can test the existance of a given header calling the hasHeader('header_name')
function.

If the result is true you can safely call the getHeader('header_name') function
that will return the non-associative array representing the header values.


## Request Body
An HTTP request may have a body following its header.

That body is useful when creating a RESTful service, because it may contains lots
of information about the requested action.

Within Gishiki you can access the body of the interested request as a stream
PSR-7 compilant. You can obtain that stream calling the getBody() function.

Let's look into an example:

```php
use Gishiki\Core\Route;
use Gishiki\Logging\Logger;
use Gishiki\HttpKernel\Request;
use Gishiki\HttpKernel\Response;
use Gishiki\Algorithms\Collections\SerializableCollection;

Route::any("/",
    function (Request $request, Response &$response, SerializableCollection &$arguments)
{
    //get the body stream
    $body = $request->getBody();

    //rewind the stream (aka reset the cursor at the beginning of the stream)
    $body->rewind();

    //read the entire request body
    $request = "";
    while (!$body->eof()) {
        $request .= read(1);
    }

    //have fun with your request body!
});
```

I know what you are thinking... You could parse that request body to obtain
something like an array or a class that you can use within your application....

Well, if that's the case you may appreciate the fact that Gishiki does this
in your place!

To trigger the request body automatic parsing you can use the getParsedBody()
function that triggers the better parser for the given 'Content-type' header!