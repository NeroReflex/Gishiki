# Installation
Installing Gishiki is really simple, even a child could be able to do it:
however, in order to succeed you need to complete following steps.

## Webserver (Apache 2)
Gishiki is a framework for the web.... of course, you need to setup a web server!

If you are using a debian or ubuntu based distro you just:

```shell
sudo apt-get install apache2 php5 libapache2-mod-php5 nano git
sudo a2enmod rewrite
sudo nano /etc/apache2/sites-available/000-default.conf
sudo service apache2 restart
```

You must have AllowOverride set to "All" and not to "None" in the file being edited by nano.

When you are done with nano just ctrl+O, Enter, ctrl+X.

## Webserver (nginx)
You may want to use nginx.... That's legit and smart, but you already know how to 
do your job, so just remember to enable the rewriting engine:

```nginx
server {
	listen 80;
	server_name mydevsite.dev;
	root /var/www/mydevsite/public;

	index index.php;

	location / {
		try_files $uri $uri/ /index.php?$query_string;
	}

	location ~ \.php$ {
		fastcgi_split_path_info ^(.+\.php)(/.+)$;
		# NOTE: You should have "cgi.fix_pathinfo = 0;" in php.ini

		# With php5-fpm:
		fastcgi_pass unix:/var/run/php5-fpm.sock;
		fastcgi_index index.php;
		include fastcgi.conf;
		fastcgi_intercept_errors on;
	}
}
```

## PHP v7
This framework is fully compatible with PHP 7, and you are encouraged to use it.

PHP v7 is the PHP version I am using while developing Gishiki.

Installation depends on your system, so read the PHP manual (or google for instructions....).

## HHVM
Do you like facebook virtual machine for PHP? Great! Gishiki can run on top of it, 

but you are going to install HHVM all by yourself (following facebook documentation).

## PHP Extensions
As written in the README.md file you will need a PHP version that has the following extensions enabled:
   
   -    OpenSSL extension (usually included in the standard PHP release)
   -    libxml2 extension (usually included in the standard PHP release)
   -    PDO extension and the PDO driver for your database
   -    cURL extension

Although it is not strictly necessary I suggest you to install the SQLite PDO extension,
because you will have a super-fast database already configured you can play on, 
immediatly after the installation went fine!

## Getting the framework
So... did you managed to arrive here? Fine! now let's dig into the real installation process:

```shell
cd <server_dir>
git clone https://github.com/NeroReflex/Gishiki.git
git checkout master
```

In the above steps you can checkout the development branch to have the latest,
cutting-edge (and unstable) version of the framework to play with.

## Getting the framework (alternative)
You would be better like unzipping, because it is what you will do after 
downloading a release or a snapshot of your favourite branch!

## Setting privileges
Gishiki needs to create a directory inside its installation path, so you have to
grant writing priviledges to php on your Gishiki installation directory.

It is your responsibility to do it right, but for testing purpouses you can just

```shell
chmod -R 0775 Gishiki
```

NEVER do this in a public machine or in the production environment/server!
You have been warned......

## Starting a new application
Direct your browser to the Gishiki directory and presto!

A new directory called application have appeared on your server...
Just explore it.... You are provided with:

   - the settings file named setting.json
   - a model descripto in an XML file (named bookstore.xml)
   - an SQLite database with the table to use the bookstore example
   - a routing + controller example named controllers.php
   - obscure encryption stuff
   - 