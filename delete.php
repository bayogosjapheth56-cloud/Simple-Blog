<?php
// delete.php - DELETE (remove post and its photo file)
require_once "db_connect.php";

$id = (int)($_GET["id"] ?? 0);

if ($id > 0) {
    // Check if the post has an associated image file
    $stmt = mysqli_prepare($conn, "SELECT image FROM posts WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $post = mysqli_fetch_assoc($res);

    // Delete post record from database
    $delStmt = mysqli_prepare($conn, "DELETE FROM posts WHERE id = ?");
    mysqli_stmt_bind_param($delStmt, "i", $id);
    mysqli_stmt_execute($delStmt);

    if (mysqli_stmt_affected_rows($delStmt) > 0) {
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
