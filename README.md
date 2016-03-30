# Advanced-Gishiki
Also called Gishiki: a modern and elegant MVC framework for PHP >= 5.6 and HHVM.

Gishiki means 'ritual' in japanese, this name was chosen because this framework will help you to perform the
ritual of creation and deployment of web digital contents.


## Services
Gishiki focuses on high-performance cloud computing in an MVC-oriented paradigm.

Any cloud application is composite of services.

Gishiki lets you easily create every service you need and helps you interacting with yours own services from wherever you want.

Moreover, a service can be used extremely easily in any environment, may it be 
an Android/iOS application, another service, a Desktop program or another website.


## Websites
Gishiki deploy a classic MVC pattern to help you building any website you want.

In order to help you achieving your goals Gishiki provides helpers to help you with most common operations,
but let you free of doing things the way you want.


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

The provided ORM is built around the php ActiveRecord ORM, because it is designed 
to be extremely fast on query generation and execution.


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

   -    Apache with mod_rewrite
   -    HHVM or PHP >= 5.6
   -    OpenSSL extension (usually included in the standard PHP release)
   -    libxml2 extension (usually included in the standard PHP release)
   -    PDO extension and the PDO driver for your database
   -    cURL extension


## Installation
You just checkout (or decompress a snapshot) from the github repository in your apache root directory and it just run out of the box.

Using "git clone" is the preferred installation procedure, because it will be easier to update the framework.


## License
Gishiki is released under Apache-2.0 license terms, read the LICENSE file to find out more!
