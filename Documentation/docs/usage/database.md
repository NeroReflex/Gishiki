# Database

Gishiki is developed to reflect the MVC pattern: this means that the data lifecycle
is a foundamental characteristic within the framework!

Data persistence, coherence and integrity is managed by the database manager.

To connect a database manager you have to edit the active [configuration](configuration.md).

There isn't a limit to the number of database connection, but each one __MUST__
have a name, and there __CANNOT__ be two connections with the same name.


## Connecting Database

A database connection have the following form:

```
adapter://adapter_manageable_conenction_query
```

where the connection query is a string that the adapter can parse.

### MongoDB

A MongoDB connection can be enstabilished by using the mongodb adapter bundled
with Gishiki.

The MongoDB adapter uses the mongodb php native extension: Composer calls it
[ext-mongodb](https://pecl.php.net/package/mongodb): 

```
mongodb://username:password@host:port/dbname
```


## Differences between databases

Each database manager has different characteristics: Gishiki aims to preserve
strong points of each one, but miracles are not possibles: everything comes to
a price.

Following are __RULES__ you __MUST__ follow when designing database tables.
   
   - The name must be the plural form of the name of object to store
   - The name must be written in underscore_case with no UPPER characters
   - The unique id field (when possible) must be called _id


## Operations on Databases

To understand how to interact with the database you have to read the [CRUD](CRUD.md)
chapter of this tutorial.