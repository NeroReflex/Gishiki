# Gishiki


| CI           | Status  |
|--------------|---------|
| Codacy       | [![Codacy Badge](https://api.codacy.com/project/badge/Grade/87f9e5edde4d4708b94306e92842d87c)](https://www.codacy.com/app/NeroReflex/Gishiki?utm_source=github.com&utm_medium=referral&utm_content=NeroReflex/Gishiki&utm_campaign=badger) |
| Code Metrics | [![Code Climate](https://codeclimate.com/github/NeroReflex/Gishiki/badges/gpa.svg)](https://codeclimate.com/github/NeroReflex/Gishiki)     |
| Coverage     | [![Test Coverage](https://codeclimate.com/github/NeroReflex/Gishiki/badges/coverage.svg)](https://codeclimate.com/github/NeroReflex/Gishiki/coverage) |
| Test         | [![Build Status](https://travis-ci.org/NeroReflex/Gishiki.svg?branch=master)](https://travis-ci.org/NeroReflex/Gishiki)  |


_*Gishiki*_: a modern and elegant MVC framework for modern versions of PHP.

Gishiki means 'ritual' in japanese, this name was chosen because this framework
will help you to perform the ritual of creation and deployment of web digital contents.

Due to its design this framework won't force you to use its features:
you are completely free to use it as you wish to,
even if that breaks up MVC principles!

What are you waiting for?
Installing is as simple as cloning the repository and in 2 minutes you are up in
experimenting its features!


## Quick Start

Do you want to have it working in the less time possible? OK! 

```shell
composer init # setup your new project
nano composer.json # or any other text editor to add "neroreflex/gishiki": "dev-development" on "require"
composer install --no-dev
./vendor/bin/gishiki new application
```

And That's it. Enjoy creating your next application!


## Documentation

A simple tutorial is inside the Documentation folder.
That tutorial is meant to be compiled using mkdocs.

You can browse [here](http://neroreflex.github.io/Gishiki) the online 
version matching the last valid development branch.


## Services

Gishiki focuses on high-performance cloud computing in an MVC-oriented paradigm.

The majority of cloud application is composite of RESTFul  services.

Gishiki lets you easily create RESTFul services to interact with your
own service from wherever you want, may it be an Android/iOS application,
another service, a Desktop program or the website for your service.


## Websites

Gishiki deploy an MVC pattern that is a bit special to help you building any digital servie you want with just one framework.

In order to help you achieving your goals Gishiki provides helpers for a lot of common operations,
but let you free of doing things the way you want.


## Security

Security is never enough, especially when dealing with people's data in a
digital environment!

To help you obtaining a secure working environment Gishiki deploy a set of
cryptographic utilities based on the OpenSSL ones and abstract away most
common cryptographic operations.


## Requirements

Everything you need is php >= 5.5 and composer!

Gishiki runs better while using a dedicated http webserver, but you can use the
one bundled with PHP!


## License

Gishiki is released under Apache-2.0 license terms,
read the LICENSE file to find out more!
