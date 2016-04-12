<?php
/*******************************************************************************
 *              
 *              Example router for the Gishiki framework
 * 
 *******************************************************************************/

//import the router (Route class)
use \Gishiki\Core\Route;

Route::get("/", function() {
   //this is the homepage, just render a small list of books...
});

Route::get("/book/author/{author}", function($params) {
    //retrive every book with the given author
    $books = Book::Dispense(\Gishiki\ActiveRecord\RecordsSelector::filters()->where_author_is($params->author)->limit(20));   
    //notice how $params is used!
    
    //print out results:
    if (!$books->is_empty()) {
        foreach ($books as $current_book) {
            echo $current_book->title . "(ISBN: " . $current_book->isbn . ")<br />";
        }
    } else {
        echo "I have no books written by " . $params->author;
    }
});

Route::get("/book/new/{isbn}/{title}/{author}/{price}/", function($params) {
    //create the new book and remove it from the voletile-memory
    $book = Book::Create($params());

    //you don't need
    //$book->save();
    
    //because the created book is automatically save!
    echo "Book registered!";
});

Route::error(Route::NotFound, function() {
   //this is what is executed if the router is unable to find a suitable route $
   echo("Unknown request: try something else");
});
