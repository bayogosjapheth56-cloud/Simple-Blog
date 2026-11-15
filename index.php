<?php
// index.php - SELECT (view all posts) + search + DELETE button
require_once "db_connect.php";
$pageTitle = "All Posts";

$search = trim($_GET["search"] ?? "");

if ($search !== "") {
    $like = "%" . $search . "%";
    $stmt = mysqli_prepare($conn,
        "SELECT * FROM posts WHERE title LIKE ? OR author LIKE ? OR category LIKE ? ORDER BY created_at DESC");
    mysqli_stmt_bind_param($stmt, "sss", $like, $like, $like);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
} else {
    $result = mysqli_query($conn, "SELECT * FROM posts ORDER BY created_at DESC");
}

$totalPosts = mysqli_num_rows($result);

include "includes/header.php";
?>

<?php if (isset($_GET["msg"])): ?>
    <div class="alert alert-custom-success alert-dismissible fade show d-flex align-items-center justify-content-between mb-4" role="alert">
        <span><?= htmlspecialchars($_GET["msg"]) ?></span>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="d-flex flex-wrap justify-content-between align-items-end mb-4 gap-3">
    <div>
        <h1 class="page-heading mb-1">Blog Posts</h1>
        <div class="post-count-subtitle"><?= $totalPosts ?> <?= $totalPosts === 1 ? 'post' : 'posts' ?> total</div>
    </div>
    <div class="d-flex align-items-center gap-2">
        <form class="d-flex" method="get" action="index.php">
            <div class="input-group search-input-group">
                <input type="text" name="search" class="form-control"
                       placeholder="Search posts"
                       value="<?= htmlspecialchars($search) ?>">
                <button class="btn btn-outline-purple" type="submit">Search</button>
            </div>
        </form>
        <a href="create.php" class="btn btn-purple text-nowrap">+ New Post</a>
    </div>
</div>

<div class="table-card">
    <div class="table-responsive">
        <table class="table custom-table mb-0">
            <thead>
                <tr>
                    <th style="width: 50px;">#</th>
                    <th>TITLE</th>
                    <th>AUTHOR</th>
                    <th>CATEGORY</th>
                    <th>STATUS</th>
                    <th>DATE</th>
                    <th class="text-end pe-4" style="min-width: 190px;">ACTIONS</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($totalPosts > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td class="td-id"><?= $row["id"] ?></td>
                    <td class="td-title">
                        <div class="d-flex align-items-center">
                            <?php if (!empty($row["image"]) && file_exists("uploads/" . $row["image"])): ?>
                                <img src="uploads/<?= htmlspecialchars($row["image"]) ?>" alt="" class="table-post-thumb">
                            <?php endif; ?>
                            <span><?= htmlspecialchars($row["title"]) ?></span>
                        </div>
                    </td>
                    <td class="td-author"><?= htmlspecialchars($row["author"]) ?></td>
                    <td>
                        <span class="badge badge-category"><?= htmlspecialchars($row["category"]) ?></span>
                    </td>
                    <td>
                        <span class="badge <?= $row["status"] === "Published" ? 'badge-published' : 'badge-draft' ?>">
                            <?= $row["status"] ?>
                        </span>
                    </td>
                    <td class="td-date">
                        <div><?= date("M d,", strtotime($row["created_at"])) ?></div>
                        <div><?= date("Y", strtotime($row["created_at"])) ?></div>
                    </td>
                    <td class="text-end pe-4 text-nowrap">
                        <a href="view.php?id=<?= $row["id"] ?>" class="btn btn-action-view">View</a>
                        <a href="edit.php?id=<?= $row["id"] ?>" class="btn btn-action-edit">Edit</a>
                        <a href="delete.php?id=<?= $row["id"] ?>" class="btn btn-action-delete"
                           onclick="return confirm('Delete this post?');">Delete</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" class="text-center text-muted py-5">
                        No posts found.
                    </td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include "includes/footer.php"; ?>
