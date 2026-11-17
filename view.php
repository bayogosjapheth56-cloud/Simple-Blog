<?php
// view.php - SELECT one post (read full content with photo)
require_once "db_connect.php";
require_once "includes/functions.php";

// Load the post together with its category name (JOIN with categories)
$id = (int) ($_GET["id"] ?? 0);
$stmt = mysqli_prepare($conn, POST_SELECT_SQL . " WHERE p.id = ?");
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
    <a href="index.php" class="btn btn-outline-secondary mb-3 text-decoration-none">
        <i class="bi bi-arrow-left me-1"></i> Back to all posts
    </a>
    <h1 class="h2 fw-bold mb-2"><?= htmlspecialchars($post["title"]) ?></h1>
    <div class="d-flex flex-wrap align-items-center gap-2 text-secondary mb-3">
        <span>By <strong class="text-body"><?= htmlspecialchars($post["author"]) ?></strong></span>
        <span>&bull;</span>
        <span><?= date("F d, Y", strtotime($post["created_at"])) ?></span>
        <span>&bull;</span>
        <span
            class="badge badge-category rounded-pill px-3 py-2"><?= htmlspecialchars($post["category"]) ?></span>
        <span
            class="badge <?= $post["status"] === "Published" ? 'bg-success' : 'bg-secondary' ?> rounded-pill px-3 py-2">
            <?= $post["status"] ?>
        </span>
    </div>
</div>

<div class="card shadow-sm border-0 rounded-4 p-4 p-md-5 mb-4">
    <?php if (!empty($post["image"]) && file_exists("uploads/" . $post["image"])): ?>
        <div class="mb-4 text-center">
            <img src="uploads/<?= htmlspecialchars($post["image"]) ?>" alt="<?= htmlspecialchars($post["title"]) ?>"
                class="img-fluid rounded-4 shadow-sm border mb-4"
                style="max-height: 480px; object-fit: cover; width: 100%;">
        </div>
    <?php endif; ?>

    <div class="fs-5 mb-4" style="line-height: 1.8;">
        <?= nl2br(htmlspecialchars($post["content"])) ?>
    </div>
    <hr class="my-4">
    <div class="d-flex gap-2">
        <a href="edit.php?id=<?= $post["id"] ?>" class="btn btn-purple px-4">
            <i class="bi bi-pencil me-1"></i> Edit Post
        </a>
        <button type="button" class="btn btn-danger px-4" data-bs-toggle="modal" data-bs-target="#deleteConfirmModal"
            data-post-id="<?= (int) $post["id"] ?>"
            data-post-title="<?= htmlspecialchars($post["title"], ENT_QUOTES) ?>">
            <i class="bi bi-trash me-1"></i> Delete
        </button>
    </div>
</div>

<?php include "includes/footer.php"; ?>