# PHP v7 & nginx
This framework is fully compatible with PHP 7, and you are encouraged to use it.

PHP v7 is the PHP version I am using while developing Gishiki.

Installation depends on your system, so read the PHP manual (or google for instructions....).

You will be provided with Ubuntu 16.04 instructions:

```shell
sudo apt-get install nginx php7.0 php7.0-dev php7.0-xml php7.0-fpm php7.0-mysql php7.0-sqlite php7.0-pgsql php7.0-curl
```

When you are done with the configuration file (/etc/nginx/sites-enabled/default), 
which should be basically:

```nginx
server {
	listen 80;
	server_name site.com;
	root /var/www/html/Gishiki;

	index index.php;

	location / {
		try_files $uri $uri/ /index.php$is_args$args;
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

