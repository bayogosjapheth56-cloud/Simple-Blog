<?php
// check.php - TEMPORARY diagnostic page. Delete it from the server once the site works!
ini_set("display_errors", "1");
error_reporting(E_ALL);
mysqli_report(MYSQLI_REPORT_OFF);

echo "<pre>";
echo "PHP version: " . PHP_VERSION . "\n\n";

// 1. Required files
foreach (["db_connect.php", "includes/functions.php", "includes/header.php",
          "includes/footer.php", "includes/post_card.php"] as $f) {
    echo str_pad($f, 28) . (file_exists(__DIR__ . "/" . $f) ? "OK" : "MISSING") . "\n";
}

// 2. Database connection (uses the same settings as db_connect.php)
$src = file_get_contents(__DIR__ . "/db_connect.php");
preg_match('/\$host\s*=\s*"([^"]*)"/', $src, $h);
preg_match('/\$user\s*=\s*"([^"]*)"/', $src, $u);
preg_match('/\$db\s*=\s*"([^"]*)"/', $src, $d);
echo "\nhost = " . ($h[1] ?? "?") . ", user = " . ($u[1] ?? "?") . ", db = " . ($d[1] ?? "?") . "\n";

require __DIR__ . "/db_connect.php"; // dies here if the connection fails
echo "Connection: OK\n\n";

// 3. Tables and columns
$tables = mysqli_query($conn, "SHOW TABLES");
echo "Tables in database:\n";
while ($t = mysqli_fetch_row($tables)) {
    echo "  - " . $t[0] . "\n";
}

foreach (["categories", "posts"] as $table) {
    $cols = mysqli_query($conn, "SHOW COLUMNS FROM `$table`");
    if (!$cols) {
        echo "\n$table: ERROR " . mysqli_error($conn) . "\n";
        continue;
    }
    $names = [];
    while ($c = mysqli_fetch_assoc($cols)) {
        $names[] = $c["Field"];
    }
    echo "\n$table columns: " . implode(", ", $names) . "\n";
}

// 4. The exact queries index.php runs
require __DIR__ . "/includes/functions.php";
$tests = [
    "stats" => "SELECT COUNT(*) FROM posts",
    "categories" => "SELECT c.name, COUNT(p.id) FROM categories c JOIN posts p ON p.category_id = c.id GROUP BY c.id, c.name",
    "posts" => POST_SELECT_SQL . " ORDER BY p.created_at DESC",
];
echo "\n";
foreach ($tests as $name => $sql) {
    $r = mysqli_query($conn, $sql);
    echo str_pad("Query $name:", 20) . ($r ? "OK" : "FAILED - " . mysqli_error($conn)) . "\n";
}
echo "</pre>";
