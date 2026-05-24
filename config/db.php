<?php
$servername = "localhost";
$username = "your_username";
$password = "your_password";
$dbname = "smartlib"; // default database

$conn = new mysqli($servername, $username, $password, $dbname);

// Set charset
$conn->set_charset("utf8");

// Check connection
if ($conn->connect_error) {
    die("❌ Database Connection Failed: " . $conn->connect_error);
}
?>