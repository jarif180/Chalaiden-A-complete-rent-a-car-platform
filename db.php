<?php

    $dsn = "mysql:host=localhost;dbname=chalaiden";
    $username = "root";
    $pw = "";


    $pdo = new PDO($dsn, $username, $pw);

    if(!$pdo){
        die("x-x");
    } else {
        // echo "<h5>connection successful</h5>";
    }

