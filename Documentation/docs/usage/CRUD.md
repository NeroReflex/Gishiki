# CRUD
The acronym __CRUD__ stands for Create, Read, Update and Delete: those are names
of main operations you will be allowed (directly or indirectly) to perform on
databases that are supporting your application.

__Notice that:__ if you only need read permissions from a database such as
PostgreSQL or MySQL, you do __NOT__ need to use an user with full access.

## Create
The creation of a new *document*/*row* either starts from a __CollectionInterface__,
such as __SerializableCollection__ or a native PHP array.

The function that has to be called is __create__ that also requires the name of
the *table*/*collection* to be affected:

```php
use Gishiki\Database\DatabaseManager;

$connection = DatabaseManager::Retrieve('connectionName');

$idOfNewDocument = $connection->create('tbname', new SerializableCollection([
    'name'      => $name,
    'surname'   => $surname,
    'nickname'  => $nickname,
    'password'  => $hash //it is NOT good to store plain passwords
]));
```

Where the name of the connection is the same name in the application [configuration](configuration.md).

## Delete
To delete a restricted set of *documents*/*rows* from a *table*/*collection*
you have to call, on the desired database connection the __delete__ function.

The delete function needs the name of the table/collection to be affected and
a valid instance of SelectionCriteria:

```php
use Gishiki\Database\DatabaseManager;
use Gishiki\Database\SelectionCriteria;
use Gishiki\Database\FieldRelationship;

$connection = DatabaseManager::Retrieve('connectionName');

$connection->delete('tbname', SelectionCriteria::Select([
            'nickname' => $nickname
        ])->or_where('email', FieldRelationship::EQUAL, $email)
    );
```

You can also delete __EVERY__ *documents*/*rows* from a *table*/*collection*
using the __deleteAll__ function.

The delete function only needs the name of the table/collection to be affected:

```php
use Gishiki\Database\DatabaseManager;
use Gishiki\Database\SelectionCriteria;
use Gishiki\Database\FieldRelationship;

$connection = DatabaseManager::Retrieve('connectionName');

$connection->deleteAll('tbname');
```

__Note that:__ calling the delete function, passing an empty SelectionCriteria
object has the same effect of calling deleteAll, however deleteAll will perform
a little better!