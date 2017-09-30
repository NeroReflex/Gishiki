# Response

The Response object passed to a controller implements __ResponseInterface__ defined on the
[PSR-7 sheet](http://www.php-fig.org/psr/psr-7/) and follows that specification sheet.

Each [Request](request.md) sent to the web server triggers the generation of a [Response](response.md).

That response is automatically sent back to the client at the end of the
application lifecycle.

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

That can be done calling the __withStatus()__ function:

```php
//the response code - phrase will be 404 - Not Found
$response->withStatus(404);
```

You can manually change the status phrase, but you are discouraged from doing such
thing with standard status code!

What you can do is using it to send a strong signal to a bad client:

```php
//numberOfRequests is the number of requests that a client has sent within 60 minutes
if ($numberOfRequests > 50) {
    //perform the complex operation (may stress the system)
    action();
} else {
    //stop flooding my servers!
    $response = $response->withStatus(666, 'FUCK YOU!');
}
```

Sorry for the bad language, that was only intended to help me to give you a (real :D)
example of usage.


## Response Header Details

Each response can contains a lot of details about itself like the length of the
content or the type of the content.

In order to edit the value of a property you have to use the __withHeader()__ function:

```php
$request = $request->withHeader('Cache-Control', 'no-store, no-cache, must-revalidate');
```

If you are unsure on how to use this feature you should read more about http response [header](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers).


## Response Body

The body of the response is the main part of the response: the __content__ of the
response.

For example if the response is an html content the response body is what the
user calls *the webpage*.

To directly modify the response body you can use the __write()__ function on the
stream reference returned by the __getBody__ function:

```php
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
$response = $response->withHeader('Content-Type', 'text/html');
$response->getBody()->write($content);
```

Keep in mind that you can provide your __OWN__ Stream by calling the *withBody()* function.

To adhere to PSR-7 standard the body stream __MUST__ implement StreamInterface.


## More about Response

This is only a brief introduction to general PSR-7 programming.

Actually Gishiki uses Zend [Diactoros](https://github.com/zendframework/zend-diactoros).

Read more about Zend Diactoros [here](https://docs.zendframework.com/zend-diactoros/overview/).
