# Installation
Installing Gishiki is really simple, even a child could be able to do it:
however, in order to succeed you need to complete following steps.


## Windows
You may want to test Gishiki or develop your application on Windows:
to install the application on windows you should install [XAMPP](https://www.apachefriends.org/) and enable PDO drivers on php.ini. 


## Mac OS X
If you are willing to develop your application on a Mac system you should use
[XAMPP](https://www.apachefriends.org/) too!


## Virtual Machine
If you don't want to pollute your desktop environment you can use a virtualization
program to install [Ubuntu Server](http://www.ubuntu.com/download/server) and follow the tutorial on that virtual machine!


## Webserver (Apache 2)
Gishiki is a framework for the web.... of course, you need to setup a web server!

If you are using a debian or ubuntu based distro you just:

```shell
sudo apt-get update
sudo apt-get install apache2 php5 php5-curl php5-sqlite php5-pgsql php5-mysql libapache2-mod-php5 nano git
sudo a2enmod rewrite
sudo nano /etc/apache2/sites-available/000-default.conf
sudo service apache2 restart
```

You must have AllowOverride set to "All" and not to "None" in the file being edited by nano.

When you are done with nano just ctrl+O, Enter, ctrl+X.

You can simply cut 'n' paste this configuration:

```apache
<VirtualHost *:80>
	#ServerName www.example.com

	ServerAdmin webmaster@localhost
	DocumentRoot /var/www/html

	ErrorLog ${APACHE_LOG_DIR}/error.log
	CustomLog ${APACHE_LOG_DIR}/access.log combined

        # globally allow .htaccess
	<Directory "/var/www/html">
		AllowOverride All
	</Directory>
</VirtualHost>

# vim: syntax=apache ts=4 sw=4 sts=4 sr noet
```

Remember that using .htaccess slows down your apache server, so if you have access
to the configuration file of your production server you *should* embed the provided .htaccess.


## Webserver (nginx)
You may want to use nginx.... That's legit and smart, but you already know how to 
do your job, so just remember to enable the rewriting engine:

```nginx
server {
	listen 80;
	server_name site.com;
	root /var/www/html/Gishiki;

	index index.php;

	location / {
		try_files $uri $uri/ /index.php?$query_string;
	}

	location ~ \.php$ {
		fastcgi_split_path_info ^(.+\.php)(/.+)$;
		# NOTE: You should have "cgi.fix_pathinfo = 0;" in php.ini

		fastcgi_pass unix:/var/run/php5-fpm.sock;
		fastcgi_index index.php;
		include fastcgi.conf;
		fastcgi_intercept_errors on;
	}
}
```

Your server configuration file should be located at /etc/nginx/nginx.conf

## PHP v7 / nginx
This framework is fully compatible with PHP 7, and you are encouraged to use it.

PHP v7 is the PHP version I am using while developing Gishiki.

Installation depends on your system, so read the PHP manual (or google for instructions....).

You will be provided with ubuntu instructions:

```shell
sudo add-apt-repository ppa:ondrej/php
sudo apt-get update
sudo apt-get install -y language-pack-en-base
sudo LC_ALL=en_US.UTF-8 add-apt-repository ppa:ondrej/php
sudo apt-get install nginx php7.0 php7.0-dev php7.0-xml php7.0-fpm php7.0-mysql php7.0-sqlite php7.0-pgsql php7.0-curl
```

You don't need to install php7.0-fpm if you are __NOT__ using nginx.

When you are done with the configuration file (/etc/nginx/sites-enabled/default), 
that should be basically:

```nginx
server {
	listen 80;
	server_name site.com;
	root /var/www/html/Gishiki;

	index index.php;

	location / {
		try_files $uri $uri/ /index.php?$query_string;
	}

	location ~ \.php$ {
		fastcgi_split_path_info ^(.+\.php)(/.+)$;
		# NOTE: You should have "cgi.fix_pathinfo = 0;" in php.ini

		fastcgi_pass unix:/var/run/php/php7.0-fpm.sock; # this is important (YOU MUST CHECK FOR THIS FILE!)
		fastcgi_index index.php;
		include fastcgi.conf;
		fastcgi_intercept_errors on;
	}
}
```

you restart the server and the php service:

```shell
sudo service nginx restart
sudo service php7.0-fpm restart
```

And the server should just work!


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
cd /var/www/html/Gishiki
git clone https://github.com/NeroReflex/Gishiki.git
git checkout master
```

In the above steps you can checkout the development branch to have the latest,
cutting-edge (and unstable) version of the framework to play with.


## Getting the framework (alternative)
I hope you like unzipping, because it is what you will do after 
downloading a release (or a snapshot of your favourite branch)!

```shell
sudo apt-get install unzip
unzip Gishiki-X.Y.Z.zip -d /var/www/html
```

You can use the tar.gz archive:
tar -zxvf backup.tar.gz
```shell
tar -zxvf Gishiki-X.Y.Z.tar.gz
```

Using the gzipped archive you'll avoid to install unzip.


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
Just explore it! The framework has given you a fresh start:

   - settings file is named setting.json
   - a model descriptor in an XML file (named bookstore.xml)
   - an SQLite database with the table to use the bookstore example
   - a routing + controller example named routes.php
   - obscure encryption stuff (discussed in another chapter)

Why don't directing your browser to the [book insertion example](site.com/book/new/1485254039/Example%20Book/Example%20Author/19.99/2010-01-02%2003:04:05)?

This should help you understanding routing and model creation (just look at routes.php).

If you check your sqlite database you will notice that.....


## Composer
As promised Gishiki is not meant to replace your favourite tools: you can still use all of them!

Doctrine? Propel? Zend framework components? Symfony components? No problem!

You have to install them and you do that using composer! If you don't have composer run:

```shell
php -r "readfile('https://getcomposer.org/installer');" > composer-setup.php
php -r "if (hash('SHA384', file_get_contents('composer-setup.php')) === '7228c001f88bee97506740ef0888240bd8a760b046ee16db8f4095c0d8d525f2367663f22a46b48d072c816e7fe19959') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
php composer-setup.php
php -r "unlink('composer-setup.php');"
```

If everything went fine you cd into Gishiki directory and, using nano, you add to "require" your components.

When you are done adding components you have to install all of them (alongside Gishiki) at once:

```shell
composer install 
```

Your components are automatically loaded and you can use them exactly as if you weren't using any framework at all!


## What's next?
To start learning how to accomplish things you *have to* learn lifecycle of a resource starting from when a request arrives from the client to the server.
