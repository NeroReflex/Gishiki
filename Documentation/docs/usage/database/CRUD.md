# CRUD
CRUD stands for __C__reate, __R__ead, __U__pdate and __D__elete.

Those are the names of most common operations you perform on your database, 
and the easiest way you have to manage your database using your models.


## Create and Update
Create the representation of a model inside your database and updating an existing 
one are two tasks ActiveRecord automatically performs when yours models exit from
the volatile memory.

This actually prevent you from accidentally lose data, spending hours to find 
where you are losing your data :( or to design your store logic.

Despite the fact your model instances are automatically saved (by default) you 
can *manually* trigger the save operation calling the save() function on the model
you want to be saved:

```PHP
class Book extends \Activerecord\Model { }

//edit the book: you modified your book, so it must be saved, or you will lost
//all of your editings!
$my_book = new Book();
$my_book->isbn = '4760375034';
$my_book->title = 'Example Book';
$my_book->author = 'Example Author';
$my_book->price = 29.99;
$my_book->publication_date = new ActiveRecord\DateTime('2016-04-04 17:56:30');

//make sure we have saved the book
$my_book->save();
```

You can also trigger an immediate model save when creating a new one:

```PHP
class Book extends \Activerecord\Model { }

$my_book = Book::create(['isbn' => '...', 'title' => '...' /* , .... */ ]);
```


Are you thinking "How do I avoid that auto-save foolish usefullness"?
If yes I hope you will re-think about that, but you can get rid of it just by calling 
the prevent_autosave() function on the model you want to be stopped from being 
automatically saved into/updated on your database:

```PHP
class Book extends \Activerecord\Model { }

//edit the book: you modified your book, so it must be saved, or you will lost
//all of your editings!
$my_book = new Book();
$my_book->title = 'Example Book';
$my_book->author = 'Example Author';
$my_book->price = 29.99;
$my_book->publication_date = new ActiveRecord\DateTime('2016-04-04 17:56:30');

//disable autosave
$my_book->prevent_autosave();

//here your book will be lost: YOU HAVE TO SAVE IT!
$my_book->save();
```

__Disclaimer__: please, keep in mind that a bad usage of prevent_autosave may lead to data loss 
and/or data corruption!


## Delete
There is data meant to stay forever in your database and data that will stay for 
a short amount of time.

Let's make an example: a book that is not going to be sold anymore because the new
edition was released:

```PHP
class Book extends \Activerecord\Model { }

//get the (only) book with that isbn
$my_old_book = Book::first(['conditions' => "isbn = 'book_isbn'"]);
//more about database read in the "Read" section

//time to be removed! Bye!
$my_old_book->delete();
```

As you can see the delete() usage is equal to save() usage: you can think about
delete as the evil twin of save :D.


## Read
Read operations are harder than write operations, thanks to the ActiveRecord design 
you have great flexibility on your database researches while maintaining distances 
from the hand-written SQL!

You can use two types of database research:
   
   - single record result
   - multiple records result


## Single record result
A single record result is generated when calling one of these functions on your 
model class:

   - last() or find('last')
   - first() or find('first')

By invoking those functions (statically on your model class) you are going to 
get a single result and that result is an instance of your model class, example:

```PHP
class Book extends \Activerecord\Model { }

//retrive the first book among your books
$first_book = Book::first(); // you can also use find('first')
```

same thing when using last:

```PHP
class Book extends \Activerecord\Model { }

//retrive the first book among your books
$last_book = Book::last(); // you can also use find('last')
```

You can retrive a model by its database ID.
The ID of a model is the value of its primary key field:

```PHP
class Book extends \Activerecord\Model { }

//retrive the book with 6 as the primary key
$my_book = Book::find(6);
```

When a fetch operation cannot be performed (no records are matching options) a NULL
model is returned.

That means you can easily check weather your research was a success:

```PHP
class Book extends \Activerecord\Model { }

//retrive the book with 6 as the primary key
$my_book = Book::find(6);

if ($my_book === null) {
    echo 'I have lost my favourite book :(';
} else {
    echo 'Found my favourite book '. $my_book->title .'!!';
}
```

if you run the example what you will see depends on the number of test you have 
done with the book model :) .


## Multiple record result
You should already know (as it is written in the above section) a call to 
last(...) or first(...) results in a find('last', ...) or find('first', ...) call. 

'first' and 'last' are used when a single record result is needed, but what triggers
a multiple record result? The answer is 'all'.

When using find('all', ....) or its alias all(...) the operation result is an array and
the result cannot be NULL: if no records are found then an empty array is returned.

Look at this trivial example:

```PHP
class Book extends \Activerecord\Model { }

//retrive all books
$my_books = Book::all();

echo "I have " . count($my_books) . " book in my library!";
```

It is a bit trivial to query all books to know the number of books,
but it is a perfect example to have you understand all().


## Limiting results
Sooner or later you will want to limit the subset of records you want to retrive 
from your database: fetching every database record means rebuilding the entire database 
in volatile memory, which is __REALLY BAD__!


### Fetch criteria
The result set of a fetch operation can be limited by adding criteria to your fetch.

With "criteria" I mean the set of contitions a record must satisfy to be retrieved 
from your database:

```PHP
class Book extends \Activerecord\Model { }

$criteria = ["genre = ? AND author = ? OR price < ?", "romance", "Example Author", 9.90];

//retrive all books matching criteria
$my_books = Book::all(['conditions' => $criteria]);


//inline everithing: $my_books = Book::all(['conditions' => ["genre = ? AND author = ? OR price < ?", "romance", "Example Author", 9.90]]);
echo "I have " . count($my_books) . " book in my library that you may enjoy reading!";
```

You can even fetch the first (or the last) record that matches given conditions:

```PHP
class Book extends \Activerecord\Model { }

$criteria = ["genre = ? AND author = ? OR price < ?", "romance", "Example Author", 9.90];

//retrive all books matching criteria
$my_book = Book::first(['conditions' => $criteria]);

if ($my_book) {
    echo "You may enjoy reading '" . $my_book->title . "'!";
} else {
    echo "I have no books you may enjoy reading :(";
}
```

Always add conditions when performing a fetch operation in your database. 

### Limit and offset

However you may want to limit the number of records result, you do this by adding a limit rule:

```PHP
class Book extends \Activerecord\Model { }

//retrive 10 books matching criteria
$my_books = Book::all(['limit' => 10 , 'conditions' => ["genre = ? AND author = ? OR price < ?", "romance", "Example Author", 9.90]]);

echo "I have " . count($my_books) . " book in my library that you may enjoy reading!";
```

Do you want to ignore first 5 results? No problem:

```PHP
class Book extends \Activerecord\Model { }

//retrive 10 books (matching criteria), ignoring 5
$my_books = Book::all([offset => 5, 'limit' => 10 , 'conditions' => ["genre = ? AND author = ? OR price < ?", "romance", "Example Author", 9.90]]);

echo "I have " . count($my_books) . " book in my library that you may enjoy reading!";
```

This is everything *should* know about model fetching.

### Ordering results
Order is important, even when fetching models from your database! To order your 
models you can set an 'order' rule alongside your 'conditions':

```PHP
class Book extends \Activerecord\Model { }

//retrive 10 books ignoring 5, ordering them by title
$my_books = Book::all(['order' => 'title desc, price asc', offset => 5, 'limit' => 10 , 'conditions' => ["genre = ? AND author = ? OR price < ?", "romance", "Example Author", 9.90]]);

foreach ($my_books as &$my_book) {
    echo $my_books->title . ", ";
}
```

Enjoy your ordered results!