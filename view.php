<?php
// view.php - SELECT one post (read full content with photo)
require_once "db_connect.php";

$id = (int) ($_GET["id"] ?? 0);
$stmt = mysqli_prepare($conn, "SELECT * FROM posts WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$post = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$post) {
    header("Location: index.php?msg=" . urlencode("Post not found."));
    exit;
}

$pageTitle = $post["title"];
include "includes/header.php";
?>

<div class="mb-4">
    <a href="index.php" class="btn btn-action-edit mb-3 text-decoration-none">
        <i class="bi bi-arrow-left me-1"></i> Back to all posts
    </a>
    <h1 class="page-heading mb-2"><?= htmlspecialchars($post["title"]) ?></h1>
    <div class="d-flex flex-wrap align-items-center gap-2 text-secondary mb-3">
        <span>By <strong style="color: var(--text-title);"><?= htmlspecialchars($post["author"]) ?></strong></span>
        <span>&bull;</span>
        <span><?= date("F d, Y", strtotime($post["created_at"])) ?></span>
        <span>&bull;</span>
        <span class="badge badge-category"><?= htmlspecialchars($post["category"]) ?></span>
        <span class="badge <?= $post["status"] === "Published" ? 'badge-published' : 'badge-draft' ?>">
            <?= $post["status"] ?>
        </span>
    </div>
</div>

<div class="table-card p-4 p-md-5 mb-4">
    <?php if (!empty($post["image"]) && file_exists("uploads/" . $post["image"])): ?>
        <div class="mb-4 text-center">
            <img src="uploads/<?= htmlspecialchars($post["image"]) ?>" alt="<?= htmlspecialchars($post["title"]) ?>"
                class="post-featured-image img-fluid">
        </div>
    <?php endif; ?>

    <div class="view-post-content mb-4">
        <?= nl2br(htmlspecialchars($post["content"])) ?>
    </div>
    <hr class="my-4" style="border-color: var(--table-border);">
    <div class="d-flex gap-2">
        <a href="edit.php?id=<?= $post["id"] ?>" class="btn btn-purple">
            <i class="bi bi-pencil me-1"></i> Edit Post
        </a>
        <a href="delete.php?id=<?= $post["id"] ?>" class="btn btn-action-delete text-decoration-none"
            onclick="return confirm('Are you sure you want to delete this post?');">
            <i class="bi bi-trash me-1"></i> Delete
        </a>
    </div>
</div>

<?php include "includes/footer.php"; ?>