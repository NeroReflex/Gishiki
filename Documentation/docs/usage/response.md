# Response

The __Gishiki\HttpKernel\Response__ class is used to fully represent an HTTP response.

The Response class is PSR-7 conformant and follows that specification sheet.

Each [Request](request.md) triggers the generation of a response.

That response is automatically sent back to the client at the end of the
application lifetime.

The main target of an application is __editing__ that response before the departure
of that response.

An HTTP response is made of two parts:

   - Response __header__ 
   - Response __body__
   
Those parts and steps to generate them are described later on this document.


## Response Header

Every HTTP response __MUST__ contains an header.

An HTTP header have a bare minimum structure that comprises the HTTP revision,
the status code and the message associated to the status code.

Since each status code have its own predefined status phrase (like 404 not found,
500 internal server error, 200 OK and so on) when the status code is changed the
status phrase is automatically changed by the framework.

That can be done calling the __withStatus__ function:

```php
use Gishiki\Core\Route;
use Gishiki\HttpKernel\Request;
use Gishiki\HttpKernel\Response;
use Gishiki\Algorithms\Collections\SerializableCollection;

Route::any(Route::NOT_FOUND,
    function (Request $request, Response &$response, SerializableCollection &$arguments)
{
    //the response code - phrase will be 404 - Not Found
    $response->withStatus(404);
});
```

You can manually change the status phrase, but you are discouraged from doing such
thing with standard status code!

What you can do is using it to send a strong signal to a bad client:

```php
use Gishiki\Core\Route;
use Gishiki\HttpKernel\Request;
use Gishiki\HttpKernel\Response;
use Gishiki\Algorithms\Collections\SerializableCollection;

Route::any("/complex",
    function (Request $request, Response &$response, SerializableCollection &$arguments)
{
    //numberOfRequests is the number of requests that a client has sent today
    if ($numberOfRequests > 5) {
        //perform the complex operation (may stress the system)
        action();
    } else {
        //stop flooding my servers!
        $response->withStatus(666, 'FUCK YOU!');
    }
});
```

Sorry for the bad language, that was only intended to help me to give you a (real :D)
example of usage.


## Response Header Details

Each response can contains a lot of details about itselfs like the length of the
content or the type of the content.

Each 'response detail' is a collection of values binded to a key which is the name
of the property.

In order to edit the value of a property you have to use the __withHeader__ function:

```php
use Gishiki\Core\Route;
use Gishiki\HttpKernel\Request;
use Gishiki\HttpKernel\Response;
use Gishiki\Algorithms\Collections\SerializableCollection;

Route::any("/complex",
    function (Request $request, Response &$response, SerializableCollection &$arguments)
{
    $request->withHeader('Cache-Control', 'no-store, no-cache, must-revalidate');
});
```

If you are unsure on how to use this feature you should read more about http response [header](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers).


## Response Body

The body of the response is the main part of the response: the __content__ of the
response.

For example if the response is an html content the response body is what the
user calls *the webpage*.

To directly modify the response body you can use the __write__ function:

```php
use Gishiki\Core\Route;
use Gishiki\HttpKernel\Request;
use Gishiki\HttpKernel\Response;
use Gishiki\Algorithms\Collections\SerializableCollection;

Route::any("/",
    function (Request $request, Response &$response, SerializableCollection &$arguments)
{
    $content = <<<EOT
<html>
    <head>
        <title>My webpage</title>
    </head>
    <body>
        <h1>My webpage</h1>
        <p>Hello, this is my personal webpage!</p>
    </body>
</html>
EOT;

    //write the response
    $response->withHeader('Content-Type', 'text/html');
    $response->write($content);
});
```

This is a simple example to Gishiki used to generate an html response,
however since Gishiki is built to be used as the foundation of RESTful services
the response body shoild be a JSON, XML, YAML etc... content.

To generate a response body from a serializable data collection Gishiki provides
a function that automate this process: this function is called __setSerializedBody__
and does more than just converting a collection to a fixed format:

```php
use Gishiki\Core\Route;
use Gishiki\HttpKernel\Request;
use Gishiki\HttpKernel\Response;
use Gishiki\Algorithms\Collections\SerializableCollection;

Route::any("/factorial/{int:integer}",
    function (Request $request, Response &$response, SerializableCollection &$arguments)
{
    $x = $arguments->int;
    $factorial = fact($x);

    $data = new SerializableCollection([
        'timestamp' => time(),
        'result'    => $factorial,
    ]);

    $response->setSerializedBody($data);
});
```

The given collection may be serialized to a JSON content, XML content or YAML content.
You may decide the content type by setting the header 'Content-Type' __BUT THAT IS A WASTE OF TIME__:
Gishiki __AUTOMAGICALLY__ uses the content type listening for client preferences.

This means that a client is not enforced to be able to deserialize a specific
content type, but can choose the preferred content-type including it on the
http request header using the 'Accept' property!

Following accept values are used to request a specific data serialization format:

   - 'text/yaml'            -> YAML
   - 'text/x-yaml'          -> YAML
   - 'application/yaml'     -> YAML
   - 'application/x-yaml'   -> XML
   - 'application/xml'      -> XML
   - 'text/xml'             -> XML
   - 'application/json'     -> JSON

Anything else triggers the default serialization format, which is JSON!
