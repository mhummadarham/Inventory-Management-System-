<?php

$localhost = "127.0.0.1";
$username = "root";
$password = ""; // Default XAMPP root password is empty
$dbname = "stock";

// Create database connection
$connect = new mysqli($localhost, $username, $password, $dbname);

// Check connection
if ($connect->connect_error) {
    die("Connection Failed: " . $connect->connect_error);
} else {
    // Optional: Uncomment to confirm connection
    // echo "Successfully connected";
}

?>