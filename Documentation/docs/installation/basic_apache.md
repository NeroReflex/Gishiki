## Webserver (Apache 2)
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

Remember that using .htaccess slows down your apache server, so if you have access
to the configuration file of your production server you *should* embed the provided .htaccess.


