# CRUD

The acronym CRUD stands for Create, Read, Update and Delete: those are names of
operations you will be allowed (directly or indirectly) to perform on databases
you are connected to.


## Create

The creation of a new *document* starts from a __CollectionInterface__, such as
__SerializableCollection__.

The function that has to be called is __Insert__ that also requires the name of
the *collection* to be affected:

```php
use Gishiki\Database\DatabaseManager;

$connection = DatabaseManager::Retrieve('connectionName');

//since this is a mongodb database the table name is given as dbname.tbname
$idOfNewDocument = $connection->Insert('dbname.tbname', new SerializableCollection([
    'name'      => $name,
    'surname'   => $surname,
    'nickname'  => $nickname,
    'password'  => $hash //it is NOT good to store plain passwords
]));
```

Where the name of the connection is the same name in the application [configuration](configuration.md).