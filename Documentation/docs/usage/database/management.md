# Database support
If you are reading this it means that you are tired of using *SQL* or any other native database extension, aren't you? :D

I know: using SQL is tedious, error-prone and stucks you on a RDBMS, because changing it would force you 
changing every SQL query you created.

Gishiki uses an OHM that is similar to ruby's one and implements the ActiveRecord pattern. 

Yes, that is not a misspell: Gishiki uses an OHM, not an ORM! OHM stands for "Object-hybrid mapper"!
This OHM has been designed and written from scratch to be perfectly integrated within Gishiki!

The ActiveRecord implementation is meant to support any database you want:

   - SQLite (~3)
   - MySQL and derivates
   - Oracle
   - PostgreSQL
   - Microsoft SQL Server / Azure
   - Sybase
   - firebase
   - MongoDB (>= 3.0)
   - Cassandra

This abstract the database away from you, wichever database you may be using! Even non-relation ones!

## Connection
Before discussing about how you manage your database you have to provide a valid connection to your database.

You do this by editing the config.json file. A connection to a database is a JSON object inside the __database_connections__
class.

The default database connection is named 'default' and you *shouldn't* delete it, however you are free to change it.

Keep in mind that there is a connection named 'development' and, like the 'default' one shouldn't be deleted, because that connection
is the default connection when the framework is in developer mode

Adding a database connection is as simple as adding a JSON object to the database_connections object:

```
"connection_name": {
    "driver": "mysql",
    "query": "root:admin@localhost/site_db"
}
```

Before testing out a connection remember to install the required *PDO driver*/*native extension* for your database server!


## Quick Syntax
You can implode the driver and the connection query into a single string, like this
one:

```
"connection_name": "mysql://root:admin@localhost/site_db"
```

This is also especially usefull to integrate Heroku database connection strings!


## MongoDB
MongoDB is for sure a great database and using it will avoid a lot of troubles.

The support for MongoDB is native and to have the better experience possible
Gishiki uses the latest driver available (currenctly the PECL mongodb package).

After installing the required extension you connect to the database using the
[mongodb-style URI](http://php.net/manual/en/mongodb-driver-manager.construct.php):

```
"connection_name": {
    "driver": "mongodb",
    "query": "[username:password@]host1[:port1][,host2[:port2],...[,hostN[:portN]]][/[database][?options]]"
}
```

Notice that 'mongodb://' is excluded from the connection query.


## MySQL Connection
MySQL is the second most widely used RDBMS in the world (as of April 2016), and one of the RDBMS with more project forks!

With a MySQL connection you can connect to [MySQL](http://www.oracle.com/us/products/mysql/overview/index.html), [Percona Server](https://www.percona.com/software/mysql-database/percona-server), [MariaDB](https://mariadb.org/) and many more!

When connecting to a MySQL server you have to use "mysql" as the server protocol:

```
"connection_name": {
    "driver": "mysql",
    "query": "user:password@localhost/site_db"
}
```

As you can see if you don't specify a server port the dafault one will be used (in MySQL it is 3306).

To specify a port you have to append it after the hostname:

```
"connection_name": {
    "driver": "mysql",
    "query": "user:password@hostname:port/site_db"
}
```

and the specified port will be used when connecting to the database.


## Oracle Connection
If you are using Oracle as your RDBMS you can connect to your database exactly like
you would connect to a MySQL database: you only need to use oci as server protocol!

```
"connection_name": {
    "driver": "oci",
    "query": "user:password@hostname:port/site_db"
}
```

and et voila' Oracle database connection performed!


## PostgreSQL Connection
I can't believe people are just ingnoring the fantastic [PostgreSQL](http://www.postgresql.org/) project: 
it is a fantastic RDBMS with a great usage license!

Everyone should think about PostgreSQL as its first choice as RDBMS, because it is easy to use, mature, 
well supported and really performs well!

To enstabilish a connection with a Postgres server you  have to use pgsql as the server protocol:

```
"connection_name": {
    "driver": "pgsql",
    "query": "user:password@hostname:port/site_db"
}
```

If you don't provide a port to your connection the ORM will use the default PostgreSQL port, which is the 5432 port.


## SQLite Connection
Connecting to a sqlite database is super simple: you just provide the file name:

```
"connection_name": {
    "driver": "sqlite",
    "query": "site_db.db"
}
```

You can also provide the real path to the file:

```
"connection_name": {
    "driver": "sqlite",
    "query": "/var/site_db.db"
}
```

If you adopt this solution you can keep the database file outside the web-server directory.

Using SQLite is great for prototyping but you don't want a sqlite file to be 
your database in a production environment due to the limited number of concurrent 
accesses sqlite can handle.


## Conclusions
Connecting a database is simple, using it even more: the connection object is 
the only thing you have to change/add when changing RDBMS and/or host!

As you may have noticed your settings file now contains your database password! 
You __MUST__ ensure no one will ever be able to reach that file!