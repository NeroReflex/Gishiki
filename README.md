#Gishiki
__**Gishiki**__ is a classic MVC PHP framework written with speed, security and simplicity in mind.


Gishiki means 'ritual' in japanese, this name was choosen because this framework will help you to perform the ritual of creating and rendering a web page or a json object.
To achieve that, Gishiki includes a base class for model and controller, alongside with a useful and powerful database manager.
Gishiki automatically bind a database connection to yours models, it is up to you to connect your model with a database, however, to avoid writing the connection string, auto-connections are also supported. Database supported types are: SQLite v3, SQLite v2, MySQL/MariaDB, PostgreSQL, Firebird and MSSQL.

Gishiki not only support database connections, but it also include a simple ORM. To use it, you only need three functions: Store, Restore and Remove.

Gishiki is very simple, easy to setup, run, and most importantly to understand. Due to its simple design and its short, simple and concise documentation, this framework can be used by everyone who can use PHP, even without a prior knowledge of the MVC pattern.



Gishiki can be expanded at will and gives you no limit of customization. This framework is so small that a single controller-model-view cycle, with a SQLite connection is executed at lighting speed, because no useless code is included.



### License
Gishiki is released under apache-2.0 license, so feel free to modify, contribute and fork this project.

### System Requirements
Gishiki only requires non-optional extensions that are part of the PHP core or bundled with PHP,
that means you can perform your rituals on every machine, server infrastructure, vps or cloud with a vanilla PHP installation.
This is the full list of requirements:

   *   Web Server with PHP support [nginx](http://wiki.nginx.org/Main) and [apache](http://httpd.apache.org) are both strongly suggested)
   *   PHP 5.3.x or higher 
   *   [HHVM](http://hhvm.com) if running an x64 OS (optional, but strongly suggested)
   *   HTTP root directory and subdirectories complete read and write permissions to PHP (Unix only)
   *   OpenSSL extension for PHP5
   *   SimpleXML extension for PHP5
   *   zlib extension for PHP5
   *   active php_fileinfo.dll for PHP5 (on windows only)
   *   SQLite3 extension for PHP5
   *   PDO drivers (add support for others RDBMS)
   *   Alternative PHP Cache (optional but strongly suggested)
   
### Installation
Instructions on how to install Gishiki can be found in the project *Documentation*. I suggest you to read the documentation, 
but, for who don't like to read documentation here you are the steps to be performed:

   *   Download Gishiki as a zip or clone the repository
   *   Edit the config.php file
   *   Edit openssl.cnf file (or delete it if you want to use the default one)
   *   Upload Gishiki to your web root directory or a sub directory
   *   Assign to php complete write/read permissions on the Gishiki directory and subdirectory (Unix only)
   *   Direct your browser to index.php
   *   Follow on-screen instructions
   *   Open the Documentation folder
   *   double-click on index.html

### Obsolete notice
This branch is obsolete, move to master for a stable version or to development to have the latest, ugfixed and cutting-edge version