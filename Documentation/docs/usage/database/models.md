# Models
You *should* read this chapter *only* if you have already read how to [manage database connections](/usage/database/management.md) 
where you learn how to connect with your database.

In this chapter you will learn what a model is and how you create and use one.

Models are the 'M' in 'MVC': they are the interface you use to load, store and access 
your data within your application.


## Defining a model
A model looks like a PHP file (inside the Models directory) containing a class 
that inherits from the \Activerecord\Model class:

```PHP
class BookSeller extends \Activerecord\Model { }
```

ActiveRecord uses Ruby's ActiveRecord naming conventions, so.... this class is a 
direct representation of the table named 'book_sellers' inside the default database.

You can change both: the database you connect to and the name that the table has
inside the database pointed by the database connection:

```PHP
class BookSeller extends \Activerecord\Model {
    # explicit the connection name since the default one is not going to be used
    static $connection = 'development';

    # explicit the table name since the real table name is not 'book_sellers'
    static $table_name = 'book_shop';
   
}
```

Every table you create a model from __MUST__ contains a primary key, and if the 
name of the primary key is __NOT__ 'id' you will have to specify it in the model:

```PHP
class BookSeller extends \Activerecord\Model {
    # the primary key of the table isn't named 'id'
    static $primary_key = 'b_seller_ID';
}
```

As you can see it is really simple to map a database table inside a PHP class. 
This brings you many advantages, many of them are:

   - No hand-writte SQL
   - Data storage engine abstraction
   - Easy RDBMS migration
   - Rapid development
   - Better logical organization of your data
   - Easy data management
   - Separation of application data and application logic
   - Operations are easy to read and understand


## Basic model operations
You have represented a database table as a PHP class, where class properties are 
the table fields; now you need to create an instance of that model and perform read/write 
on properties of that object!

Let's consider a table named 'books' that has a primary key named 'id', a (TEXT) field 'title', 
a (TEXT) field 'author', a (REAL) field 'price' and a (DATETIME) field 'publication_date':

```PHP
class Book extends \Activerecord\Model { }

$my_book = new Book();
$my_book->title = 'Example Book';
$my_book->author = 'Example Author';
$my_book->price = 29.99;
$my_book->publication_date = new ActiveRecord\DateTime('2016-04-04 17:56:30');
```

What? Ugly to look at? Ok...

```PHP
$my_book = new Book(['title' => 'Example Book', 
                        'author' => 'Example Author',
                        'price' => 29.99,
                        'publication_date' => new ActiveRecord\DateTime('2016-04-04 17:56:30')]);
```

Oh? What was that!? Was my database filled!?!? How?!?!? Where?!?!?

Yup. As explained in the [CRUD](CRUD.md) section, in order to simplify even more your 
database interaction, you have your models being saved automatically!

## Advanced model operations
Accessiong your model data as a class property can be easy, but it doesn't give you 
a lot of flexibility: think about a password: You want to encrypt a password before 
storing it into the database... You would need to do something like this:

```PHP
class User extends \Activerecord\Model { }

$my_user = new User();
//$my_user->....

//encrypt the passowrd
$enc_pwd = encrypt($plain_password);
$my_user->password = $enc_pwd;
```

It is ugly to read, you will mostly likely forget to encrypt() your password somewere
and you will need to call unencrypt() each time you want to read the password.

In this situation you *should* use custom getters and setters, allowing you to 
abstract away from your controllers the encryption stuff, embedding it into the model:

```PHP
class User extends \Activerecord\Model {
    public function set_password($plain_password) {
        $this->assign_attribute('password', encrypt($plain_password));
    }

    public function get_password() {
        $encrypted = read_attribute('password');
        return unencrypt($encrypted);
    }
}

$my_user = new User();
$my_user->password = $plain_password;

echo $my_user->password; //print out $plain_password
```

Note: you *can* call setters and getter you created, if you want:

```PHP
$my_user = new User();
$my_user->set_password($plain_password);

echo $my_user->get_password(); //print out $plain_password
```

If you call a custom getter/setter you haven't created it will result in a standard value
read/write, without custom behaviour, so... just use the syntax you like the most when
dealing with your models.

## Date and Time
As you can see from above examples you need to use ActiveRecord\DateTime object when
dealing with a model property that represents time.

DateTime usage is not explained here, it is so simple you can understand everything just by reading the [API](/API/class-ActiveRecord.DateTime.html).


## Conclusions
__Store 'n' Load__: what this chapter describes is how to deal whit your data when 
it is in-memory, however not your entire data can reside into volatile memory: 
this is why you are using a database, right?

This means it must be a way to easily manage your data on the database...

Actually, it is really simple, you can find everything in the [CRUD](CRUD.md) section.