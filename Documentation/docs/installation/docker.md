# Docker
The official Docker environment for Gishiki can be found on [DockerHub](https://hub.docker.com/r/neroreflex/gishiki/).

This Docker image is derived from php:7.1-apache (more info [here](https://hub.docker.com/_/php/)),
the purpose of that image is to be able to deploy an application based on Gishiki on every environment
with a few minutes of work.

## Usage
You have to pull the base image, create a container and execute that container.

This can be done with the following command line arguments:

```sh
docker pull neroreflex/gishiki
docker run -d -p 80:80 --name gishiki-app -v "$PWD":/var/www/html neroreflex/gishiki
```

The first command pull the base image.

The second command performs multiple operations:
   - create the container from the gishiki image
   - symlink the current directory with the documentRoot directory inside the container
   - bind the port 80 inside the container to the host port 80
   - run the apache service
   
You can change port by issuig the following command:

```sh
docker run -d -p 80:8081 --name gishiki-app -v "$PWD":/var/www/html neroreflex/gishiki
```

## Caching
As Gishiki can speedup configuration loading using memcached you should _really_
deploy a memcached container and link it with the application one:

```sh
docker run -d --name gishiki-cache -d memcached
docker run -d -p 80:8081 --name gishiki-app -v "$PWD":/var/www/html --link gishiki-cache:cachelink neroreflex/gishiki
```

## Database
The vast majority of applications, services and websites need a place to store data,
usually that place is a database.

I suggest you to use the [PostgreSQL](https://hub.docker.com/_/postgres/) image,
or the [MySQL](https://hub.docker.com/_/mysql/) one, when pgsql __cannot__ be used.

Provided you have a database container running, and that container is called "database-container"
you can link the application container with that database container:

```sh
docker run -d -p 80:8081 --name gishiki-app -v "$PWD":/var/www/html --link database-container:dblink neroreflex/gishiki
```

## Edit & Save
You can use the container shell in order to prepare and personalize your container:

```sh
docker exec -i -t gishiki-app /bin/bash
```

When you are done remember to create a container out of your customized environment
and push it to [DockerHub](https://hub.docker.com/) (create a new repository before, in this example my_image):

```sh
docker commit gishiki-app customized_gishiki_img

export DOCKER_ID_USER="username"
docker login #login to dockerhub
docker tag customized_gishiki_img $DOCKER_ID_USER/my_image
docker push $DOCKER_ID_USER/my_image
```

You can find a tutorial [here](https://docs.docker.com/docker-cloud/builds/push-images/).

## Final Notes

The Gishiki image deploy everything is needed to run the gishiki framework:
   - PHP (obviously)
   - PDO drivers (for database access)
   - Memcached (faster framework execution when used)
   - XDebug (remotely debug your application)
   
__WARNING:__ XDebug is a powerful extension but when leaved on in a release environment
is to be considered a security breach: make sure to fine-tune your own environment!