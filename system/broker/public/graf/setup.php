<?php

// Include the ORM library
require_once('lib/idiorm.php');

$host = '127.0.0.1';
$user = 'root';
$pass = 'sam159753';
$database = 'bidder';

ORM::configure("mysql:host=$host;dbname=$database");
ORM::configure('username', $user);
ORM::configure('password', $pass);


ORM::configure('driver_options', array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'));
