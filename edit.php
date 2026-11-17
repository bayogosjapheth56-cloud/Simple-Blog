<?php
// edit.php - UPDATE (edit existing post with photo support)
require_once "db_connect.php";
require_once "includes/functions.php";
require_once "includes/upload_helper.php";
$pageTitle = "Edit Post";
$errors = [];

$id = (int) ($_GET["id"] ?? $_POST["id"] ?? 0);

// Load the post together with its category name (JOIN with categories)
$stmt = mysqli_prepare($conn, POST_SELECT_SQL . " WHERE p.id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$post = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$post) {
    header("Location: index.php?msg=" . urlencode("Post not found."));
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = trim($_POST["title"] ?? "");
    $author = trim($_POST["author"] ?? "");
    $category = trim($_POST["category"] ?? "");
    $content = trim($_POST["content"] ?? "");
    $status = $_POST["status"] ?? "Draft";
    $removePhoto = isset($_POST["remove_photo"]) && $_POST["remove_photo"] === "1";

    if (!csrf_valid()) {
        $errors[] = "Your session expired. Please submit the form again.";
    } else {
        $errors = validate_post_fields($title, $author, $category, $content);
    }
    if (!in_array($status, ["Draft", "Published"], true))
        $status = "Draft";

    $oldImage = $post["image"];   // photo currently saved in the database
    $imageName = $oldImage;        // photo that will be saved
    $uploadedNew = null;           // newly uploaded file (deleted again if saving fails)

    // Handle new photo upload only when the form itself is valid
    // (shared validation in includes/upload_helper.php)
    if (empty($errors) && isset($_FILES["image"]) && $_FILES["image"]["error"] !== UPLOAD_ERR_NO_FILE) {
        $uploadedNew = save_uploaded_image($_FILES["image"], $errors);
        if ($uploadedNew) {
            $imageName = $uploadedNew;   // old file is NOT deleted yet
        }
    } elseif ($removePhoto) {
        $imageName = null;               // old file is NOT deleted yet
    }

    // keep what the user typed if there are errors
    $post = array_merge($post, compact("title", "author", "category", "content", "status"));

    if (empty($errors)) {
        // Look up the category in the categories table (created if it is new)
        $categoryId = get_or_create_category($conn, $category);

        $stmt = mysqli_prepare(
            $conn,
            "UPDATE posts SET title=?, author=?, category_id=?, content=?, status=?, image=? WHERE id=?"
        );
        mysqli_stmt_bind_param($stmt, "ssisssi", $title, $author, $categoryId, $content, $status, $imageName, $id);

        if ($categoryId !== null && mysqli_stmt_execute($stmt)) {
            // Update succeeded, so it is now safe to remove the old photo file
            if (!empty($oldImage) && $oldImage !== $imageName && file_exists("uploads/" . $oldImage)) {
                @unlink("uploads/" . $oldImage);
            }
            header("Location: index.php?msg=" . urlencode("Post updated successfully!"));
            exit;
        }

        // Log the details for the developer, show a friendly message to the user
        error_log("Post update failed: " . mysqli_error($conn));
        $errors[] = "The post could not be saved. Please try again.";
    }

    // Something failed: discard the newly uploaded file so no orphan is left behind
    if (!empty($errors) && $uploadedNew && file_exists("uploads/" . $uploadedNew)) {
        @unlink("uploads/" . $uploadedNew);
    }
}

$allCategories = get_all_categories($conn);

include "includes/header.php";
?>

<div class="mb-4">
    <h1 class="h2 fw-bold mb-1">Edit Post</h1>
    <div class="text-secondary small">Modify the details of this blog post</div>
</div>

