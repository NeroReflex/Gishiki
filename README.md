# Gishiki
_*Gishiki*_: a modern and elegant MVC framework for PHP >= 5.6 and HHVM.

Gishiki means 'ritual' in japanese, this name was chosen because this framework will help you to perform the
ritual of creation and deployment of web digital contents.

Due to its design this framework won't force you to use its features: you are completely free of using it as you wish,
even if that breaks up MVC principles and/or uses a custom ORM.

Gishiki is so fast to deploy that you don't even need to configure it to get started!

What are you waiting for? 
Installing is as simple as cloning the repository and in 2 minutes you are up in experimenting its features!


## Documentation
A simple tutorial and a hand-written documentation will be available in the near future.

While waiting you can browse the API documentation for the current version on the master branch that can be found [here](http://neroreflex.github.io/Gishiki).

If you are looking for the next cutting-edge documentation you can build the documentation for the development branch by yourself using PHP ApiGen!


## Services
Gishiki focuses on high-performance cloud computing in an MVC-oriented paradigm.

Any cloud application is composite of services.

Gishiki lets you easily create every service you need and helps you interacting with yours own services from wherever you want.

Moreover, a service can be used extremely easily in any environment, may it be 
an Android/iOS application, another service, a Desktop program or another website.


## Websites
Gishiki deploy a minimal but classic MVC pattern to help you building any website you want.

In order to help you achieving your goals Gishiki provides helpers to help you with most common operations,
but let you free of doing things the way you want.


## RESTful services
Thanks to the really fast router you can deploy a RESTful service in a minute or two...

The HTTP router is written from scratch to be as fast as possible!


## Security
As you know the security is never enough, especially when dealing with people's data
in a digital environment.

To help you obtaining a secure working environment Gishiki deploy a set of cryptographic utilities
based on the OpenSSL ones and abstract away most common cryptographic operations.


## Description
This framework was written with speed, security and simplicity in mind!
Gishiki helps you creating in a very short amount of time web services and web applications which are both: maintainable and expandable.

With Gishiki you can create any digital service that your new business activity may need, without permanently binding yourself to a single technology, program or 3rd party service.
This is achieved by giving you the option of changing a service just by editing a configuration file.

Using apache as webserver it is easy to deploy an application with user-friendly URLs, 
thanks to the routing engine included bundled with Gishiki, that supports both: regex routing and passive routing!


## Database
Gishiki uses an ORM (Object-relational mapping) to help you interacting with your own databases.

The provided ORM is built around the PHP ActiveRecord ORM, because it is designed 
to be extremely fast on query generation and execution.

With the Gishiki ORM you are free to decide whether to use the ActiveRecord component writing your own PHP classes or let
Gishiki creating your documented PHP code for the table you describe.

Gishiki can generate models out of a simple XML file, but it is planned to extend the code generation with the JSON and yaml formats.


## Caching
Gishiki gives you the ability of saving computational results into caching, saving time if the same computation, 

with the same input is required once again in the future.

Cache is managed automatically by Gishiki for most common operations.


## Logging
Gishiki gives you the ability of logging what happens on your server.

Gishiki automatically logs exceptions, but delivers you full-control over your logs, exposing a simple logging API.

Gishiki uses UNIX syslog for all of its logging management, allowing you to do whatever you want with your log entries.


## Requirements
Gishiki has a few dependencies that you should install to have the framework fully-functional.

If you plan to install Gishiki without composer you will have to manually install:

   -    Apache with mod_rewrite or nginx
   -    HHVM or PHP >= 5.6
   -    OpenSSL extension (usually included in the standard PHP release)
   -    libxml2 extension (usually included in the standard PHP release)
   -    PDO extension and the PDO driver for your database
   -    cURL extension


## Installation
You just checkout (or decompress a snapshot) from the github repository in your apache root directory and it just run out of the box.

To have a step-by-step mini tutorial read the Documentation/docs/installation.md file on this repository or the 
documentation section of the documentation!


## License
Gishiki is released under Apache-2.0 license terms, read the LICENSE file to find out more!
