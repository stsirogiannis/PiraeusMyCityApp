<?php
// Σε Docker ο host της βάσης είναι το όνομα του service ("db"),
// σε τοπική εγκατάσταση XAMPP είναι το localhost.
if (getenv('DB_HOST')) {
    $servername = getenv('DB_HOST');
    $password   = getenv('DB_PASS');
} else {
    $servername = "localhost";
    $password   = "";
}

$username = "root";
$dbname   = "mycity";

// Create connection
$conn = mysqli_connect($servername, $username, $password, $dbname);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8mb4");