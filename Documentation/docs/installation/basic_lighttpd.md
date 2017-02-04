# Basic lighttpd
If you are planning to use lighttpd as webserver for your application you
__MUST__ be provided with instructions:

```
url.rewrite-if-not-file = ("(.*)" => "/index.php/$0")
```

There you are! You just need to paste that code into the configuration file of lighttpd!

This requires lighttpd >= 1.4.24.