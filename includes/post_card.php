<?php
// includes/post_card.php - one post card on the Content Board (used for Drafts and Published)
// Expects $post (a row from POST_SELECT_SQL) to be set by the including page.
?>
<div class="card shadow-sm border-0 rounded-4 mb-3 board-card-item"
    data-search="<?= htmlspecialchars(mb_strtolower($post["title"] . " " . $post["author"] . " " . $post["category"])) ?>">
    <div class="card-body p-3">
        <div class="d-flex align-items-center gap-3 mb-3">
            <?php if (!empty($post["image"]) && file_exists("uploads/" . $post["image"])): ?>
                <img src="uploads/<?= htmlspecialchars($post["image"]) ?>" alt="" width="48" height="48"
                    class="rounded-3 object-fit-cover flex-shrink-0">
            <?php else: ?>
                <!-- No photo: show the first letter of the title instead -->
                <div class="card-avatar-purple rounded-3 d-flex align-items-center justify-content-center fw-bold fs-5 flex-shrink-0"
                    style="width: 48px; height: 48px;">
                    <?= htmlspecialchars(mb_strtoupper(mb_substr($post["title"], 0, 1))) ?>
                </div>
            <?php endif; ?>
            <div class="flex-grow-1 overflow-hidden">
                <a href="view.php?id=<?= (int) $post["id"] ?>"
                    class="fw-bold text-body text-decoration-none text-truncate d-block">
                    <?= htmlspecialchars($post["title"]) ?>
                </a>
                <small class="text-secondary">
                    <?= htmlspecialchars($post["author"]) ?> &middot;
                    <?= date("M d, Y", strtotime($post["created_at"])) ?>
                </small>
            </div>
        </div>

        <div class="d-flex align-items-center justify-content-between pt-1">
            <span class="badge badge-category rounded-pill px-3 py-2"><?= htmlspecialchars($post["category"]) ?></span>

            <div class="d-flex align-items-center gap-2">
                <a href="view.php?id=<?= (int) $post["id"] ?>" class="btn-action-view" title="View">
                    <i class="bi bi-eye"></i>
                </a>
                <a href="edit.php?id=<?= (int) $post["id"] ?>" class="btn-action-edit" title="Edit">
                    <i class="bi bi-pencil"></i>
                </a>
                <button type="button" class="btn-action-delete" title="Delete"
                    data-bs-toggle="modal" data-bs-target="#deleteConfirmModal"
                    data-post-id="<?= (int) $post["id"] ?>"
                    data-post-title="<?= htmlspecialchars($post["title"], ENT_QUOTES) ?>">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        </div>
    </div>
</div>
