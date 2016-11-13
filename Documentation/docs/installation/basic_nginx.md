# Basic nginx
You may want to use nginx.... That's legit and smart, but you already know how to 
do your job, so just remember to enable PHP and the rewriting engine:

```nginx
server {
	listen 80;
	server_name site.com;
	root /var/www/html;
        error_log /var/www/error.log;
        access_log /var/www/access.log;

	index index.php;

	location / {
		try_files $uri $uri/ /index.php$is_args$args;
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

