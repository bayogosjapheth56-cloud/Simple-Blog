<?php
// db_connect.php - database connection
// LOCAL (XAMPP) settings. For InfinityFree, replace with the values from
// Control Panel -> MySQL Databases (hostname, username, password, database name).

$host = "localhost";      // InfinityFree: e.g. sql123.infinityfree.com
$user = "root";           // InfinityFree: e.g. if0_12345678
$pass = "";               // InfinityFree: your database password
$db = "blog_db";        // InfinityFree: e.g. if0_12345678_blog_db

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8mb4");

// Automatically ensure 'image' column exists in 'posts' table
$colCheck = mysqli_query($conn, "SHOW COLUMNS FROM posts LIKE 'image'");
if ($colCheck && mysqli_num_rows($colCheck) === 0) {
    mysqli_query($conn, "ALTER TABLE posts ADD COLUMN image VARCHAR(255) NULL AFTER content");
}
