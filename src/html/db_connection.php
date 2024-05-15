<?php // login.php
    $hn = 'localhost'; //hostname
    $db = 'database_name'; //database
    $un = 'username'; //username
    $pw = 'password'; //password

    $conn = new mysqli($hn, $un, $pw, $db);

// Check connection
    if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
    }
?>