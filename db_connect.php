<?php
// db_connect.php - database connection
// LOCAL (XAMPP) settings. For InfinityFree, replace with the values from
// Control Panel -> MySQL Databases (hostname, username, password, database name).

$host = "localhost";      // InfinityFree: e.g. sql123.infinityfree.com
$user = "root";           // InfinityFree: e.g. if0_12345678
$pass = "";               // InfinityFree: your database password
$db = "blog_db";          // InfinityFree: e.g. if0_12345678_blog_db

// Turn off mysqli exceptions: every page checks return values itself
mysqli_report(MYSQLI_REPORT_OFF);

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    // Log the real reason for the developer; show a generic message to visitors
    error_log("Database connection failed: " . mysqli_connect_error());
    http_response_code(500);
    die("Sorry, the site is temporarily unavailable. Please try again later.");
}

mysqli_set_charset($conn, "utf8mb4");
