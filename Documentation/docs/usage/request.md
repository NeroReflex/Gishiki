# Request

The Request object passed to a controller implements __RequestInterface__ defined on the
[PSR-7 sheet](http://www.php-fig.org/psr/psr-7/) and follows that specification sheet.

Each [Request](request.md) sent to the web server triggers the generation of a [Response](response.md).

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

You can inspect the HTTP request’s method with the Request object method
appropriately named getMethod()

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
//get the URI of the current request
$uri = $request->getUri();
    
//do something with this URI
```

Operation allowed on an URI are:

   - *getScheme()*
   - *getAuthority()*
   - *getUserInfo()*
   - *getHost()*
   - *getPort()*
   - *getPath()*
   - *getBasePath()*
   - *getQuery()*
   - *getFragment()*
   - *getBaseUrl()*
   - *getQueryParams()*

where *getQueryParams()* returns an associative array, *getQuery()* returns the complete
query string and *getBaseUrl(*) the complete URL of the request.


## Request Headers
Headers are metadata that describe the HTTP request but are not visible in the
request’s body.

Each header can contain more values: this is why the value of a single header is
represented as a non-associative array.

You can have the complete list of headers, in form of an associative array by
calling the *getHeaders()* function the interested request.

A simple example can be:

```php
foreach ($request->getHeaders() as $name => $values) {
    $response->write("name: ". $name . " => values:" . implode(", ", $values));
}
```

joining each value of the request with a comma is equal is equal to calling the
getHeaderLine('header_name') function on the interested request.

The previous example can be rewritten as:

```php
foreach (array_keys($request->getHeaders()) as $name) {
    $response->write("name: ". $name . " => values:" . $request->getHeaderLine($name);
}
```

You can test whether a given header exists by calling the *hasHeader()*
function by passing the header name as its argument.

If the result is true you can safely call the getHeader('header_name') function
that will return the non-associative array representing the header values.


## Request Body
An HTTP request may have a body following its header.

That body is useful when creating a RESTful service, because it may contains lots
of information about the requested action.

Within Gishiki you can access the body of the interested request as a stream,
as defined in PSR-7. You can obtain a reference to that stream by calling the *getBody()* function.

Let's look into an example:

```php
//get the body stream
$bodyStream = $request->getBody();

//rewind the stream (aka reset the cursor at the beginning of the stream)
$bodyStream->rewind();

//read the entire request body
$request = (string)$bodyStream;

//have fun with your request body!
```


## More about Requests
This is only a brief introduction to general PSR-7 programming.

Actually Gishiki uses Zend [Diactoros](https://github.com/zendframework/zend-diactoros).

Read more about Zend Diactoros [here](https://docs.zendframework.com/zend-diactoros/overview/).