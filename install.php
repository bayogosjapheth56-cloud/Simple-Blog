<?php
// install.php - TEMPORARY one-time setup: creates the tables from database.sql.
// Delete this file (and database.sql) from the server after running it!
ini_set("display_errors", "1");
error_reporting(E_ALL);
require __DIR__ . "/db_connect.php";

echo "<pre>";

$existing = mysqli_query($conn, "SHOW TABLES LIKE 'posts'");
if ($existing && mysqli_num_rows($existing) > 0) {
    die("The 'posts' table already exists - nothing to do. Delete install.php now.");
}

$sql = @file_get_contents(__DIR__ . "/database.sql");
if ($sql === false) {
    die("database.sql not found - upload it next to install.php.");
}

if (!mysqli_multi_query($conn, $sql)) {
    die("Install failed: " . mysqli_error($conn));
}
// Step through every statement's result so errors in later statements are reported
do {
    if ($r = mysqli_store_result($conn)) {
        mysqli_free_result($r);
    }
} while (mysqli_more_results($conn) && mysqli_next_result($conn));

if (mysqli_errno($conn)) {
    die("Install stopped with an error: " . mysqli_error($conn));
}

echo "Done! Tables created in database '" . htmlspecialchars($db) . "'.\n";
echo "Now DELETE install.php and database.sql from the server, then open index.php.";
echo "</pre>";