<?php if ($errors): ?>
    <div class="alert alert-danger mb-4">
        <ul class="mb-0 ps-3">
            <?php foreach ($errors as $e): ?>
                <li><?= htmlspecialchars($e) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<div class="card shadow-sm border-0 rounded-4 p-4">
    <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?= (int) $post["id"] ?>">
        <?= csrf_field() ?>

        <div class="mb-3">
            <label class="form-label fw-bold">Title</label>
            <input type="text" name="title" class="form-control" maxlength="150"
                value="<?= htmlspecialchars($post["title"]) ?>" required>
        </div>

        <div class="row">
            <div class="col-md-4 mb-3">
                <label class="form-label fw-bold">Author</label>
                <input type="text" name="author" class="form-control" maxlength="100"
                    value="<?= htmlspecialchars($post["author"]) ?>" required>
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label fw-bold">Category</label>
                <!-- Pick an existing category or type a new one (saved in the categories table) -->
                <input type="text" name="category" class="form-control" maxlength="50" list="categoryOptions"
                    value="<?= htmlspecialchars($post["category"]) ?>" required>
                <?php include "includes/category_datalist.php"; ?>
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label fw-bold">Status</label>
                <select name="status" class="form-select">
                    <option value="Draft" <?= $post["status"] === "Draft" ? "selected" : "" ?>>Draft</option>
                    <option value="Published" <?= $post["status"] === "Published" ? "selected" : "" ?>>Published</option>
                </select>
            </div>
        </div>

        <!-- Featured Photo Section -->
        <div class="mb-3">
            <label class="form-label fw-bold d-block">Featured Photo</label>

            <?php if (!empty($post["image"]) && file_exists("uploads/" . $post["image"])): ?>
                <div id="currentPhotoContainer" class="mb-3">
                    <div class="mb-2">
                        <img src="uploads/<?= htmlspecialchars($post["image"]) ?>" alt="Current photo"
                            class="img-fluid rounded-3 border shadow-sm" style="max-height: 240px;">
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="remove_photo" value="1" id="removePhotoCheck">
                        <label class="form-check-label text-danger small fw-semibold" for="removePhotoCheck">
                            <i class="bi bi-trash me-1"></i> Delete this photo
                        </label>
                    </div>
                </div>
            <?php endif; ?>

            <div class="d-flex align-items-center flex-wrap gap-3">
                <label for="imageUpload" class="btn-add-photo">
                    <i class="bi bi-image fs-5"></i>
                    <span><?= !empty($post["image"]) ? "Change photo" : "Add photo" ?></span>
                </label>
                <input type="file" name="image" id="imageUpload"
                    accept="image/png, image/jpeg, image/jpg, image/gif, image/webp" class="d-none">
                <span id="fileNameDisplay" class="text-secondary small">No new photo chosen</span>
                <button type="button" id="removePhotoBtn" class="btn btn-sm btn-outline-danger d-none">
                    <i class="bi bi-x-circle me-1"></i> Cancel selection
                </button>
            </div>

            <!-- New Image Preview -->
            <div id="imagePreviewContainer" class="mt-3 d-none">
                <img id="imagePreview" src="#" alt="Preview" class="img-fluid rounded-3 border shadow-sm"
                    style="max-height: 240px;">
            </div>
        </div>

        <div class="mb-4">
            <label class="form-label fw-bold">Content</label>
            <textarea name="content" rows="8" class="form-control"
                required><?= htmlspecialchars($post["content"]) ?></textarea>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-purple px-4">Update Post</button>
            <a href="index.php" class="btn btn-secondary px-4 text-decoration-none">Cancel</a>
        </div>
    </form>
</div>

<!-- Image Preview Script -->
<script>
    (function () {
        const imageUpload = document.getElementById('imageUpload');
        const fileNameDisplay = document.getElementById('fileNameDisplay');
        const previewContainer = document.getElementById('imagePreviewContainer');
        const previewImg = document.getElementById('imagePreview');
        const removeBtn = document.getElementById('removePhotoBtn');

        imageUpload.addEventListener('change', function () {
            const file = this.files && this.files[0];
            if (file) {
                fileNameDisplay.textContent = file.name;
                removeBtn.classList.remove('d-none');

                const reader = new FileReader();
                reader.onload = function (e) {
                    previewImg.src = e.target.result;
                    previewContainer.classList.remove('d-none');
                };
                reader.readAsDataURL(file);
            } else {
                resetPhoto();
            }
        });

        function resetPhoto() {
            imageUpload.value = '';
            fileNameDisplay.textContent = 'No new photo chosen';
            previewContainer.classList.add('d-none');
            previewImg.src = '#';
            removeBtn.classList.add('d-none');
        }

        removeBtn.addEventListener('click', resetPhoto);
    })();
</script>

<?php include "includes/footer.php"; ?>