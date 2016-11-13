# Basic Apache 2.4
If you are using a debian or ubuntu based distro you have to remember that you
need to enable mod_rewrite on apache:

```shell
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

You can just copy the .htaccess file from the Gishiki main rapository to your
application root, here I am reporting it:

```apache
<IfModule mod_rewrite.c>
    Options +FollowSymLinks
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^ index.php [QSA,L]
</IfModule>
```

You can even be more aggressive here:

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^ index.php [QSA,L]
</IfModule>

#allow requests from outside the domain
Header add Access-Control-Allow-Origin "*"
Header add Access-Control-Allow-Headers "origin, x-requested-with, content-type"
Header add Access-Control-Allow-Methods "PUT, GET, POST, DELETE, OPTIONS"
```

That shorted version will call the framework even if the client is asking for a
resource that is a file inside you webroot!

Remember that using .htaccess slows down your apache server,
so if you have access to the configuration file of your production server you
*should* embed the provided ".htaccess".


