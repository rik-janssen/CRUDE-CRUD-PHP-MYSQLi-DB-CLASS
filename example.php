<?php

include 'db.class.php';

// New DB class:
$db = new db();

//////////////////////////////
// $db->insert(); example:
// ---
// Insert rows into the database
// by creating an array with 
// settings and sanitize options
//////////////////////////////

$args = array(
    'table' => 'Persons',
    'fields' => array(
        'LastName' => array(
            'value' => 'My Name'.rand(0,3000), 
            'sanitize' => 'text', 
            'placeholder'=>'My other name'
        ),
        'FirstName' => array(
            'value' => 'Wilhelm', 
            'sanitize' => 'text'
        ),
        'Address' => array(
            'value' => 'My Address street', 
            'sanitize' => 'text',
            'placeholder'=>'My other street'
        ),
        'Number' => array(
            'value' => rand(0,3000), 
            'sanitize' => 'int'
        ),
        'City' => array(
            'value' => 'Citytowns', 
            'sanitize' => 'email'
        )
    ) // end fields
);
$db->insert($args);

// Notes: 

// - Sanitize can hold: 
// 'float' / 'int' / 'string' / 'email' / 'url'
// and does not present errors.

// - Placeholder works as a default value
// for when there is no value supplied

//////////////////////////////
// $db->update(); example:
// ---
// Insert rows into the database
// by creating an array with 
// settings and sanitize options
//////////////////////////////
$args = array(
    'table' => 'Persons',
    'set' => array(
        'LastName' => array(
            'value' => "Another Last Name!",
            'sanitize' => "text"
        ),
        'City' => array(
            'value' => "Another Town!",
            'sanitize' => "text"
        )
    ),
    'where' => array(
        'id' => array(
            'value' =>rand(0,100) ,
            'sanitize' => 'int'
        )
    )
);

$db->update($args);

// Notes: 

// - Sanitize can hold: 
// 'float' / 'int' / 'string' / 'email' / 'url'
// and does not present errors.

//////////////////////////////
// $db->delete(); example:
// ---
// Delete rows from the database
// by creating an array with 
// settings and sanitize options
//////////////////////////////
$args = array(
    'table' => 'Persons',
    'where' => array(
        'id' => array(
            'value' => rand(1,22),
            'sanitize' => 'int'
        )
    )
);

$db->delete($args);


// Notes: 

// - Sanitize can hold: 
// 'float' / 'int' / 'string' / 'email' / 'url'
// and does not present errors.

//////////////////////////////
// $db->get(); example:
// ---
// GET rows from the database
// by creating an array with 
// settings and sanitize options
//////////////////////////////
$args = array(
    'table' => 'Persons',
    'where' => array(
        'City' => array(
            'value' => 'Citytowns',
            'sanitize' => 'text',
            'compare' => 'is'

        ),
        'FirstName' => array(
            'value' => 'Hank',
            'sanitize' => 'text',
            'compare' => 'not'
        )
        ),
        'order' => 'ASC',
        'orderBy' => 'id',
        'limit' => 10
);

print_r($db->get($args));


// Notes: 

// - Sanitize can hold: 
// 'float' / 'int' / 'string' / 'email' / 'url'
// and does not present errors.
// - Compare can hold:
// 'is' / 'not' / 'largerthen' / 'smallerthen'

//////////////////////////////
// $db->count(); example:
// ---
// GET rows from the database
// by creating an array with 
// settings and sanitize options
//////////////////////////////
$args = array(
    'table' => 'Persons',
    'where' => array(
        'City' => array(
            'value' => 'Citytowns',
            'sanitize' => 'text',
            'compare' => 'is'

        ),
        'FirstName' => array(
            'value' => 'Hank',
            'sanitize' => 'text',
            'compare' => 'not'
        )
        ),
        'order' => 'ASC',
        'orderBy' => 'id'
    );

print_r($db->count($args));

//////////////////////////////
// $db->sql(); example:
// ---
// Use this to run custom
// SQL if the other options
// won't work for your needs
//////////////////////////////

/*
$db->sql("
CREATE TABLE Persons (
    id int  AUTO_INCREMENT,
    LastName varchar(255),
    FirstName varchar(255),
    Address varchar(255),
    Number int,
    City varchar(255),
    PRIMARY KEY (id)
);
"); */

var_dump ($db);

// Close the connection
$db->close();
