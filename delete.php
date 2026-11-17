<?php
// delete.php - DELETE (remove post and its photo file)
// Only accepts POST requests with a valid CSRF token, so a simple link or
// crawler visiting delete.php?id=... can never delete a post.
require_once "db_connect.php";
require_once "includes/functions.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit;
}

if (!csrf_valid()) {
    header("Location: index.php?msg=" . urlencode("Your session expired. Please try deleting again."));
    exit;
}

$id = (int) ($_POST["id"] ?? 0);

if ($id > 0) {
    // Check if the post has an associated image file
    $stmt = mysqli_prepare($conn, "SELECT image FROM posts WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $post = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

    // Delete post record from database
    $delStmt = mysqli_prepare($conn, "DELETE FROM posts WHERE id = ?");
    mysqli_stmt_bind_param($delStmt, "i", $id);
    mysqli_stmt_execute($delStmt);

    if (mysqli_stmt_affected_rows($delStmt) > 0) {
        // Record is gone, so the photo file can be removed too
        if (!empty($post["image"]) && file_exists("uploads/" . $post["image"])) {
            @unlink("uploads/" . $post["image"]);
        }
        $msg = "Post deleted successfully!";
    } else {
        $msg = "Post not found.";
    }
} else {
    $msg = "Invalid post ID.";
}

header("Location: index.php?msg=" . urlencode($msg));
exit;
