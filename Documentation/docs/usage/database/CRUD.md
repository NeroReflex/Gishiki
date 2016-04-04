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
$my_book->title = 'Example Book';
$my_book->author = 'Example Author';
$my_book->price = 29.99;
$my_book->publication_date = new ActiveRecord\DateTime('2016-04-04 17:56:30');

//make sure we have saved the book
$my_book->save();
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
$my_old_book = Book::last(array('conditions' => "isbn = 'book_isbn'"));
//more about database read in the "Read" section

//time to be removed! Bye!
$my_book->delete();
```

As you can see the delete() usage is equal to save() usage: you can say that
the delete is the evil twin of save :D.


## Read
Read operations are harder than write operations, thanks to the ActiveRecord design 
you have great flexibility on your database researches while maintaining distances 
from the hand-written SQL!