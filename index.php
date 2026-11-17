<?php
// index.php - Content Board & Table View (Purple UI/UX with Auto-Search & Search Button)
require_once "db_connect.php";
require_once "includes/functions.php";
$pageTitle = "Blogify - Content Board";

$view = $_GET["view"] ?? "board";
$search = trim($_GET["search"] ?? "");
$category = trim($_GET["category"] ?? "");

$activeNav = ($view === "table") ? "table" : "board";
$noContentPadding = ($view === "board");

// Overall stats counters
$statsQuery = mysqli_query($conn, "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'Published' THEN 1 ELSE 0 END) as published,
    SUM(CASE WHEN status = 'Draft' THEN 1 ELSE 0 END) as drafts
    FROM posts");
if (!$statsQuery) {
    // Usually means the tables are missing or outdated: import database.sql
    error_log("Stats query failed: " . mysqli_error($conn));
    http_response_code(500);
    die("Sorry, the site is temporarily unavailable. Please try again later.");
}
$stats = mysqli_fetch_assoc($statsQuery);
$totalAll = (int) ($stats["total"] ?? 0);
$publishedAll = (int) ($stats["published"] ?? 0);
$draftsAll = (int) ($stats["drafts"] ?? 0);

// Fetch categories that have posts, with post counts, for the filter pills
$catResult = mysqli_query($conn, "SELECT c.name AS category, COUNT(p.id) AS count
                                  FROM categories c
                                  JOIN posts p ON p.category_id = c.id
                                  GROUP BY c.id, c.name
                                  ORDER BY c.name ASC");
$allCategories = [];
if ($catResult) {
    while ($c = mysqli_fetch_assoc($catResult)) {
        $allCategories[] = $c;
    }
}

// Build query with search and category filters
$conditions = [];
$params = [];
$types = "";

if ($search !== "") {
    $conditions[] = "(p.title LIKE ? OR p.author LIKE ? OR c.name LIKE ?)";
    $like = "%" . $search . "%";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $types .= "sss";
}

if ($category !== "") {
    $conditions[] = "c.name = ?";
    $params[] = $category;
    $types .= "s";
}

// Posts joined with their category name (see POST_SELECT_SQL in includes/functions.php)
$querySql = POST_SELECT_SQL;
if (!empty($conditions)) {
    $querySql .= " WHERE " . implode(" AND ", $conditions);
}
$querySql .= " ORDER BY p.created_at DESC";

if (!empty($params)) {
    $stmt = mysqli_prepare($conn, $querySql);
    $result = false;
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
    }
} else {
    $result = mysqli_query($conn, $querySql);
}

if (!$result) {
    error_log("Posts query failed: " . mysqli_error($conn));
    http_response_code(500);
    die("Sorry, the site is temporarily unavailable. Please try again later.");
}

$draftPosts = [];
$publishedPosts = [];
$allTablePosts = [];

while ($row = mysqli_fetch_assoc($result)) {
    $allTablePosts[] = $row;
    if ($row["status"] === "Published") {
        $publishedPosts[] = $row;
    } else {
        $draftPosts[] = $row;
    }
}

include "includes/header.php";
?>

<?php if ($view === "board"): ?>
    <!-- ==========================================
         CONTENT BOARD VIEW (Purple UI/UX)
         ========================================== -->

    <!-- Deep Purple Hero Header (Responsive on Mobile & Tablet) -->
    <div class="bg-purple-header text-white px-3 py-4 px-md-5 py-md-4 border-bottom border-white border-opacity-10">
        <div class="text-uppercase small fw-bold mb-1" style="color: #a491d3; letter-spacing: 0.08em;">
            CONTENT BOARD
        </div>
        <h1 class="h2 fw-bold text-white mb-3">Your posts, organised</h1>

        <div class="d-flex flex-column flex-lg-row align-items-start align-items-lg-center justify-content-between gap-3">
            <!-- Stats Counters -->
            <div class="d-flex align-items-baseline gap-3 gap-sm-4">
                <div class="d-flex align-items-baseline gap-1">
                    <span class="fs-4 fw-bold"><?= $totalAll ?></span>
                    <span class="text-white text-opacity-75 small">total</span>
                </div>
                <div class="d-flex align-items-baseline gap-1">
                    <span class="fs-4 fw-bold" style="color: #4ade80;"><?= $publishedAll ?></span>
                    <span class="text-white text-opacity-75 small">published</span>
                </div>
                <div class="d-flex align-items-baseline gap-1">
                    <span class="fs-4 fw-bold" style="color: #c4b5fd;"><?= $draftsAll ?></span>
                    <span class="text-white text-opacity-75 small">drafts</span>
                </div>
            </div>

            <!-- Search Bar with Search Button & Auto-Search (Responsive on Mobile & Tablet) -->
            <div
                class="d-flex flex-wrap flex-sm-nowrap align-items-center gap-2 w-100 w-lg-auto justify-content-between justify-content-lg-end">
                <form method="get" action="index.php" class="d-flex flex-grow-1 flex-sm-grow-0" id="boardSearchForm">
                    <input type="hidden" name="view" value="board">
                    <?php if ($category !== ""): ?>
                        <input type="hidden" name="category" value="<?= htmlspecialchars($category) ?>">
                    <?php endif; ?>
                    <div class="input-group" style="min-width: 220px; max-width: 320px;">
                        <span class="input-group-text border-0 text-white" style="background: rgba(255, 255, 255, 0.15);">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" name="search" id="boardSearchInput" class="form-control text-white border-0"
                            style="background: rgba(255, 255, 255, 0.15);" placeholder="Search posts"
                            value="<?= htmlspecialchars($search) ?>" autocomplete="off">
                        <button class="btn btn-purple px-3 fw-semibold border border-start-0 border-white border-opacity-25"
                            type="submit" id="searchBtnBoard">
                            Search
                        </button>
                    </div>
                </form>
                <a href="create.php" class="btn btn-light fw-bold text-nowrap rounded-3"
                    style="color: var(--primary-purple);">+ New
                    Post</a>
            </div>
        </div>

        <!-- Category Filter Pills -->
        <div class="d-flex flex-wrap align-items-center gap-2 mt-3">
            <a href="index.php?view=board<?= $search !== '' ? '&search=' . urlencode($search) : '' ?>"
                class="btn btn-sm <?= $category === '' ? 'btn-purple' : 'btn-light' ?> rounded-pill fw-semibold px-3 py-1">
                All categories
            </a>
            <?php foreach ($allCategories as $cat): ?>
                <a href="index.php?view=board&category=<?= urlencode($cat["category"]) ?><?= $search !== '' ? '&search=' . urlencode($search) : '' ?>"
                    class="btn btn-sm <?= $category === $cat["category"] ? 'btn-purple' : 'btn-light' ?> rounded-pill fw-semibold px-3 py-1 text-dark">
                    <?= htmlspecialchars($cat["category"]) ?> &middot; <?= $cat["count"] ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Board Columns (Drafts & Published) -->
    <div class="p-3 p-md-4 p-lg-5 bg-body-tertiary">
        <?php if (isset($_GET["msg"])): ?>
            <div class="alert alert-success alert-dismissible fade show d-flex align-items-center justify-content-between mb-4"
                role="alert">
                <span><?= htmlspecialchars($_GET["msg"]) ?></span>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if ($category !== "" || $search !== ""): ?>
            <div class="d-flex align-items-center justify-content-between mb-3 text-secondary small">
                <div>
                    Filtering:
                    <?php if ($category !== ""): ?>
                        <span class="badge badge-category rounded-pill me-2"><?= htmlspecialchars($category) ?></span>
                    <?php endif; ?>
                    <?php if ($search !== ""): ?>
                        <span id="activeSearchQueryLabel">matching "<?= htmlspecialchars($search) ?>"</span>
                    <?php endif; ?>
                </div>
                <a href="index.php?view=board" class="text-danger text-decoration-none">
                    <i class="bi bi-x-circle me-1"></i> Clear filters
                </a>
            </div>
        <?php endif; ?>

        <div class="row g-4">
            <!-- Drafts Column -->
            <div class="col-lg-6">
                <div class="p-3 p-sm-4 board-col-drafts rounded-4 h-100" id="draftsColumnContainer">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0 fw-bold d-flex align-items-center gap-2">
                            <i class="bi bi-circle-fill" style="font-size: 0.6rem; color: #7b748f;"></i> Drafts
                        </h5>
                        <span class="badge bg-body text-body shadow-sm rounded-pill px-3 py-2 fw-bold"
                            id="draftsCountBadge"><?= count($draftPosts) ?></span>
                    </div>

                    <div id="draftsCardsList">
                        <?php if (!empty($draftPosts)): ?>
                            <?php foreach ($draftPosts as $post): ?>
                                <?php include "includes/post_card.php"; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <!-- No Match Feedback for Drafts -->
                    <div id="draftsNoMatch"
                        class="text-center text-secondary py-5 bg-body rounded-4 shadow-sm <?= empty($draftPosts) ? '' : 'd-none' ?>">
                        <i class="bi bi-file-earmark-text fs-2 d-block mb-2"></i>
                        No drafts found
                    </div>
                </div>
            </div>

            <!-- Published Column -->
            <div class="col-lg-6">
                <div class="p-3 p-sm-4 board-col-published rounded-4 h-100" id="publishedColumnContainer">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0 fw-bold d-flex align-items-center gap-2" style="color: #17753e;">
                            <i class="bi bi-circle-fill text-success" style="font-size: 0.6rem;"></i> Published
                        </h5>
                        <span class="badge bg-body text-body shadow-sm rounded-pill px-3 py-2 fw-bold"
                            id="publishedCountBadge"><?= count($publishedPosts) ?></span>
                    </div>

                    <div id="publishedCardsList">
                        <?php if (!empty($publishedPosts)): ?>
                            <?php foreach ($publishedPosts as $post): ?>
                                <?php include "includes/post_card.php"; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <!-- No Match Feedback for Published -->
                    <div id="publishedNoMatch"
                        class="text-center text-secondary py-5 bg-body rounded-4 shadow-sm <?= empty($publishedPosts) ? '' : 'd-none' ?>">
                        <i class="bi bi-send fs-2 d-block mb-2 text-success"></i>
                        No published posts found
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php else: ?>
    <!-- ==========================================
         ALL POSTS TABLE VIEW (No Number Column List)
         ========================================== -->
    <div class="p-3 p-md-4 p-lg-5">
        <?php if (isset($_GET["msg"])): ?>
            <div class="alert alert-success alert-dismissible fade show d-flex align-items-center justify-content-between mb-4"
                role="alert">
                <span><?= htmlspecialchars($_GET["msg"]) ?></span>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="d-flex flex-wrap justify-content-between align-items-end mb-4 gap-3">
            <div>
                <h1 class="h2 fw-bold mb-1">
                    <?php if ($category !== ""): ?>
                        Posts in "<?= htmlspecialchars($category) ?>"
                    <?php else: ?>
                        All Posts
                    <?php endif; ?>
                </h1>
                <div class="text-secondary small">
                    <span id="tableTotalCount"><?= count($allTablePosts) ?></span> posts total
                    <?php if ($category !== ""): ?>
                        &bull; filtered by category
                        <a href="index.php?view=table<?= $search !== '' ? '&search=' . urlencode($search) : '' ?>"
                            class="text-danger small ms-1 text-decoration-none">
                            <i class="bi bi-x-circle"></i> Clear
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <div class="d-flex align-items-center flex-wrap gap-2">
                <!-- Table Search with Search Button & Auto-Search -->
                <form class="d-flex" method="get" action="index.php" id="tableSearchForm">
                    <input type="hidden" name="view" value="table">
                    <?php if ($category !== ""): ?>
                        <input type="hidden" name="category" value="<?= htmlspecialchars($category) ?>">
                    <?php endif; ?>
                    <div class="input-group" style="max-width: 320px;">
                        <input type="text" name="search" id="tableSearchInput" class="form-control"
                            placeholder="Search posts" value="<?= htmlspecialchars($search) ?>" autocomplete="off">
                        <button class="btn btn-purple px-3 fw-semibold" type="submit">Search</button>
                    </div>
                </form>

                <a href="create.php" class="btn btn-purple text-nowrap">+ New Post</a>
            </div>
        </div>

        <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle mb-0" id="postsTable">
                    <thead class="bg-purple-header text-white">
                        <tr>
                            <!-- Removed number list column (#) -->
                            <th class="ps-4">TITLE</th>
                            <th>AUTHOR</th>
                            <th>CATEGORY</th>
                            <th>STATUS</th>
                            <th>DATE</th>
                            <th class="text-end pe-4" style="min-width: 170px;">ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody">
                        <?php if (!empty($allTablePosts)): ?>
                            <?php foreach ($allTablePosts as $row): ?>
                                <tr class="table-row-item"
                                    data-search="<?= htmlspecialchars(mb_strtolower($row["title"] . " " . $row["author"] . " " . $row["category"] . " " . $row["status"])) ?>">
                                    <td class="fw-bold ps-4">
                                        <div class="d-flex align-items-center">
                                            <?php if (!empty($row["image"]) && file_exists("uploads/" . $row["image"])): ?>
                                                <img src="uploads/<?= htmlspecialchars($row["image"]) ?>" alt="" width="40" height="40"
                                                    class="rounded-2 object-fit-cover me-2">
                                            <?php endif; ?>
                                            <a href="view.php?id=<?= $row["id"] ?>" class="text-decoration-none text-body">
                                                <?= htmlspecialchars($row["title"]) ?>
                                            </a>
                                        </div>
                                    </td>
                                    <td><?= htmlspecialchars($row["author"]) ?></td>
                                    <td>
                                        <span
                                            class="badge badge-category rounded-pill px-3 py-2"><?= htmlspecialchars($row["category"]) ?></span>
                                    </td>
                                    <td>
                                        <span
                                            class="badge <?= $row["status"] === "Published" ? 'bg-success' : 'bg-secondary' ?> rounded-pill px-3 py-2">
                                            <?= $row["status"] ?>
                                        </span>
                                    </td>
                                    <td class="text-secondary small">
                                        <div><?= date("M d,", strtotime($row["created_at"])) ?></div>
                                        <div><?= date("Y", strtotime($row["created_at"])) ?></div>
                                    </td>
                                    <td class="text-end pe-4 text-nowrap">
                                        <div class="btn-group btn-group-sm">
                                            <a href="view.php?id=<?= $row["id"] ?>" class="btn btn-outline-purple">View</a>
                                            <a href="edit.php?id=<?= $row["id"] ?>" class="btn btn-outline-secondary">Edit</a>
                                            <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal"
                                                data-bs-target="#deleteConfirmModal"
                                                data-post-id="<?= (int) $row["id"] ?>"
                                                data-post-title="<?= htmlspecialchars($row["title"], ENT_QUOTES) ?>">Delete</button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr id="tableInitialEmpty">
                                <td colspan="6" class="text-center text-secondary py-5">
                                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                    No posts found.
                                </td>
                            </tr>
                        <?php endif; ?>
                        <tr id="tableLiveNoMatch" class="d-none">
                            <td colspan="6" class="text-center text-secondary py-5">
                                <i class="bi bi-search fs-2 d-block mb-2"></i>
                                No posts match your search query.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- ========================================================
     AUTO-SEARCH SCRIPT (Live Instant Search As You Type)
     ======================================================== -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Shows only the items whose data-search text contains the query;
        // returns how many items are still visible.
        function filterItems(items, query) {
            let visible = 0;
            items.forEach(item => {
                const match = (item.dataset.search || '').includes(query);
                item.style.display = match ? '' : 'none';
                if (match) visible++;
            });
            return visible;
        }

        // Keeps ?search= in the address bar in sync without reloading the page
        function updateSearchParam(query) {
            const url = new URL(window.location);
            if (query) {
                url.searchParams.set('search', query);
            } else {
                url.searchParams.delete('search');
            }
            window.history.replaceState({}, '', url);
        }

        // 1. Board View Live Search (Drafts + Published columns)
        const boardSearchInput = document.getElementById('boardSearchInput');
        if (boardSearchInput) {
            const columns = [
                { items: document.querySelectorAll('#draftsCardsList .board-card-item'),
                  badge: document.getElementById('draftsCountBadge'),
                  empty: document.getElementById('draftsNoMatch') },
                { items: document.querySelectorAll('#publishedCardsList .board-card-item'),
                  badge: document.getElementById('publishedCountBadge'),
                  empty: document.getElementById('publishedNoMatch') }
            ];

            boardSearchInput.addEventListener('input', function () {
                const query = this.value.trim().toLowerCase();
                columns.forEach(col => {
                    const visible = filterItems(col.items, query);
                    if (col.badge) col.badge.textContent = visible;
                    if (col.empty) col.empty.classList.toggle('d-none', visible > 0);
                });
                updateSearchParam(query);
            });
        }

        // 2. Table View Live Search
        const tableSearchInput = document.getElementById('tableSearchInput');
        if (tableSearchInput) {
            const tableRows = document.querySelectorAll('.table-row-item');
            const tableNoMatch = document.getElementById('tableLiveNoMatch');
            const tableTotalCount = document.getElementById('tableTotalCount');

            tableSearchInput.addEventListener('input', function () {
                const query = this.value.trim().toLowerCase();
                const visible = filterItems(tableRows, query);
                if (tableTotalCount) tableTotalCount.textContent = visible;
                if (tableNoMatch) tableNoMatch.classList.toggle('d-none', visible > 0 || tableRows.length === 0);
                updateSearchParam(query);
            });
        }
    });
</script>

<?php include "includes/footer.php"; ?>