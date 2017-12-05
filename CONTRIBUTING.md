# Contributing

## Locale Setup (Docker)
Steps to test the code (using [PHPUnit]()) are following:
  1. Install PHP and required extensions
  1. Download/Install composer
  1. Install [Docker](https://www.docker.com/)
  1. Setup docker containers (continue reading)
  1. Run tests with "composer test"
  
The following is a list of commands to be given on the host machine to setup docker containers.

Setup a PostgreSQL database:

```sh
docker run -d -p 5432:5432 \
--name gishiki-postgres \
-e POSTGRES_PASSWORD=vagrant \
-e POSTGRES_USER=vagrant \
-e POSTGRES_DB=travis \
postgres:10.1-alpine
```

Setup a MySQL database:
```sh
docker run -d -p 3306:3306 \
--name gishiki-mysql \
-e MYSQL_ALLOW_EMPTY_PASSWORD=yes \
-e MYSQL_DATABASE=travis \
mysql:8.0.3
```

Then you are ready to test the framework:

```sh
composer install # mandatory: download dependencies
export COMPOSER_PROCESS_TIMEOUT=600 # give testing some time to process
composer test
```

## Coding Style
Source code of Gishiki __must__ be following [PSR-2](http://www.php-fig.org/psr/psr-2/) specification.

## Guideline
You can improve the framework by applying a pull request on the [GitHub repo](https://github.com/NeroReflex/Gishiki).

When adding new source code you __have to__ provide PHPUnit tests to cover at least 90% of new source.

## New Classes
When adding a new class you __must__ provide your full name and, optionally, your email address.
