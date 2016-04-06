# Database support
If you are reading this it means that you are tired of using *SQL*, aren't you? :D

I know: using SQL is tedious, error-prone and stucks you on a RDBMS, because changing it would force you 
changing every SQL query you created.

In order to abstract SQL away from your project you need an ORM (Object-relational mapping).

Gishiki uses an ORM that is similar to ruby's one: PHP ActiveRecord. This ORM has been modified to better integrate 
with Gishiki.


## Connection
Before discussing about how you manage your database you have to provide a valid connection to your database.

You do this by editing the config.json file. A connection to a database is a JSON property inside the __connections__
class.

The default database connection is named 'default' and you *shouldn't* delete it, however you are free to change it.

Adding a database connection is as simple as adding a JSON property like: "&lt;connection_name&gt;": "&lt;connection_str&gt;".

The connection string is something like:
```
server://username:password@host:port/db_name
```

You can specify a charset that a database will use:
```
server://username:password@host:port/db_name?charset=utf8
```

You can find some examples of database connections on the settings file, but I am going to give a better explaination here.

Before testing out a connection remember to install the required PDO driver for your database server!


## MySQL Connection
MySQL is the second most widely used RDBMS in the world (as of April 2016), and one of the RDBMS with more project forks!

With a MySQL connection you can connect to [MySQL](http://www.oracle.com/us/products/mysql/overview/index.html), [Percona Server](https://www.percona.com/software/mysql-database/percona-server), [MariaDB](https://mariadb.org/) and many more!

When connecting to a MySQL server you have to use "mysql" as the server protocol:

```
mysql://root:admin@localhost/site_db
```

As you can see if you don't specify a server port the dafault one will be used (in MySQL it is 3306).


## Oracle Connection
If you are using Oracle as your RDBMS you can connect to your database exactly like you would connect to a MySQL database,
you only need to use oci as server protocol (change mysql with oci):

```
oci://root:admin@host:port/site_db
```

and et voila' Oracle database connection performed!


## PostgreSQL Connection
I can't believe people are just ingnoring the fantastic [PostgreSQL](http://www.postgresql.org/) project: 
it is a fantastic RDBMS with a great usage license!

Everyone should think about PostgreSQL as its first choice as RDBMS, because it is easy to use, mature, 
well supported and really performs well!

To enstabilish a connection with a Postgre server you  have to use pgsql as the server protocol:

```
pgsql://root:admin@host:port/site_db
```

If you don't provide a port to your connection the ORM will use the default PostgreSQL port, which is the 5432 port.


## SQLite Connection
Connecting to a sqlite database is super simple: you just provide the file name:

```
sqlite://../relative/path/database_file.db
```

On a Unix system you can provide the full path to a database this way:

```
sqlite://unix(/var/database_file.db)
```

On a windows system you can do the exact same thing:

```
sqlite://unix(C:\absolute\path\database_file.db)
```

If you adopt this solution you can keep the database file outside the web-server directory.

Using SQLite is great for prototyping but you don't want a sqlite file to be 
your database in a production environment due to the limited number of concurrent 
accesses sqlite can handle.


## Conclusions
Connecting a database is simple, using it even more: the connection string is 
the only thing you have to change when changing RDBMS and/or host!

As you may have noticed you settings file now contains your database password! 
You __MUST__ ensure no one will ever be able to reach that file!